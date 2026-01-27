package CommandProvider

object OutputManager {
    // Используем стандартный вывод по умолчанию
    private var outputHandler: (String) -> Unit = { message ->
        kotlin.io.println(message)
    }

    /**
     * Устанавливает пользовательский обработчик вывода.
     * @param handler Функция, которая будет обрабатывать вывод
     */
    fun setOutputHandler(handler: (String) -> Unit) {
        outputHandler = handler
    }

    /**
     * Выводит строку с помощью текущего обработчика вывода.
     * @param message Сообщение для вывода
     */
    fun print(message: String) {
        outputHandler(message)
    }

    /**
     * Выводит строку с переносом с помощью текущего обработчика вывода.
     * @param message Сообщение для вывода
     */
    fun println(message: String) {
        outputHandler(message + "\n")
    }

    /**
     * Выводит разделительную линию.
     * @param length Длина линии (по умолчанию 20)
     * @param char Символ для линии (по умолчанию '=')
     */
    fun printSeparator(length: Int = 20, char: Char = '=') {
        println(char.toString().repeat(length))
    }

    /**
     * Выводит сообщение, обрамленное разделителями.
     * @param message Сообщение для вывода
     * @param separatorLength Длина разделителей
     * @param separatorChar Символ разделителей
     */
    fun printWithSeparators(message: String, separatorLength: Int = 20, separatorChar: Char = '=') {
        printSeparator(separatorLength, separatorChar)
        println(message)
        printSeparator(separatorLength, separatorChar)
    }
}