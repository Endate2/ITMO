package CommandProvider.Commands

import Products.Product
import java.util.*
import CommandProvider.CommandManager

/**
 * Объект, представляющий команду для удаления продуктов с ключами, меньшими заданного, в коллекции.
 */
object SortByElement {

    /**
     * Удаляет из коллекции все продукты, ключ которых меньше заданного.
     *
     * @param collection Коллекция объектов типа TreeMap, где ключ типа Int и значение типа Product.
     * @param key Заданный ключ, все элементы с ключом меньше которого будут удалены из коллекции.
     */
    fun removeLowerByKey(collection: TreeMap<Int, Product>, key: Int,commandManager: CommandManager) {
        if (collection.isEmpty()) {
            println("Коллекция пуста.")
            return
        }

        val lowerElements = TreeMap(collection.headMap(key, false))

        if (lowerElements.isEmpty()) {
            println("Нет элементов меньше заданного ключа.")
        } else {
            collection.headMap(key, false).clear()
            println("Удалены элементы с ключом меньше $key")
        }
        CommandManager.setAllProcessesCompleted()
    }
}
