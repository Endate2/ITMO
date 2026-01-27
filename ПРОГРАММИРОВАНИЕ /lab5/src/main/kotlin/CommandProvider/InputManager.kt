// InputManager.kt
package CommandProvider

object InputManager {
    /**
     * Читает строку ввода
     * @param prompt Сообщение-приглашение для ввода
     * @return Введенная строка
     */
    fun readLine(prompt: String = ""): String {
        if (prompt.isNotEmpty()) {
            OutputManager.print(prompt)
        }
        return kotlin.io.readLine() ?: ""
    }

    /**
     * Читает целое число
     * @param prompt Сообщение-приглашение
     * @return Введенное число
     * @throws NumberFormatException Если ввод не является числом
     */
    fun readInt(prompt: String = ""): Int {
        return readLine(prompt).toInt()
    }

    /**
     * Читает число типа Long
     * @param prompt Сообщение-приглашение
     * @return Введенное число
     * @throws NumberFormatException Если ввод не является числом
     */
    fun readLong(prompt: String = ""): Long {
        return readLine(prompt).toLong()
    }

    /**
     * Читает число типа Double
     * @param prompt Сообщение-приглашение
     * @return Введенное число
     * @throws NumberFormatException Если ввод не является числом
     */
    fun readDouble(prompt: String = ""): Double {
        return readLine(prompt).toDouble()
    }

    /**
     * Читает булево значение (true/false, yes/no, 1/0)
     * @param prompt Сообщение-приглашение
     * @return Введенное значение
     */
    fun readBoolean(prompt: String = ""): Boolean {
        val input = readLine(prompt).lowercase()
        return when (input) {
            "true", "yes", "1" -> true
            "false", "no", "0" -> false
            else -> throw IllegalArgumentException("Некорректное булево значение")
        }
    }
}