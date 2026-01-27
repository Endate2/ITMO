package serverUtils

import basicClasses.Product
import collection.CollectionManager
import exceptions.NoEnvironmentVariableFound
import java.io.FileOutputStream
import java.io.OutputStreamWriter
import java.io.BufferedWriter

class Saver {

    fun save(filename: String, collectionManager: CollectionManager) {
        try {
            val file = if (filename == "") {
                try {
                    System.getenv("COLLECTION")
                } catch (e:Exception) {
                    throw NoEnvironmentVariableFound()
                }
            } else {
                filename
            }

            val processedFileName = if (!file.endsWith(".csv")) "$file.csv" else file

            BufferedWriter(OutputStreamWriter(FileOutputStream(processedFileName))).use { writer ->
                for ((key, product) in collectionManager.getCollection()) {
                    writer.write(productToCSV(key, product))
                    writer.newLine()
                }
            }
        } catch (e: Exception) {
            throw e
        }
    }


    private fun productToCSV(key: Int, product: Product): String {
        val coordinates = product.getCoordinates()
        val organization = product.getManufacturer()

        return if (coordinates != null && organization != null) {
            "[$key," +
                    "${coordinates.getX()}," +
                    "${coordinates.getY()}," +
                    "${organization.getName()}," +
                    "${organization.getEmployeesCount()}," +
                    "${organization.getType()}," +
                    "${product.getName()}," +
                    "${product.getUnitOfMeasure()}," +
                    "${product.getPrice()}]"
        } else {
            throw IllegalStateException("Координаты или организация равны нулю для продукта с ключом $key")
        }
    }
}