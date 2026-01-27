import java.io.IOException
import java.util.logging.Logger
import javax.servlet.Filter
import javax.servlet.FilterChain
import javax.servlet.FilterConfig
import javax.servlet.ServletException
import javax.servlet.ServletRequest
import javax.servlet.ServletResponse
import javax.servlet.http.HttpServletRequest

class UserAgentCounterFilter : Filter {
    private lateinit var userAgentMap: MutableMap<String, Long>
    private val logger: Logger = Logger.getLogger(this::class.java.name)


    @Throws(IOException::class, ServletException::class)
    override fun doFilter(request: ServletRequest, response: ServletResponse, chain: FilterChain) {
        val httpRequest = request as HttpServletRequest
        val userAgent = httpRequest.getHeader("User-Agent") ?: "Unknown"

        val servletContext = httpRequest.servletContext
        var userAgentMap = servletContext.getAttribute("userAgentMap") as? MutableMap<String, Long>

        if (userAgentMap == null) {
            userAgentMap = mutableMapOf()
            servletContext.setAttribute("userAgentMap", userAgentMap)
        }

        synchronized(userAgentMap) {
            userAgentMap[userAgent] = userAgentMap.getOrDefault(userAgent, 0) + 1
        }

        chain.doFilter(request, response)
    }


}