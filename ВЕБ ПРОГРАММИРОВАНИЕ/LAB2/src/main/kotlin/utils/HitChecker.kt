package utils

object HitChecker {
    fun hit(x: Float, y: Float, r: Float): Boolean {
        return if (x > 0 && y < 0) {
            false
        } else if (x <= 0 && y <= 0) {
            (x * x + y * y <= r * r)
        } else if (x <= 0) {
            x <= r && kotlin.math.abs(y) <= r
        } else {
            kotlin.math.abs(x) <= r  && y <= -1/2*x + r / 2
        }
    }
}
