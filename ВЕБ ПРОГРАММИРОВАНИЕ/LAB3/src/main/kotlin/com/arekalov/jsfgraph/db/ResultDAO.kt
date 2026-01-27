package com.arekalov.jsfgraph.db

import com.arekalov.jsfgraph.entity.ResultEntity

interface ResultDAO {
    fun addNewResult(result: ResultEntity?)

    fun updateResult(result_id: Long?, result: ResultEntity?)

    fun getResultById(result_id: Long?): ResultEntity?

    fun getAllResults(): Collection<ResultEntity>

    fun deleteResult(result: ResultEntity?)

    fun clearResults()
}