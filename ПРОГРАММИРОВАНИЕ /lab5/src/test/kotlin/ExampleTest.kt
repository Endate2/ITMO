package test

import CommandProvider.CommandManager
import CommandProvider.Commands.*
import Data.DataProvider
import Products.Product
import org.junit.jupiter.api.Assertions.*
import org.junit.jupiter.api.Test
import java.util.TreeMap
import java.io.ByteArrayOutputStream
import java.io.PrintStream
import org.junit.jupiter.api.BeforeAll
import org.junit.jupiter.api.AfterAll
import org.junit.jupiter.api.BeforeEach
import org.mockito.Mockito.spy
import org.mockito.Mockito.verify


class ExampleTest {
    private val commandManager = CommandManager()
    private val testCollection = TreeMap<Int, Product>()

    companion object {
        private val dp = DataProvider()
        private val mainCollection = TreeMap<Int, Product>()

        @BeforeAll
        @JvmStatic
        fun beforeAll() {
            Load.LoadCollection(
                "/Users/antoncimirev/Desktop/лабы прога/laba5/src/main/kotlin/CommandProvider/Commands/data.csv",
                dp,
                mainCollection
            )
        }

        @AfterAll
        @JvmStatic
        fun afterAll() {
            Save.SaveCommand(mainCollection, "final", CommandManager())
            Exit.exitCommand(mainCollection)
        }
    }

    @BeforeEach
    fun setUp() {
        // Создаем свежую копию коллекции для каждого теста
        testCollection.clear()
        testCollection.putAll(mainCollection)
    }

    @Test
    fun RemoveLower() {
        val originalSize = testCollection.size
        assertTrue(originalSize > 0, "Коллекция не должна быть пустой в начале теста")

        val keyToRemove = 15
        RemoveLower.removeLowerByKey(testCollection, keyToRemove, commandManager)

        assertNotEquals(originalSize, testCollection.size, "Размер коллекции должен измениться после удаления")
        testCollection.keys.forEach {
            assertTrue(it >= keyToRemove, "Коллекция должна содержать только ключи >= $keyToRemove")
        }
    }


    @Test
    fun testHelpCommand() {
        val outputStream = ByteArrayOutputStream()
        System.setOut(PrintStream(outputStream))

        Help.HelpCommand(commandManager)

        val output = outputStream.toString()
        System.setOut(System.out)

        assertTrue(output.contains("help - доступные команды"), "Вывод должен содержать команду help")
        assertTrue(output.contains("info - информация о коллекции"), "Вывод должен содержать команду info")
    }


    @Test
    fun testClearCommand() {
        assertTrue(testCollection.isNotEmpty(), "Коллекция не должна быть пустой перед очисткой")
        Clear.clearCollection(testCollection, commandManager)
        assertTrue(testCollection.isEmpty(), "Коллекция должна быть пустой после очистки")
    }


    @Test
    fun testInfoCommand() {
        val outputStream = ByteArrayOutputStream()
        System.setOut(PrintStream(outputStream))

        Info.InfoCommand(testCollection, commandManager)

        val output = outputStream.toString()
        System.setOut(System.out)

        assertTrue(output.contains("Размер коллекции:"), "Вывод должен содержать размер коллекции")
        assertTrue(output.contains("Кол-во элементов: ${testCollection.size}"),
            "Вывод должен содержать количество элементов")

        if (testCollection.isNotEmpty()) {
            assertTrue(output.contains("Дата последнего изменения коллекции:"),
                "Вывод должен содержать дату изменения")
        } else {
            assertTrue(output.contains("Коллекция пуста, нет даты изменения."),
                "Вывод должен содержать сообщение о пустой коллекции")
        }
    }


    @Test
    fun RemoveGreaterInfo() {
        val originalSize = testCollection.size
        assertTrue(originalSize > 0, "Коллекция не должна быть пустой в начале теста")

        val keyToRemove = 12
        RemoveGreater.removeGreaterByKey(testCollection, keyToRemove, commandManager)

        assertNotEquals(originalSize, testCollection.size, "Размер коллекции должен измениться после удаления")
        testCollection.keys.forEach {
            assertTrue(it <= keyToRemove, "Коллекция должна содержать только ключи <= $keyToRemove")
        }
    }


    @Test
    fun ReplaceIfLowerInfo() {
        // Проверяем, что ключ существует в коллекции
        assertTrue(testCollection.containsKey(12), "Коллекция должна содержать ключ 12")

        // Сохраняем оригинальную цену для сравнения
        val originalPrice = testCollection[12]?.price

        ReplaceIfLower.replaceIfLower(testCollection, 1, 12, 1, commandManager)

        val updatedPrice = testCollection[12]?.price
        assertNotNull(updatedPrice, "Цена не должна быть null после обновления")
        assertEquals(1, updatedPrice, "Цена должна обновиться до 1")

        if (originalPrice != null) {
            assertTrue(updatedPrice!! < originalPrice,
                "Цена должна обновляться только если новое значение меньше")
        }
    }


    @Test
    fun ReplaceIfLower_ShouldNotUpdate_WhenNewValueIsHigher() {
        // Убедимся, что ключ существует в коллекции
        assertTrue(testCollection.containsKey(12), "Коллекция должна содержать ключ 12")

        // Сохраняем оригинальную цену для сравнения
        val originalPrice = testCollection[12]?.price
        assertNotNull(originalPrice, "Оригинальная цена не должна быть null")

        // Пытаемся обновить цену на значение, которое больше текущего
        val higherPrice = originalPrice!! + 1
        ReplaceIfLower.replaceIfLower(testCollection, 1, 12, higherPrice, commandManager)

        // Проверяем, что цена не изменилась
        val currentPrice = testCollection[12]?.price
        assertEquals(originalPrice, currentPrice, "Цена не должна меняться, когда новое значение больше")
    }


    @Test
    fun Remove() {
        assertTrue(testCollection.containsKey(12), "Коллекция должна содержать ключ 12 перед удалением")

        Remove.removeCommand(testCollection, 12, commandManager)

        assertFalse(testCollection.containsKey(12), "Элемент с ключом 12 должен быть удален")
    }


    @Test
    fun GroupByCreationDate() {
        val originalProducts = testCollection.values.toList()
        assertTrue(originalProducts.size > 1, "Нужно как минимум 2 продукта для тестирования сортировки")

        val sortedProducts = GroupByCreationDate.sortProductsByDate(testCollection, commandManager)

        assertEquals(originalProducts.size, sortedProducts.size,
            "Отсортированная коллекция должна быть того же размера, что и оригинальная")

        for (i in 0 until sortedProducts.size - 1) {
            assertTrue(
                sortedProducts[i].date <= sortedProducts[i + 1].date,
                "Продукты должны быть отсортированы по дате создания"
            )
        }

    }
    @Test
    fun IdTest() {
        val product = Product(ignoreInit = true)

        assertTrue(product.id in 1..1_000_001, "ID продукта должен быть между 1 и 1,000,001")
    }


}