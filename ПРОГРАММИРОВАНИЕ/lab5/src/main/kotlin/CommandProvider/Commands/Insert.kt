package CommandProvider.Commands

import Products.Product
import java.util.*
import CommandProvider.CommandManager
/**
 * Объект, представляющий команду для вставки нового продукта в коллекцию.
 */
object Insert {

    /**
     * Команда для вставки нового продукта в коллекцию по заданному ключу.
     *
     * @param collection Коллекция объектов типа TreeMap, где ключ типа Int и значение типа Product.
     * @param key Ключ, по которому будет вставлен новый объект Product в коллекцию.
     */
    fun InsertCommand(collection: TreeMap<Int, Product>, key: Int,commandManager: CommandManager) {
        val sp = Product()
        collection[key] = sp
        CommandManager.setAllProcessesCompleted()
    }
}

