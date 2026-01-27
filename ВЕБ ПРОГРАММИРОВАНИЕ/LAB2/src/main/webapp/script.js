
function getUserAgent() {
    return navigator.userAgent;
}

document.getElementById('valForm').addEventListener('submit', function (e) {
    e.preventDefault();

    let xCheckboxes = document.querySelectorAll('input[name="x"]:checked');
    let y = document.getElementById('y');
    let r = document.getElementById('r');

    let yValue = y.value.replace(',', '.');

    if (validate(xCheckboxes, y, r)) {
        let xValue = xCheckboxes[0].value;
        send(xValue, yValue, r.value, "form");
    }
});

function getCurrentTimeZone() {
    return Intl.DateTimeFormat().resolvedOptions().timeZone;
}


document.getElementById('r').addEventListener('change', function (e) {
    document.querySelectorAll("circle").forEach(point => point.remove());
    drawPoints(clickedPoints);
});

document.getElementById('clear_table').addEventListener('click', function (e) {
    document.querySelectorAll("circle").forEach(point => point.remove());
    document.getElementById("resultBody").querySelectorAll("tr").forEach(row => row.remove());
    fetch("/lab2/clear", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        }
    })
        .then(response => response.json())
        .then(data => {
            console.log("Сервер подтвердил очистку:", data);
            clickedPoints = [];
        })
        .catch(error => console.error('Error:', error));
});

document.getElementById('area').addEventListener('click', function (e) {
    const point = document.getElementById('graph').createSVGPoint();
    point.x = e.clientX;
    point.y = e.clientY;

    const svgPoint = point.matrixTransform(document.getElementById('graph').getScreenCTM().inverse());
    let r = document.getElementById("r");
    if (r.value !== "") {
        let userPointX = ((svgPoint.x - 150) / 100 * r.value).toFixed(2);
        let userPointY = ((150 - svgPoint.y) / 100 * r.value).toFixed(2);
        console.log(`Координаты на плоскости: x=${userPointX}, y=${userPointY}, Координаты в svg: (${svgPoint.x.toFixed(2)}, ${svgPoint.y.toFixed(2)})`);
        send(userPointX, userPointY, r.value, "click");
    } else {
        showError(document.getElementById('graph'), "Необходимо выбрать значение радиуса");
    }
})


document.querySelectorAll("input[name='x']").forEach(checkbox => {
    checkbox.addEventListener('change', function () {
        if (this.checked) {
            document.querySelectorAll("input[name='x']").forEach(cb => {
                if (cb !== this) cb.checked = false;
            });
        }
    });
});

function send(x, y, r, flag) {
    const data = JSON.stringify({
        x: x,
        y: y,
        r: r,
        flag: flag,
        timeZone: getCurrentTimeZone(),
        userAgent: getUserAgent()
    });
    fetch("/lab2/controller", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: data
    })
        .then(response => response.json())
        .then(data => {
            saveResponseToLocalStorage(data);
            showResponse(data);
            drawPoint(data.x, data.y, data.r, data.value);
        }).catch(error => console.error('Error:', error));
}
document.getElementById('get_stats').addEventListener('click', function (e) {
    fetch("/lab2/stats")
        .then(response => response.json())
        .then(data => {
            displayStats(data);
        })
        .catch(error => console.error('Error:', error));
});


function saveResponseToLocalStorage(response) {
    clickedPoints.push({x: response.x, y: response.y, r: response.r, value: response.value, time: response.time, execTime: response.execTime});
}

function showResponse(response) {
    const resultBody = document.getElementById('resultBody');
    const newRow = document.createElement('tr');

    newRow.innerHTML = `
            <td>${response.x}</td>
            <td>${response.y}</td>
            <td>${response.r}</td>
            <td>${response.value !== undefined ? (response.value === "true" ? 'попал' : 'попробуй еще раз') : 'undefined'}</td>
            <td>${response.time !== undefined ? response.time : 'undefined'}</td>
            <td>${response.execTime !== undefined ? response.execTime : 'undefined'}</td>
            `;

    resultBody.appendChild(newRow);
}

