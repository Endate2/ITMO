package CommandProvider.Commands

import Products.Product
import java.util.*
import java.util.concurrent.atomic.AtomicBoolean
import kotlin.system.exitProcess


/**
 * Объект `Exit` предоставляет команду для завершения работы программы.
 *
 * Этот объект содержит метод `exitCommand`, который выводит сообщение о завершении программы
 * и завершает её выполнение с кодом 0 (успешное завершение).
 *
 * @author Endatee
 * @since 1.0
 */
object Exit {
    // Флаг для отслеживания завершения всех процессов

    /**
     * Завершает выполнение программы.
     *
     * Этот метод выводит сообщение "Программа завершается." и завершает работу программы
     * с использованием функции `exitProcess(0)`, что означает успешное завершение.
     *
     * @see kotlin.system.exitProcess
     */
    fun exitCommand(collection: TreeMap<Int, Product>) {
        println("Программа завершается.")
        SaveData.SaveCommand(collection, "/Users/antoncimirev/Desktop/laba5/src/main/kotlin/CommandProvider/Commands/data.csv")
        exitProcess(0)
    }


}