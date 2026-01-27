<?php
session_start();
require '../php/db.php';

// Проверка profession_id
if (!isset($_GET['profession_id'])) {
    header("Location: ../pages/professii.php");
    exit();
}

$profession_id = $_GET['profession_id'];

// Проверка роли пользователя
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'expert') {
    header("Location: experts_pvk.php?profession_id=" . $profession_id);
    exit();
}

// Получаем все ПВК
$stmt = $conn->prepare("SELECT id, name, description FROM pvk");
$stmt->execute();
$pvk_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Проверяем текущий шаг
$current_step = isset($_GET['step']) ? intval($_GET['step']) : 1;

// Обработка выбора ПВК (шаг 1)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pvk_ids'])) {
    $_SESSION['selected_pvk'] = [
        'profession_id' => $_POST['profession_id'],
        'pvk_ids' => $_POST['pvk_ids']
    ];
    header("Location: pvk.php?profession_id=" . $_POST['profession_id'] . "&step=2");
    exit();
}

// Обработка сохранения приоритетов (шаг 2)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['priorities'])) {
    $profession_id = $_POST['profession_id'];
    $expert_id = $_SESSION['user_id'];
    $priorities = $_POST['priorities'];

    // Удаляем старые оценки
    $stmt = $conn->prepare("DELETE FROM expert_pvk_lists WHERE expert_id = ? AND profession_id = ?");
    $stmt->bind_param("ii", $expert_id, $profession_id);
    $stmt->execute();
    $stmt->close();

    // Сохраняем новые оценки
    foreach ($priorities as $pvk_id => $priority) {
        $stmt = $conn->prepare("
            INSERT INTO expert_pvk_lists (expert_id, profession_id, pvk_id, priority)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiii", $expert_id, $profession_id, $pvk_id, $priority);
        $stmt->execute();
        $stmt->close();
    }

    unset($_SESSION['selected_pvk']);
    header("Location: experts_pvk.php?profession_id=" . $profession_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оценка ПВК</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .pvk-item { 
            margin-bottom: 15px; 
            padding: 15px; 
            border: 1px solid #ddd; 
            border-radius: 5px;
            margin-left: 10px; /* Сдвиг вправо */
        }
        .form-check-input {
            margin-top: 8px; /* Опускаем чекбокс ниже */
            margin-left: 5px; /* Сдвиг чекбокса вправо */
        }
	.pvk-text-content {
            margin-left: 25px;
	}
        .form-check-label {
            padding-left: 10px; /* Отступ текста от чекбокса */
            vertical-align: middle; /* Выравнивание по вертикали */
        }
        .priority-input { width: 80px; }
        .scrollable-container { max-height: 500px; overflow-y: auto; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <a class="navbar-brand" href="../index.php">ITMO Portal</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
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

    <div class="container mt-5 pt-5">
        <h1 class="text-center mb-4">Оценка ПВК</h1>
        
        <?php if ($current_step === 1): ?>
            <!-- Шаг 1: Выбор ПВК -->
            <h2>Выберите ПВК для оценки</h2>
            <form method="POST" action="pvk.php?profession_id=<?= $profession_id ?>">
                <input type="hidden" name="profession_id" value="<?= $profession_id ?>">
                <div class="scrollable-container">
                    <?php foreach ($pvk_list as $pvk): ?>
                        <div class="form-check pvk-item">
    				<div class="d-flex align-items-start"> 
        				<input class="form-check-input" type="checkbox" 
               					name="pvk_ids[]" 
               					value="<?= $pvk['id'] ?>" 
               					id="pvk_<?= $pvk['id'] ?>">
        				<div class="pvk-text-content"> <!-- Новый контейнер для текста -->
            			<h5 class="mb-1"><?= htmlspecialchars($pvk['name']) ?></h5>
            <p class="mb-0 text-muted"><?= htmlspecialchars($pvk['description']) ?></p>
        </div>
    </div>
</div>

                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Далее →</button>
            </form>

        <?php elseif ($current_step === 2 && isset($_SESSION['selected_pvk'])): ?>
            <!-- Шаг 2: Установка приоритетов -->
            <h2>Укажите приоритеты (1-10)</h2>
            <form method="POST" action="pvk.php?profession_id=<?= $profession_id ?>">
                <input type="hidden" name="profession_id" value="<?= $profession_id ?>">
                <div class="scrollable-container">
                    <?php 
                    $selected_ids = $_SESSION['selected_pvk']['pvk_ids'];
                    $selected_pvk = array_filter($pvk_list, function($item) use ($selected_ids) {
                        return in_array($item['id'], $selected_ids);
                    });
                    
                    foreach ($selected_pvk as $pvk): ?>
                        <div class="pvk-item">
                            <h5><?= htmlspecialchars($pvk['name']) ?></h5>
                            <p><?= htmlspecialchars($pvk['description']) ?></p>
                            <div class="form-group">
                                <label>Приоритет:</label>
                                <input type="number" 
                                       name="priorities[<?= $pvk['id'] ?>]" 
                                       class="form-control priority-input" 
                                       min="1" 
                                       max="10" 
                                       required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn btn-success">Сохранить оценки</button>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

