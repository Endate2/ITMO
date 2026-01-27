package CommandProvider.Commands

import Products.Product
import java.util.*
import CommandProvider.CommandManager
/**
 * Объект, представляющий команду для обновления продукта в коллекции по заданному ключу.
 */
object Updateid {

    /**
     * Обновляет продукт в коллекции по заданному ключу.
     *
     * @param collection Коллекция объектов типа TreeMap, где ключ типа Int и значение типа Product.
     * @param key Ключ продукта, который необходимо обновить в коллекции.
     */
    fun UpdateCommand(collection: TreeMap<Int, Product>, key: Int,commandManager: CommandManager) {
        try {
            if (collection.containsKey(key)) {
                val sp = Product() // Создайте и настройте новый объект Product
                collection[key] = sp
                println("Элемент был успешно обновлен!")
            } else {
                println("Элемента с ID $key не существует.")
            }
        } catch (e: Exception) {
            println("Произошла ошибка при обновлении: ${e.message}")
            // В зависимости от того, как критична ошибка, можно добавить дополнительную логику
        }
        CommandManager.setAllProcessesCompleted()
    }
}

