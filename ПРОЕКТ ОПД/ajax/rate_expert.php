<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] === 'expert') {
    die("Доступ запрещен. Только пользователи могут ставить оценки.");
}

$user_id = $_SESSION['user_id'];
$expert_id = $_POST['expert_id'];
$rating = $_POST['rating'];
$profession_id = $_POST['profession_id'];

// Проверяем, не поставил ли пользователь уже оценку этому эксперту
$stmt = $conn->prepare("SELECT id FROM user_ratings WHERE user_id = ? AND expert_id = ?");
$stmt->bind_param("ii", $user_id, $expert_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    die("Вы уже оценили этого эксперта.");
}
$stmt->close();

// Добавляем оценку
$stmt = $conn->prepare("INSERT INTO user_ratings (user_id, expert_id, rating) VALUES (?, ?, ?)");
$stmt->bind_param("iii", $user_id, $expert_id, $rating);
$stmt->execute();
$stmt->close();

// Обновляем рейтинг эксперта
$stmt = $conn->prepare("
    UPDATE experts 
    SET rating = (SELECT AVG(rating) FROM user_ratings WHERE expert_id = ?)
    WHERE user_id = ?
");
$stmt->bind_param("ii", $expert_id, $expert_id);
$stmt->execute();
$stmt->close();

header("Location: ../pages/reviews.php?profession_id=" . $profession_id);
exit();
?>