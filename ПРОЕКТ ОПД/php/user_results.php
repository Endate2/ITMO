<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../php/db.php'; // Убедитесь, что путь правильный

// Проверка роли пользователя
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Получение результатов всех тестов
$sql_all_tests = "SELECT * FROM test_results WHERE user_id = ? ORDER BY created_at DESC";
$stmt_all_tests = $conn->prepare($sql_all_tests);
$stmt_all_tests->bind_param("i", $user_id);
$stmt_all_tests->execute();
$all_tests = $stmt_all_tests->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_all_tests->close();

// Получение динамики для одного вида теста (например, тест на реакцию на свет)
$sql_dynamics = "SELECT * FROM test_results WHERE user_id = ? AND test_name = ? ORDER BY created_at ASC";
$stmt_dynamics = $conn->prepare($sql_dynamics);
$test_name = "Тест на реакцию на свет"; // Пример для одного теста
$stmt_dynamics->bind_param("is", $user_id, $test_name);
$stmt_dynamics->execute();
$dynamics = $stmt_dynamics->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_dynamics->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои результаты</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
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

    <div class="container mt-5 pt-5">
        <h1 class="text-center">Мои результаты</h1>

        <!-- Результаты всех тестов -->
        <div class="mt-4">
            <h3>Результаты всех тестов</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Тест</th>
                        <th>Время реакции</th>
                        <th>Правильные ответы</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_tests as $test): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($test['test_name']); ?></td>
                            <td><?php echo $test['reaction_time']; ?></td>
                            <td><?php echo $test['correct_answers']; ?></td>
                            <td><?php echo $test['created_at']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Динамика одного вида теста -->
        <div class="mt-4">
            <h3>Динамика теста: <?php echo htmlspecialchars($test_name); ?></h3>
            <canvas id="testChart"></canvas>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('testChart').getContext('2d');
        const testChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($dynamics, 'created_at')); ?>,
                datasets: [{
                    label: 'Время реакции',
                    data: <?php echo json_encode(array_column($dynamics, 'reaction_time')); ?>,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    fill: false
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>