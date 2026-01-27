package validation

class Validate {
    private val xRange = listOf(-5, -4, -3, -2, -1, 0, 1, 2, 3)
    private var log = "all ok"

    fun check(x: Int?, y: Float?, r: Float?): Boolean {
        if (x == null || y == null || r == null) {
            log = "Input values must not be null"
            return false
        }
        return checkX(x) && checkY(y) && checkR(r)
    }

    fun getErr(): String = log

    fun checkX(x: Int): Boolean {
        return if (xRange.contains(x)) {
            true
        } else {
            log = "X must be selected"
            false
        }
    }

    fun checkY(y: Float): Boolean {
        return if (y in -3.0..5.0) {
            true
        } else {
            log = "Y value must be -3<=y<=5"
            false
        }
    }

    fun checkR(r: Float): Boolean {
        return if (r in 1.0..4.0) {
            true
        } else {
            log = "R value must be 1<=y<=4"
            false
        }
    }

}
