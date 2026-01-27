

import com.fasterxml.jackson.databind.ObjectMapper
import java.util.logging.Logger
import javax.servlet.ServletException
import javax.servlet.http.HttpServlet
import javax.servlet.http.HttpServletRequest
import javax.servlet.http.HttpServletResponse
import java.io.IOException

class ControllerServlet : HttpServlet() {

    private val logger: Logger = Logger.getLogger(this::class.java.name)

    @Throws(ServletException::class, IOException::class)
    override fun doPost(request: HttpServletRequest, response: HttpServletResponse) {
        request.characterEncoding = "UTF-8"

        val mapper = ObjectMapper()
        val jsonMap: Map<String, Any> = mapper.readValue(request.reader, Map::class.java) as Map<String, Any>

        val x = jsonMap["x"].toString()
        val y = jsonMap["y"].toString()
        val r = jsonMap["r"].toString()
        val flag = jsonMap["flag"].toString()
        val timeZone = jsonMap["timeZone"].toString()

        request.setAttribute("x", x)
        request.setAttribute("y", y)
        request.setAttribute("r", r)
        request.setAttribute("flag", flag)
        request.setAttribute("timeZone", timeZone)

        request.getRequestDispatcher("/checkArea").forward(request, response)
    }
}
