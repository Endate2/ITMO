package CommandProvider.Commands

import CommandProvider.OutputManager
import Data.DataProvider
import Products.Product
import java.util.*
import Products.Coordinates
import Products.Organization
import Products.UnitOfMeasure
import java.time.LocalDateTime

object Load {
    fun LoadCollection(fileName: String, dp: DataProvider, collection: TreeMap<Int, Product>) {
        try {
            val lines = dp.LoadScript(fileName)
            if (lines.isEmpty()) {
                OutputManager.println("Файл пуст или не содержит данных")
                return
            }

            var successCount = 0
            var errorCount = 0

            for (line in lines) {
                try {
                    val data = line.removeSurrounding("[", "]").split(",")
                    if (data.size != 9) {
                        OutputManager.println("Ошибка: Некорректное количество данных в строке: $line")
                        errorCount++
                        continue
                    }

                    val key = data[0].trim().toInt()
                    val x = data[1].trim().toInt()
                    val y = data[2].trim().toDouble()
                    val organizationName = data[3].trim()
                    val employeesCount = data[4].trim().toInt()
                    val numberOfOrganization = data[5].trim().toInt()
                    val productName = data[6].trim()
                    val unitOfMeasure = data[7].trim().toInt()
                    val price = data[8].trim().toInt()

                    if (numberOfOrganization !in 1..3) {
                        OutputManager.println("Ошибка: Некорректное значение NumberOfOrganization в строке: $line")
                        errorCount++
                        continue
                    }
                    if (unitOfMeasure !in 1..4) {
                        OutputManager.println("Ошибка: Некорректное значение UnitOfMeasure в строке: $line")
                        errorCount++
                        continue
                    }

                    val coordinates = Coordinates(ignoreInit = true).apply {
                        this.x = x
                        this.y = y
                    }

                    val organization = Organization().apply {
                        this.name = organizationName
                        this.employeesCount = employeesCount
                        generateId()
                        inputOrganizationType(numberOfOrganization)
                    }

                    val product = Product(ignoreInit = true).apply {
                        generateId()
                        inputPrice(price)
                        this.name = productName
                        this.organization = organization
                        this.coordinates = coordinates
                        updateUnitOfMeasure(unitOfMeasure)
                        updateDate(LocalDateTime.now())
                    }

                    collection[key] = product
                    successCount++

                } catch (e: NumberFormatException) {
                    OutputManager.println("Ошибка: Некорректный формат числа в строке: $line")
                    errorCount++
                } catch (e: Exception) {
                    OutputManager.println("Ошибка при обработке строки: $line")
                    e.printStackTrace()
                    errorCount++
                }
            }

            OutputManager.printWithSeparators(
                "Загрузка завершена. Успешно: $successCount, с ошибками: $errorCount",
                separatorChar = '='
            )

        } catch (e: Exception) {
            OutputManager.println("Ошибка при загрузке файла: ${e.message}")
        }
    }
}