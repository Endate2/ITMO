package CommandProvider.Commands

import Products.Product
import java.util.*
import CommandProvider.CommandManager
import CommandProvider.OutputManager

/**
 * Объект `Clear` предоставляет метод для очистки коллекции продуктов.
 *
 * Использует OutputManager для унифицированного вывода сообщений.
 *
 * @author Endate
 * @since 1.0
 */
object Clear {
    /**
     * Очищает переданную коллекцию продуктов.
     *
     * Если коллекция уже пуста, выводится сообщение о том, что коллекция и так пуста.
     * В противном случае коллекция очищается, и выводится сообщение об успешной очистке.
     *
     * @param collection Коллекция продуктов (TreeMap<Int, Product>)
     * @param commandManager Менеджер команд для управления состоянием выполнения
     */
    fun clearCollection(collection: TreeMap<Int, Product>, commandManager: CommandManager) {
        if (collection.isEmpty()) {
            OutputManager.printWithSeparators(
                message = "Коллекция уже пуста",
                separatorLength = 30,
                separatorChar = '-'
            )
        } else {
            collection.clear()
            OutputManager.printWithSeparators(
                message = "Коллекция успешно очищена",
                separatorLength = 30,
                separatorChar = '='
            )
        }

        CommandManager.setAllProcessesCompleted()
    }
}