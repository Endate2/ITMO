import { updateDateTime, parseR } from './utils.js';

import {
    setupCanvas,
    getCanvas,
    getContext,
    getWidth,
    getHeight,
    getDynamicScalingFactor,
    getK,
    getGraphSetup,
    getDrawModeState,
    getMagnetModeState,
    setWidth,
    setHeight,
    setDynamicScalingFactor,
    setK,
    setDrawModeState,
    setMagnetModeState
} from "./canvas-setup.js";

$(document).ready(() => {
    updateDateTime();
    setInterval(updateDateTime, 11000);
    setupSmoothScrolling();
    setupZoomButtons();
    setupGraphModes();
    setupParallax();
});

window.onload = () => {
    const r = 3;
    setupCanvas(r);
    attachCanvasListeners();
    drawGraph(r);
    restorePoints();
};

window.drawGraph = function drawGraph(R) {
    setDynamicScalingFactor(R);
    const { ctx, width, height, k, dynamicScalingFactor } = getGraphSetup();

    const yAxisOffset = 15;
    const xAxisStartX = (width / 2) - ((width / 4) * k);
    const xAxisEndX = (width / 2) + ((width / 4) * k);
    const yAxisStartY = (height / 2) + ((height / 4) * k);
    const yAxisEndY = (height / 2) - ((height / 4) * k);

    ctx.clearRect(0, 0, width, height);
    ctx.font = "15px Arial";
    ctx.strokeStyle = "gray";
    ctx.lineWidth = 1;

    drawAxis(ctx, xAxisStartX, height / 2, xAxisEndX, height / 2, k);
    drawAxis(ctx, width / 2, yAxisStartY, width / 2, yAxisEndY, k);

    ctx.fillStyle = "#FFFF0010";
    ctx.beginPath();
    ctx.moveTo(width / 2, height / 2);
    ctx.lineTo(width / 2, height / 2 + R  * dynamicScalingFactor);
    ctx.lineTo(width / 2 + R * dynamicScalingFactor, height / 2);
    ctx.closePath();
    ctx.fill();
    ctx.strokeStyle = "#FFFF00";
    ctx.stroke();

    ctx.fillStyle = "#0000FF10";
    ctx.fillRect(width / 2, height / 2, -R * dynamicScalingFactor, -R/2 * dynamicScalingFactor);
    ctx.strokeStyle = "#0000FF";
    ctx.strokeRect(width / 2, height / 2, -R * dynamicScalingFactor, -R/2 * dynamicScalingFactor);

    ctx.fillStyle = "#39FF1410";
    ctx.beginPath();
    ctx.arc(width / 2, height / 2, R * dynamicScalingFactor, Math.PI, 0.5 * Math.PI, true);
    ctx.lineTo(width / 2, height / 2);
    ctx.closePath();
    ctx.fill();
    ctx.strokeStyle = "#39FF14";
    ctx.stroke();

    ctx.fillStyle = "white";

    ctx.fillText(R.toString(), width / 2 + R * dynamicScalingFactor, height / 2 + 30);
    ctx.fillText((R / 2).toString(), width / 2 + (R / 2) * dynamicScalingFactor, height / 2 + 30);
    ctx.fillText((-R).toString(), width / 2 - R * dynamicScalingFactor, height / 2 + 30);
    ctx.fillText((-R / 2).toString(), width / 2 - (R / 2) * dynamicScalingFactor, height / 2 + 30);
    ctx.fillText("X", width / 2 + (3.12 * R / 2) * dynamicScalingFactor, height / 2 + 5);

    ctx.fillText(R.toString(), width / 2 + yAxisOffset, height / 2 - R * dynamicScalingFactor);
    ctx.fillText((R / 2).toString(), width / 2 + yAxisOffset, height / 2 - (R / 2) * dynamicScalingFactor);
    ctx.fillText((-R).toString(), width / 2 + yAxisOffset, height / 2 + R * dynamicScalingFactor);
    ctx.fillText((-R / 2).toString(), width / 2 + yAxisOffset, height / 2 + (R / 2) * dynamicScalingFactor);
    ctx.fillText("Y", width / 2 - 5, height / 2 - (3.15 * R / 2) * dynamicScalingFactor);


    ctx.fillStyle = "white";

    const tickLength = 10;
    for (let tickValue = -R; tickValue <= R; tickValue += R / 2) {
        const xTickPosition = width / 2 + tickValue * dynamicScalingFactor;
        ctx.beginPath();
        ctx.moveTo(xTickPosition, height / 2 - tickLength / 2);
        ctx.lineTo(xTickPosition, height / 2 + tickLength / 2);
        ctx.stroke();
    }


    for (let tickValue = -R; tickValue <= R; tickValue += R / 2) {
        const yTickPosition = height / 2 - tickValue * dynamicScalingFactor;
        ctx.beginPath();
        ctx.moveTo(width / 2 - tickLength / 2, yTickPosition);
        ctx.lineTo(width / 2 + tickLength / 2, yTickPosition);
        ctx.stroke();
    }
}

