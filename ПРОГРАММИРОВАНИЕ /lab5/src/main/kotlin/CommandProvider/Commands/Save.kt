package CommandProvider.Commands

import Products.Product
import java.io.BufferedWriter
import java.io.FileWriter
import java.io.IOException
import Products.Coordinates
import Products.Organization
import java.time.LocalDateTime
import CommandProvider.CommandManager
import java.util.*

/**
 * Объект, представляющий команду для сохранения коллекции продуктов в файл.
 */
object Save {

    /**
     * Сохраняет коллекцию продуктов в CSV-файл.
     *
     * @param collection Коллекция объектов типа TreeMap, где ключ типа Int и значение типа Product.
     * @param fileName Имя файла, в который будет сохранена коллекция.
     * @return Имя файла, в который была сохранена коллекция.
     */
    fun SaveCommand(collection: TreeMap<Int, Product>, fileName: String,commandManager: CommandManager): String {
        var processedFileName = fileName.replace(" ".toRegex(), "").replace("\\.".toRegex(), "")

        if (processedFileName == "") {
            processedFileName = "data"
        }

        processedFileName += ".csv"

        try {
            BufferedWriter(FileWriter(processedFileName)).use { writer ->
                for ((key, product) in collection) {
                    writer.write(productToCSV(key, product))
                    writer.newLine()
                }
            }
            println("Успешно сохранено!")
        } catch (e: IOException) {
            println("Ошибка при сохранении в файл: " + e.message)
        }
        CommandManager.setAllProcessesCompleted()
        return processedFileName
    }


    /**
     * Преобразует объект Product к строке формата CSV.
     *
     * @param key Ключ продукта в коллекции.
     * @param product Объект типа Product для преобразования.
     * @return Строка, представляющая продукт в формате CSV.
     */
    private fun productToCSV(key: Int, product: Product): String {
        val coordinates = product.coordinates
        val organization = product.organization

        // Проверяем, что coordinates и organization не null
        return if (coordinates != null && organization != null) {
            "[$key," +
                    "${coordinates.x}," +
                    "${coordinates.y}," +
                    "${organization.name}," +
                    "${organization.employeesCount}," +
                    "${organization.reviewOrganizationType()}," +
                    "${product.name}," +
                    "${product.unitOfMeasureCode}," +
                    "${product.price}]"
        } else {
            // Если coordinates или organization равны null, возвращаем пустую строку или обрабатываем ошибку
            println("Ошибка: coordinates или organization равны null для продукта с ключом $key")
            ""
        }

    }
}
