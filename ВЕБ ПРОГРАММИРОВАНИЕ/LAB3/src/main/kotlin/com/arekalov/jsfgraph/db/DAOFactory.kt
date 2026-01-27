package com.arekalov.jsfgraph.db

class DAOFactory {
    val resultDAO: ResultDAO
        get() {
            if (Companion.resultDAO == null) Companion.resultDAO = ResultDAOImpl()
            return Companion.resultDAO!!
        }

    companion object {
        private var resultDAO: ResultDAO? = null

        var instance: DAOFactory? = null
            get() {
                if (field == null) field = DAOFactory()
                return field
            }
            private set
    }
}