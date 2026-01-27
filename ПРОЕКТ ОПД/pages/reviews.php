<?php
session_start();



require '../php/db.php'; 

if (!isset($_GET['profession_id'])) {
    header("Location: ../pages/professii.php"); 
    exit();
}

$profession_id = $_GET['profession_id'];

$stmt = $conn->prepare("
    SELECT r.id as review_id, r.review, rt.rating, r.created_at, u.username, r.expert_id 
    FROM reviews r
    LEFT JOIN ratings rt ON r.expert_id = rt.expert_id AND r.profession_id = rt.profession_id
    JOIN users u ON r.expert_id = u.id 
    WHERE r.profession_id = ?
");
$stmt->bind_param("i", $profession_id);
$stmt->execute();
$result = $stmt->get_result();
$reviews = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отзывы</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center mb-4">Отзывы</h1>
        <a href="professii.php" class="btn btn-secondary mb-4">Назад</a>
        <div class="list-group">
            <?php if (count($reviews) > 0): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="list-group-item">
                        <h5><?= htmlspecialchars($review['username']) ?></h5>
                        <p><?= htmlspecialchars($review['review']) ?></p>
                        <p><strong>Оценка:</strong> <?= $review['rating'] ?? 'Нет оценки' ?></p>
                        <small><?= $review['created_at'] ?></small>
                        <?php if (isset($_SESSION['user_role'])&&($_SESSION['user_role'] === 'admin' || $_SESSION['user_id'] == $review['expert_id'])): ?>
                            <form action="../ajax/deletereview.php" method="POST" style="display: inline;">
                                <input type="hidden" name="review_id" value="<?= $review['review_id'] ?>">
                                <input type="hidden" name="profession_id" value="<?= $profession_id ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="list-group-item">
                    <p>Отзывов пока нет.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>