import Validate from './validate.js';
const validator = new Validate();


const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;

function addRowToTable(res) {
    var table = document.getElementById("res-table");
    var tbody = table.getElementsByTagName("tbody")[0] || table;
    var row = document.createElement("tr");

    var isHit = document.createElement("td");
    var x = document.createElement("td");
    var y = document.createElement("td");
    var r = document.createElement("td");
    var time = document.createElement("td");
    var worktime = document.createElement("td");

    if (res.result === "true") {
        isHit.innerText = "Точно в цель";
    } else {
        isHit.innerText = "Попробуйте ещё раз";
    }

    x.innerText = res.x;
    y.innerText = res.y;
    r.innerText = res.r;
    time.innerText = res.time;
    worktime.innerText = res.workTime;

    row.appendChild(isHit);
    row.appendChild(x);
    row.appendChild(y);
    row.appendChild(r);
    row.appendChild(time);
    row.appendChild(worktime);
    tbody.appendChild(row);

    document.getElementById("dot").setAttribute("cx", String(300 + Number(res.x) * (200 / Number(res.r))));
    document.getElementById("dot").setAttribute("cy", String(300 - Number(res.y) * (200 / Number(res.r))));
    document.getElementById("dot").setAttribute("visibility", "visible");
}

function loadSavedResults() {
    const savedResults = localStorage.getItem("results");
    if (savedResults) {
        try {
            const results = JSON.parse(savedResults);
            results.forEach(result => {
                addRowToTable(result);
            });
        } catch (e) {
            console.error("Ошибка при загрузке сохраненных результатов:", e);
            localStorage.removeItem("results");
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    loadSavedResults();
});

document.getElementById('send-btn').addEventListener('click', function(event) {
    event.preventDefault();
    const x = document.querySelector('input[name="x"]:checked');
    const y = document.querySelector('#coord-y');
    const r = document.querySelector('#coord-r');
    const check = validator.check(x, y, r);

    if (check.allOk) {
        const coords = validator.getCoords();
        fetch(`http://localhost:8080/fcgi-bin/server.jar?x=${coords.x}&y=${coords.y}&r=${coords.r}&timeZone=${encodeURIComponent(timeZone)}`, {
            method: 'GET',
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`${response.status}`);
                }
                return response.json();
            })
            .then(function(res) {
                if (res.error === 'all ok') {
                    document.getElementById("input-log").innerText = '';

                    addRowToTable(res);

                    const savedResults = localStorage.getItem("results");
                    let resultsArray = [];

                    if (savedResults) {
                        resultsArray = JSON.parse(savedResults);
                    }

                    resultsArray.push(res);
                    localStorage.setItem("results", JSON.stringify(resultsArray));
                } else {
                    if (res.error === "fill") {
                        document.getElementById("input-log").innerText = "Заполните все поля";
                    } else if (res.error === "method") {
                        document.getElementById("input-log").innerText = "Только GET запросы";
                    }
                }
            })
            .catch(error => {
                alert(`${error.message}`);
            });
    } else {
        document.getElementById("input-log").innerText = check.log;
    }
});