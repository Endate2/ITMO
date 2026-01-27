package CommandProvider.Commands

import Products.Product
import java.util.*
import CommandProvider.CommandManager
/**
 * Объект, представляющий команду для удаления продуктов с ключами, большими заданного.
 */
object RemoveGreater {

    /**
     * Удаляет из коллекции все продукты, ключ которых больше заданного.
     *
     * @param collection Коллекция объектов типа TreeMap, где ключ типа Int и значение типа Product.
     * @param key Заданный ключ, все элементы с ключом больше которого будут удалены из коллекции.
     */
    fun removeGreaterByKey(collection: TreeMap<Int, Product>, key: Int,commandManager: CommandManager) {
        if (collection.isEmpty()) {
            println("Коллекция пуста.")
            return
        }

        val greaterElements = TreeMap(collection.tailMap(key, false))

        if (greaterElements.isEmpty()) {
            println("Нет элементов больше заданного ключа.")
        } else {
            collection.tailMap(key, false).clear()
            println("Удалены элементы с ключом больше $key")
        }
        CommandManager.setAllProcessesCompleted()
    }
}