export function drawAxis(context, fromX, fromY, toX, toY, k) {
    const headLength = 10 * k;
    const angle = Math.atan2(toY - fromY, toX - fromX);

    context.beginPath();
    context.moveTo(fromX, fromY);
    context.lineTo(toX, toY);
    context.lineTo(toX - headLength * Math.cos(angle - Math.PI / 6), toY - headLength * Math.sin(angle - Math.PI / 6));
    context.moveTo(toX, toY);
    context.lineTo(toX - headLength * Math.cos(angle + Math.PI / 6), toY - headLength * Math.sin(angle + Math.PI / 6));
    context.stroke();
} // отрисовка стрелочек

export function translateCanvasCoordsToReal(canvasX, canvasY) {
    const width = getWidth();
    const height = getHeight();
    const dynamicScalingFactor = getDynamicScalingFactor();

    let graphX = (canvasX - width / 2) / dynamicScalingFactor;
    let graphY = (height / 2 - canvasY) / dynamicScalingFactor;
    return { x: graphX, y: graphY };
} // преобразование координат

export function translateRealCoordsToCanvas(x, y) {
    const width = getWidth();
    const height = getHeight();
    const dynamicScalingFactor = getDynamicScalingFactor();

    let canvasX = x * dynamicScalingFactor + width / 2;
    let canvasY = height / 2 - y * dynamicScalingFactor;
    return { canvasX, canvasY };
} // преобразование координат

export function setupSmoothScrolling() {
    $("#scrollToTop").click(() => {
        $('html, body').animate({ scrollTop: 0 }, 'smooth');
    });


    $("#scrollToTable").click(() => {
        $("#scrollToTable").get(0).scrollIntoView({ behavior: "smooth" });
    });
} // плавный скроллинг

export function attachCanvasListeners() {
    let debounceTimer;
    const canvas = getCanvas();
    if (!canvas) {
        console.error("Canvas element not found!");
        return;
    }

    canvas.addEventListener("click", (e) => {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;

        let canvasX = (e.clientX - rect.left) * scaleX;
        let canvasY = (e.clientY - rect.top) * scaleY;

        let { x, y } = translateCanvasCoordsToReal(canvasX, canvasY);

        // Snap coordinates if magnet mode is active
        if (getMagnetModeState()) {
            x = snapToGrid(x);
            y = snapToGrid(y);
        }

        ({ canvasX, canvasY } = translateRealCoordsToCanvas(x, y));
        drawDotOnCanvas(canvasX, canvasY);

        console.log(x, y);
        sendCoords(x, y);
    });

    window.addEventListener("resize", () => {
        setWidth(canvas.width);
        setHeight(canvas.height);

        drawGraph(parseR());
        restorePoints();
    });

    canvas.addEventListener("mousemove", (e) => {
        if (!getDrawModeState()) return;

        if (debounceTimer) clearTimeout(debounceTimer);

        debounceTimer = setTimeout(() => {
            const boundingRect = canvas.getBoundingClientRect();
            const scaleMouseX = canvas.width / boundingRect.width;
            const scaleMouseY = canvas.height / boundingRect.height;

            const mouseX = (e.clientX - boundingRect.left) * scaleMouseX;
            const mouseY = (e.clientY - boundingRect.top) * scaleMouseY;

            // Convert mouse coordinates to real graph coordinates
            let { x, y } = translateCanvasCoordsToReal(mouseX, mouseY);

            // Snap coordinates if magnet mode is active
            if (getMagnetModeState()) {
                x = snapToGrid(x);
                y = snapToGrid(y);
            }

            const { canvasX, canvasY } = translateRealCoordsToCanvas(x, y);

            drawDot(canvasX, canvasY);

            sendCoords(x, y);
        }, 30);
    });
}

window.sendCoords = function sendCoords(x, y) {
    $('#graph-form\\:xGraphValue').val(x);
    $('#graph-form\\:yGraphValue').val(y);
    console.log("sending coords to the server...");
    $('#graph-form\\:handleClick').click();
}

export function snapToGrid(value) {
    const step = parseR() / 2;
    return Math.round(value / step) * step;
}

