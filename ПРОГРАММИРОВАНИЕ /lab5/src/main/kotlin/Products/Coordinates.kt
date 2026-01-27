// Coordinates.kt
package Products

import java.io.Serializable
import CommandProvider.InputValidator

class Coordinates(
    private val ignoreInit: Boolean = false,
    private val validator: InputValidator = InputValidator
) : Serializable {
    var x: Int = 0
    var y: Double = 0.0

    init {
        if (!ignoreInit) {
            x = validator.validateX()
            y = validator.validateY()
        }
    }

    override fun toString(): String = "x: $x, y: $y"
}