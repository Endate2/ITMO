// InputValidator.kt
package CommandProvider

import Products.OrganizationType
import Products.UnitOfMeasure

object InputValidator {
    /**
     * Функция для валидации ввода целого числа.
     * @param prompt Сообщение, которое будет показано пользователю.
     * @param errorMessage Сообщение об ошибке, если ввод некорректен.
     * @param isValid Функция, которая проверяет, является ли ввод корректным.
     * @return Корректное целое число, введенное пользователем.
     */
    fun validateIntInput(
        prompt: String,
        errorMessage: String,
        isValid: (Int) -> Boolean
    ): Int {
        while (true) {
            try {
                val input = InputManager.readInt(prompt)
                if (isValid(input)) {
                    return input
                }
                OutputManager.println(errorMessage)
            } catch (e: NumberFormatException) {
                OutputManager.println("Ошибка: требуется целое число")
            }
        }
    }

    /**
     * Функция для валидации ввода числа типа Long.
     * @param prompt Сообщение, которое будет показано пользователю.
     * @param errorMessage Сообщение об ошибке, если ввод некорректен.
     * @param isValid Функция, которая проверяет, является ли ввод корректным.
     * @return Корректное число типа Long, введенное пользователем.
     */
    fun validateLongInput(
        prompt: String,
        errorMessage: String,
        isValid: (Long) -> Boolean
    ): Long {
        while (true) {
            try {
                val input = InputManager.readLong(prompt)
                if (isValid(input)) {
                    return input
                }
                OutputManager.println(errorMessage)
            } catch (e: NumberFormatException) {
                OutputManager.println("Ошибка: требуется число типа Long")
            }
        }
    }

    /**
     * Функция для валидации ввода числа типа Double.
     * @param prompt Сообщение, которое будет показано пользователю.
     * @param errorMessage Сообщение об ошибке, если ввод некорректен.
     * @param isValid Функция, которая проверяет, является ли ввод корректным.
     * @return Корректное число типа Double, введенное пользователем.
     */
    fun validateDoubleInput(
        prompt: String,
        errorMessage: String,
        isValid: (Double) -> Boolean
    ): Double {
        while (true) {
            try {
                val input = InputManager.readDouble(prompt)
                if (isValid(input)) {
                    return input
                }
                OutputManager.println(errorMessage)
            } catch (e: NumberFormatException) {
                OutputManager.println("Ошибка: требуется число типа Double")
            }
        }
    }

    /**
     * Функция для валидации ввода строки.
     * @param prompt Сообщение, которое будет показано пользователю.
     * @param errorMessage Сообщение об ошибке, если ввод некорректен.
     * @param isValid Функция, которая проверяет, является ли ввод корректным.
     * @return Корректная строка, введенная пользователем.
     */
    fun validateStringInput(
        prompt: String,
        errorMessage: String,
        isValid: (String) -> Boolean
    ): String {
        while (true) {
            val input = InputManager.readLine(prompt)
            if (isValid(input)) {
                return input
            }
            OutputManager.println(errorMessage)
        }
    }

    // Остальные методы остаются аналогичными, но используют InputManager/OutputManager
    fun validateX(): Int = validateLongInput(
        prompt = "Введите (Long)x > -771: ",
        errorMessage = "Число должно быть больше -771!",
        isValid = { it > -771 }
    ).toInt()

    fun validateY(): Double = validateDoubleInput(
        prompt = "Введите (Double)y < 770: ",
        errorMessage = "Число должно быть меньше 770!",
        isValid = { it < 770 }
    )

    fun validateOrganizationName(): String = validateStringInput(
        prompt = "Введите имя Organization: ",
        errorMessage = "Имя не может быть пустым!",
        isValid = { it.trim().isNotEmpty() }
    )

    fun validateEmployeesCount(): Int = validateIntInput(
        prompt = "Введите 0 < employeesCount: ",
        errorMessage = "Число должно быть больше 0!",
        isValid = { it > 0 }
    )

    fun validateOrganizationType(): OrganizationType = validateIntInput(
        prompt = "Введите номер организации:\n1 - COMMERCIAL\n2 - GOVERNMENT\n3 - PRIVATE_LIMITED_COMPANY\n",
        errorMessage = "Вы указали неправильный номер! Проверьте и введите еще раз.",
        isValid = { it in 1..3 }
    ).let {
        when (it) {
            1 -> OrganizationType.COMMERCIAL
            2 -> OrganizationType.GOVERNMENT
            3 -> OrganizationType.PRIVATE_LIMITED_COMPANY
            else -> throw IllegalArgumentException("Неправильный тип организации")
        }
    }

    fun validateProductName(): String = validateStringInput(
        prompt = "Введите имя: ",
        errorMessage = "Имя не может быть пустой строкой!",
        isValid = { it.trim().isNotEmpty() }
    )

    fun validatePrice(): Int = validateIntInput(
        prompt = "Введите Price (0 < price): ",
        errorMessage = "Цена должна быть больше 0!",
        isValid = { it > 0 }
    )
    fun validateUnitOfMeasureNumber(): Int {
        return validateIntInput(
            prompt = "Введите номер UnitOfMeasure:\n1 - METERS\n2 - SQUARE_METERS\n3 - GRAMS\n4 - MILLIGRAMS\n",
            errorMessage = "Вы указали неправильный номер! Проверьте и введите еще раз.",
            isValid = { it in 1..4 }
        )
    }

    fun validateOrganizationChoice(): Int = validateIntInput(
        prompt = "Если хотите добавить Organization, введите 1 иначе 2: ",
        errorMessage = "Введите 1 или 2!",
        isValid = { it == 1 || it == 2 }
    )

    fun validateUnitOfMeasure(): UnitOfMeasure = validateIntInput(
        prompt = "Введите номер UnitOfMeasure:\n1 - METERS\n2 - SQUARE_METERS\n3 - GRAMS\n4 - MILLIGRAMS\n",
        errorMessage = "Вы указали неправильный номер! Проверьте и введите еще раз.",
        isValid = { it in 1..4 }
    ).let {
        when (it) {
            1 -> UnitOfMeasure.METERS
            2 -> UnitOfMeasure.SQUARE_METERS
            3 -> UnitOfMeasure.GRAMS
            4 -> UnitOfMeasure.MILLIGRAMS
            else -> throw IllegalArgumentException("Неправильный тип единицы измерения")
        }
    }

    fun validateKey(collection: Map<Int, *>): Int = validateIntInput(
        prompt = "Введите key: ",
        errorMessage = "Элемент с таким key уже существует. Попробуйте снова.",
        isValid = { !collection.containsKey(it) }
    )
}