<?php
session_start();
require_once '../php/db.php';

// Проверка роли пользователя
if ($_SESSION['user_role'] !== 'consultant') {
    header('Location: ../index.php');
    exit();
}

// Пагинация для результатов тестов
$limit = 5; // Количество строк на странице
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Получение общего количества записей
$sql = "SELECT COUNT(*) as total FROM test_results";
$total_results = $conn->query($sql)->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);

// Получение результатов с пагинацией
$sql = "SELECT * FROM test_results ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$results = $stmt->get_result();

// Получение списка пользователей
$sql = "SELECT id, username FROM users WHERE role = 'user'";
$users = $conn->query($sql);

// Получение сообщений для выбранного пользователя
$selected_user_id = $_GET['user_id'] ?? null;
$messages = [];
if ($selected_user_id) {
    $sql = "SELECT * FROM messages WHERE (from_user_id = ? AND to_user_id = ?) OR (from_user_id = ? AND to_user_id = ?) ORDER BY created_at";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $_SESSION['user_id'], $selected_user_id, $selected_user_id, $_SESSION['user_id']);
    $stmt->execute();
    $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель консультанта</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .chat-box {
            height: 300px;
            overflow-y: scroll;
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
        }
        .message {
            margin-bottom: 10px;
        }
        .message p {
            margin: 0;
        }
        .message small {
            color: #666;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
        }
        .table-container {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Панель консультанта</h1>
        
        <!-- Таблица с результатами -->
        <div class="table-container">
            <h3>Результаты тестов</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Тест</th>
                        <th>Время реакции</th>
                        <th>Правильные ответы</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $results->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['user_id']; ?></td>
                            <td><?php echo $row['test_name']; ?></td>
                            <td><?php echo $row['reaction_time']; ?></td>
                            <td><?php echo $row['correct_answers']; ?></td>
                            <td><?php echo $row['created_at']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Пагинация -->
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>

        <!-- Назначение тестов -->
        <div class="row mt-4">
            <div class="col-md-12">
                <h3>Назначить тест</h3>
                <form action="assign_test.php" method="POST">
                    <div class="form-group">
                        <label for="respondent_id">Выберите респондента:</label>
                        <select name="respondent_id" id="respondent_id" class="form-control" required>
                            <?php
                            $sql = "SELECT id, username FROM users WHERE role = 'user'";
                            $result = $conn->query($sql);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>{$row['username']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="test_name">Выберите тест:</label>
                        <select name="test_name" id="test_name" class="form-control" required>
                            <option value="simple_reaction">Простая сенсомоторная реакция</option>
                            <option value="complex_reaction">Сложная сенсомоторная реакция</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Назначить тест</button>
                </form>
            </div>
        </div>

        <!-- Чат -->
        <div class="row mt-4">
            <div class="col-md-4">
                <h3>Пользователи</h3>
                <ul class="list-group">
                    <?php
                    $sql = "SELECT id, username FROM users WHERE role = 'user'";
                    $result = $conn->query($sql);
                    while ($user = $result->fetch_assoc()): ?>
                        <li class="list-group-item">
                            <a href="?user_id=<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <div class="col-md-8">
                <?php if ($selected_user_id): ?>
                    <h3>Чат с пользователем</h3>
                    <div class="chat-box">
                        <?php foreach ($messages as $message): ?>
                            <div class="message <?php echo $message['from_user_id'] == $_SESSION['user_id'] ? 'text-right' : 'text-left'; ?>">
                                <p><strong><?php echo $message['from_user_id'] == $_SESSION['user_id'] ? 'Вы' : 'Пользователь'; ?>:</strong></p>
                                <p><?php echo htmlspecialchars($message['message']); ?></p>
                                <small><?php echo $message['created_at']; ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Форма отправки сообщения -->
                    <form action="send_message.php" method="POST" class="mt-3">
                        <input type="hidden" name="to_user_id" value="<?php echo $selected_user_id; ?>">
                        <div class="form-group">
                            <textarea name="message" class="form-control" placeholder="Введите сообщение" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Отправить</button>
                    </form>
                <?php else: ?>
                    <p>Выберите пользователя, чтобы начать чат.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>