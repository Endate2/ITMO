package commands

import basicClasses.*
import collection.CollectionManager
import exceptions.InvalidArgumentException
import serverUtils.*
import utils.*
import java.nio.channels.SocketChannel


class CommandReceiver(private val collectionManager: CollectionManager,
                      private val connectionManager: ConnectionManager
) {

    private val jsonCreator = JsonWorker()


    fun info(channel: SocketChannel) {
        try {
            val answer = Answer(AnswerType.OK, collectionManager.getInfo())
            connectionManager.send(answer, channel)
        } catch (e: Exception) {
            val answer = Answer(AnswerType.ERROR, e.message.toString())
            connectionManager.send(answer, channel)
        }
    }


    fun show(channel: SocketChannel) {
        try {
            val answer = Answer(AnswerType.OK, collectionManager.show().joinToString("\n"))
            connectionManager.send(answer, channel)
        } catch (e: Exception) {
            val answer = Answer(AnswerType.ERROR, e.message.toString())
            connectionManager.send(answer, channel)
        }
    }


    fun add(args: Map<String, String>,channel: SocketChannel) {
        try {
            val product = jsonCreator.stringToObject<Product>(args["product"]!!)
            collectionManager.add(product)
            val answer = Answer(AnswerType.OK, "Product ${product.getName()} был создан и добавлен в коллекцию")
            connectionManager.send(answer, channel)
        } catch (e: Exception) {
            val answer = Answer(AnswerType.ERROR, e.message.toString())
            connectionManager.send(answer, channel)
        }
    }

    fun addRandom(args: Map<String, String>,channel: SocketChannel) {
        try {
            val quantity = args["quantity"]?.toIntOrNull() ?: throw InvalidArgumentException("Неверное количество элементов")
            var count = 0
            repeat(quantity) {
                Thread.sleep((10..100).random().toLong())
                val coordinates = Coordinates(x = 12, y = 12.0)


                val organization = Organization(
                    name = "djknsknds",
                    employeesCount = 12,
                    type = OrganizationType.COMMERCIAL
                )

                val product = Product(
                    name = "sdishidsds",
                    coordinates = coordinates,
                    price = 12,
                    unitOfMeasure = UnitOfMeasure.MILLIGRAMS,
                    manufacturer = organization
                )
                collectionManager.add(product)
                count++
            }
            val answer = Answer(AnswerType.OK, "Добавлено $count элементов")
            connectionManager.send(answer, channel)
        } catch (e: Exception) {
            val answer = Answer(AnswerType.ERROR, e.message.toString())
            connectionManager.send(answer, channel)
        }
    }


    fun updateByID(args: Map<String, String>,channel: SocketChannel) {
        val id = args["id"]!!

        try {
            val oldProduct = collectionManager.getByID(id.toInt())
                ?: throw InvalidArgumentException("Нет продукта с Id: $id")
            val newProduct = jsonCreator.stringToObject<Product>(args["product"]!!)
            collectionManager.update(newProduct, oldProduct)
            val answer = Answer(AnswerType.OK, "Product ${oldProduct.getName()} был обнавлен")
            connectionManager.send(answer, channel)

        } catch (e: Exception) {
            val answer = Answer(AnswerType.ERROR, e.message.toString())
            connectionManager.send(answer, channel)
        }
    }


    fun removeByID(args: Map<String, String>,channel: SocketChannel) {
        val id = args["id"]!!

        try {
            val product= collectionManager.getByID(id.toInt())
                ?: throw InvalidArgumentException("Продукт с id: $id не найден")

            collectionManager.remove(product)
            val answer = Answer(AnswerType.OK, "Продукт ${product.getName()} был удален")
            connectionManager.send(answer, channel)

        } catch (e: Exception) {
            val answer = Answer(AnswerType.ERROR, e.message.toString())
            connectionManager.send(answer, channel)
        }
    }


    fun clear(channel: SocketChannel) {
        if (collectionManager.getCollection().size > 0) {
            try {
                collectionManager.clear()
                val answer = Answer(AnswerType.OK, "Коллекция была очищена")
                connectionManager.send(answer, channel)
            } catch (e: Exception) {
                val answer = Answer(AnswerType.ERROR, e.message.toString())
                connectionManager.send(answer, channel)
            }
        } else {
            val answer = Answer(AnswerType.ERROR, "Коллекция уже пуста")
            connectionManager.send(answer, channel)
        }
    }


    fun save(filepath:String,channel: SocketChannel) {
        try {
            Saver().save(filepath, collectionManager)
            val answer = Answer(AnswerType.OK, "Коллекция была успешно сохранена")
            connectionManager.send(answer, channel)
        } catch (e:Exception) {
            val answer = Answer(AnswerType.ERROR, e.message.toString())
            connectionManager.send(answer, channel)
        }
    }

    fun groupByCreationDate(args: Map<String, String>,channel: SocketChannel) {
        val collection = collectionManager.getCollection()

        if (collection.isEmpty()) {
            val answer = Answer(AnswerType.OK, "Коллекция пуста.")
            connectionManager.send(answer, channel)
            return
        }

        val groupedByDate = collection
            .map { it.value }
            .groupBy { it.getCreationDate() }  // Предполагается, что creationDate есть в Product

        val result = buildString {
            appendLine("Группировка элементов по дате создания:")
            groupedByDate.forEach { (date, products) ->
                appendLine("- $date: ${products.size} элементов")
            }
        }

        val answer = Answer(AnswerType.OK, result)
        connectionManager.send(answer, channel)
    }

    fun replaceLower(args: Map<String, String>,channel: SocketChannel) {
        val id = args["id"] ?: throw InvalidArgumentException("Не указан ID продукта")

        try {
            val oldProduct = collectionManager.getByID(id.toInt())
                ?: throw InvalidArgumentException("Нет продукта с ID: $id")

            val newProduct = jsonCreator.stringToObject<Product>(args["product"]!!)

            if (newProduct < oldProduct) {
                collectionManager.update(newProduct, oldProduct)
                val answer = Answer(AnswerType.OK, "Продукт ${oldProduct.getName()} был обновлён (новое значение меньше)")
                connectionManager.send(answer, channel)
            } else {
                val answer = Answer(AnswerType.ERROR, "Продукт НЕ обновлён: новое значение не меньше старого")
                connectionManager.send(answer, channel)
            }

        } catch (e: NumberFormatException) {
            val answer = Answer(AnswerType.ERROR, "Неверный формат ID: должен быть числом")
            connectionManager.send(answer, channel)
        } catch (e: Exception) {
            val answer = Answer(AnswerType.ERROR, e.message ?: "Неизвестная ошибка при замене продукта")
            connectionManager.send(answer, channel)
        }
    }


    fun removeGreater(args: Map<String, String>,channel: SocketChannel) {

        try {
            val collection = collectionManager.getCollection()
            val product = jsonCreator.stringToObject<Product>(args["product"]!!)
            var count = 0

            if (collection.isNotEmpty()) {
                var lastEntry = collectionManager.getCollection().lastEntry()
                while (lastEntry != null && lastEntry.value > product) {
                    collectionManager.remove(lastEntry.value)
                    count++
                    lastEntry = collectionManager.getCollection().lastEntry()
                }
            }
            val answer = Answer(AnswerType.OK, when (count) {
                0 -> { "Ни один продукт не был удален" }
                1 -> { "Только один продукт был удален" }
                else -> { "$count Столько продуктов было удалено" }
            })
            connectionManager.send(answer, channel)

        } catch (e: Exception) {
            val answer = Answer(AnswerType.ERROR, "ОШИБКА ВЫПОЛНЕНИЯ КОМАНДЫ" )
            connectionManager.send(answer, channel)
        }
    }

    fun removeLower(args: Map<String, String>,channel: SocketChannel) {
        try {
            val collection = collectionManager.getCollection()
            val product = jsonCreator.stringToObject<Product>(args["product"]!!)
            var count = 0

            if (collection.isNotEmpty()) {
                var lastEntry = collectionManager.getCollection().lastEntry()
                while (lastEntry != null && lastEntry.value < product) {
                    collectionManager.remove(lastEntry.value)
                    count++
                    lastEntry = collectionManager.getCollection().lastEntry()
                }
            }
            val answer = Answer(AnswerType.OK, when (count) {
                0 -> { "Ни один продукт не был удален" }
                1 -> { "Только один продукт был удален" }
                else -> { "$count Столько продуктов было удалено" }
            })
            connectionManager.send(answer, channel)

        } catch (e: Exception) {
            val answer = Answer(AnswerType.ERROR, "ОШИБКА ВЫПОЛНЕНИЯ КОМАНДЫ" )
            connectionManager.send(answer, channel)
        }
    }


    fun filterByPrice(args: Map<String, String>,channel: SocketChannel) {
        val price = args["price"]
        val filteredList = mutableListOf<Product>()
        try {

            val collection = collectionManager.getCollection()
            var count = 0

            for (product in collection) {
                if (product.value.getPrice() > price!!.toInt()) {
                    filteredList.add(product.value)
                    count++
                }
            }

            if (filteredList.isEmpty()) {
                throw Exception("Продукт с ценой $price не был найден")
            }

            val answer = Answer(AnswerType.OK, filteredList.joinToString("\n"))
            connectionManager.send(answer, channel)


        } catch (e: Exception) {
            val answer = Answer(AnswerType.ERROR, e.message.toString())
            connectionManager.send(answer, channel)
        }

    }





    fun filterByUnitOfMeasure(args: Map<String, String>,channel: SocketChannel) {
        try {
            val unitOfMeasure = jsonCreator.stringToObject<UnitOfMeasure>(args["unitOfMeasure"]!!)
            val filteredList = mutableListOf<Product>()

            for (i in collectionManager.filter { e -> e.getUnitOfMeasure()!! == unitOfMeasure }) {
                filteredList.add(i)
            }
            if (filteredList.isEmpty()) {
                throw Exception("No Space Marines with $unitOfMeasure were found")
            }

            val answer = Answer(AnswerType.OK, filteredList.joinToString("\n"))
            connectionManager.send(answer, channel)

        } catch (e: Exception) {
            val answer = Answer(AnswerType.ERROR, e.message.toString())
            connectionManager.send(answer, channel)
        }
    }

}
