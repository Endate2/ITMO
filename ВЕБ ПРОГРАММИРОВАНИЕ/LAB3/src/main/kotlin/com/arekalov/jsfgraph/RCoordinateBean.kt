package com.arekalov.jsfgraph

import jakarta.annotation.PostConstruct
import jakarta.faces.application.FacesMessage
import jakarta.faces.component.UIComponent
import jakarta.faces.context.FacesContext
import jakarta.faces.validator.ValidatorException
import java.io.Serializable

class RCoordinateBean : Serializable {
    var r: Double? = 0.0

    fun validateRBeanValue(facesContext: FacesContext, uiComponent: UIComponent, o: Any?) {
        if (o == null) {
            val message = FacesMessage("R value should be in (1, 5) interval")
            throw ValidatorException(message)
        }
    }

    @PostConstruct
    fun init() {
        if (r == null || r == 0.0) {
            r = 3.0
        }
    }
}
