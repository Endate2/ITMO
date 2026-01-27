package com.arekalov.jsfgraph.mbeans

class MissPercentage(pointsCounter: PointsCounterMBean) : MissPercentageMBean {
    private val pointsCounter: PointsCounterMBean

    init {
        this.pointsCounter = pointsCounter
    }

    override val missPercentage: Double
        get() {
            val totalPoints: Int = pointsCounter.getTotalPoints()
            val missedPoints: Int = pointsCounter.getMissedPoints()
            if (totalPoints == 0) {
                return 0.0
            }
            return missedPoints.toDouble() / totalPoints * 100
        }
}