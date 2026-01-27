<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php"); 
    exit();
}

require '../php/db.php'; 

if (!isset($_POST['review_id']) || !isset($_POST['profession_id'])) {
    header("Location: ../pages/professii.php");
    exit();
}

$review_id = $_POST['review_id'];
$profession_id = $_POST['profession_id'];

$stmt = $conn->prepare("SELECT expert_id FROM reviews WHERE id = ?");
$stmt->bind_param("i", $review_id);
$stmt->execute();
$result = $stmt->get_result();
$review = $result->fetch_assoc();
$stmt->close();

if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_id'] == $review['expert_id']) {
    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->bind_param("i", $review_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM ratings WHERE expert_id = ? AND profession_id = ?");
    $stmt->bind_param("ii", $review['expert_id'], $profession_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: ../pages/reviews.php?profession_id=" . $profession_id);
exit();
?>