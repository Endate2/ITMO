package CommandProvider.Commands

import Products.Product
import java.util.*
import CommandProvider.CommandManager
/**
 * Объект, предоставляющий функциональность для группировки продуктов по дате создания.
 *
 * Этот объект содержит метод для сортировки коллекции продуктов по дате их создания.
 */
object GroupByCreationDate {

    /**
     * Сортирует коллекцию продуктов по дате создания.
     *
     * @param collection Коллекция продуктов, представленная в виде `TreeMap<Int, Product>`,
     * где ключ — это идентификатор продукта, а значение — объект продукта.
     * @return Возвращает отсортированный список продуктов, начиная с самого старого.
     *
     * @see Product
     * @see TreeMap
     */
    fun sortProductsByDate(collection: TreeMap<Int, Product>,commandManager: CommandManager): List<Product> {
        val products = collection.values.toList()
        val sortedProducts = products.sortedBy { it.date }
        println("Отсортированная коллекция по Date:")
        for (product in sortedProducts) {
            println(product)
        }
        CommandManager.setAllProcessesCompleted()
        return sortedProducts
    }
}
