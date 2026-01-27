package check

class Checker {
    fun hit(x: Int, y: Float, r: Float): Boolean {
        return inRect(x, y, r) || inTriangle(x, y, r) || inCircle(x, y, r)
    }

    private fun inRect(x: Int, y: Float, r: Float): Boolean {
        return x <= 0 && y <= 0 && x >= -r && y >= -r.toFloat() / 2
    }

    private fun inTriangle(x: Int, y: Float, r: Float): Boolean {
        return x >= 0 && y <= 0 && x <= r && y >= -r.toFloat() / 2 && y >= -x - r.toFloat() / 2
    }

    private fun inCircle(x: Int, y: Float, r: Float): Boolean {
        return (x * x + y * y) <= (r * r).toFloat() && x <= 0 && y >= 0
    }
}