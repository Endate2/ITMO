package CommandProvider.Commands

import Products.Product
import java.util.*
import Products.UnitOfMeasure
import CommandProvider.CommandManager
/**
 * Объект `FilterUnit` предоставляет функциональность для фильтрации коллекции продуктов по единице измерения.
 *
 * Этот объект содержит метод `filterByUnitOfMeasure`, который фильтрует продукты в коллекции,
 * выводя только те, у которых единица измерения соответствует заданному значению.
 *
 * @author Endatee
 * @since 1.0
 */
object FilterUnit {
    /**
     * Фильтрует коллекцию продуктов по единице измерения.
     *
     * Метод принимает коллекцию продуктов и значение `unitOfMeasure`, и выводит все продукты,
     * у которых единица измерения совпадает с указанным значением. Если коллекция пуста,
     * выводится соответствующее сообщение. Если ни один продукт не соответствует критерию,
     * выводится сообщение об отсутствии подходящих элементов.
     *
     * @param collection Коллекция продуктов, представленная в виде `TreeMap<Int, Product>`.
     *                   Ключом является целое число, а значением — объект типа `Product`.
     * @param unitOfMeasure Единица измерения, по которой производится фильтрация.
     *                      Если `null`, фильтрация не выполняется.
     *
     * @see Product
     * @see UnitOfMeasure
     * @see TreeMap
     */
    fun filterByUnitOfMeasure(collection: TreeMap<Int, Product>, unitOfMeasure: UnitOfMeasure?,commandManager: CommandManager) {
        if (collection.isEmpty()) {
            println("Коллекция пуста.")
            CommandManager.setAllProcessesCompleted()
            return
        }

        var found = false
        for (product in collection.values) {
            if (unitOfMeasure == product.unitOfMeasure) {
                println(product)
                found = true
            }
        }
        CommandManager.setAllProcessesCompleted()
        if (!found) {
            println("Нет элементов с заданным значением поля unitOfMeasure.")
            CommandManager.setAllProcessesCompleted()
        }
    }
}
