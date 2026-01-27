package Data



import java.io.BufferedInputStream
import java.io.FileInputStream
import java.io.IOException
import java.util.Vector

/**
 * Класс DataProvider предназначен для чтения текста из файла
 * и загрузки его построчно в Vector строк.
 */
class DataProvider {

    /**
     * Читает указанный файл сценария и загружает его содержимое
     * в Vector строк. Каждая строка удаляется от пробельных символов.
     *
     * @param fileName имя файла, который нужно прочитать.
     * @return Vector, содержащий каждую строку файла как отдельную строку.
     * @throws IOException если файл недоступен или не найден.
     */
    fun LoadScript(fileName: String): Vector<String> {
        val lines = Vector<String>()
        try {
            BufferedInputStream(FileInputStream(fileName)).use { bis ->
                val content = bis.readBytes().toString(Charsets.UTF_8)
                val contentLines = content.lines()
                for (line in contentLines) {
                    lines.add(line.trim())
                }
            }
        } catch (e: IOException) {
            println("К файлу нет доступа или он отсутствует!")
        }
        return lines
    }
}
