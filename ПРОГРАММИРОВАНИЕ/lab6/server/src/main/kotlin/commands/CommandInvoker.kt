package commands

import commands.consoleCommands.Command
import utils.Answer
import utils.AnswerType
import serverUtils.ConnectionManager
import utils.Query
import java.nio.channels.SocketChannel


class CommandInvoker(private val connectionManager: ConnectionManager) {
    private var commandMap:Map<String, Command> = mapOf()
    private var commandsHistory:Array<String> = arrayOf()

    fun register(name: String, command: Command) {
        commandMap += name to command
    }

    fun executeCommand(query: Query,channel: SocketChannel) {
        try {
            val commandName = query.information
            commandsHistory += commandName

            val command = commandMap[commandName] ?: throw IllegalArgumentException("Команда не найдена")
            command.execute(query.args, channel)
        } catch (e: Exception) {
            val answer = Answer(AnswerType.ERROR, e.toString())
            connectionManager.send(answer,channel)
        }
    }


    fun getCommandMap() : Map<String, Command> {
        return commandMap
    }
}