package serverUtils

import basicClasses.*
import collection.CollectionManager
import org.apache.logging.log4j.LogManager
import org.apache.logging.log4j.Logger
import java.io.File
import java.sql.Timestamp
import java.time.LocalDate

class FileManager {
    private val logger: Logger = LogManager.getLogger(FileManager::class.java)
    private val collectionFilePath = "servercollection.csv"

    fun load(collectionManager: CollectionManager) {
        try {
            logger.info("Загрузка коллекции из файла: $collectionFilePath")
            val file = File(collectionFilePath)

            if (!file.exists()) {
                logger.warn("Файл не найден. Создание пустой коллекции.")
                file.createNewFile()
                return
            }

            val lines = file.readLines()
            if (lines.isEmpty()) {
                logger.info("Файл пуст.")
                return
            }

            var successCount = 0
            var errorCount = 0

            lines.forEachIndexed { index, line ->
                try {
                    // Добавляем случайную паузу от 10 до 100 миллисекунд
                    Thread.sleep((10..100).random().toLong())

                    val trimmedLine = line.trim()
                    if (trimmedLine.isEmpty()) {
                        logger.warn("Строка ${index + 1} пуста, пропускаем.")
                        errorCount++
                        return@forEachIndexed
                    }

                    val cleanLine = trimmedLine.removeSurrounding("[", "]").trim()
                    val data = cleanLine.split(",").map { it.trim() }

                    if (data.size != 9) {
                        logger.error("Строка ${index + 1}: Ожидалось 9 элементов, найдено ${data.size}.")
                        errorCount++
                        return@forEachIndexed
                    }

                    // Остальной код обработки...
                    val key = data[0].toInt()
                    val x = data[1].toLong()
                    val y = data[2].toDouble()
                    val orgName = data[3]
                    val employeesCount = data[4].toInt()
                    val orgTypeCode = data[5]
                    val productName = data[6]
                    val unitCode = data[7]
                    val price = data[8].toInt()

                    // Валидация и создание объектов...
                    val organizationType = try {
                        OrganizationType.valueOf(orgTypeCode)
                    } catch (e: IllegalArgumentException) {
                        logger.error("Строка ${index + 1}: Недопустимый тип организации: $orgTypeCode")
                        errorCount++
                        return@forEachIndexed
                    }

                    // Преобразование строки в UnitOfMeasure
                    val unitOfMeasure = try {
                        UnitOfMeasure.valueOf(unitCode)
                    } catch (e: IllegalArgumentException) {
                        logger.error("Строка ${index + 1}: Недопустимая единица измерения: $unitCode")
                        errorCount++
                        return@forEachIndexed
                    }

                    val coordinates = Coordinates(x = x, y = y)


                    val organization = Organization(
                        name = orgName,
                        employeesCount = employeesCount,
                        type = organizationType
                    )

                    val product = Product(
                        name = productName,
                        coordinates = coordinates,
                        price = price,
                        unitOfMeasure = unitOfMeasure,
                        manufacturer = organization
                    )

                    collectionManager.add(product)
                    successCount++

                } catch (e: NumberFormatException) {
                    logger.error("Строка ${index + 1}: Неверный числовой формат: ${e.message}")
                    errorCount++
                } catch (e: InterruptedException) {
                    logger.warn("Загрузка прервана во время ожидания")
                    Thread.currentThread().interrupt()
                } catch (e: Exception) {
                    logger.error("Строка ${index + 1}: Ошибка обработки строки: ${e.message}")
                    errorCount++
                }
            }

            logger.info("Загрузка завершена. Успешно загружено: $successCount, Ошибок: $errorCount")

        } catch (e: Exception) {
            logger.error("Критическая ошибка при загрузке коллекции: ${e.message}")
        }
    }
}