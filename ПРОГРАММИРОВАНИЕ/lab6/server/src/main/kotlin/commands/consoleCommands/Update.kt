package commands.consoleCommands

import commands.CommandReceiver
import serverUtils.Validator
import exceptions.InvalidArgumentException
import java.nio.channels.SocketChannel


class Update() : Command() {

    private lateinit var commandReceiver: CommandReceiver
    constructor(commandReceiver: CommandReceiver) : this() {
        this.commandReceiver = commandReceiver
    }

    private val info = "Обновляет значения элемента с указанным Id"
    private val argsTypes = mapOf(
        "id" to "Int",
        "product" to "Product"
    )

    override fun getInfo(): String {
        return info
    }

    override fun getArgsTypes(): Map<String, String> {
        return argsTypes
    }

    /**
     * Calls [CommandReceiver.updateByID] with provided id
     */
    override fun execute(args: Map<String, String>,channel: SocketChannel) {
        if (Validator.verifyArgs(2, args)) {
            try {
                commandReceiver.updateByID(args, channel)
            } catch (e:Exception) {
                throw InvalidArgumentException("Неверный аргумент")
            }
        } else throw InvalidArgumentException("Были введены неверные аргументы. Используйте команду HELP для проверки")
    }

}