package test

import Products.Product
import org.junit.jupiter.api.Test
import org.mockito.Mockito.spy
import org.mockito.Mockito.verify

class ProductGenerationTest {

    @Test
    fun ProvercaId() {
        val product = spy(Product(ignoreInit = true))

        verify(product).generateId()
    }
}