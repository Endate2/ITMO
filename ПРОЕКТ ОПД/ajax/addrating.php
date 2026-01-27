<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user' && $_SESSION['user_role'] !== 'expert') {
    header("Location: ../pages/registr.html"); 
    exit();
}

require '../php/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $expert_id = $_SESSION['user_id'];
    $profession_id = $_POST['profession_id'];
    $rating = $_POST['rating'];

    $stmt = $conn->prepare("INSERT INTO ratings (expert_id, profession_id, rating) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $expert_id, $profession_id, $rating);
    $stmt->execute();
    $stmt->close();

    header("Location: ../pages/professii.php");
    exit();
}
?>