<?php
session_start();
require '../php/db.php';

// Проверка наличия profession_id
if (!isset($_GET['profession_id'])) {
    header("Location: ../pages/professii.php");
    exit();
}

$profession_id = intval($_GET['profession_id']);

// Инициализация переменных
$profession_name = '';
$pvk_list = [];
$ratings_details = [];
$is_assigned_expert = false;
$is_expert_or_admin = false;

// Проверка роли пользователя
if (isset($_SESSION['user_role'])) {
    $is_expert_or_admin = in_array($_SESSION['user_role'], ['expert', 'admin']);
    
    // Проверка, является ли пользователь закрепленным экспертом
    if ($_SESSION['user_role'] === 'expert' && isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT id FROM profession_expert WHERE profession_id = ? AND expert_id = ?");
        $stmt->bind_param("ii", $profession_id, $_SESSION['user_id']);
        $stmt->execute();
        $is_assigned_expert = $stmt->get_result()->num_rows > 0;
        $stmt->close();
    }
}

// Обработка удаления оценки
if (isset($_GET['delete_rating']) && ($is_assigned_expert || (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'))) {
    $rating_id = intval($_GET['delete_rating']);
    $stmt = $conn->prepare("DELETE FROM expert_pvk_lists WHERE id = ?");
    $stmt->bind_param("i", $rating_id);
    if ($stmt->execute()) {
        header("Location: experts_pvk.php?profession_id=" . $profession_id);
        exit();
    }
    $stmt->close();
}

// Получаем название профессии
$stmt = $conn->prepare("SELECT name FROM professions WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $profession_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $profession_name = $result->fetch_assoc()['name'] ?? '';
    }
    $stmt->close();
}

// Основной запрос для получения списка ПВК с учетом коэффициента 1.5 для закрепленных экспертов
$stmt = $conn->prepare("
    SELECT 
        pvk.id, 
        pvk.name, 
        pvk.description,
        AVG(CASE 
            WHEN pe.id IS NOT NULL THEN epl.priority * 1.5  -- Коэффициент 1.5 для закрепленных
            ELSE epl.priority
        END) as weighted_avg,
        IFNULL(STDDEV(epl.priority), 0) as std_deviation,
        COUNT(epl.id) as ratings_count
    FROM expert_pvk_lists epl
    JOIN pvk ON epl.pvk_id = pvk.id
    LEFT JOIN profession_expert pe ON epl.expert_id = pe.expert_id 
        AND epl.profession_id = pe.profession_id
    WHERE epl.profession_id = ?
    GROUP BY pvk.id
    ORDER BY weighted_avg ASC, std_deviation ASC
");

if ($stmt) {
    $stmt->bind_param("i", $profession_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $pvk_list = $result->fetch_all(MYSQLI_ASSOC) ?? [];
    }
    $stmt->close();
}

// Получаем детали оценок только если нужно показать детали
if (isset($_GET['show_details']) && $is_expert_or_admin) {
    $pvk_id = intval($_GET['show_details']);
    $stmt = $conn->prepare("
        SELECT 
            epl.id, 
            epl.priority, 
            u.username,
            CASE 
                WHEN pe.id IS NOT NULL THEN 'Да (x1.5)' 
                ELSE 'Нет' 
            END as expert_status
        FROM expert_pvk_lists epl
        JOIN users u ON epl.expert_id = u.id
        LEFT JOIN profession_expert pe ON epl.expert_id = pe.expert_id 
            AND epl.profession_id = pe.profession_id
        WHERE epl.profession_id = ? AND epl.pvk_id = ?
    ");
    if ($stmt) {
        $stmt->bind_param("ii", $profession_id, $pvk_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $ratings_details = $result->fetch_all(MYSQLI_ASSOC) ?? [];
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Рейтинг ПВК для профессии</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .scrollable-table { max-height: 500px; overflow-y: auto; }
        .table thead th { position: sticky; top: 0; background: white; }
        .rating-cell { min-width: 150px; }
        .details-row { background-color: #f8f9fa; }
        .expert-weighted { color: #d63384; font-weight: bold; }
    </style>
</head>
<body>
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

    <div class="container mt-5 pt-5">
        <h1 class="text-center mb-4">Рейтинг ПВК для профессии: <?= htmlspecialchars($profession_name) ?></h1>
        
        <?php if ($is_expert_or_admin): ?>
            <div class="mb-4">
                <a href="pvk.php?profession_id=<?= $profession_id ?>" class="btn btn-primary">
                    <?= $_SESSION['user_role'] === 'admin' ? 'Управление ПВК' : 'Изменить оценки ПВК' ?>
                </a>
            </div>
        <?php endif; ?>

        <div class="alert alert-info">
            <strong>Примечание:</strong> Оценки закрепленных экспертов учитываются с коэффициентом 1.5
        </div>

        <div class="scrollable-table">
            <table class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ПВК</th>
                        <th>Описание</th>
                        <th class="rating-cell">Средний рейтинг</th>
                        <?php if ($is_expert_or_admin): ?>
                            <th class="rating-cell">Отклонение</th>
                            <th>Кол-во оценок</th>
                            <th>Детали</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pvk_list)): ?>
                        <?php foreach ($pvk_list as $pvk): ?>
                            <tr>
                                <td><?= htmlspecialchars($pvk['name']) ?></td>
                                <td><?= htmlspecialchars($pvk['description']) ?></td>
                                <td><?= number_format($pvk['weighted_avg'], 2) ?></td>
                                <?php if ($is_expert_or_admin): ?>
                                    <td><?= number_format($pvk['std_deviation'], 2) ?></td>
                                    <td><?= $pvk['ratings_count'] ?></td>
                                    <td>
                                        <a href="?profession_id=<?= $profession_id ?>&show_details=<?= $pvk['id'] ?>" 
                                           class="btn btn-sm btn-info">
                                            Показать
                                        </a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            
                            <?php if ($is_expert_or_admin && isset($_GET['show_details']) && $_GET['show_details'] == $pvk['id'] && !empty($ratings_details)): ?>
                                <tr class="details-row">
                                    <td colspan="<?= $is_expert_or_admin ? '6' : '3' ?>">
                                        <h5>Детали оценок для: <?= htmlspecialchars($pvk['name']) ?></h5>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Эксперт</th>
                                                    <th>Оценка</th>
                                                    <th>Закреплён</th>
                                                    <?php if ($is_assigned_expert || (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin')): ?>
                                                    <th>Действие</th>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($ratings_details as $rating): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($rating['username']) ?></td>
                                                        <td><?= $rating['priority'] ?></td>
                                                        <td class="<?= strpos($rating['expert_status'], 'Да') !== false ? 'expert-weighted' : '' ?>">
                                                            <?= $rating['expert_status'] ?>
                                                        </td>
                                                        <?php if ($is_assigned_expert || (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin')): ?>
                                                        <td>
                                                            <a href="?profession_id=<?= $profession_id ?>&delete_rating=<?= $rating['id'] ?>" 
                                                               class="btn btn-sm btn-danger"
                                                               onclick="return confirm('Вы уверены, что хотите удалить эту оценку?')">
                                                                Удалить
                                                            </a>
                                                        </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= $is_expert_or_admin ? '6' : '3' ?>" class="text-center">Нет данных о ПВК</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="footer mt-5 py-3 bg-light">
        <div class="container text-center">
            <p>&copy; 2024 ITMO Portal. Все права защищены.</p>
        </div>
    </footer>
</body>
</html>