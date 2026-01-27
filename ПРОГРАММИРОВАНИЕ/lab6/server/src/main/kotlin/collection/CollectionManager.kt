package collection

import basicClasses.Product
import exceptions.ProductIdAlreadyExists
import java.util.*
import java.util.function.Predicate


class CollectionManager {
    private val collection = TreeMap<Int,Product>()
    private val date: Date = Date()

    fun getCollection(): TreeMap<Int, Product> {
        return collection
    }

    fun add(element: Product) {
        if (collection.containsKey(element.getId())) {
            throw ProductIdAlreadyExists("Продукт с id ${element.getId()} уже существует")
        }
        collection[element.getId()] = element // Используем ID продукта как ключ
    }


    fun getInfo() : String {
        return "Информация о коллекции: размер=${collection.size}"
    }


    fun show(): MutableList<String> {
        return if (collection.isEmpty()) {
            mutableListOf("Коллекция Пуста")
        } else {
            val output = mutableListOf<String>()
            for (product in collection) {
                output.add(product.toString())
            }
            output
        }
    }


    fun update(data: Product, product: Product) {
        product.setName(data.getName())
        product.setCoordinates(data.getCoordinates())
        product.setPrice(data.getPrice())
        product.setUnitOfMeasure(data.getUnitOfMeasure())
    }


    fun remove(product: Product): Boolean {
        return collection.remove(product.getId()) != null
    }

    fun clear() {
        collection.clear()
    }


    fun getByID(id: Int): Product? {
        return collection[id]
    }

    fun filter(predicate: Predicate<Product>): List<Product> {
        return collection.values.filter { predicate.test(it) }
    }

}