function showError(element, message) {
    const errorElement = document.createElement('div');
    errorElement.classList.add('error-message');
    errorElement.textContent = message;
    errorElement.style.color = 'red';
    errorElement.style.fontSize = '13px';
    errorElement.style.textAlign = 'center';
    element.parentNode.insertBefore(errorElement, element.nextSibling);
    setTimeout(function () {
        errorElement.remove();
    }, 3000);
}

function validate(xCheckboxes, y, r) {
    if (xCheckboxes.length === 0) {
        showError(document.querySelector('.x-checkbox'), "Необходимо выбрать значение координаты X :(");
        console.warn("No X value selected");
        return false;
    }

    if (xCheckboxes.length > 1) {
        showError(document.querySelector('.x-checkbox'), "Нужно выбрать только 1 значение X :(");
        console.warn("Multiple X values selected:", xCheckboxes.length);
        return false;
    }

    if (!y.value) {
        showError(y, "Необходимо ввести значение координаты Y :(");
        console.warn("Empty Y value");
        return false;
    }

    if (!/^(-?\d+([,.]\d{1,5})?)$/.test(y.value)){
        showError(y, "Y должен быть числом :( и содержать максимум 5 знаков в дробной части");
        console.warn("Invalid Y value (not a number):", y.value);
        return false;
    }
    let yNum = parseFloat(y.value.replace(',', '.'));
    if (yNum < -3 || yNum > 5) {
        showError(y, "Y должен быть в диапазоне от -3 до 5 :(");
        console.warn("Y value out of range:", y.value);
        return false;
    }

    if (r.value === "") {
        showError(r, "Необходимо выбрать значение радиуса :(");
        console.warn("Invalid R value:", r.value);
        return false;
    }

    return true;
}

function resetForm() {
    document.getElementById("valForm").reset();
}

function drawPoint(x, y, r, hit) {
    let svgPointX = 100 * parseFloat(x) / r + 150;
    let svgPointY = 150 - parseFloat(y) * 100 / r;
    let svg = document.getElementById("graph");
    let dot = document.createElementNS("http://www.w3.org/2000/svg", "circle");
    dot.setAttribute("cx", svgPointX.toString());
    dot.setAttribute("cy", svgPointY.toString());
    dot.setAttribute("r", "4");
    dot.setAttribute("fill", hit === "true" ? "green" : "red");
    svg.appendChild(dot);
}

function drawPoints(points) {
    let r = document.getElementById('r').value;
    if (r === "") return;

    for (let i = 0; i < points.length; i++) {
        let point = points[i];
        if (Math.abs(point.x) < r * 1.5 && Math.abs(point.y) < r * 1.5) {
            drawPoint(point.x, point.y, r, point.value);
        }
    }
}
function displayStats(stats) {
    // Создаем модальное окно или отображаем в консоли
    const statsDiv = document.createElement('div');
    statsDiv.style.position = 'fixed';
    statsDiv.style.top = '50%';
    statsDiv.style.left = '50%';
    statsDiv.style.transform = 'translate(-50%, -50%)';
    statsDiv.style.backgroundColor = 'white';
    statsDiv.style.padding = '20px';
    statsDiv.style.border = '2px solid black';
    statsDiv.style.zIndex = '1000';

    let html = '<h3>Статистика User-Agent</h3>';
    html += `<p>Всего уникальных user-agent'ов: ${stats.totalUniqueUserAgents}</p>`;
    html += '<table border="1"><tr><th>User-Agent</th><th>Количество</th></tr>';

    for (const [ua, count] of Object.entries(stats.userAgentStats)) {
        html += `<tr><td style="max-width: 400px; word-wrap: break-word;">${ua}</td><td>${count}</td></tr>`;
    }

    html += '</table>';
    html += '<button onclick="this.parentElement.remove()">Закрыть</button>';

    statsDiv.innerHTML = html;
    document.body.appendChild(statsDiv);
}

window.onload = () => {
    for (let i = 0; i < clickedPoints.length; i++) {
        showResponse(clickedPoints[i]);
    }
    drawPoints(clickedPoints);
}