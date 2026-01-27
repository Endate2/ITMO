<?php
session_start();
require '../php/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Доступ запрещен. Только администраторы могут просматривать эту страницу.");
}

// Получаем список пользователей
$stmt = $conn->prepare("SELECT id, username, email, role FROM users");
$stmt->execute();
$result = $stmt->get_result();

// Получаем список профессий
$stmt = $conn->prepare("SELECT id, name FROM professions");
$stmt->execute();
$professions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();


// Получаем список закрепленных профессий за экспертами
$stmt = $conn->prepare("SELECT ep.expert_id, ep.profession_id, u.username, p.name 
                        FROM profession_expert ep
                        JOIN users u ON ep.expert_id = u.id
                        JOIN professions p ON ep.profession_id = p.id");
$stmt->execute();
$expert_professions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Получаем список экспертов
$stmt = $conn->prepare("SELECT id, username FROM users WHERE role = 'expert'");
$stmt->execute();
$experts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>



<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style1.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <a class="navbar-brand" href="../index.php">ITMO Portal</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <?php if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'expert')): ?>
                    <li class="nav-item">
                        <a href="chat.php" class="btn btn-outline-dark mr-2">Чат</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="btn btn-outline-dark mr-2" href="polizovateli.php">Типы пользователей</a>
                </li>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a href="proflist.php" class="btn btn-outline-dark mr-2">Управление профессиями</a>
                    </li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a href="admin_page.php" class="btn btn-outline-dark mr-2">Админ панель</a>
                    </li>
                <?php endif; ?>
                <?php if (!isset($_SESSION['username'])): ?>
                    <li class="nav-item">
                        <a href="register.html" class="btn btn-outline-dark mr-2">Зарегистрироваться</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <?php if (isset($_SESSION['username'])): ?>
                        <span class="navbar-text mr-2"><?= htmlspecialchars($_SESSION['username']) ?></span>
                        <a href="logout.php" class="btn btn-outline-dark">Выйти</a>
                    <?php else: ?>
                        <a href="registr.html" class="btn btn-outline-dark">Вход на портал</a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5 pt-5">
        <h1 class="text-center mb-4">Панель администратора</h1>

<!-- Таблица закрепленных профессий за экспертами -->
<div class="container mt-4">
    <h2>Закрепленные профессии за экспертами</h2>
    <table class="table table-bordered text-center">
        <thead class="thead-dark">
            <tr>
                <th>Эксперт</th>
                <th>Профессия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($expert_professions as $ep): ?>
                <tr>
                    <td><?= htmlspecialchars($ep['username']) ?></td>
                    <td><?= htmlspecialchars($ep['name']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
        <!-- Форма для закрепления эксперта за профессией -->
        <div class="container mt-4">
            <h2>Закрепить эксперта за профессией</h2>
            <form action="../ajax/assign_expert.php" method="POST">
                <div class="form-group">
                    <label for="profession_id">Профессия</label>
                    <select name="profession_id" id="profession_id" class="form-control" required>
                        <?php foreach ($professions as $profession): ?>
                            <option value="<?= $profession['id'] ?>"><?= htmlspecialchars($profession['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="expert_id">Эксперт</label>
                    <select name="expert_id" id="expert_id" class="form-control" required>
                        <?php foreach ($experts as $expert): ?>
                            <option value="<?= $expert['id'] ?>"><?= htmlspecialchars($expert['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Закрепить</button>
            </form>
        </div>

<!-- Форма для удаления закрепления эксперта -->
<div class="container mt-4 mb-4"> <!-- Добавлен класс mb-4 для отступа снизу -->
    <h2>Удалить закрепление эксперта</h2>
    <form action="../ajax/remove_expert.php" method="POST">
        <div class="form-group">
            <label for="profession_id">Профессия</label>
            <select name="profession_id" id="profession_id" class="form-control" required>
                <?php foreach ($professions as $profession): ?>
                    <option value="<?= $profession['id'] ?>"><?= htmlspecialchars($profession['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="expert_id">Эксперт</label>
            <select name="expert_id" id="expert_id" class="form-control" required>
                <?php foreach ($experts as $expert): ?>
                    <option value="<?= $expert['id'] ?>"><?= htmlspecialchars($expert['username']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-danger">Удалить закрепление</button>
    </form>
</div>


        <!-- Таблица пользователей -->
        <input type="text" id="search" class="form-control mb-4" placeholder="Поиск по имени, email или id" onkeyup="searchUsers()">
        <table class="table table-bordered text-center">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Имя пользователя</th>
                    <th>Email</th>
                    <th>Роль</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <form action="../ajax/update_role.php" method="POST" class="form-inline justify-content-center">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <select name="role" class="form-control mr-2">
                                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Пользователь</option>
                                    <option value="expert" <?= $user['role'] === 'expert' ? 'selected' : '' ?>>Эксперт</option>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Админ</option>
                                    <option value="consultant" <?= $user['role'] === 'consultant' ? 'selected' : '' ?>>Консультант</option>
                                </select>
                                <button type="submit" class="btn btn-primary">Сохранить</button>
                            </form>
                        </td>
                        <td>
                            <form action="delete_user.php" method="POST" class="d-inline">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Удалить пользователя?');">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
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
    <script>
        function searchUsers() {
            let input = document.getElementById("search").value.toLowerCase();
            let rows = document.querySelectorAll("table tbody tr");
            rows.forEach((row) => {
                let columns = row.getElementsByTagName("td");
                let match = false;
                for (let col of columns) {
                    if (col.textContent.toLowerCase().includes(input)) {
                        match = true;
                        break;
                    }
                }
                row.style.display = match ? "" : "none";
            });
        }
    </script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>