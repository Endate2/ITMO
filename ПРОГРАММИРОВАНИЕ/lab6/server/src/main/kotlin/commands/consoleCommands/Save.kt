package commands.consoleCommands

import commands.CommandReceiver
import exceptions.InvalidArgumentException
import java.nio.channels.SocketChannel

class Save() : Command() {

    private lateinit var commandReceiver: CommandReceiver
    constructor(commandReceiver: CommandReceiver) : this() {
        this.commandReceiver = commandReceiver
    }

    private val info = "Сохраняет данные сбора в файл save.txt"
    private val argsTypes = mapOf<String, String>()  // Нет аргументов

    override fun getInfo(): String {
        return info
    }

    override fun getArgsTypes(): Map<String, String> {
        return argsTypes
    }

    override fun execute(args: Map<String, String>, channel: SocketChannel) {
        if (args.isNotEmpty()) {
            throw InvalidArgumentException("Команда 'save' не принимает аргументы. Используйте: save")
        }
        commandReceiver.save("save",channel)
    }
}