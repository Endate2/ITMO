package utils

object Validator {
    fun validateX(x: Float): Boolean = x in -3f..5f

    fun validateY(y: Float): Boolean = y in -3f..5f

    fun validateR(r: Float): Boolean = r in 1f..3f
}
