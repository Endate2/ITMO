

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


class AreaCheckServlet : HttpServlet() {

    private val logger: Logger = Logger.getLogger(this::class.java.name)
    private val mapper = ObjectMapper()

    @Throws(ServletException::class, IOException::class)
    override fun doPost(request: HttpServletRequest, response: HttpServletResponse) {
        try {
            val start = System.nanoTime()
            val x = request.getAttribute("x").toString().toFloat()
            val y = request.getAttribute("y").toString().toFloat()
            val r = request.getAttribute("r").toString().toFloat()


            val timeZoneStr = request.getAttribute("timeZone")?.toString().takeIf { !it.isNullOrEmpty() } ?: "America/Buenos_Aires"
            val zoneId = try {
                ZoneId.of(timeZoneStr)
            } catch (e: Exception) {
                ZoneId.of("Africa/Johannesburg")
            }

            if (Validator.validateX(x) && Validator.validateY(y) && Validator.validateR(r)) {
                var resultsBean = request.session.getAttribute("results") as ResultsBean?
                if (resultsBean == null) {
                    resultsBean = ResultsBean()
                    request.session.setAttribute("results", resultsBean)
                }

                val currentTime = ZonedDateTime.now(zoneId)
                val formatter = DateTimeFormatter.ofPattern("HH:mm:ss z")
                val workTime = currentTime.format(formatter)

                val execTimeValue = "%.5f".format((System.nanoTime() - start).toDouble() / 1_000_000_000)

                val result: ResultsBean.Result = ResultsBean.Result(String.valueOf(x),String.valueOf(y),String.valueOf(r),HitChecker.hit(x, y, r).toString(),workTime,String.valueOf(execTimeValue))

                resultsBean.addResult(result)

                val jsonResponse = mapOf(
                    "x" to result.x,
                    "y" to result.y,
                    "r" to result.r,
                    "value" to result.value,
                    "execTime" to execTimeValue,
                    "time" to result.time
                )

                response.contentType = "application/json; charset=UTF-8"
                response.characterEncoding = "UTF-8"
                val out: PrintWriter = response.writer
                mapper.writeValue(out, jsonResponse)
                out.flush()
            } else {
                response.sendError(HttpServletResponse.SC_BAD_REQUEST, "Недопустимые параметры")
            }
        } catch (e: Exception) {
            response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, e.message)
        }
    }
}
