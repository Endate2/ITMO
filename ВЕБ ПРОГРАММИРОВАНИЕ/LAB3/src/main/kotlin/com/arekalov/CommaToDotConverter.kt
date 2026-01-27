package com.arekalov

import jakarta.faces.convert.Converter
import jakarta.faces.convert.FacesConverter
import jakarta.faces.component.UIComponent
import jakarta.faces.context.FacesContext
import jakarta.faces.convert.ConverterException


@FacesConverter("com.arekalov.CommaToDotConverter")
class CommaToDotConverter : Converter<Double> {

    override fun getAsObject(context: FacesContext?, component: UIComponent?, value: String?): Double? {
        if (value == null) return null
        return try {
            value.replace(',', '.').toDouble()
        } catch (e: NumberFormatException) {
            throw ConverterException("Invalid format")
        }
    }

    override fun getAsString(context: FacesContext?, component: UIComponent?, value: Double?): String {
        return value?.toString() ?: ""
    }
}
