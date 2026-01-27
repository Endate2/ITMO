package Products

import java.io.Serializable
import java.time.LocalDateTime
import CommandProvider.InputValidator

open class Product(private val ignoreInit: Boolean = false) : Serializable, Comparable<Product> {
    var id: Int = (Math.random() * 1_000_000).toInt() + 1
        private set

    var name: String? = null
    var coordinates: Coordinates? = null
    var date: LocalDateTime = LocalDateTime.now()
    var price: Int = 0
    var unitOfMeasure: UnitOfMeasure? = null
    var organization: Organization? = null

    // Внедряем зависимость от валидатора
    private val validator: InputValidator = InputValidator

    init {
        if (!ignoreInit) {
            initializeProduct()
            println("Product $name создан")
        }
    }

    fun generateId() {
        this.id = (Math.random() * 1_000_000).toInt() + 1
    }

    private fun initializeProduct() {
        inputName()
        inputUnitOfMeasure()
        inputPrice()
        inputCoordinates()
        inputOrganization()
    }

    fun inputCoordinates(coordinates: Coordinates? = null) {
        this.coordinates = coordinates ?: Coordinates().also {
            it.x = validator.validateX()
            it.y = validator.validateY()
        }
    }

    fun inputOrganization() {
        when (validator.validateOrganizationChoice()) {
            1 -> Organization().also {
                it.initialize(validator)
                organization = it
            }
            2 -> organization = null
        }
    }

    fun inputUnitOfMeasure(unit: UnitOfMeasure? = null) {
        unitOfMeasure = unit ?: validator.validateUnitOfMeasure()
    }

    fun inputPrice(price: Int? = null) {
        this.price = price ?: validator.validatePrice()
    }

    fun inputName(name: String? = null) {
        this.name = name ?: validator.validateProductName()
    }

    fun updateUnitOfMeasure(x: Int) {
        unitOfMeasure = when (x) {
            1 -> UnitOfMeasure.METERS
            2 -> UnitOfMeasure.SQUARE_METERS
            3 -> UnitOfMeasure.GRAMS
            4 -> UnitOfMeasure.MILLIGRAMS
            else -> throw IllegalArgumentException("Invalid unit of measure type")
        }
    }

    fun updateDate(date: LocalDateTime) {
        this.date = date
    }

    val unitOfMeasureCode: Int?
        get() = when (unitOfMeasure) {
            UnitOfMeasure.METERS -> 1
            UnitOfMeasure.SQUARE_METERS -> 2
            UnitOfMeasure.GRAMS -> 3
            UnitOfMeasure.MILLIGRAMS -> 4
            else -> null
        }

    override fun toString(): String {
        return """
            |Name: $name
            |ID: $id
            |Coordinates: $coordinates
            |Date: $date
            |Price: $price
            |Unit of Measure: $unitOfMeasure
            |Organization: $organization
        """.trimMargin()
    }

    override fun compareTo(other: Product): Int = id.compareTo(other.id)

    companion object {
        fun createWithValues(
            id: Int,
            name: String?,
            coordinates: Coordinates?,
            date: LocalDateTime,
            price: Int,
            unitOfMeasure: UnitOfMeasure?,
            organization: Organization?
        ): Product {
            return Product(ignoreInit = true).apply {
                this.id = id
                this.name = name
                this.coordinates = coordinates
                this.date = date
                this.price = price
                this.unitOfMeasure = unitOfMeasure
                this.organization = organization
            }
        }
    }
}