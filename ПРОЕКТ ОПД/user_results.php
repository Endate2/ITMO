<?php
session_start();

// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Подключение к базе данных
try {
    require_once 'php/db.php'; // Убедитесь, что путь правильный
    
    // Проверка соединения
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

// Проверка авторизации и роли
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if ($_SESSION['user_role'] !== 'user') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Функция для безопасного получения данных
function getTestResults($conn, $user_id, $test_name = null) {
    try {
        if ($test_name) {
            $sql = "SELECT test_name, reaction_time, correct_answers, total_questions, 
                    attempts, average_time, created_at 
                    FROM test_results 
                    WHERE user_id = ? AND test_name = ?
                    ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $user_id, $test_name);
        } else {
            $sql = "SELECT test_name, reaction_time, correct_answers, total_questions, 
                    attempts, average_time, created_at 
                    FROM test_results 
                    WHERE user_id = ? 
                    ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Ошибка выполнения запроса: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $data;
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

// Получаем все результаты тестов
$all_tests = getTestResults($conn, $user_id);

// Получаем динамику для теста на реакцию на свет
$test_name = "Тест на реакцию на свет";
$dynamics = getTestResults($conn, $user_id, $test_name);

// Закрываем соединение
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои результаты</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .container {
            margin-top: 80px;
        }
        .chart-container {
            height: 400px;
            margin-bottom: 40px;
        }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <a class="navbar-brand" href="index.php">ITMO Portal</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <span class="navbar-text mr-2"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="pages/logout.php" class="btn btn-outline-dark">Выйти</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1 class="text-center mb-4">Мои результаты</h1>

        <!-- Результаты всех тестов -->
        <div class="card mb-4">
            <div class="card-header">
                <h3>Результаты всех тестов</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Название теста</th>
                                <th>Время реакции (мс)</th>
                                <th>Правильные ответы</th>
                                <th>Всего вопросов</th>
                                <th>Попытки</th>
                                <th>Среднее время (мс)</th>
                                <th>Дата</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($all_tests)): ?>
                                <?php foreach ($all_tests as $test): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($test['test_name']); ?></td>
                                        <td><?php echo number_format($test['reaction_time'], 2); ?></td>
                                        <td><?php echo $test['correct_answers']; ?></td>
                                        <td><?php echo $test['total_questions']; ?></td>
                                        <td><?php echo $test['attempts']; ?></td>
                                        <td><?php echo number_format($test['average_time'], 2); ?></td>
                                        <td><?php echo date('d.m.Y H:i', strtotime($test['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Нет данных о тестах.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Динамика теста на реакцию на свет -->
        <div class="card">
            <div class="card-header">
                <h3>Динамика теста: <?php echo htmlspecialchars($test_name); ?></h3>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="testChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('testChart').getContext('2d');
            
            // Подготовка данных для графика
            const labels = <?php echo json_encode(array_map(function($item) {
                return new Date(item['created_at']).toLocaleDateString();
            }, $dynamics)); ?>;
            
            const data = <?php echo json_encode(array_column($dynamics, 'reaction_time')); ?>;
            
            // Создание графика
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Время реакции (мс)',
                        data: data,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        borderWidth: 2,
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: false,
                            title: {
                                display: true,
                                text: 'Время реакции (мс)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Дата прохождения теста'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y.toFixed(2) + ' мс';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>