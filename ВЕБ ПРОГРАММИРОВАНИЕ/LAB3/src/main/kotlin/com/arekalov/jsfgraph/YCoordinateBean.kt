package com.arekalov.jsfgraph

import jakarta.faces.application.FacesMessage
import jakarta.faces.validator.ValidatorException
import lombok.Data
import lombok.NoArgsConstructor
import java.io.Serializable

@Data
@NoArgsConstructor
class YCoordinateBean : Serializable {
    var y = 0.0

    fun validateYBeanValue(o: Any?) {
        if (o == null) {
            val message: FacesMessage = FacesMessage("Y value should be in (-5, 3) interval")
            throw ValidatorException(message)
        }
    }
}