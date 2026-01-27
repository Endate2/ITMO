<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'expert') {
    header("Location: ../pages/login.php");
    exit();
}

require '../php/db.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $expert_id = $_SESSION['user_id'];
    $profession_id = $_POST['profession_id'];
    $review = trim($_POST['review']);

    $stmt = $conn->prepare("SELECT id FROM reviews WHERE expert_id = ? AND profession_id = ?");
    $stmt->bind_param("ii", $expert_id, $profession_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Вы уже оставили отзыв для этой профессии.";
        $stmt->close();
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO reviews (expert_id, profession_id, review) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $expert_id, $profession_id, $review);

    if ($stmt->execute()) {
        header("Location: ../pages/professii.php");
        exit();
    } else {
        echo "Ошибка при добавлении отзыва.";
    }

    $stmt->close();
}
?>