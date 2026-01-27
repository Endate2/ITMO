
package com.arekalov.jsfgraph.mbeans

interface PointsCounterMBean {
    fun getTotalPoints(): Int
    fun getMissedPoints(): Int
    fun incrementTotalPoints()
    fun incrementMissedPoints()
    fun resetConsecutiveMisses()
    fun resetAndInitializeCounts()
}