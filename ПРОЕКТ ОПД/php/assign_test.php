<?php
session_start();
require_once '../php/db.php';

// Проверка роли пользователя
if ($_SESSION['user_role'] !== 'consultant') {
    die("Доступ запрещен.");
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
    echo "Тест успешно назначен!";
} else {
    echo "Ошибка: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>