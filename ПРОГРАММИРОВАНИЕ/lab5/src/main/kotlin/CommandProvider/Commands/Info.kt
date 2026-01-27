package CommandProvider.Commands

import Products.Product
import java.time.LocalDateTime
import java.util.*
import CommandProvider.CommandManager

/**
 * Объект, представляющий команду для вывода информации о коллекции продуктов.
 */
object Info {

    /**
     * Команда вывода информации о коллекции продуктов.
     *
     * @param collection Коллекция объектов типа TreeMap, где ключ типа Int и значение типа Product.
     * @param commandManager Экземпляр CommandManager для управления флагами.
     */
    fun InfoCommand(collection: TreeMap<Int, Product>, commandManager: CommandManager) {
        val size = collection.size
        println("Размер коллекции: $size")

        if (size > 0) {
            var maxDate: LocalDateTime? = null
            for (sp in collection.values) {
                if (maxDate == null || maxDate.isBefore(sp.date)) {
                    maxDate = sp.date
                }
            }
            println("Дата последнего изменения коллекции: $maxDate")
        } else {
            println("Коллекция пуста, нет даты изменения.")
        }

        // Устанавливаем флаг через статический метод
        CommandManager.setAllProcessesCompleted()

        // Получаем значение флага и печатаем его
        val processesStatus = CommandManager.getAllProcessesCompleted()
        println("Статус процессов: $processesStatus")

        println("Кол-во элементов: $size")
    }
}