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
    <title>Тест на визуальную память (числа)</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .number-display {
            width: 100%;
            height: 200px;
            background-color: #f8f9fa;
            margin: 20px auto;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 72px;
            font-weight: bold;
            color: #343a40;
            transition: all 0.3s ease;
        }
        .input-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }
        .number-input {
            width: 300px;
            height: 60px;
            font-size: 24px;
            text-align: center;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 2px solid #ced4da;
        }
        .number-input:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        .btn-check {
            margin-top: 10px;
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
        .instructions {
            max-width: 600px;
            margin: 0 auto 20px;
            text-align: center;
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
        .level-info {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
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
        .timer {
            font-size: 24px;
            font-weight: bold;
            color: #dc3545;
            margin-bottom: 15px;
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
        <h1 class="text-center">Тест на визуальную память (числа)</h1>
        <div class="instructions">
            <p>Вам будет показано число на несколько секунд. Запомните его и введите в поле ниже.</p>
            <p>С каждым уровнем число будет становиться длиннее, а время для запоминания - короче.</p>
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

                <div id="level-info" class="level-info hidden"></div>
                <div id="timer" class="timer hidden"></div>

                <div id="number-display" class="number-display hidden"></div>

                <div id="input-area" class="input-area hidden">
                    <input type="text" id="number-input" class="number-input" placeholder="Введите число" maxlength="20">
                    <button id="check-button" class="btn btn-primary btn-check">Проверить</button>
                </div>

                <div class="progress-container">
                    <div class="progress">
                        <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>

                <p id="result" class="mt-3"></p>
                <div id="final-results" class="final-results hidden"></div>
                <button id="restart-button" class="btn btn-primary hidden">Пройти ещё раз</button>
                <a href="next_test.html" id="next-test-button" class="btn btn-success hidden">Следующий тест</a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Обработка модального окна Zoom
            const agreeCheckbox = document.getElementById('agree-checkbox');
            const startTestBtn = document.getElementById('start-test-btn');

            if (agreeCheckbox && startTestBtn) {
                agreeCheckbox.addEventListener('change', function() {
                    startTestBtn.disabled = !this.checked;
                });

                startTestBtn.addEventListener('click', function() {
                    sendZoomConnectedNotification();
                    document.getElementById('zoom-modal').style.display = 'none';
                    document.getElementById('main-content').classList.remove('hidden');
                });
            }

            // Конфигурация уровней сложности
            const difficultySettings = {
                easy: {
                    name: "Лёгкий",
                    startDigits: 3,
                    maxLevel: 5,
                    displayTime: 5000,
                    timeDecreasePerLevel: 500,
                    digitsIncreasePerLevel: 1
                },
                medium: {
                    name: "Средний",
                    startDigits: 4,
                    maxLevel: 6,
                    displayTime: 4000,
                    timeDecreasePerLevel: 500,
                    digitsIncreasePerLevel: 1
                },
                hard: {
                    name: "Сложный",
                    startDigits: 5,
                    maxLevel: 7,
                    displayTime: 3000,
                    timeDecreasePerLevel: 400,
                    digitsIncreasePerLevel: 1
                }
            };

            let currentDifficulty = null;
            let currentNumber = '';
            let level = 1;
            let digitsCount = 0;
            let displayTime = 0;
            let correctAnswers = 0;
            let totalTime = 0;
            let testStartTime;
            let timerInterval;
            let remainingTime = 0;

            // Инициализация теста при выборе уровня сложности
            document.querySelectorAll('.difficulty-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    currentDifficulty = this.getAttribute('data-level');
                    const settings = difficultySettings[currentDifficulty];

                    digitsCount = settings.startDigits;
                    displayTime = settings.displayTime;

                    document.getElementById('difficulty-selection').classList.add('hidden');
                    document.getElementById('test-content').classList.remove('hidden');
                });
            });

            document.getElementById('start-button').addEventListener('click', startTest);
            document.getElementById('check-button').addEventListener('click', checkAnswer);
            document.getElementById('restart-button').addEventListener('click', restartTest);
            document.getElementById('number-input').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    checkAnswer();
                }
            });

            function startTest() {
                console.log("Тест начат на уровне сложности: " + currentDifficulty);
                const numberDisplay = document.getElementById('number-display');
                const inputArea = document.getElementById('input-area');
                const result = document.getElementById('result');
                const nextButton = document.getElementById('next-test-button');
                const finalResults = document.getElementById('final-results');

                // Сброс предыдущих результатов
                result.textContent = '';
                finalResults.classList.add('hidden');
                document.getElementById('start-button').disabled = true;
                nextButton.classList.add('hidden');
                document.getElementById('restart-button').classList.add('hidden');
                inputArea.classList.add('hidden');

                testStartTime = Date.now();
                level = 1;
                correctAnswers = 0;
                totalTime = 0;

                // Устанавливаем начальные параметры для уровня
                setupLevel();
            }

            function setupLevel() {
                const settings = difficultySettings[currentDifficulty];
                digitsCount = settings.startDigits + (level - 1) * settings.digitsIncreasePerLevel;
                displayTime = settings.displayTime - (level - 1) * settings.timeDecreasePerLevel;

                // Генерируем случайное число
                currentNumber = generateRandomNumber(digitsCount);

                // Обновляем информацию об уровне
                document.getElementById('level-info').textContent =
                    `Уровень сложности: ${settings.name}. Уровень ${level} из ${settings.maxLevel}.
                    Запомните ${digitsCount}-значное число.`;
                document.getElementById('level-info').classList.remove('hidden');

                // Показываем таймер
                document.getElementById('timer').textContent = `Осталось времени: ${(displayTime/1000).toFixed(1)} сек.`;
                document.getElementById('timer').classList.remove('hidden');

                // Обновляем прогресс
                updateProgress((level-1) / settings.maxLevel * 100);

                // Показываем число
                showNumber();
            }

            function generateRandomNumber(length) {
                let result = '';
                for (let i = 0; i < length; i++) {
                    result += Math.floor(Math.random() * 10).toString();
                }
                return result;
            }

            function showNumber() {
                const numberDisplay = document.getElementById('number-display');
                numberDisplay.textContent = currentNumber;
                numberDisplay.classList.remove('hidden');

                // Запускаем таймер
                remainingTime = displayTime / 1000;
                updateTimerDisplay();

                timerInterval = setInterval(function() {
                    remainingTime -= 0.1;
                    updateTimerDisplay();

                    if (remainingTime <= 0) {
                        clearInterval(timerInterval);
                        hideNumber();
                    }
                }, 100);
            }

            function updateTimerDisplay() {
                document.getElementById('timer').textContent = `Осталось времени: ${remainingTime.toFixed(1)} сек.`;
            }

            function hideNumber() {
                const numberDisplay = document.getElementById('number-display');
                numberDisplay.classList.add('hidden');
                document.getElementById('timer').classList.add('hidden');

                // Показываем поле для ввода
                document.getElementById('input-area').classList.remove('hidden');
                document.getElementById('number-input').value = '';
                document.getElementById('number-input').focus();
            }

            function checkAnswer() {
                const userInput = document.getElementById('number-input').value.trim();
                const result = document.getElementById('result');
                const settings = difficultySettings[currentDifficulty];

                if (userInput === currentNumber) {
                    correctAnswers++;
                    const levelTime = (Date.now() - testStartTime) / 1000;
                    totalTime += levelTime;

                    if (level < settings.maxLevel) {
                        // Переход на следующий уровень
                        level++;
                        result.textContent = `Правильно! Переход на уровень ${level}.`;
                        document.getElementById('input-area').classList.add('hidden');
                        testStartTime = Date.now();

                        setTimeout(() => {
                            setupLevel();
                        }, 1500);
                    } else {
                        // Тест завершен успешно
                        const averageTime = totalTime / settings.maxLevel;
                        result.textContent = `Тест завершён! Вы правильно воспроизвели все числа.`;

                        // Показываем финальные результаты
                        document.getElementById('final-results').innerHTML = `
                            <h3>Результаты теста</h3>
                            <p><strong>Уровень сложности:</strong> ${settings.name}</p>
                            <p><strong>Пройдено уровней:</strong> ${settings.maxLevel}</p>
                            <p><strong>Правильных ответов:</strong> ${correctAnswers} из ${settings.maxLevel}</p>
                            <p><strong>Среднее время ответа:</strong> ${averageTime.toFixed(2)} сек.</p>
                            <p><strong>Максимальная длина числа:</strong> ${digitsCount} цифр</p>
                        `;
                        document.getElementById('final-results').classList.remove('hidden');

                        document.getElementById('start-button').disabled = false;
                        document.getElementById('next-test-button').classList.remove('hidden');
                        document.getElementById('restart-button').classList.remove('hidden');
                        document.getElementById('input-area').classList.add('hidden');

                        // Данные для отправки
                        const testData = {
                            user_id: <?php echo $_SESSION['user_id']; ?>,
                            test_name: 'Тест на визуальную память (числа)',
                            difficulty_level: currentDifficulty,
                            reaction_time: averageTime,
                            correct_answers: correctAnswers,
                            total_questions: settings.maxLevel,
                            attempts: 1,
                            average_time: averageTime,
                            max_digits: digitsCount
                        };

                        saveResult(testData);
                    }
                } else {
                    // Неправильный ответ
                    const levelTime = (Date.now() - testStartTime) / 1000;
                    result.textContent = `Неверно! Правильный ответ: ${currentNumber}`;
                    document.getElementById('input-area').classList.add('hidden');
                    document.getElementById('start-button').disabled = false;
                    document.getElementById('restart-button').classList.remove('hidden');

                    // Показываем финальные результаты
                    const levelsCompleted = level - 1;
                    const averageTime = levelsCompleted > 0 ? totalTime / levelsCompleted : 0;

                    document.getElementById('final-results').innerHTML = `
                        <h3>Результаты теста</h3>
                        <p><strong>Уровень сложности:</strong> ${settings.name}</p>
                        <p><strong>Пройдено уровней:</strong> ${levelsCompleted} из ${settings.maxLevel}</p>
                        <p><strong>Правильных ответов:</strong> ${correctAnswers} из ${levelsCompleted > 0 ? levelsCompleted : 1}</p>
                        <p><strong>Среднее время ответа:</strong> ${levelsCompleted > 0 ? averageTime.toFixed(2) : 0} сек.</p>
                        <p><strong>Максимальная длина числа:</strong> ${digitsCount} цифр</p>
                    `;
                    document.getElementById('final-results').classList.remove('hidden');

                    // Данные для отправки (в случае ошибки)
                    const testData = {
                        user_id: <?php echo $_SESSION['user_id']; ?>,
                        test_name: 'Тест на визуальную память (числа)',
                        difficulty_level: currentDifficulty,
                        reaction_time: levelTime,
                        correct_answers: correctAnswers,
                        total_questions: settings.maxLevel,
                        attempts: 1,
                        average_time: averageTime,
                        max_digits: digitsCount
                    };

                    saveResult(testData);
                }

                clearInterval(timerInterval);
                updateProgress((level-1) / settings.maxLevel * 100);
            }

            function updateProgress(percent) {
                document.getElementById('progress-bar').style.width = `${percent}%`;
            }

            function restartTest() {
                document.getElementById('final-results').classList.add('hidden');
                document.getElementById('restart-button').classList.add('hidden');
                document.getElementById('next-test-button').classList.add('hidden');
                document.getElementById('result').textContent = '';
                document.getElementById('difficulty-selection').classList.remove('hidden');
                document.getElementById('test-content').classList.add('hidden');
                document.getElementById('number-display').classList.add('hidden');
                document.getElementById('input-area').classList.add('hidden');
                document.getElementById('level-info').classList.add('hidden');
                document.getElementById('timer').classList.add('hidden');

                if (timerInterval) {
                    clearInterval(timerInterval);
                }
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
                const testName = "Тест на визуальную память (числа)";
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