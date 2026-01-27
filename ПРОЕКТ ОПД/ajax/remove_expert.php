<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Доступ запрещен.");
}

$profession_id = $_POST['profession_id'];
$expert_id = $_POST['expert_id'];

$stmt = $conn->prepare("DELETE FROM profession_expert WHERE profession_id = ? AND expert_id = ?");
$stmt->bind_param("ii", $profession_id, $expert_id);
$stmt->execute();
$stmt->close();

echo "Закрепление удалено.";
header("Location: ../pages/admin_page.php");
exit();
?>