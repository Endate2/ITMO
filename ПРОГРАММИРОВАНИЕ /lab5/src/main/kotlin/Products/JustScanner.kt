package Products

import java.util.*

/**
 * Объект JustScanner предоставляет статический метод для считывания строкового ввода
 * от пользователя через консоль с использованием класса Scanner.
 */
object JustScanner {
    /**
     * Считывает и возвращает строку, введенную пользователем в консоль.
     *
     * @return строка, считанная из ввода пользователя.
     */
    @JvmStatic
    fun Scan(): String {

        print(">>> ")
        val scanner = Scanner(System.`in`)
        return scanner.nextLine()
    }
}
