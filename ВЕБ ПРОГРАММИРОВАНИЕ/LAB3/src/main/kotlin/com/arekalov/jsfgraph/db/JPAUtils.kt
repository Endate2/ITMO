package com.arekalov.jsfgraph.db

import jakarta.persistence.EntityManagerFactory
import jakarta.persistence.Persistence
import lombok.Getter
import java.util.*


object JPAUtils {
    @Getter
    var factory: EntityManagerFactory? = null

    init {
        try {
            val info = Properties()
            info.load(JPAUtils::class.java.getClassLoader().getResourceAsStream("/db.cfg"))
            factory = Persistence.createEntityManagerFactory("default", info)
        } catch (ex: Throwable) {
            System.err.println("Something went wrong during initializing EclipseLink: " + ex)
            throw ExceptionInInitializerError()
        }
    }
}