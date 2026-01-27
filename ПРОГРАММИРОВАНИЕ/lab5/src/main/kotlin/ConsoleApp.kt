import CommandProvider.CommandManager
import CommandProvider.Commands.Help
import CommandProvider.Commands.Load
import CommandProvider.InputManager

object ConsoleApp {
    @JvmStatic
    fun main(args: Array<String>) {
        val server = CommandManager()

        // Запрашиваем имя файла у пользователя
        val fileName = InputManager.readLine("Введите путь к файлу с данными: ")

        // Загружаем коллекцию из файла
        Load.LoadCollection(
            fileName = fileName,
            dp = server.getDataProvider(),
            collection = server.getCollection()
        )

        Help.HelpCommand(server)

        while (true) {
            print("> ")
            server.CommandChecker(InputManager.readLine())
        }
    }
}