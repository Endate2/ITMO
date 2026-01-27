<?php
session_start();
require '../php/db.php'; // Подключение к базе данных

// Обработка добавления новой профессии
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_profession'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];

    // Валидация данных
    if (!empty($name) && !empty($description)) {
        $stmt = $conn->prepare("INSERT INTO professions (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        $stmt->execute();
        $stmt->close();
        header("Location: proflist.php"); // Перезагрузка страницы
        exit();
    } else {
        $error = "Все поля обязательны для заполнения!";
    }
}

// Обработка удаления профессии
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM professions WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: proflist.php"); // Перезагрузка страницы
    exit();
}

// Обработка редактирования профессии
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_profession'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];

    if (!empty($name) && !empty($description)) {
        $stmt = $conn->prepare("UPDATE professions SET name = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $description, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: proflist.php"); // Перезагрузка страницы
        exit();
    } else {
        $error = "Все поля обязательны для заполнения!";
    }
}

// Получение списка профессий
$stmt = $conn->prepare("SELECT id, name, description FROM professions");
$stmt->execute();
$result = $stmt->get_result();
$professions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-16">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление профессиями</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style1.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #FFFFFF;
            color: #212529;
        }

        .navbar {
            background-color: #FFFFFF !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .btn-outline-dark {
            border-color: #212529;
            color: #212529;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .btn-outline-dark:hover {
            background-color: #212529;
            color: #FFFFFF;
        }

        .profession-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .profession-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .hidden-content {
            display: none;
        }

        .profession-card:hover .hidden-content {
            display: block;
        }

        .footer {
            margin-top: auto;
        }

        .footer a {
            color: #212529;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: #6C757D !important;
        }

        .hidden-form {
            display: none;
        }

        .table {
            margin-top: 20px;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Навигационная панель -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <a class="navbar-brand" href="../index.php">ITMO Portal</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a href="admin_page.php" class="btn btn-outline-dark mr-2">Админ панель</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="btn btn-outline-dark mr-2" href="../index.php">На главную</a>
                </li>
                <?php if (isset($_SESSION['username'])): ?>
                    <span class="navbar-text mr-2"><?= htmlspecialchars($_SESSION['username']) ?></span>
                    <a href="logout.php" class="btn btn-outline-dark">Выйти</a>
                <?php else: ?>
                    <a href="registr.html" class="btn btn-outline-dark">Вход на портал</a>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Основной контент -->
    <div class="container mt-5 pt-5">
        <h1 class="text-center mb-4">Управление профессиями</h1>

        <!-- Форма добавления новой профессии -->
        <div class="card profession-card">
            <div class="card-body">
                <h5 class="card-title">Добавить новую профессию</h5>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Название профессии</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Краткое описание</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <button type="submit" name="add_profession" class="btn btn-outline-dark">Добавить</button>
                </form>
            </div>
        </div>

        <!-- Таблица списка профессий -->
        <div class="card profession-card">
            <div class="card-body">
                <h5 class="card-title">Список профессий</h5>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Описание</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($professions as $profession): ?>
                            <tr>
                                <td><?= htmlspecialchars($profession['id']) ?></td>
                                <td><?= htmlspecialchars($profession['name']) ?></td>
                                <td><?= htmlspecialchars($profession['description']) ?></td>
                                <td>
                                    <a href="?delete_id=<?= $profession['id'] ?>" class="btn btn-outline-dark btn-sm" onclick="return confirm('Вы уверены?')">Удалить</a>
                                    <button class="btn btn-outline-dark btn-sm edit-btn" data-id="<?= $profession['id'] ?>" data-name="<?= htmlspecialchars($profession['name']) ?>" data-description="<?= htmlspecialchars($profession['description']) ?>">Редактировать</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Форма редактирования профессии (скрытая) -->
        <div id="editForm" class="card profession-card hidden-form">
            <div class="card-body">
                <h5 class="card-title">Редактировать профессию</h5>
                <form method="POST" action="">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="form-group">
                        <label for="edit_name">Название профессии</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Краткое описание</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                    <button type="submit" name="edit_profession" class="btn btn-outline-dark">Сохранить</button>
                    <button type="button" id="cancelEdit" class="btn btn-outline-dark">Отмена</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Футер -->
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

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        // Открытие формы редактирования
        $(document).ready(function() {
            $('.edit-btn').click(function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const description = $(this).data('description');

                $('#edit_id').val(id);
                $('#edit_name').val(name);
                $('#edit_description').val(description);
                $('#editForm').removeClass('hidden-form');
            });

            // Отмена редактирования
            $('#cancelEdit').click(function() {
                $('#editForm').addClass('hidden-form');
            });
        });
    </script>
</body>
</html>
