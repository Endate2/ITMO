<?php
session_start();
require_once 'php/db.php'; // Подключение к базе данных

// Если есть параметр mark_as_read в URL, помечаем уведомление как прочитанное
if (isset($_GET['mark_as_read']) && is_numeric($_GET['mark_as_read']) && isset($_SESSION['user_id'])) {
    $notification_id = $_GET['mark_as_read'];
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Перенаправляем обратно без параметра, чтобы избежать повторной обработки
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITMO Portal</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style1.css" rel="stylesheet">
    <style>
        .notification-item {
            padding: 10px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .notification-item hr {
            margin: 5px 0;
        }
        .alert-info {
            max-height: 500px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <!-- Навигационная панель -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <a class="navbar-brand" href="index.php">ITMO Portal</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <?php if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'expert')): ?>
                    <li class="nav-item">
                        <a href="pages/chat.php" class="btn btn-outline-dark mr-2">Чат</a>
                    </li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a href="pages/proflist.php" class="btn btn-outline-dark mr-2">Управление профессиями</a>
                    </li>
                    <li class="nav-item">
                        <a href="pages/admin_page.php" class="btn btn-outline-dark mr-2">Админ панель</a>
                    </li>
                <?php endif; ?>
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="nav-item">
                        <div class="dropdown">
                            <button class="btn btn-outline-dark mr-2 dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Пройти тесты
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item" href="tests/operativ_memory_test.php?user_id=<?php echo $_SESSION['user_id']; ?>">Тест на оперативную память</a>
                                <a class="dropdown-item" href="tests/additiontest.html?user_id=<?php echo $_SESSION['user_id']; ?>">Тест на сложение</a>
                                <a class="dropdown-item" href="tests/combinedtest.html?user_id=<?php echo $_SESSION['user_id']; ?>">Комплексный тест</a>
                                <a class="dropdown-item" href="tests/testtest.html?user_id=<?php echo $_SESSION['user_id']; ?>">Тест с одним кружком</a>
                                <a class="dropdown-item" href="tests/visual_memory_test.php?user_id=<?php echo $_SESSION['user_id']; ?>">Тест на визувльную память</a>
                            </div>
                        </div>
                    </li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'consultant'): ?>
                    <li class="nav-item">
                        <a href="consultant/consultant_panel.php" class="btn btn-outline-dark mr-2">Панель консультанта</a>
                    </li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user'): ?>
                    <li class="nav-item">
                        <a href="user_results.php" class="btn btn-outline-dark mr-2">Мои результаты</a>
                    </li>
                <?php endif; ?>
                <?php if (!isset($_SESSION['username'])): ?>
                    <li class="nav-item">
                        <a href="pages/register.html" class="btn btn-outline-dark mr-2">Зарегистрироваться</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <?php if (isset($_SESSION['username'])): ?>
                        <span class="navbar-text mr-2"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="pages/logout.php" class="btn btn-outline-dark">Выйти</a>
                    <?php else: ?>
                        <a href="pages/registr.html" class="btn btn-outline-dark">Вход на портал</a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Уведомления -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <?php
        // Получение уведомлений для текущего пользователя
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        ?>

        <?php if (count($notifications) > 0): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert" style="margin-top: 70px;">
                <strong>У вас новые уведомления!</strong>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item">
                        <p><?php echo htmlspecialchars($notification['message']); ?></p>
                        <?php if (strpos($notification['message'], 'назначен тест') !== false): ?>
                            <?php 
                            // Извлекаем название теста из сообщения
                            preg_match('/назначен тест: (\w+)/', $notification['message'], $matches);
                            $test_name = $matches[1] ?? '';
                            $test_url = '';
                            
                            // Сопоставляем названия тестов с URL
                            switch($test_name) {
                                case 'simple_reaction':
                                    $test_url = 'tests/light_test.html';
                                    break;
                                case 'complex_reaction':
                                    $test_url = 'tests/combinedtest.html';
                                    break;
                                case 'addition_parity_test':
                                    $test_url = 'tests/additiontest.html';
                                    break;
                                case 'circle_reaction_test':
                                    $test_url = 'tests/testtest.html';
                                    break;
                                case 'visual_memory_test':
                                        $test_url = 'tests/visual_memory_test.php';
                                        break;
                                case 'combined_operator':
                                        $test_url = 'tests/operativ_memory_tests.php';
                                        break;
                            }
                            
                            if ($test_url): ?>
                                <a href="<?php echo $test_url; ?>?user_id=<?php echo $_SESSION['user_id']; ?>&notification_id=<?php echo $notification['id']; ?>" 
                                   class="btn btn-primary btn-sm mt-2">
                                    Пройти тест
                                </a>
                                <a href="index.php?mark_as_read=<?php echo $notification['id']; ?>" 
                                   class="btn btn-secondary btn-sm mt-2">
                                    Прочитано
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="index.php?mark_as_read=<?php echo $notification['id']; ?>" 
                               class="btn btn-secondary btn-sm mt-2">
                                Прочитано
                            </a>
                        <?php endif; ?>
                    </div>
                    <hr>
                <?php endforeach; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Основной контент -->
    <div class="container mt-5 pt-5">
        <div class="text-center mb-4">
            <img src="assets/img/1.png" alt="Логотип" class="img-fluid" style="max-height: 150px;">
        </div>

        <h1 class="text-center mb-4">Добро пожаловать на ITMO Portal!</h1>
        <p class="text-center">Здесь вы можете узнать о различных IT-профессиях, зарегистрироваться и получить консультацию.</p>

        <!-- Кнопка по центру -->
        <div class="text-center">
            <a href="pages/professii.php" class="btn btn-outline-dark mt-4">Начать свой путь в ИТ</a>
        </div>

        <!-- Информация о портале -->
        <div class="row mt-5">
            <div class="col-md-4 mb-4">
                <div class="card profession-card">
                    <div class="card-body">
                        <h5 class="card-title">Кто мы?</h5>
                        <p class="card-text">Мы команда экспертов в области IT, которые помогают людям найти свой путь в мире информационных технологий.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card profession-card">
                    <div class="card-body">
                        <h5 class="card-title">Зачем мы?</h5>
                        <p class="card-text">Мы даем возможность людям узнать подробнее о профессиях в информационных технологиях и выбрать подходящую карьеру.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card profession-card">
                    <div class="card-body">
                        <h5 class="card-title">Наша миссия</h5>
                        <p class="card-text">Мы стремимся сделать IT-образование доступным для всех, чтобы каждый мог реализовать свой потенциал в цифровом мире.</p>
                    </div>
                </div>
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

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>