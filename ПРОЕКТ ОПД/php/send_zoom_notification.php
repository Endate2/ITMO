<?php
session_start();
require_once '../php/db.php';

// Логирование для отладки
file_put_contents('zoom_notification.log', date('Y-m-d H:i:s') . " - Script started\n", FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        file_put_contents('zoom_notification.log', "Invalid JSON data\n", FILE_APPEND);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data']);
        exit();
    }

    $respondentId = $data['respondent_id'] ?? null;
    $testName = $data['test_name'] ?? null;

    // Получаем информацию о пользователе
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $respondentId);
    $stmt->execute();
    $respondent = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$respondent) {
        file_put_contents('zoom_notification.log', "Respondent not found: $respondentId\n", FILE_APPEND);
        echo json_encode(['status' => 'error', 'message' => 'Respondent not found']);
        exit();
    }

    $message = "Пользователь " . htmlspecialchars($respondent['username']) . 
               " подключился к Zoom для прохождения теста: " . htmlspecialchars($testName);

    // Получаем всех консультантов (исправленная строка)
    $consultants = $conn->query("SELECT id FROM users WHERE role = 'consultant'");
    
    if (!$consultants) {
        file_put_contents('zoom_notification.log', "SQL Error: " . $conn->error . "\n", FILE_APPEND);
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
        exit();
    }

    $notificationsCount = 0;
    while ($consultant = $consultants->fetch_assoc()) {
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $consultant['id'], $message);
        $stmt->execute();
        $notificationsCount += $stmt->affected_rows;
        $stmt->close();
    }

    file_put_contents('zoom_notification.log', "Created $notificationsCount notifications\n", FILE_APPEND);
    echo json_encode(['status' => 'success', 'count' => $notificationsCount]);
} else {
    file_put_contents('zoom_notification.log', "Invalid request method\n", FILE_APPEND);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>