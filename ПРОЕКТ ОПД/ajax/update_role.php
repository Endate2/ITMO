<?php
session_start();
require '../php/db.php'; 

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Ошибка: доступ запрещен.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $new_role = trim($_POST['role']);
    
    if (!in_array($new_role, ['user', 'expert', 'admin', 'consultant'])) {
        die("Ошибка: недопустимая роль.");
    }
    
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $new_role, $user_id);
    
    if ($stmt->execute()) {
	header("Location: ../pages/admin_page.php");
    } else {
        echo "Ошибка при обновлении роли: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo "Некорректный запрос.";
}
?>