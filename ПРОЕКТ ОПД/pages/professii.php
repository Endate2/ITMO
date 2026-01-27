<?php
session_start();
require '../php/db.php';

// Получаем список профессий
$stmt = $conn->prepare("SELECT id, name, description FROM professions");
$stmt->execute();
$result = $stmt->get_result();
$professions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профессии в IT</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style1.css">
</head>
<body>
    <!-- Навигационная панель -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <a class="navbar-brand" href="../index.php">ITMO Portal</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <?php if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'expert')): ?>
                    <li class="nav-item">
                        <a href="chat.php" class="btn btn-outline-dark mr-2">Чат</a>
                    </li>
                <?php endif; ?>
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
                    <a class="btn btn-outline-dark mr-2" href="../index.php">На главную</a>
                </li>
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

    <!-- Основной контент -->
    <div class="container mt-5 pt-5">
        <h1 class="text-center mb-4">Профессии в IT</h1>
        <div class="row">
            <?php foreach ($professions as $profession): ?>
                <div class="col-md-4 mb-4">
                    <div class="card profession-card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($profession['name']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($profession['description']) ?></p>

                            <!-- Средняя оценка -->
                            <?php
                            $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM ratings WHERE profession_id = ?");
                            $stmt->bind_param("i", $profession['id']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $avg_rating = $result->fetch_assoc()['avg_rating'];
                            $stmt->close();
                            ?>
                            <p><strong>Средняя оценка:</strong> <?= $avg_rating ? round($avg_rating, 1) : 'Нет оценок' ?></p>

                            <!-- Ссылки на отзывы и ПВК -->
                            <div class="hidden-content">
                                <a href="reviews.php?profession_id=<?= $profession['id'] ?>" class="btn btn-outline-dark btn-sm mb-2">Посмотреть отзывы</a>
                                <a href="pvk.php?profession_id=<?= $profession['id'] ?>" class="btn btn-outline-dark btn-sm mb-2">Посмотреть ПВК</a>

                                <!-- Формы для экспертов -->
                                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'expert'): ?>
                                    <?php
                                    $stmt = $conn->prepare("SELECT id FROM reviews WHERE expert_id = ? AND profession_id = ?");
                                    $stmt->bind_param("ii", $_SESSION['user_id'], $profession['id']);
                                    $stmt->execute();
                                    $stmt->store_result();
                                    $has_review = $stmt->num_rows > 0;
                                    $stmt->close();

                                    $stmt = $conn->prepare("SELECT id FROM ratings WHERE expert_id = ? AND profession_id = ?");
                                    $stmt->bind_param("ii", $_SESSION['user_id'], $profession['id']);
                                    $stmt->execute();
                                    $stmt->store_result();
                                    $has_rating = $stmt->num_rows > 0;
                                    $stmt->close();
                                    ?>

                                    <!-- Форма для отзыва -->
                                    <?php if (!$has_review): ?>
                                    <form action="../ajax/addreview.php" method="POST" class="mb-2">
                                        <input type="hidden" name="profession_id" value="<?= $profession['id'] ?>">
                                        <textarea name="review" placeholder="Ваш отзыв" required class="form-control mb-2"></textarea>
                                        <button type="submit" class="btn btn-outline-dark btn-sm">Оставить отзыв</button>
                                    </form>
                                    <?php endif; ?>
				    <?php if ($has_review): ?>
					<div>
					    <small class="text-danger">Вы уже оставили отзыв на эту профессию.</small>
					</div>
				    <?php endif; ?>

                                    <!-- Форма для оценки -->
				    <?php if (!$has_rating): ?>
                                    <form action="../ajax/addrating.php" method="POST">
                                        <input type="hidden" name="profession_id" value="<?= $profession['id'] ?>">
                                        <input type="number" name="rating" min="1" max="5" placeholder="Оценка (1-5)" required class="form-control mb-2">
                                        <button type="submit" class="btn btn-outline-dark btn-sm">Оценить</button>
                                    </form>
				    <?php endif; ?>
				    <?php if ($has_rating): ?>
					<div>
					   <small class="text-danger">Вы уже оценили эту профессию.</small>
					</div>
				    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
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