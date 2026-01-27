package com.arekalov.jsfgraph.utils

object AreaChecker {
    fun isInArea(x: Double, y: Double, r: Double): Boolean {
        if (r == 0.0) {
            return false
        }
        if (x >= 0 && y <= 0) {
            return y >= ((x - r))
        } else if (x <= 0 && y >= 0) {
            return x >= -r  && y <= r/2
        } else if (x <= 0 && y <= 0) {
            return (x * x + y * y) <= (r * r)
        }
        return false
    }
}