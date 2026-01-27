package com.arekalov.jsfgraph;

import com.arekalov.jsfgraph.db.DAOFactory
import com.arekalov.jsfgraph.entity.ResultEntity
import com.arekalov.jsfgraph.mbeans.MissPercentage
import com.arekalov.jsfgraph.mbeans.MissPercentageMBean
import com.arekalov.jsfgraph.mbeans.PointsCounter
import com.arekalov.jsfgraph.mbeans.PointsCounterMBean
import com.arekalov.jsfgraph.utils.AreaChecker.isInArea
import jakarta.enterprise.context.ApplicationScoped
import jakarta.inject.Named
import lombok.Getter
import lombok.Setter
import lombok.extern.slf4j.Slf4j
import java.io.Serializable
import java.lang.management.ManagementFactory
import java.util.*
import javax.management.Notification
import javax.management.NotificationListener
import javax.management.ObjectName
import javax.management.StandardMBean
import jakarta.annotation.PostConstruct
import jakarta.faces.context.FacesContext
import java.util.ArrayList
import java.util.Locale

@Named
@ApplicationScoped
@Getter
@Setter
@Slf4j
class ResultsControllerBean: Serializable {
    private var xCoordinateBean: XCoordinateBean? = null
    private var yCoordinateBean: YCoordinateBean? = null
    private var rCoordinateBean: RCoordinateBean? = null
    private var pointsCounter: PointsCounterMBean? = null
    private var missPercentage: MissPercentageMBean? = null

    var results = ArrayList<ResultEntity>()

    @PostConstruct
    fun init() {
        var resultsEntities = DAOFactory.instance!!.resultDAO.getAllResults()
        results = java.util.ArrayList<ResultEntity>(resultsEntities)
        // Регистрация MBeans
        try {
            val mbs = ManagementFactory.getPlatformMBeanServer()
            val pointsCounterName = ObjectName("com.worthant.jsfgraph.mbeans:type=PointsCounter")
            pointsCounter = PointsCounter(DAOFactory.instance!!.resultDAO)
            mbs.registerMBean(pointsCounter, pointsCounterName)

            // Регистрация слушателя уведомлений
            val listener =
                NotificationListener { notification: Notification?, handback: Any? -> println("Received notification: " + notification!!.getMessage()) }
            mbs.addNotificationListener(pointsCounterName, listener, null, null)

            missPercentage = MissPercentage(pointsCounter!!)
            val missPercentageName = ObjectName("com.worthant.jsfgraph.mbeans:type=MissPercentage")
            val missPercentageMBean = StandardMBean(missPercentage, MissPercentageMBean::class.java)
            mbs.registerMBean(missPercentageMBean, missPercentageName)
        } catch (e: Exception) {
            e.printStackTrace()
        }
    }

    @Synchronized
    fun addResult(x: Double, y: Double, r: Double) {
        val result = isInArea(x, y, r)
        if (result) {
            pointsCounter!!.incrementTotalPoints()
            pointsCounter!!.resetConsecutiveMisses()
        } else {
            pointsCounter!!.incrementTotalPoints()
            pointsCounter!!.incrementMissedPoints()
        }

        val en = ResultEntity(
            id = 0L,
            x = x,
            y = y,
            r = r,
            result = result
        )

        results.add(en!!)
        DAOFactory.instance!!.resultDAO.addNewResult(en)

        val script = String.format(
            Locale.US, "window.drawDotOnCanvas(%f, %f, %f, %b, true);", x, y, r,
            result
        )
        FacesContext.getCurrentInstance()
            .getPartialViewContext()
            .getEvalScripts()
            .add(script)
    }

    @Synchronized
    fun updateCanvas(r: Double) {
        for (en in results) {
            val result = isInArea(en.x, en.y, r)
            en.r = r
            en.result = result

            val script = String.format(
                Locale.US, "window.drawDotOnCanvas(%f, %f, %f, %b, true);",
                en.x, en.y, r, result
            )
            println("Script: " + script)
            FacesContext.getCurrentInstance()
                .getPartialViewContext()
                .getEvalScripts()
                .add(script)
        }
    }

    @Synchronized
    fun clearResults() {
        DAOFactory.instance!!.resultDAO.clearResults()
        results.clear()
        pointsCounter!!.resetAndInitializeCounts()
    }
}