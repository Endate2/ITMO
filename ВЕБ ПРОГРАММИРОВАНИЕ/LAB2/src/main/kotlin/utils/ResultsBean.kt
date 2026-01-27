package utils

import java.io.Serializable

class ResultsBean : Serializable {
    private val results: MutableList<Result> = mutableListOf()

    fun addResult(result: Result) {
        results.add(result)
    }

    fun getResults(): List<Result> = results

    override fun toString(): String {
        return results.joinToString(separator = "\n") { it.toString() }
    }

    data class Result(
        var x: String,
        var y: String,
        var r: String,
        var value: String,
        var time: String,
        var execTime: String,
    ) : Serializable
}
