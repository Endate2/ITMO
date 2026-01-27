<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест на зрительную память</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .stimulus {
            width: 200px;
            height: 200px;
            background-color: #ccc;
            margin: 20px auto;
            border-radius: 10px;
            transition: background-color 0.3s ease;
            cursor: pointer;
        }
        .color-options {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .color-option {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            cursor: pointer;
            border: 3px solid transparent;
            transition: transform 0.2s, border-color 0.2s;
        }
        .color-option:hover {
            transform: scale(1.1);
        }
        .color-option.selected {
            border-color: #000;
        }
        .navbar {
            margin-bottom: 20px;
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin-top: -50px;
        }
        .btn-next {
            margin-top: 20px;
            display: none;
        }
        .instructions {
            max-width: 600px;
            margin: 0 auto 20px;
            text-align: center;
        }
        .sequence-info {
            margin-top: 15px;
            font-weight: bold;
        }
        .progress-container {
            width: 100%;
            max-width: 400px;
            margin: 20px auto;
        }
        .difficulty-buttons {
            margin: 20px 0;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .hidden {
            display: none;
        }
        .final-results {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            max-width: 500px;
        }
        .zoom-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .zoom-modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            text-align: center;
        }
        .zoom-info {
            background-color: #f0f7ff;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 5px solid #0066cc;
        }
        .zoom-code {
            font-size: 24px;
            font-weight: bold;
            color: #0066cc;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <!-- Навигационная панель -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <a class="navbar-brand" href="../index.php">ITMO Portal</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <span class="navbar-text mr-2"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="../pages/logout.php" class="btn btn-outline-dark">Выйти</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Модальное окно Zoom -->
    <div id="zoom-modal" class="zoom-modal">
        <div class="zoom-modal-content">
            <h2>Требование к прохождению теста</h2>
            <p>Для обеспечения честности тестирования вам необходимо подключиться к Zoom конференции.</p>
            <p>Пожалуйста, подключитесь к конференции перед началом теста. Ваше изображение и экран будут записываться.</p>

            <div class="zoom-info">
                <h4>Данные для подключения:</h4>
                <div class="zoom-code">
                    Код конференции: 123 456 7890
                </div>
                <div class="zoom-code">
                    Пароль: 987654
                </div>
                <p>Или перейдите по ссылке: <a href="https://zoom.us/j/1234567890?pwd=QWERTY123456" target="_blank">https://zoom.us/j/1234567890</a></p>
            </div>

            <p>После подключения нажмите кнопку "Я подключился", чтобы начать тест.</p>

            <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" id="agree-checkbox">
                <label class="form-check-label" for="agree-checkbox">Я подтверждаю, что подключился к Zoom конференции и готов начать тест</label>
            </div>

            <button id="start-test-btn" class="btn btn-primary" disabled>Я подключился</button>
        </div>
    </div>

    <!-- Основной контент -->
    <div class="container hidden" id="main-content">
        <h1 class="text-center">Тест на зрительную память</h1>
        <div class="instructions">
            <p>Вам будет показана последовательность цветов. Запомните её и затем воспроизведите, нажимая на цветные квадраты в том же порядке.</p>
        </div>

        <!-- Выбор уровня сложности -->
        <div id="difficulty-selection">
            <h3 class="text-center">Выберите уровень сложности:</h3>
            <div class="difficulty-buttons">
                <button class="btn btn-success difficulty-btn" data-level="easy">Лёгкий</button>
                <button class="btn btn-warning difficulty-btn" data-level="medium">Средний</button>
                <button class="btn btn-danger difficulty-btn" data-level="hard">Сложный</button>
            </div>
        </div>

        <div id="test-content" class="hidden">
            <div class="text-center">
                <button id="start-button" class="btn btn-primary">Начать тест</button>
                <div id="stimulus" class="stimulus mt-4"></div>
                <div id="color-options" class="color-options hidden">
                    <!-- Цвета будут добавлены динамически -->
                </div>

                <div class="progress-container">
                    <div class="progress">
                        <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>

                <p id="sequence-info" class="sequence-info"></p>
                <p id="result" class="mt-3"></p>
                <div id="final-results" class="final-results hidden"></div>
                <button id="restart-button" class="btn btn-primary hidden">Пройти ещё раз</button>
                <a href="next_test.html" id="next-test-button" class="btn btn-success btn-next hidden">Следующий тест</a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // Ожидаем полной загрузки DOM
        document.addEventListener('DOMContentLoaded', function() {
            // Проверяем существование элементов перед добавлением обработчиков
            const agreeCheckbox = document.getElementById('agree-checkbox');
            const startTestBtn = document.getElementById('start-test-btn');

            if (agreeCheckbox && startTestBtn) {
                agreeCheckbox.addEventListener('change', function() {
                    startTestBtn.disabled = !this.checked;
                });

                startTestBtn.addEventListener('click', function() {
                    // Отправляем уведомление консультанту о подключении к Zoom
                    sendZoomConnectedNotification();

                    // Показываем тест
                    document.getElementById('zoom-modal').style.display = 'none';
                    document.getElementById('main-content').classList.remove('hidden');
                });
            } else {
                console.error('Не удалось найти элементы для обработки Zoom модального окна');
            }

            // Пометить уведомление как прочитанное при загрузке теста
            const urlParams = new URLSearchParams(window.location.search);
            const notificationId = urlParams.get('notification_id');

            if (notificationId) {
                fetch(`index.php?mark_as_read=${notificationId}`)
                    .then(response => {
                        if (!response.ok) {
                            console.error('Ошибка при пометке уведомления как прочитанного');
                        }
                    })
                    .catch(error => {
                        console.error('Ошибка:', error);
                    });
            }

            // Конфигурация уровней сложности
            const difficultySettings = {
                easy: {
                    name: "Лёгкий",
                    startLength: 3,
                    maxLevel: 5,
                    colorDisplayTime: 1000,
                    pauseBetweenColors: 500,
                    colors: ['red', 'green', 'blue', 'yellow']
                },
                medium: {
                    name: "Средний",
                    startLength: 4,
                    maxLevel: 6,
                    colorDisplayTime: 800,
                    pauseBetweenColors: 300,
                    colors: ['red', 'green', 'blue', 'yellow', 'purple']
                },
                hard: {
                    name: "Сложный",
                    startLength: 5,
                    maxLevel: 7,
                    colorDisplayTime: 600,
                    pauseBetweenColors: 200,
                    colors: ['red', 'green', 'blue', 'yellow', 'purple', 'orange']
                }
            };

            let currentDifficulty = null;
            let sequence = [];
            let userSequence = [];
            let sequenceLength = 0;
            let level = 1;
            let maxLevel = 0;
            let correctAnswers = 0;
            let totalTime = 0;
            let testStartTime;
            let colorDisplayTime = 0;
            let pauseBetweenColors = 0;
            let availableColors = [];

            // Инициализация теста при выборе уровня сложности
            document.querySelectorAll('.difficulty-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    currentDifficulty = this.getAttribute('data-level');
                    const settings = difficultySettings[currentDifficulty];

                    // Устанавливаем параметры теста
                    sequenceLength = settings.startLength;
                    maxLevel = settings.maxLevel;
                    colorDisplayTime = settings.colorDisplayTime;
                    pauseBetweenColors = settings.pauseBetweenColors;
                    availableColors = settings.colors;

                    // Показываем тестовый интерфейс
                    document.getElementById('difficulty-selection').classList.add('hidden');
                    document.getElementById('test-content').classList.remove('hidden');

                    // Обновляем цветовые опции в соответствии с уровнем сложности
                    updateColorOptions();
                });
            });

            function updateColorOptions() {
                const colorOptionsContainer = document.getElementById('color-options');
                colorOptionsContainer.innerHTML = '';

                availableColors.forEach(color => {
                    const option = document.createElement('div');
                    option.className = 'color-option';
                    option.setAttribute('data-color', color);
                    option.style.backgroundColor = color;
                    option.addEventListener('click', handleColorSelection);
                    colorOptionsContainer.appendChild(option);
                });
            }

            document.getElementById('start-button').addEventListener('click', startTest);
            document.getElementById('restart-button').addEventListener('click', restartTest);

            function startTest() {
                console.log("Тест начат на уровне сложности: " + currentDifficulty);
                const stimulus = document.getElementById('stimulus');
                const result = document.getElementById('result');
                const nextButton = document.getElementById('next-test-button');
                const colorOptions = document.getElementById('color-options');
                const finalResults = document.getElementById('final-results');

                // Сброс предыдущих результатов
                result.textContent = '';
                finalResults.classList.add('hidden');
                document.getElementById('start-button').disabled = true;
                nextButton.classList.add('hidden');
                colorOptions.classList.add('hidden');
                document.getElementById('restart-button').classList.add('hidden');

                testStartTime = Date.now();
                level = 1;
                correctAnswers = 0;
                totalTime = 0;
                sequenceLength = difficultySettings[currentDifficulty].startLength;

                // Генерируем случайную последовательность
                generateSequence();

                // Показываем последовательность пользователю
                showSequence();
            }

            function generateSequence() {
                sequence = [];
                for (let i = 0; i < sequenceLength; i++) {
                    const randomIndex = Math.floor(Math.random() * availableColors.length);
                    sequence.push(availableColors[randomIndex]);
                }
                document.getElementById('sequence-info').textContent =
                    `Уровень сложности: ${difficultySettings[currentDifficulty].name}. Уровень ${level} из ${maxLevel}.
                    Запомните последовательность из ${sequenceLength} цветов.`;
            }

            function showSequence() {
                const stimulus = document.getElementById('stimulus');
                let index = 0;

                stimulus.style.backgroundColor = '#ccc';

                const showNextColor = () => {
                    if (index >= sequence.length) {
                        // Последовательность показана, теперь пользователь должен повторить
                        setTimeout(() => {
                            stimulus.style.backgroundColor = '#ccc';
                            document.getElementById('color-options').classList.remove('hidden');
                            userSequence = [];
                            updateProgress(0);
                        }, 500);
                        return;
                    }

                    stimulus.style.backgroundColor = sequence[index];
                    index++;

                    setTimeout(() => {
                        stimulus.style.backgroundColor = '#ccc';
                        setTimeout(showNextColor, pauseBetweenColors); // Пауза между цветами
                    }, colorDisplayTime); // Время показа каждого цвета
                };

                showNextColor();
            }

            function updateProgress(percent) {
                document.getElementById('progress-bar').style.width = `${percent}%`;
            }

            function handleColorSelection() {
                const selectedColor = this.getAttribute('data-color');
                this.classList.add('selected');

                setTimeout(() => {
                    this.classList.remove('selected');
                }, 300);

                userSequence.push(selectedColor);
                updateProgress((userSequence.length / sequence.length) * 100);

                // Проверяем, завершил ли пользователь последовательность
                if (userSequence.length === sequence.length) {
                    checkSequence();
                }
            }

            function checkSequence() {
                const result = document.getElementById('result');
                const colorOptions = document.getElementById('color-options');
                const stimulus = document.getElementById('stimulus');
                const finalResults = document.getElementById('final-results');

                let isCorrect = true;
                for (let i = 0; i < sequence.length; i++) {
                    if (sequence[i] !== userSequence[i]) {
                        isCorrect = false;
                        break;
                    }
                }

                if (isCorrect) {
                    correctAnswers++;
                    const levelTime = (Date.now() - testStartTime) / 1000;
                    totalTime += levelTime;

                    if (level < maxLevel) {
                        // Переход на следующий уровень
                        level++;
                        sequenceLength++;
                        result.textContent = `Правильно! Переход на уровень ${level}.`;
                        colorOptions.classList.add('hidden');
                        testStartTime = Date.now();

                        // Генерируем новую последовательность и показываем её
                        generateSequence();
                        setTimeout(showSequence, 1500); // Задержка перед показом новой последовательности
                    } else {
                        // Тест завершен успешно
                        const averageTime = totalTime / maxLevel;
                        result.textContent = `Тест завершён! Вы правильно воспроизвели все последовательности.`;

                        // Показываем финальные результаты
                        finalResults.innerHTML = `
                            <h3>Результаты теста</h3>
                            <p><strong>Уровень сложности:</strong> ${difficultySettings[currentDifficulty].name}</p>
                            <p><strong>Пройдено уровней:</strong> ${maxLevel}</p>
                            <p><strong>Правильных ответов:</strong> ${correctAnswers} из ${maxLevel}</p>
                            <p><strong>Среднее время ответа:</strong> ${averageTime.toFixed(2)} сек.</p>
                        `;
                        finalResults.classList.remove('hidden');

                        document.getElementById('start-button').disabled = false;
                        document.getElementById('next-test-button').classList.remove('hidden');
                        document.getElementById('restart-button').classList.remove('hidden');
                        colorOptions.classList.add('hidden');

                        // Данные для отправки
                        const testData = {
                            user_id: <?php echo $_SESSION['user_id']; ?>,
                            test_name: 'Тест на зрительную память',
                            difficulty_level: currentDifficulty,
                            reaction_time: averageTime,
                            correct_answers: correctAnswers,
                            total_questions: maxLevel,
                            attempts: 1,
                            average_time: averageTime
                        };

                        saveResult(testData);
                    }
                } else {
                    // Неправильная последовательность
                    const levelTime = (Date.now() - testStartTime) / 1000;
                    result.textContent = 'Неверно! Тест завершен.';
                    colorOptions.classList.add('hidden');
                    document.getElementById('start-button').disabled = false;
                    document.getElementById('restart-button').classList.remove('hidden');
                    updateProgress(0);

                    // Показываем финальные результаты
                    const levelsCompleted = level - 1;
                    const averageTime = levelsCompleted > 0 ? totalTime / levelsCompleted : 0;

                    finalResults.innerHTML = `
                        <h3>Результаты теста</h3>
                        <p><strong>Уровень сложности:</strong> ${difficultySettings[currentDifficulty].name}</p>
                        <p><strong>Пройдено уровней:</strong> ${levelsCompleted} из ${maxLevel}</p>
                        <p><strong>Правильных ответов:</strong> ${correctAnswers} из ${levelsCompleted > 0 ? levelsCompleted : 1}</p>
                        <p><strong>Среднее время ответа:</strong> ${levelsCompleted > 0 ? averageTime.toFixed(2) : 0} сек.</p>
                    `;
                    finalResults.classList.remove('hidden');

                    // Данные для отправки (в случае ошибки)
                    const testData = {
                        user_id: <?php echo $_SESSION['user_id']; ?>,
                        test_name: 'Тест на зрительную память',
                        difficulty_level: currentDifficulty,
                        reaction_time: levelTime,
                        correct_answers: correctAnswers,
                        total_questions: maxLevel,
                        attempts: 1,
                        average_time: averageTime
                    };

                    saveResult(testData);
                }
            }

            function restartTest() {
                document.getElementById('final-results').classList.add('hidden');
                document.getElementById('restart-button').classList.add('hidden');
                document.getElementById('next-test-button').classList.add('hidden');
                document.getElementById('result').textContent = '';
                document.getElementById('difficulty-selection').classList.remove('hidden');
                document.getElementById('test-content').classList.add('hidden');
            }

            function saveResult(testData) {
                fetch('../php/save_test_result.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(testData)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Ошибка сети: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        console.log('Результат успешно сохранен в базе данных');
                    } else {
                        const errorMessage = data.message || 'Неизвестная ошибка';
                        console.error('Ошибка при сохранении результата:', errorMessage);
                    }
                })
                .catch(error => {
                    console.error('Ошибка при отправке данных:', error);
                });
            }

            function sendZoomConnectedNotification() {
                console.log("Sending Zoom notification...");
                const testName = "Тест на зрительную память";
                const respondentId = <?php echo $_SESSION['user_id']; ?>;
                
                fetch('../php/send_zoom_notification.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        respondent_id: respondentId,
                        test_name: testName
                    })
                })
                .then(response => {
                    console.log("Response status:", response.status);
                    return response.json();
                })
                .then(data => {
                    console.log("Response data:", data);
                    if (data.status !== 'success') {
                        console.error('Ошибка при отправке уведомления');
                    }
                })
                .catch(error => {
                    console.error('Ошибка:', error);
                });
            }
        });
    </script>
</body>
</html>