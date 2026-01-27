package utils

import kotlinx.serialization.Serializable

@Serializable
data class Query (val queryType: ExecuteType, val information: String, val args: Map<String, String>) {
    companion object
}
