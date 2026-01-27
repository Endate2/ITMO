<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Включение отображения ошибок (для разработки)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Настройки подключения к БД
$host = 'localhost';
$dbname = 'it_portal';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка подключения к базе данных: ' . $e->getMessage()]);
    exit;
}

// Получаем данные из тела запроса
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Проверяем, что все необходимые поля присутствуют
if (!$data || !isset($data['user_id'], $data['test_name'], $data['circles'], $data['color_test'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Неверные данные',
        'received_data' => $data
    ]);
    exit;
}

try {
    // Начинаем транзакцию
    $conn->beginTransaction();
    
    // 1. Сохраняем основную информацию о тесте
    $stmt = $conn->prepare("INSERT INTO combined_test_results 
        (user_id, test_name, created_at) 
        VALUES 
        (:user_id, :test_name, NOW())");
    
    $stmt->bindParam(':user_id', $data['user_id']);
    $stmt->bindParam(':test_name', $data['test_name']);
    $stmt->execute();
    
    $testId = $conn->lastInsertId();
    
    // 2. Сохраняем результаты по кругам
    foreach ($data['circles'] as $circle) {
        $stmt = $conn->prepare("INSERT INTO combined_test_circle_results 
            (test_id, level, speed, attempts, correct_answers, average_time, best_time) 
            VALUES 
            (:test_id, :level, :speed, :attempts, :correct_answers, :average_time, :best_time)");
        
        $stmt->bindParam(':test_id', $testId);
        $stmt->bindParam(':level', $circle['level']);
        $stmt->bindParam(':speed', $circle['speed']);
        $stmt->bindParam(':attempts', $circle['attempts']);
        $stmt->bindParam(':correct_answers', $circle['correct_answers']);
        $stmt->bindParam(':average_time', $circle['average_time']);
        $stmt->bindParam(':best_time', $circle['best_time']);
        $stmt->execute();
    }
    
    // 3. Сохраняем результаты цветового теста
    $colorTest = $data['color_test'];
    $stmt = $conn->prepare("INSERT INTO combined_test_color_results 
        (test_id, attempts, correct_answers, average_time, best_time) 
        VALUES 
        (:test_id, :attempts, :correct_answers, :average_time, :best_time)");
    
    $stmt->bindParam(':test_id', $testId);
    $stmt->bindParam(':attempts', $colorTest['attempts']);
    $stmt->bindParam(':correct_answers', $colorTest['correct_answers']);
    $stmt->bindParam(':average_time', $colorTest['average_time']);
    $stmt->bindParam(':best_time', $colorTest['best_time']);
    $stmt->execute();
    
    // Завершаем транзакцию
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Результаты успешно сохранены',
        'test_id' => $testId
    ]);
} catch (PDOException $e) {
    // Откатываем транзакцию при ошибке
    $conn->rollBack();
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Ошибка базы данных: ' . $e->getMessage(),
        'error_info' => isset($stmt) ? $stmt->errorInfo() : null
    ]);
}
?>