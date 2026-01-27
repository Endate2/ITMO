package com.arekalov.jsfgraph.mbeans

import com.arekalov.jsfgraph.db.ResultDAO
import com.arekalov.jsfgraph.entity.ResultEntity
import java.util.concurrent.atomic.AtomicInteger
import javax.management.Notification
import javax.management.NotificationBroadcasterSupport

class PointsCounter(private val resultDAO: ResultDAO) : NotificationBroadcasterSupport(), PointsCounterMBean {
    private val totalPoints = AtomicInteger(0)
    private val missedPoints = AtomicInteger(0)
    private var consecutiveMisses = 0
    private var sequenceNumber: Long = 1

    init {
        initializeCounts()
    }

    private fun initializeCounts() {
        totalPoints.set(0)
        missedPoints.set(0)
        val results: Collection<ResultEntity> = resultDAO.getAllResults()
        for (result in results) {
            totalPoints.incrementAndGet()
            if (!result.result) {
                missedPoints.incrementAndGet()
            }
        }
    }

    override fun resetAndInitializeCounts() {
        totalPoints.set(0)
        missedPoints.set(0)
        consecutiveMisses = 0
        initializeCounts()
    }


    override fun incrementTotalPoints() {
        totalPoints.incrementAndGet()
    }

    override fun getTotalPoints(): Int = totalPoints.get()
    override fun getMissedPoints(): Int = missedPoints.get()

    override fun incrementMissedPoints() {
        missedPoints.incrementAndGet()
        consecutiveMisses++
        if (consecutiveMisses == 2) {
            val n = Notification(
                "ConsecutiveMisses", this, sequenceNumber++,
                System.currentTimeMillis(), "Two consecutive misses occurred"
            )
            sendNotification(n)
            consecutiveMisses = 0
        }
    }

    override fun resetConsecutiveMisses() {
        consecutiveMisses = 0
    }
}