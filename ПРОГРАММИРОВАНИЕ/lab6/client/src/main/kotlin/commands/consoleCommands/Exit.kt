package commands.consoleCommands

import clientUtils.Validator
import exceptions.InvalidArgumentException


class Exit(
) : Command() {

    override fun getInfo(): String {
        return "Завершение работы консольного приложения"
    }

    override fun execute(args: List<String>) {
        if (Validator.verifyArgs(0, args)) {
            setFlag(false)
        } else throw InvalidArgumentException("Были введены неверные аргументы. Используйте команду HELP для проверки")
    }
}