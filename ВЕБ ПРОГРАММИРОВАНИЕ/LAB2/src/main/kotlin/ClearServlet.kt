import java.io.IOException
import javax.servlet.ServletException
import javax.servlet.http.HttpServlet
import javax.servlet.http.HttpServletRequest
import javax.servlet.http.HttpServletResponse

class ClearServlet : HttpServlet() {

    @Throws(ServletException::class, IOException::class)
    override fun doPost(request: HttpServletRequest, response: HttpServletResponse) {

        request.session.removeAttribute("results")

        response.contentType = "application/json"
        response.characterEncoding = "UTF-8"

        response.writer.print("{\"status\": \"success\", \"message\": \"Результаты очищены\"}")
    }

}