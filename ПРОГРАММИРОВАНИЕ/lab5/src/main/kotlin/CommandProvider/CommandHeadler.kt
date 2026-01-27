package CommandProvider

import CommandProvider.CommandManager.Companion.setAllProcessesNotCompleted
import CommandProvider.Commands.*
import Products.UnitOfMeasure

// Интерфейс для всех хендлеров
interface CommandHandler {
    fun handle(commandManager: CommandManager)
}

// Реализация хендлеров для каждой команды

class HelpCommandHandler : CommandHandler {
    override fun handle(commandManager: CommandManager) {
        setAllProcessesNotCompleted()
        Help.HelpCommand(commandManager)
    }
}

class InsertCommandHandler : CommandHandler {
    override fun handle(commandManager: CommandManager) {
        val key = InputValidator.validateKey(commandManager.getCollection())
        Insert.InsertCommand(commandManager.getCollection(), key, commandManager)
    }
}

class UpdateidCommandHandler : CommandHandler {
    override fun handle(commandManager: CommandManager) {
        val key = InputValidator.validateIntInput(
            prompt = "Введите key, который хотите изменить: ",
            errorMessage = "Надо ввести int!",
            isValid = { true }
        )
        Updateid.UpdateCommand(commandManager.getCollection(), key, commandManager)
    }
}

class ShowCommandHandler : CommandHandler {
    override fun handle(commandManager: CommandManager) {
        Show.ShowCommand(commandManager.getCollection(), commandManager)
    }
}

class RemoveCommandHandler : CommandHandler {
    override fun handle(commandManager: CommandManager) {
        val key = InputValidator.validateIntInput(
            prompt = "Введите key: ",
            errorMessage = "Надо ввести int!",
            isValid = { true }
        )
        Remove.removeCommand(commandManager.getCollection(), key, commandManager)
    }
}

class ClearCommandHandler : CommandHandler {
    override fun handle(commandManager: CommandManager) {
        Clear.clearCollection(commandManager.getCollection(), commandManager)
    }
}

class InfoCommandHandler : CommandHandler {
    override fun handle(commandManager: CommandManager) {
        Info.InfoCommand(commandManager.getCollection(), commandManager)
    }
}

class RemoveGreaterCommandHandler : CommandHandler {
    override fun handle(commandManager: CommandManager) {
        val key = InputValidator.validateIntInput(
            prompt = "Введите key, выше которого нужно удалить: ",
            errorMessage = "Надо ввести int!",
            isValid = { true }
        )
        RemoveGreater.removeGreaterByKey(commandManager.getCollection(), key, commandManager)
    }
}

class RemoveLowerCommandHandler : CommandHandler {
    override fun handle(commandManager: CommandManager) {
        val key = InputValidator.validateIntInput(
            prompt = "Введите key, ниже которого нужно удалить: ",
            errorMessage = "Надо ввести int!",
            isValid = { true }
        )
        RemoveLower.removeLowerByKey(commandManager.getCollection(), key, commandManager)
    }
}

class ExitCommandHandler : CommandHandler {

    override fun handle(commandManager: CommandManager) {
        val processesStatus = CommandManager.getAllProcessesCompleted()
        println("Статус процессов: $processesStatus")
        if (CommandManager.allProcessesCompleted == 1) {
            Exit.exitCommand(commandManager.getCollection())
        } else {
            println("Не все процессы завершены. Программа не может быть завершена.")
        }
    }
}

class FilterUnitCommandHandler : CommandHandler {
    override fun handle(commandManager: CommandManager) {
        val unit = InputValidator.validateUnitOfMeasureNumber()
        when (unit) {
            1 -> FilterUnit.filterByUnitOfMeasure(commandManager.getCollection(), UnitOfMeasure.METERS, commandManager)
            2 -> FilterUnit.filterByUnitOfMeasure(commandManager.getCollection(), UnitOfMeasure.SQUARE_METERS, commandManager)
            3 -> FilterUnit.filterByUnitOfMeasure(commandManager.getCollection(), UnitOfMeasure.GRAMS, commandManager)
            4 -> FilterUnit.filterByUnitOfMeasure(commandManager.getCollection(), UnitOfMeasure.MILLIGRAMS, commandManager)
        }
    }
}

class FilterPriceCommandHandler : CommandHandler {
    override fun handle(commandManager: CommandManager) {
        val price = InputValidator.validateIntInput(
            prompt = "Введите price, чтобы вывести все product, у которых цена будет больше: ",
            errorMessage = "Надо ввести int!",
            isValid = { true }
        )
        FilterPrice.filterByPrice(commandManager.getCollection(), price, commandManager)
    }
}

class GroupByCreationDateCommandHandler : CommandHandler {
    override fun handle(commandManager: CommandManager) {
        GroupByCreationDate.sortProductsByDate(commandManager.getCollection(), commandManager)
    }
}

class SaveCommandHandler : CommandHandler {
    override fun handle(commandManager: CommandManager) {
        val file = InputValidator.validateStringInput(
            prompt = "Имя файла, чтобы сохранить коллекцию в него: ",
            errorMessage = "Имя файла не может быть пустым.",
            isValid = { it.isNotEmpty() }
        )
        Save.SaveCommand(commandManager.getCollection(), file, commandManager)
    }
}

class ReplaceIfLowerCommandHandler : CommandHandler {
    override fun handle(commandManager: CommandManager) {
        val unit = InputValidator.validateIntInput(
            prompt = "Введите номер характеристики, которую хотите сравнить:\n1 - Price\n2 - EMPLOYEESCOUNT\n",
            errorMessage = "Вы указали неправильный номер! Проверьте и введите еще раз.",
            isValid = { it in 1..2 }
        )
        val key = InputValidator.validateIntInput(
            prompt = "Введите key, изменить элемент, если он меньше: ",
            errorMessage = "Надо ввести int!",
            isValid = { true }
        )
        val new = InputValidator.validateIntInput(
            prompt = "Введите значение new элемента: ",
            errorMessage = "Надо ввести int!",
            isValid = { true }
        )
        ReplaceIfLower.replaceIfLower(commandManager.getCollection(), unit, key, new, commandManager)
    }
}

class ExecuteScriptCommandHandler : CommandHandler {
    override fun handle(commandManager: CommandManager) {
        val file = InputValidator.validateStringInput(
            prompt = "Имя файла, чтобы выполнить скрипт из него: ",
            errorMessage = "Имя файла не может быть пустым.",
            isValid = { it.isNotEmpty() }
        )
        ReadScript.ExecuteScriptCommand(file, commandManager.getDataProvider(), commandManager)
    }
}