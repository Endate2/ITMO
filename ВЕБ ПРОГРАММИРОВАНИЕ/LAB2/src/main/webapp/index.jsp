<%@page contentType="text/html; charset=UTF-8" %>
<%@taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<jsp:useBean id="results" scope="session" class="utils.ResultsBean"/>
<html>
<style>
    <%@include file="style.css" %>
</style>
<head><title>lab2</title>
    <meta charset="UTF-8">
</head>
<body>
<div class="header">
    <h1>
        Чимирев Антон Олегович
    </h1>
    <h3>Группа: P3222</h3>
    <h3>Номер варианта:541221</h3>
</div>
<div class="form-block">
    <script>

        clickedPoints = [
            <c:forEach var="result" items="${results.results}">
            {
                x: "${result.x}",
                y: "${result.y}",
                r: "${result.r}",
                value: "${result.value}",
                time: "${result.time}",
                execTime:"${result.execTime}"
            },
            </c:forEach>
        ];
    </script>
    <div class="graph" id="area">
        <svg height="300px" width="300px" id="graph" xmlns="http://www.w3.org/2000/svg">

            <!-- Оси со стрелками -->
            <line x1="0" x2="300" y1="150" y2="150"></line>
            <line x1="150" x2="150" y1="0" y2="300"></line>
            <polygon class="arrow" points="150,0 144,15 156,15"></polygon>
            <polygon class="arrow" points="300,150 285,156 285,144"></polygon>

            <!-- Засечки -->
            <line x1="200" x2="200" y1="155" y2="145"></line>
            <line x1="250" x2="250" y1="155" y2="145"></line>

            <line x1="50" x2="50" y1="155" y2="145"></line>
            <line x1="100" x2="100" y1="155" y2="145"></line>

            <line x1="145" x2="155" y1="100" y2="100"></line>
            <line x1="145" x2="155" y1="50" y2="50"></line>

            <line x1="145" x2="155" y1="200" y2="200"></line>
            <line x1="145" x2="155" y1="250" y2="250"></line>

            <!-- Подписи к засечкам    -->
            <text x="195" y="140">R/2</text>
            <text x="248" y="140">R</text>

            <text x="40" y="140">-R</text>
            <text x="90" y="140">-R/2</text>

            <text x="160" y="105">R/2</text>
            <text x="160" y="55">R</text>

            <text x="160" y="205">-R/2</text>
            <text x="160" y="255">-R</text>

            <text x="160" y="15">Y</text>
            <text x="285" y="140">X</text>

            <!-- Квадрат (4 четверть) -->
            <rect class="area" x="50" y="50" width="100" height="100"></rect>

            <!-- Треугольник (3 четверть) -->

            <polygon class="area" points="250,150 150,100 150,150"></polygon>

            <path class="area" d="M 50 150 A 100, 100 0, 0,0, 150, 250 L 150 150 Z"></path>

        </svg>
    </div>



    <div id="message"></div>
    <form id="valForm">
        <label id="y_label">Координата X:</label>
        <div class="x-checkbox">
            <label><input type="checkbox" name="x" value="-3">-3</label>
            <label><input type="checkbox" name="x" value="-2">-2</label>
            <label><input type="checkbox" name="x" value="-1">-1</label>
            <label><input type="checkbox" name="x" value="0">0</label>
            <label><input type="checkbox" name="x" value="1">1</label>
            <label><input type="checkbox" name="x" value="2">2</label>
            <label><input type="checkbox" name="x" value="3">3</label>
            <label><input type="checkbox" name="x" value="4">4</label>
            <label><input type="checkbox" name="x" value="5">5</label>
        </div>

        <label for="y">Координата Y:</label>
        <label><input type="text" id="y" placeholder="{-3...5}" maxlength="10"></label>

        <label for="r">Радиус R:</label>
        <select name="rad" id="r">
            <option value="">Необходимо выбрать радиус</option>
            <option value="1">1</option>
            <option value="1.5">1.5</option>
            <option value="2">2</option>
            <option value="2.5">2.5</option>
            <option value="3">3</option>
        </select>
        <table class="buttons">
            <tr>
                <th><input type="submit" class="data-button" value="Отправить данные"></th>
                <th><input type="reset" class="data-button" onclick="resetForm()" value="Сбросить данные"></th>
            </tr>
            <tr>
                <th colspan="2" style="place-items: center"><input type="button" class="data-button"
                                                                   value="Сбросить результаты" id="clear_table"></th>
            </tr>
            <tr>
                <th colspan="2" style="place-items: center"><input type="button" class="data-button"
                                                                   value="Статистика User Agent" id="get_stats"></th>
            </tr>
        </table>


    </form>
</div>
<div class="table-block">
    <table id="result_table" cellspacing="6" cellpadding="15">
        <caption><b>Результаты</b></caption>
        <thead>
        <tr>
            <th>Координата X</th>
            <th>Координата Y</th>
            <th>Радиус R</th>
            <th>Факт попадания в область</th>
            <th>Текущее время</th>
            <th>Время выполнения скрипта (ms)</th>
        </tr>
        </thead>
        <tbody id="resultBody">
        </tbody>
    </table>
</div>
<script src="script.js"></script>
</body>
</html>