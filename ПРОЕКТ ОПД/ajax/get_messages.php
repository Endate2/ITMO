<?php
session_start();
require '../php/db.php';

if (isset($_GET['receiver_id'])) {
    $receiver_id = $_GET['receiver_id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY timestamp ASC");
    $stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($messages as $msg): ?>
        <p class="message">
            <strong><?= $msg['sender_id'] == $user_id ? 'Вы' : ($_SESSION['user_role'] === 'user' ? 'Консультант' : 'Пользователь') ?>:</strong>
            <?= htmlspecialchars($msg['message']) ?>
        </p>
    <?php endforeach;
}
?>