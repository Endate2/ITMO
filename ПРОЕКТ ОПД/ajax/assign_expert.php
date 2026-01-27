<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Доступ запрещен.");
}

$profession_id = $_POST['profession_id'];
$expert_id = $_POST['expert_id'];

// Проверяем, не закреплен ли уже эксперт за этой профессией
$stmt = $conn->prepare("SELECT id FROM profession_expert WHERE profession_id = ? AND expert_id = ?");
$stmt->bind_param("ii", $profession_id, $expert_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    die("Этот эксперт уже закреплен за данной профессией.");
}
$stmt->close();

// Закрепляем эксперта за профессией
$stmt = $conn->prepare("INSERT INTO profession_expert (profession_id, expert_id) VALUES (?, ?)");
$stmt->bind_param("ii", $profession_id, $expert_id);
$stmt->execute();
$stmt->close();

echo "Эксперт успешно закреплен за профессией.";
header("Location: ../pages/admin_page.php");
exit();
?>