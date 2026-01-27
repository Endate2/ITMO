<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/registr.html"); 
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['user_role'];
$username = $_SESSION['username'];

if ($role === 'consultant') {
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE role = 'user'");
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

if ($role === 'user') {
    $stmt = $conn->prepare("SELECT sender_id FROM messages WHERE receiver_id = ? AND sender_id IN (SELECT id FROM users WHERE role = 'consultant') LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $consultant = $result->fetch_assoc();
    $stmt->close();

    if ($consultant) {
        $consultant_id = $consultant['sender_id'];
    } else {
        $no_consultant_message = "Консультант еще не связался с вами.";
    }
}

if (isset($_GET['receiver_id']) || isset($consultant_id)) {
    $receiver_id = ($role === 'consultant') ? $_GET['receiver_id'] : $consultant_id;

    $stmt = $conn->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY timestamp ASC");
    $stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Чат с консультантом</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style1.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <a class="navbar-brand" href="../index.php">ITMO Portal</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="btn btn-outline-dark mr-2" href="../index.php">На главную</a>
                </li>
                <li class="nav-item">
                    <span class="navbar-text mr-2"><?= htmlspecialchars($username) ?></span>
                    <a class="btn btn-outline-dark" href="logout.php">Выйти</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5 pt-5">
        <h1 class="text-center mb-4">Чат с консультантом</h1>

        <?php if ($role === 'consultant'): ?>
            <div class="form-group">
                <form method="GET" action="">
                    <label for="receiver">Выберите пользователя:</label>
                    <select name="receiver_id" id="receiver" class="form-control">
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary mt-2">Выбрать</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if (isset($no_consultant_message)): ?>
            <div class="alert alert-info text-center mt-4">
                <?= $no_consultant_message ?>
            </div>
        <?php endif; ?>

        <div id="chat-box" class="border p-3 mb-3" style="height: 400px; overflow-y: auto;">
            <?php if (isset($messages) && !empty($messages)): ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message mb-2">
                        <strong><?= $msg['sender_id'] == $user_id ? 'Вы' : ($role === 'user' ? 'Консультант' : 'Пользователь') ?>:</strong>
                        <span><?= htmlspecialchars($msg['message']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center mt-4">
                    <p class="text-muted">Нет сообщений. Начните общение!</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if (isset($receiver_id)): ?>
            <div id="chat-input">
                <form method="POST" action="../ajax/send_message.php">
                    <input type="hidden" name="receiver_id" value="<?= $receiver_id ?>">
                    <div class="input-group">
                        <input type="text" name="message" id="message" class="form-control" placeholder="Введите сообщение" required>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">Отправить</button>
                        </div>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer bg-light text-dark mt-5">
        <div class="container py-4">
            <div class="row">
                <div class="col-md-4">
                    <h5>ITMO Portal</h5>
                    <p>Ваш портал в мир IT-профессий.</p>
                </div>
                <div class="col-md-4">
                    <h5>Контакты</h5>
                    <p>Email: info@itmoportal.ru</p>
                    <p>Телефон: +7 (999) 123-45-67</p>
                </div>
                <div class="col-md-4">
                    <h5>Социальные сети</h5>
                    <a href="#" class="text-dark">Facebook</a><br>
                    <a href="#" class="text-dark">Twitter</a><br>
                    <a href="#" class="text-dark">Instagram</a>
                </div>
            </div>
        </div>
        <div class="text-center py-3" style="background-color: rgba(0, 0, 0, 0.05);">
            © 2024 ITMO Portal. Все права защищены.
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../assets/js/chat.js"></script>
</body>
</html>