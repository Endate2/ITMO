package CommandProvider.Commands

import Products.Product
import java.util.*
import CommandProvider.CommandManager
import CommandProvider.OutputManager

/**
 * Объект, представляющий команду для отображения содержимого коллекции продуктов.
 */
object Show {

    /**
     * Выводит все элементы коллекции продуктов с использованием OutputManager.
     *
     * @param collection Коллекция объектов типа TreeMap, где ключ типа Int и значение типа Product.
     * @param commandManager Менеджер команд для управления состоянием выполнения.
     */
    fun ShowCommand(collection: TreeMap<Int, Product>, commandManager: CommandManager) {
        if (collection.isEmpty()) {
            OutputManager.println("Коллекция пуста.")
        } else {
            OutputManager.printSeparator(30, '-')
            OutputManager.println("Содержимое коллекции:")
            OutputManager.printSeparator(30, '-')

            collection.forEach { (key, product) ->
                OutputManager.println("Ключ: $key")
                OutputManager.println("Продукт:")
                OutputManager.println(product.toString()) // Предполагается, что Product имеет переопределенный toString()
                OutputManager.printSeparator(20, '.')
            }

            OutputManager.println("Всего элементов: ${collection.size}")
        }

        CommandManager.setAllProcessesCompleted()
    }
}