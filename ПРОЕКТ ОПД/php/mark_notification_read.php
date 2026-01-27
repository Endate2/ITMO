<?php
session_start();
require_once '../php/db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['id'])) {
    die(json_encode(['status' => 'error', 'message' => 'Недостаточно прав']));
}

$notification_id = $_POST['id'];
$user_id = $_SESSION['user_id'];

// Проверяем, принадлежит ли уведомление текущему пользователю
$check_sql = "SELECT id FROM notifications WHERE id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $notification_id, $user_id);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
    // Помечаем уведомление как прочитанное
    $update_sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $notification_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Уведомление не найдено']);
}

$check_stmt->close();
$conn->close();
?>