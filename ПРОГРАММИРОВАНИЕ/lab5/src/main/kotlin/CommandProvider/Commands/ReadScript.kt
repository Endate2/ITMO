package CommandProvider.Commands

import CommandProvider.CommandManager
import Data.DataProvider
/**
 * Объект, представляющий команду для выполнения скрипта из файла.
 */
object ReadScript {

    /**
     * Исполняет команды из скрипта, загруженного из файла.
     *
     * @param fileName Имя файла, содержащего скрипт для выполнения.
     * @param dp Провайдер данных, отвечающий за загрузку скрипта из файла.
     */
    fun ExecuteScriptCommand(fileName: String, dp: DataProvider,commandManager: CommandManager) {
        val lines = dp.LoadScript(fileName)
        val cm = CommandManager()

        for (s in lines) {
            cm.CommandChecker(s)
        }
        CommandManager.setAllProcessesCompleted()
    }
}
