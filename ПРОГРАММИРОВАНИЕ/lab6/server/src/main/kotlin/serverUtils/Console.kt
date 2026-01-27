package serverUtils

import collection.CollectionManager
import commands.CommandInvoker
import commands.CommandReceiver
import commands.consoleCommands.*
import utils.*
import org.apache.logging.log4j.LogManager
import org.apache.logging.log4j.Logger
import java.nio.channels.SelectionKey
import java.nio.channels.Selector
import java.nio.channels.ServerSocketChannel
import java.nio.channels.SocketChannel
import java.util.concurrent.Executors


class Console {
    private val connectionManager = ConnectionManager()
    private val fileManager = FileManager()
    private val collectionManager = CollectionManager()
    private val commandInvoker = CommandInvoker(connectionManager)
    private val commandReceiver = CommandReceiver(collectionManager, connectionManager)
    private val jsonCreator = JsonWorker()
    private val logger: Logger = LogManager.getLogger(Console::class.java)
    private var executeFlag = true
    private val selector = Selector.open()
    private val executor = Executors.newFixedThreadPool(10) // Пул потоков

    fun start(actions: ConnectionManager.() -> Unit) {
        connectionManager.actions()
    }


    fun initialize() {
        logger.info("Инициализация сервера")
        commandInvoker.register("info", Info(commandReceiver))
        commandInvoker.register("show", Show(commandReceiver))
        commandInvoker.register("add", Add(commandReceiver))
        commandInvoker.register("update_id", Update(commandReceiver))
        commandInvoker.register("remove_by_id", RemoveID(commandReceiver))
        commandInvoker.register("clear", Clear(commandReceiver))
        commandInvoker.register("save", Save(commandReceiver))
        commandInvoker.register("remove_greater", RemoveGreater(commandReceiver))
        commandInvoker.register("remove_lower", RemoveLower(commandReceiver))
        commandInvoker.register("filter_by_unitofmeasure", FilterByUnitOfMeasure(commandReceiver))
        commandInvoker.register("filter_by_creationdate", GroupByCreationDate(commandReceiver))
        commandInvoker.register("filter_by_price", FilterByPrice(commandReceiver))
        commandInvoker.register("replace_lower", ReplaceLower(commandReceiver))
        logger.debug("Все команды сервера были загружены")


        fileManager.load(collectionManager)
        logger.info("Коллекция загружена")

    }


    fun onConnect(clientChannel1: SocketChannel) {
        logger.trace("Получен запрос на инициализацию подключения")

        // Создаем структуру для отправки информации о командах
        val sendingInfo = mutableMapOf<String, MutableMap<String, String>>(
            // Секция с описанием команд
            "commands" to mutableMapOf(),
            // Секция с типами аргументов команд
            "arguments" to mutableMapOf()
        )

        // Получаем карту всех зарегистрированных команд
        val commands = commandInvoker.getCommandMap()

        // Заполняем информацию о каждой команде
        for (command in commands.keys) {
            // Добавляем описание команды
            sendingInfo["commands"]!! += (command to commands[command]!!.getInfo())
            // Добавляем информацию об аргументах команды (сериализованную в JSON)
            sendingInfo["arguments"]!! += (command to jsonCreator.objectToString(commands[command]!!.getArgsTypes()))
        }

        // Формируем ответ для клиента с системной информацией
        val answer = Answer(AnswerType.SYSTEM, jsonCreator.objectToString(sendingInfo))

        // Отправляем информацию о командах новому клиенту
        connectionManager.send(answer, clientChannel1)
    }


    fun startInteractiveMode() {
        logger.info("Сервер готов к приему команд")

        // Регистрируем серверный канал в селекторе для обработки новых подключений
        connectionManager.serverSocketChannel.register(selector, SelectionKey.OP_ACCEPT)

        // Основной цикл работы сервера
        while (executeFlag) {
            // Ожидаем событий (блокирующий вызов, пока не появятся готовые каналы)
            selector.select()

            // Получаем набор ключей с готовыми событиями
            val selectedKeys = selector.selectedKeys()
            val iter = selectedKeys.iterator()

            // Обрабатываем все готовые каналы
            while (iter.hasNext()) {
                val key = iter.next()
                iter.remove() // Удаляем обработанный ключ

                try {
                    when {
                        // Обработка нового подключения
                        key.isAcceptable -> handleAccept(key)

                        // Обработка данных от клиента
                        key.isReadable -> handleRead(key)

                        // Неизвестное событие
                        else -> logger.warn("Неизвестный тип события: $key")
                    }
                } catch (e: Exception) {
                    logger.error("Ошибка обработки ключа: ${e.message}")
                    key.cancel() // Отменяем регистрацию ключа
                    key.channel().close() // Закрываем канал
                }
            }
        }

        // Завершение работы: закрываем все каналы
        selector.keys().forEach { it.channel().close() }
        selector.close() // Закрываем селектор

        // Завершаем работу пула потоков
        executor.shutdown()
        connectionManager.stop()
        logger.info("Сервер закрыт")
    }
    private fun handleAccept(key: SelectionKey) {
        val serverChannel = key.channel() as ServerSocketChannel
        val clientChannel = serverChannel.accept().apply {
            configureBlocking(false)
            register(selector, SelectionKey.OP_READ)
        }
        logger.info("Новый клиент: ${clientChannel.remoteAddress}")
    }

    private fun handleRead(key: SelectionKey) {
        val clientChannel = key.channel() as SocketChannel
        try {
            val query = connectionManager.receive(clientChannel)
            when (query?.queryType) {
                ExecuteType.COMMAND_READ -> {
                    logger.info("Полученная команда: ${query.information}")
                    executor.submit {
                        try {
                            commandInvoker.executeCommand(query, clientChannel)
                        } catch (e: Exception) {
                            logger.error("Не удалось выполнить команду: ${e.message}")
                            key.cancel()
                            clientChannel.close()
                        }
                    }
                }
                ExecuteType.DISCHARGE -> onConnect(clientChannel)
                null -> {
                    logger.warn("Получен нулевой запрос, соединение закрывается")
                    key.cancel()
                    clientChannel.close()
                }
            }
        } catch (e: Exception) {
            logger.error("Чтение для обработки ошибок: ${e.message}")
            key.cancel()
            clientChannel.close()
        }
    }

    fun stop() {
        executeFlag = false
        selector.wakeup()
    }

    fun save() {
        val saver = Saver()
        try {
            saver.save("servercollection", collectionManager)
            logger.info("Коллекция успешно сохранена")
        } catch (e:Exception) {
            logger.warn("Коллекция не была сохранена: ${e.message}")
        }
    }
}