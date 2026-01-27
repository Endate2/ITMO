package com.arekalov.jsfgraph

import jakarta.faces.application.FacesMessage
import jakarta.faces.validator.ValidatorException
import lombok.Data
import lombok.NoArgsConstructor
import java.io.Serializable

@Data
@NoArgsConstructor
class XCoordinateBean : Serializable {
    var x = 0.0

    fun validateXBeanValue(o: Any?) {
        if (o == null) {
            val message: FacesMessage = FacesMessage("X value should be in (-3, 5) interval")
            throw ValidatorException(message)
        }
    }
}