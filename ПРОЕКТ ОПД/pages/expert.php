<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'expert') {
    header("Location: ../pages/login.php");
    exit();
}

require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profession_id'], $_POST['rating'])) {
    $profession_id = intval($_POST['profession_id']);
    $rating = intval($_POST['rating']);
    $expert_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO ratings (expert_id, profession_id, rating) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE rating = VALUES(rating)");
    $stmt->bind_param("iii", $expert_id, $profession_id, $rating);
    $stmt->execute();
}

$professions = $conn->query("SELECT * FROM professions");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Оценка профессий</title>
</head>
<body>
    <h1>Оценка профессий</h1>
    <table>
        <tr><th>Профессия</th><th>Оценка</th><th>Действие</th></tr>
        <?php while ($row = $professions->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="profession_id" value="<?= $row['id'] ?>">
                        <select name="rating">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                        <button type="submit">Оценить</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>