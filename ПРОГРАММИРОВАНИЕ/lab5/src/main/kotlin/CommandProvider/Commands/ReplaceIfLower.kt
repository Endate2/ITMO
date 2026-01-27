package CommandProvider.Commands

import Products.JustScanner.Scan
import Products.Product
import java.util.*
import CommandProvider.CommandManager
/**
 * Объект, представляющий команду для замены значения в коллекции, если новое значение меньше существующего.
 */
object ReplaceIfLower {

    /**
     * Заменяет свойство продукта в коллекции, если новое значение меньше существующего.
     *
     * @param collection Коллекция объектов типа TreeMap, где ключ типа Int и значение типа Product.
     * @param type Тип замены: 1 для изменения цены продукта, 2 для изменения количества сотрудников организации.
     */
    fun replaceIfLower(collection: TreeMap<Int, Product>, type: Int,key: Int,new:Int,commandManager: CommandManager) {

        when (type) {
            1 -> {
                val product = collection[key]
                if (product != null) {
                    val existingPrice = product.price
                    if (new < existingPrice) {
                        product.inputPrice(new)
                        println("Элемент с ID $key был заменен, так как новая цена ($new) меньше текущей ($existingPrice).")
                    } else {
                        println("Новая цена ($new) не меньше текущей ($existingPrice), замена не произведена.")
                    }
                } else {
                    println("Элемента с ключем $key не существует.")
                }
            }
            2 -> {
                val organization = collection[key]?.organization
                if (organization != null) {
                    val existingEmployeesCount = organization.employeesCount
                    if (new < existingEmployeesCount) {
                        organization.name = new.toString()
                        println("Элемент с ID $key был заменен, так как новое количество рабочих ($new) меньше текущего ($existingEmployeesCount).")
                    } else {
                        println("Новая численность ($new) не меньше текущей ($existingEmployeesCount), замена не произведена.")
                    }
                } else {
                    println("Элемента с ключем $key не существует.")
                }
            }
        }
        CommandManager.setAllProcessesCompleted()
    }

    /**
     * Запрашивает у пользователя ввод целого числа и возвращает его. Повторяет запрос до получения корректного ввода.
     *
     * @param prompt Сообщение, отображаемое перед вводом.
     * @return Введенное пользователем целое число.
     */
    fun readInt(prompt: String): Int {
        while (true) {
            print(prompt)
            try {
                return Scan().toInt()
            } catch (e: NumberFormatException) {
                println("Надо ввести int!")
            }
        }
    }
}
