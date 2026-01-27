package CommandProvider.Commands
import CommandProvider.CommandManager
import CommandProvider.OutputManager
import CommandProvider.CommandManager.*

object Help {
    /**
     * display help on available commands
     */
    fun HelpCommand(commandManager: CommandManager) {
        val helpText = """
            
            help - доступные команды
            info - информация о коллекции
            ExecuteScriptCommand - считать и исполнить скрипт из указанного файла
            show - вывести в стандартный поток вывода все элементы коллекции
            remove - удалить элемент из коллекции по его ключу
            remove_lower - удалить из коллекции все элементы, меньшие, чем заданный
            clear - очистить коллекцию
            info - вывести информацию о коллекции (тип, дата инициализации и т.д.)
            remove_greater - удалить из коллекции все элементы, превышающие заданный
            exit - завершить программу
            filterByUnitOfMeasure - вывести элементы, значение поля unitOfMeasure которых равно заданному
            filterGreaterThanPrice - вывести элементы, значение поля price которых больше заданного
            groupByCreationDate - вывести элементы, значение поля price которых больше заданного
            save - сохранить в csv
            replaceIfLower - заменить значение по ключу, если новое значение меньше старого
        """.trimIndent()

        OutputManager.printWithSeparators(helpText)
        CommandManager.setAllProcessesCompleted()
    }
}