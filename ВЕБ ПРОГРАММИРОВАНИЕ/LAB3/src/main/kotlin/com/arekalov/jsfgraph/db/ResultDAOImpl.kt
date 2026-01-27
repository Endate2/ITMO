package com.arekalov.jsfgraph.db

import com.arekalov.jsfgraph.entity.ResultEntity
import jakarta.persistence.EntityManager


class ResultDAOImpl : ResultDAO {
    private val entityManager: EntityManager = JPAUtils.factory!!.createEntityManager()

    override fun addNewResult(result: ResultEntity?) {
        entityManager.getTransaction().begin()
        entityManager.persist(result)
        entityManager.getTransaction().commit()
    }

    override fun updateResult(result_id: Long?, result: ResultEntity?) {
        entityManager.getTransaction().begin()
        entityManager.merge<ResultEntity?>(result)
        entityManager.getTransaction().commit()
    }

    override fun getResultById(result_id: Long?): ResultEntity? {
        return entityManager.getReference<ResultEntity?>(ResultEntity::class.java, result_id)
    }

    override fun getAllResults(): Collection<ResultEntity> {
        val query = entityManager.createQuery("SELECT r FROM ResultEntity r", ResultEntity::class.java)
        return query.resultList
    }

    override fun deleteResult(result: ResultEntity?) {
        entityManager.getTransaction().begin()
        entityManager.remove(result)
        entityManager.getTransaction().commit()
    }

    override fun clearResults() {
        entityManager.getTransaction().begin()
        try {
            val query = entityManager.createQuery("DELETE FROM ResultEntity r")
            query.executeUpdate()
            entityManager.getTransaction().commit()
        } catch (e: Exception) {
            if (entityManager.getTransaction().isActive()) {
                entityManager.getTransaction().rollback()
            }
            throw e // Or handle the exception as needed
        } finally {
            entityManager.clear()
        }
    }
}