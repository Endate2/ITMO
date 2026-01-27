

import utils.ResultsBean
import utils.HitChecker
import utils.Validator
import java.io.IOException
import java.io.PrintWriter
import java.lang.String
import java.time.ZoneId
import java.time.ZonedDateTime
import java.time.format.DateTimeFormatter
import java.util.logging.Logger
import javax.servlet.ServletException
import javax.servlet.http.HttpServlet
import javax.servlet.http.HttpServletRequest
import javax.servlet.http.HttpServletResponse
import kotlin.Exception
import kotlin.Throws
import kotlin.takeIf
import com.fasterxml.jackson.databind.ObjectMapper


class UserAgentStatsServlet : HttpServlet() {

    private val logger: Logger = Logger.getLogger(this::class.java.name)
    private val mapper = ObjectMapper()

    @Throws(ServletException::class, IOException::class)
    override fun doGet(request: HttpServletRequest, response: HttpServletResponse) {
        val userAgentMap = request.servletContext.getAttribute("userAgentMap") as? Map<String, Long>

        response.contentType = "application/json; charset=UTF-8"
        response.characterEncoding = "UTF-8"

        if (userAgentMap != null) {
            val jsonResponse = mapOf(
                "status" to "success",
                "userAgentStats" to userAgentMap,
                "totalUniqueUserAgents" to userAgentMap.size
            )
            mapper.writeValue(response.writer, jsonResponse)
        }else{
            val errorResponse = mapOf(
                "status" to "error",
                "message" to "Статистика user-agent'ов недоступна"
            )
            mapper.writeValue(response.writer, errorResponse)
        }
    }
}
