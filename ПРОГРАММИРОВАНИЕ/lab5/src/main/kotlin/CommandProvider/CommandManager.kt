package CommandProvider

import CommandProvider.Commands.*
import CommandProvider.Commands.ReplaceIfLower.readInt
import Data.DataProvider
import Products.JustScanner
import Products.JustScanner.Scan
import Products.Product
import Products.UnitOfMeasure
import java.util.*

/**
 * Класс CommandManager управляет выполнением команд, введенных пользователем.
 * Он взаимодействует с коллекцией продуктов и выполняет различные операции в зависимости от введенной команды.
 */
class CommandManager {
    private val dp = DataProvider() // Провайдер данных
    private val collection = TreeMap<Int, Product>() // Коллекция продуктов, отсортированная по ключу

    fun getDataProvider(): DataProvider {
        return dp
    }

    // Геттер для коллекции
    fun getCollection(): TreeMap<Int, Product> {
        return collection
    }

    companion object {
        var allProcessesCompleted: Int = 0

        fun setAllProcessesCompleted() {
            allProcessesCompleted = 1
        }

        fun setAllProcessesNotCompleted(){
            allProcessesCompleted = 0
        }

        fun getAllProcessesCompleted(): String {
            return allProcessesCompleted.toString() // Преобразуем Int в String
        }
    }

    // Инициализация хендлеров
    private val commandHandlers = mapOf<String, CommandHandler>(
        "help" to HelpCommandHandler(),
        "insert" to InsertCommandHandler(),
        "updateid" to UpdateidCommandHandler(),
        "show" to ShowCommandHandler(),
        "remove" to RemoveCommandHandler(),
        "clear" to ClearCommandHandler(),
        "info" to InfoCommandHandler(),
        "remove_greater" to RemoveGreaterCommandHandler(),
        "remove_lower" to RemoveLowerCommandHandler(),
        "exit" to ExitCommandHandler(),
        "filterByUnitOfMeasure" to FilterUnitCommandHandler(),
        "filterGreaterThanPrice" to FilterPriceCommandHandler(),
        "groupByCreationDate" to GroupByCreationDateCommandHandler(),
        "save" to SaveCommandHandler(),
        "replaceIfLower" to ReplaceIfLowerCommandHandler(),
        "ExecuteScriptCommand" to ExecuteScriptCommandHandler()
    )

    /**
     * Метод CommandChecker анализирует введенную пользователем строку и выполняет соответствующую команду.
     *
     * @param userString Введенная пользователем строка, содержащая команду и, возможно, дополнительные аргументы.
     */
    fun CommandChecker(userString: String) {
        val trimmedString = userString.trim()
        if (trimmedString.isEmpty()) {
            println("Ошибка: Введена пустая строка. Пожалуйста, введите команду.")
            return
        }

        val commandToWords = trimmedString.split("\\s+".toRegex()).toTypedArray()
        val command = commandToWords[0]

        val handler = commandHandlers[command]
        if (handler != null) {
            handler.handle(this)
        } else {
            println("Ошибка: Неизвестная команда '$command'. Введите 'help' для списка команд.")
        }
    }
}