function drawDot(x, y) {
    const canvas = document.getElementById("graphCanvas");
    const ctx = canvas.getContext("2d");

    // Set the style for the dot
    ctx.fillStyle = "rgba(128, 128, 128, 0.5)"; // Semi-transparent gray

    // Draw the dot
    ctx.beginPath();
    ctx.arc(x, y, 5, 0, Math.PI * 2);
    ctx.fill();
}

window.drawDotOnCanvas = function drawDotOnCanvas(x, y, r, result, isRealCoords = false) {
    const ctx = getContext();
    let canvasX, canvasY;
    console.log(x, y, r, result, isRealCoords);
    console.log("drawing");

    if (isRealCoords) {
        // setDynamicScalingFactor(r);
        ({ canvasX, canvasY } = translateRealCoordsToCanvas(x, y));
        console.log("Translation: ", x, y);
    } else {
        canvasX = x;
        canvasY = y;
    }

    console.log("Coords: ", canvasX, canvasY);

    ctx.fillStyle = result === undefined ? "gray" : result ? "green" : "red";
    ctx.beginPath();
    ctx.arc(canvasX, canvasY, 5, 0, Math.PI * 2);
    ctx.fill();
}

export function setupZoomButtons() {
    $("#zoomInBtn").on("click", () => {
        setK(getK()*1.1);

        drawGraph(parseR());
        restorePoints();
    });
    $("#zoomOutBtn").on("click", () => {
        setK(getK()/1.1);

        drawGraph(parseR());
        restorePoints();
    });
    $("#restoreZoom").on("click", () => {
        setK(1.7); // base scaling constant

        drawGraph(parseR());
        restorePoints();
    });
}

export function setupGraphModes() {
    $("#drawModeBtn").click(() => {
        setDrawModeState(!getDrawModeState());
        $("#drawModeBtn").toggleClass("pressed");
    });
    $("#magnetModeBtn").click(() => {
        setMagnetModeState(!getMagnetModeState());
        $("#magnetModeBtn").toggleClass("pressed");
    });
}

window.updateX = function updateX(event, ui) {
    const label = $("#input-form\\:xValueLabel");
    label.text("X: " + ui.value);
    console.log(ui.value);
    console.log(label.text());
}

window.updateCanvas = function updateCanvas(event, ui) {
    const r = ui.value;
    const label = $("#input-form\\:rValueLabel");
    console.log("Here is the label: ", label)
    label.text("R: " + r);
    console.log("here is the r value in a label:");
    console.log(label.text());
    drawGraph(r);
    console.log("graph updated!)");

    $("#update-radius\\:radius").val(r);
    $("#update-radius\\:updateCanvas").click();
}

window.showConfirmationPopup = function showConfirmationPopup() {
    $('#confirmationPopup').removeClass('hidden');
}

window.hideConfirmationPopup = function hideConfirmationPopup() {
    $('#confirmationPopup').addClass('hidden');
}

window.deletePoints = function deletePoints() {
    hideConfirmationPopup();
    drawGraph(parseR());
    $("#clear-points\\:clearPoints").click();
}

export function restorePoints() {
    $("#update-radius\\:radius").val(parseR());
    $("#update-radius\\:updateCanvas").click();
}

export function setupParallax() {
    $(window).on('scroll', function() {
        var scrollTop = $(this).scrollTop();
        var tableTop = $('#scrollToTable').offset().top;
        var windowHeight = $(window).height();
        var distance = Math.max(tableTop - scrollTop - windowHeight, 0);
        var maxDistance = 500;
        var transitionFactor = Math.max(1 - distance / maxDistance, 0);

        var red = 255 - transitionFactor * (255 - 0);
        var green = 255 - transitionFactor * (255 - 255);
        var blue = 255 - transitionFactor * (255 - 255);
        var borderColor = 'rgb(' + red + ',' + green + ',' + blue + ')';

        $('#scrollToTable').css('border-color', borderColor);
    });

    $("#scrollToTable").click(() => {
        $("#scrollToTable").get(0).scrollIntoView({ behavior: "smooth" });
    });

    $("a").on('click', function(event) {
        if (this.hash !== "") {
            event.preventDefault();
            const hash = this.hash;
            $('html, body').animate({
                scrollTop: $(hash).offset().top
            }, 800, function(){
                window.location.hash = hash;
            });
        }
    });
}

window.toggleTheme = function toggleTheme() {
    $('html').toggleClass('dark');
    $('#lightBtn').toggleClass('hidden');
    $('#darkBtn').toggleClass('hidden');
}

window.toggleThemeMobile = function toggleThemeMobile() {
    $('html').toggleClass('dark');
    $('#lightMobileBtn').toggleClass('hidden');
    $('#darkMobileBtn').toggleClass('hidden');
}

