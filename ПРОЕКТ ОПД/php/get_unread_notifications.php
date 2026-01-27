<?php
session_start();
require_once '../php/db.php';

if (!isset($_SESSION['user_id'])) {
    die("0");
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

echo $result['count'];
$conn->close();
?>