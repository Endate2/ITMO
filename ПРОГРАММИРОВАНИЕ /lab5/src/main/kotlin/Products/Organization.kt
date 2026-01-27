package Products

import java.io.Serializable
import CommandProvider.InputValidator  // Добавляем импорт InputValidator

class Organization : Serializable {
    var id: Int = 0
    var name: String? = null
    var employeesCount: Int = 0
    var organizationType: OrganizationType? = null

    /**
     * Инициализирует организацию с валидацией ввода
     * @param validator Валидатор ввода (по умолчанию используется InputValidator)
     */
    fun initialize(validator: InputValidator = InputValidator) {
        generateId()
        name = validator.validateOrganizationName()
        employeesCount = validator.validateEmployeesCount()
        organizationType = validator.validateOrganizationType()
    }

    fun generateId() {
        this.id = (Math.random() * 1_000_000).toInt() + 1
    }

    /**
     * Конвертирует OrganizationType в числовое представление
     */
    fun reviewOrganizationType(): Int? = when (organizationType) {
        OrganizationType.COMMERCIAL -> 1
        OrganizationType.GOVERNMENT -> 2
        OrganizationType.PRIVATE_LIMITED_COMPANY -> 3
        else -> null
    }

    /**
     * Устанавливает тип организации по числовому значению
     * @param x Числовое представление типа (1-3)
     * @throws IllegalArgumentException при некорректном значении
     */
    fun inputOrganizationType(x: Int) {
        organizationType = when (x) {
            1 -> OrganizationType.COMMERCIAL
            2 -> OrganizationType.GOVERNMENT
            3 -> OrganizationType.PRIVATE_LIMITED_COMPANY
            else -> throw IllegalArgumentException("Неправильный тип организации")
        }
    }

    override fun toString(): String {
        return """
            |   name: $name
            |   employeesCount: $employeesCount
            |   OrganizationType: $organizationType
            |   Id: $id
        """.trimMargin()
    }

    companion object {
        /**
         * Создает организацию с готовыми значениями
         */
        fun createWithValues(
            id: Int,
            name: String?,
            employeesCount: Int,
            type: OrganizationType?
        ): Organization {
            return Organization().apply {
                this.id = id
                this.name = name
                this.employeesCount = employeesCount
                this.organizationType = type
            }
        }
    }
}