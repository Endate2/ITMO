<?php
session_start();
require_once '../php/db.php';

// Проверка роли пользователя
if ($_SESSION['user_role'] !== 'consultant') {
    die("Доступ запрещен.");
}

// Проверка наличия данных в $_POST
if (!isset($_POST['respondent_id']) || !isset($_POST['test_name'])) {
    die("Не все данные были переданы.");
}

// Получение данных из формы
$expert_id = $_SESSION['user_id'];
$respondent_id = $_POST['respondent_id'];
$test_name = $_POST['test_name'];

// Вставка данных в таблицу assigned_tests
$sql = "INSERT INTO assigned_tests (expert_id, respondent_id, test_name) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $expert_id, $respondent_id, $test_name);

if ($stmt->execute()) {
    // Добавление уведомления для пользователя
    $message = "Вам назначен тест: " . htmlspecialchars($test_name);
    $sql_notification = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
    $stmt_notification = $conn->prepare($sql_notification);
    $stmt_notification->bind_param("is", $respondent_id, $message);
    $stmt_notification->execute();
    $stmt_notification->close();

    echo "Тест успешно назначен!";
} else {
    echo "Ошибка: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>