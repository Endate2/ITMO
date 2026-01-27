<?php
header('Content-Type: application/json'); // Убедитесь, что заголовок установлен

$host = 'localhost';
$dbname = 'it_portal'; // Используем правильное имя базы данных
$username = 'root';
$password = ''; // Укажите пароль, если он есть

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка подключения к базе данных: ' . $e->getMessage()]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Проверяем, что все необходимые поля присутствуют
if (isset($data['user_id'], $data['test_name'], $data['reaction_time'], $data['correct_answers'], $data['total_questions'], $data['attempts'])) {
    $userId = $data['user_id'];
    $testName = $data['test_name'];
    $reactionTime = $data['reaction_time'];
    $correctAnswers = $data['correct_answers'];
    $totalQuestions = $data['total_questions'];
    $attempts = $data['attempts'];
    $averageTime = $data['average_time'] ?? null; // Опциональное поле

    $stmt = $conn->prepare("INSERT INTO test_results (user_id, test_name, reaction_time, correct_answers, total_questions, attempts, average_time, created_at) VALUES (:user_id, :test_name, :reaction_time, :correct_answers, :total_questions, :attempts, :average_time, NOW())");
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':test_name', $testName);
    $stmt->bindParam(':reaction_time', $reactionTime);
    $stmt->bindParam(':correct_answers', $correctAnswers);
    $stmt->bindParam(':total_questions', $totalQuestions);
    $stmt->bindParam(':attempts', $attempts);
    $stmt->bindParam(':average_time', $averageTime);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Результат успешно сохранен']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Ошибка при сохранении результата']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Неверные данные']);
}