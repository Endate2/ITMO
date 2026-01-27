import com.fastcgi.FCGIInterface
import validation.Validate
import check.Checker
import java.nio.charset.StandardCharsets
import java.time.ZoneId
import java.time.ZonedDateTime
import java.time.format.DateTimeFormatter
import java.util.LinkedHashMap

object Server {
    @JvmStatic
    fun main(args: Array<String>) {
        val fcgiInterface = FCGIInterface()
        val v = Validate()
        val checker = Checker()

        while (fcgiInterface.FCGIaccept() >= 0) {
            val method = FCGIInterface.request.params.getProperty("REQUEST_METHOD") // извлекаем метод запроса GET POST и тд
            if (method == "GET") {
                val startTime = System.nanoTime() // стартовое время
                val req = FCGIInterface.request.params.getProperty("QUERY_STRING") // параметры из URL
                if (!req.isNullOrEmpty()) {
                    val m = getValues(req) // парсим параметры в ключ:значение
                    try {
                        val x = m["x"]?.toIntOrNull()
                        val y = m["y"]?.toFloatOrNull()
                        val r = m["r"]?.toFloatOrNull()
                        val timeZone = m["timeZone"] ?: "UTC"

                        if (x != null && y != null && r != null) {
                            val isValid = v.check(x, y, r)
                            val isShot = checker.hit(x, y, r)
                            if (isValid) {
                                println(resp(isShot, x.toString(), y.toString(), r.toString(), startTime, timeZone))
                            } else {
                                println(err(v.getErr()))
                            }
                        } else {
                            println(err("Invalid data"))
                        }
                    } catch (e: Exception) {
                        println(err("Invalid data"))
                    }
                } else {
                    println(err("fill"))
                }
            } else {
                println(err("method"))
            }
        }
    }

    private fun getValues(inpString: String): LinkedHashMap<String, String> {
        val args = inpString.split("&") //разбивает строку на пары ключ=значение
        val map = LinkedHashMap<String, String>() //  сохраняет порядок добавления элементов
        for (s in args) {
            val arg = s.split("=") // разделяет каждую пару на ключ и значение
            if (arg.size == 2) {
                map[arg[0]] = arg[1]
            }
        }
        return map
    }

    private fun resp(isShoot: Boolean, x: String, y: String, r: String, startTime: Long, timeZone: String): String {
        val zoneId = try { //Создает временную зону, при ошибке использует системную по умолчанию
            ZoneId.of(timeZone)
        } catch (e: Exception) {
            ZoneId.systemDefault()
        }

        val currentTime = ZonedDateTime.now(zoneId)
        val workTime = currentTime.format(DateTimeFormatter.ofPattern("HH:mm:ss"))

        val processingTime = "%.5f".format((System.nanoTime() - startTime).toDouble() / 1_000_000_000)

        val content = """
        {"result":"$isShoot","x":"$x","y":"$y","r":"$r","time":"$processingTime","workTime":"$workTime","error":"all ok"}
    """.trimIndent()

        return """
        Content-Type: application/json; charset=utf-8
        Content-Length: ${content.toByteArray(StandardCharsets.UTF_8).size}
        
        $content
    """.trimIndent() //trimIndent() - это функция Kotlin для работы с многострочными строками (raw strings), которая автоматически убирает общие отступы.
    }

    private fun err(msg: String): String {
        val content = """
            {"error":"$msg"}
        """.trimIndent()
        return """
            Content-Type: application/json; charset=utf-8
            Content-Length: ${content.toByteArray(StandardCharsets.UTF_8).size}
            
            $content
        """.trimIndent()
    }
}