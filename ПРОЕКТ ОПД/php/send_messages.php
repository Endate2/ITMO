<?php
session_start();
require_once 'php/db.php'; // Подключение к базе данных

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php'); // Перенаправление на главную страницу, если пользователь не авторизован
    exit();
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение данных из формы
    $from_user_id = $_SESSION['user_id']; // ID отправителя (текущий пользователь)
    $to_user_id = $_POST['to_user_id'];   // ID получателя
    $message = trim($_POST['message']);   // Текст сообщения

    // Проверка, что сообщение не пустое
    if (empty($message)) {
        $_SESSION['error_message'] = "Сообщение не может быть пустым.";
        header("Location: consultant_panel.php?user_id=$to_user_id"); // Перенаправление обратно в чат
        exit;
    }

    // Вставка сообщения в базу данных (используем таблицу messagescon)
    $sql = "INSERT INTO messagescon (from_user_id, to_user_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $from_user_id, $to_user_id, $message);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Сообщение отправлено.";
    } else {
        $_SESSION['error_message'] = "Ошибка при отправке сообщения: " . $stmt->error;
    }

    $stmt->close();
    header("Location: consultant_panel.php?user_id=$to_user_id"); // Перенаправление обратно в чат
    exit;
} else {
    // Если запрос не POST, перенаправляем на главную страницу
    header('Location: ../index.php');
    exit;
}
?>