<?php
session_start();

// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Подключение к базе данных
require_once '../php/db.php';

// Проверка роли пользователя
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if ($_SESSION['user_role'] !== 'consultant') {
    header('Location: ../index.php');
    exit();
}

// Обработка формы назначения тестов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['respondent_id']) && isset($_POST['tests'])) {
    $expert_id = $_SESSION['user_id'];
    $respondent_id = $_POST['respondent_id'];
    $tests = $_POST['tests'];
    
    // Начинаем транзакцию
    $conn->begin_transaction();
    
    try {
        // Удаляем предыдущие назначения для этого пользователя
        $delete_sql = "DELETE FROM assigned_tests WHERE respondent_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $respondent_id);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        // Добавляем новые назначения с учетом порядка
        $order = 1;
        foreach ($tests as $test_name) {
            $insert_sql = "INSERT INTO assigned_tests (expert_id, respondent_id, test_name, test_order) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iisi", $expert_id, $respondent_id, $test_name, $order);
            $insert_stmt->execute();
            $insert_stmt->close();
            
            // Добавляем уведомление для пользователя
            $message = "Вам назначен тест: " . htmlspecialchars($test_name);
            $notification_sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
            $notification_stmt = $conn->prepare($notification_sql);
            $notification_stmt->bind_param("is", $respondent_id, $message);
            $notification_stmt->execute();
            $notification_stmt->close();
            
            $order++;
        }
        
        $conn->commit();
        $_SESSION['success_message'] = "Тесты успешно назначены!";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Ошибка при назначении тестов: " . $e->getMessage();
    }
    
    header("Location: consultant_panel.php");
    exit();
}

// Обработка уведомлений о подключении к Zoom


// Получаем уведомления для текущего консультанта
$notifications_sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$notifications_stmt = $conn->prepare($notifications_sql);
$notifications_stmt->bind_param("i", $_SESSION['user_id']);
$notifications_stmt->execute();
$notifications = $notifications_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$notifications_stmt->close();
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
        .test-item {
            margin-bottom: 10px;
        }
        .sortable-chosen {
            opacity: 0.8;
            background: #f8f9fa;
        }
        .notification-item.unread {
            background-color: #f0f8ff;
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <!-- Навигационная панель -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <a class="navbar-brand" href="../index.php">ITMO Portal</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="nav-item">
                        <span class="navbar-text mr-2"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="../pages/logout.php" class="btn btn-outline-dark">Выйти</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <?php
                            $unread_count = 0;
                            foreach ($notifications as $notification) {
                                if (!$notification['is_read']) $unread_count++;
                            }
                            if ($unread_count > 0): ?>
                                <span class="badge badge-danger notification-badge"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationsDropdown">
                            <h6 class="dropdown-header">Уведомления</h6>
                            <?php if (empty($notifications)): ?>
                                <a class="dropdown-item" href="#">Нет новых уведомлений</a>
                            <?php else: ?>
                                <?php foreach ($notifications as $notification): ?>
                                    <a class="dropdown-item notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>" href="#" data-id="<?php echo $notification['id']; ?>">
                                        <?php echo htmlspecialchars($notification['message']); ?>
                                        <small class="text-muted d-block"><?php echo $notification['created_at']; ?></small>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Основной контент -->
    <div class="container mt-5 pt-5">
        <h1 class="text-center">Панель консультанта</h1>

        <!-- Отображение сообщений об успехе/ошибке -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

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
                    <?php
                    $sql = "SELECT tr.*, u.username
                            FROM test_results tr
                            JOIN users u ON tr.user_id = u.id
                            ORDER BY tr.created_at DESC";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['test_name']); ?></td>
                                <td><?php echo $row['reaction_time']; ?></td>
                                <td><?php echo $row['correct_answers']; ?></td>
                                <td><?php echo $row['created_at']; ?></td>
                            </tr>
                        <?php endwhile;
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>Нет данных о тестах.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Назначить тест -->
        <div class="row mt-4">
            <div class="col-md-12">
                <h3>Назначить тесты</h3>
                <form action="consultant_panel.php" method="POST" id="assignTestsForm">
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
                        <label>Выберите тесты и порядок:</label>
                        <div id="testsContainer">
                            <div class="test-item mb-2 d-flex align-items-center">
                                <select name="tests[]" class="form-control mr-2" required>
                                    <option value="simple_reaction">Простая сенсомоторная реакция</option>
                                    <option value="complex_reaction">Сложная сенсомоторная реакция</option>
                                    <option value="circle_reaction_test">Тест на круги</option>
                                    <option value="addition_parity_test">Тест на сложение и четность</option>
                                    <option value="visual_memory_test">Тест на зрительную память</option>
                                </select>
                                <button type="button" class="btn btn-sm btn-danger remove-test">×</button>
                            </div>
                        </div>
                        <button type="button" id="addTest" class="btn btn-sm btn-secondary mt-2">Добавить тест</button>
                    </div>

                    <button type="submit" class="btn btn-primary">Назначить тесты</button>
                </form>
            </div>
        </div>
    <!-- Оценка ПВК и рекомендация профессий -->
    <div class="row mt-4">
        <div class="col-md-12">
            <h3>Оценка ПВК и рекомендация профессий</h3>

            <!-- Форма выбора пользователя и ввода пульса -->
            <form id="evaluateForm" method="POST">
                <div class="form-group">
                    <label for="evaluate_user_id">Выберите пользователя:</label>
                    <select name="user_id" id="evaluate_user_id" class="form-control" required>
                        <option value="">-- Выберите пользователя --</option>
                        <?php
                        $sql = "SELECT id, username FROM users WHERE role = 'user'";
                        $result = $conn->query($sql);
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['username']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="pulse_before">Пульс до теста (уд/мин):</label>
                        <input type="number" class="form-control" id="pulse_before" name="pulse_before" min="40" max="200" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="pulse_during">Пульс во время теста (уд/мин):</label>
                        <input type="number" class="form-control" id="pulse_during" name="pulse_during" min="40" max="200" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="pulse_after">Пульс после теста (уд/мин):</label>
                        <input type="number" class="form-control" id="pulse_after" name="pulse_after" min="40" max="200" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Оценить ПВК</button>
            </form>

            <?php
            // Обработка формы оценки ПВК
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
                $user_id = $_POST['user_id'];
                $pulse_before = intval($_POST['pulse_before']);
                $pulse_during = intval($_POST['pulse_during']);
                $pulse_after = intval($_POST['pulse_after']);

                // Анализ показателей пульса
                $pulse_change = $pulse_during - $pulse_before;
                $recovery_rate = $pulse_after - $pulse_during;

                $stress_level = "нормальный";
                if ($pulse_change > 30) {
                    $stress_level = "повышенный";
                } elseif ($pulse_change > 50) {
                    $stress_level = "высокий";
                }

                $recovery_ability = "хорошая";
                if ($recovery_rate > 20) {
                    $recovery_ability = "удовлетворительная";
                } elseif ($recovery_rate > 40) {
                    $recovery_ability = "низкая";
                }

                // Получаем данные пользователя
                $user_sql = "SELECT username FROM users WHERE id = ?";
                $user_stmt = $conn->prepare($user_sql);
                $user_stmt->bind_param("i", $user_id);
                $user_stmt->execute();
                $user_result = $user_stmt->get_result();
                $user = $user_result->fetch_assoc();
                $user_stmt->close();

                // Получаем результаты тестов пользователя
                $tests_sql = "SELECT test_name, reaction_time, correct_answers, total_questions
                              FROM test_results
                              WHERE user_id = ?
                              ORDER BY created_at DESC";
                $tests_stmt = $conn->prepare($tests_sql);
                $tests_stmt->bind_param("i", $user_id);
                $tests_stmt->execute();
                $test_results = $tests_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $tests_stmt->close();

                // Критерии оценки ПВК на основе тестов
                $pvk_scores = [];

                foreach ($test_results as $test) {
                    // Оценка ПВК на основе каждого теста
                    switch ($test['test_name']) {
                        case 'simple_reaction':
                            // Оценка скорости реакции
                            if ($test['reaction_time'] < 300) {
                                $pvk_scores[28] = ($pvk_scores[28] ?? 0) + 3; // Оперативность
                                $pvk_scores[40] = ($pvk_scores[40] ?? 0) + 2; // Внимательность
                            } elseif ($test['reaction_time'] < 500) {
                                $pvk_scores[28] = ($pvk_scores[28] ?? 0) + 2;
                                $pvk_scores[40] = ($pvk_scores[40] ?? 0) + 1;
                            }
                            break;

                        case 'complex_reaction':
                            // Оценка сложной реакции
                            $accuracy = $test['correct_answers'] / $test['total_questions'];
                            if ($accuracy > 0.8 && $test['reaction_time'] < 500) {
                                $pvk_scores[18] = ($pvk_scores[18] ?? 0) + 3; // Стрессоустойчивость
                                $pvk_scores[20] = ($pvk_scores[20] ?? 0) + 2; // Решительность
                                $pvk_scores[40] = ($pvk_scores[40] ?? 0) + 2; // Внимательность
                            }
                            break;

                        case 'circle_reaction':
                            // Оценка зрительного восприятия
                            if ($test['correct_answers'] / $test['total_questions'] > 0.75) {
                                $pvk_scores[32] = ($pvk_scores[32] ?? 0) + 3; // Зрительная память
                                $pvk_scores[31] = ($pvk_scores[31] ?? 0) + 2; // Пространственное воображение
                            }
                            break;

                        case 'simple_reaction':
                            // Оценка математических способностей
                            if ($test['correct_answers'] / $test['total_questions'] > 0.8) {
                                $pvk_scores[6] = ($pvk_scores[6] ?? 0) + 2; // Организованность
                                $pvk_scores[36] = ($pvk_scores[36] ?? 0) + 3; // Умственная работоспособность
                            }
                            break;

                        case 'visual_memory_test':
                            // Оценка памяти
                            if ($test['correct_answers'] / $test['total_questions'] > 0.7) {
                                $pvk_scores[32] = ($pvk_scores[32] ?? 0) + 4; // Зрительная память
                                $pvk_scores[29] = ($pvk_scores[29] ?? 0) + 2; // Образное представление
                            }
                            break;
                    }
                }

                // Корректировка ПВК на основе показателей пульса
                if ($stress_level === "повышенный") {
                    $pvk_scores[18] = ($pvk_scores[18] ?? 0) - 1; // Снижаем оценку стрессоустойчивости
                } elseif ($stress_level === "высокий") {
                    $pvk_scores[18] = ($pvk_scores[18] ?? 0) - 2; // Снижаем оценку стрессоустойчивости
                }

                if ($recovery_ability === "удовлетворительная") {
                    $pvk_scores[39] = ($pvk_scores[39] ?? 0) - 1; // Снижаем оценку выносливости
                } elseif ($recovery_ability === "низкая") {
                    $pvk_scores[39] = ($pvk_scores[39] ?? 0) - 2; // Снижаем оценку выносливости
                }

                // Получаем названия ПВК
                $pvk_names = [];
                if (!empty($pvk_scores)) {
                    $pvk_ids = implode(",", array_keys($pvk_scores));
                    $pvk_sql = "SELECT id, name FROM pvk WHERE id IN ($pvk_ids)";
                    $pvk_result = $conn->query($pvk_sql);
                    while ($row = $pvk_result->fetch_assoc()) {
                        $pvk_names[$row['id']] = $row['name'];
                    }
                }

                // Получаем все профессии и их ПВК
                $professions_sql = "SELECT p.id, p.name, GROUP_CONCAT(pp.pvk_id) as pvk_ids
                                  FROM professions p
                                  LEFT JOIN profession_pvk pp ON p.id = pp.profession_id
                                  GROUP BY p.id";
                $professions_result = $conn->query($professions_sql);
                $professions = [];
                while ($row = $professions_result->fetch_assoc()) {
                    $professions[$row['id']] = [
                        'name' => $row['name'],
                        'pvk_ids' => $row['pvk_ids'] ? explode(",", $row['pvk_ids']) : []
                    ];
                }

                // Оцениваем соответствие профессиям
                $profession_matches = [];
                foreach ($professions as $prof_id => $prof) {
                    $match_score = 0;
                    $required_pvks = count($prof['pvk_ids']);
                    $matched_pvks = 0;

                    foreach ($prof['pvk_ids'] as $pvk_id) {
                        if (isset($pvk_scores[$pvk_id])) {
                            $match_score += $pvk_scores[$pvk_id];
                            $matched_pvks++;
                        }
                    }

                    if ($required_pvks > 0) {
                        $match_percent = round(($matched_pvks / $required_pvks) * 100);
                        $profession_matches[$prof_id] = [
                            'name' => $prof['name'],
                            'match_percent' => $match_percent,
                            'match_score' => $match_score,
                            'matched_pvks' => $matched_pvks,
                            'required_pvks' => $required_pvks
                        ];
                    }
                }

                // Сортируем профессии по степени соответствия
                usort($profession_matches, function($a, $b) {
                    return $b['match_percent'] <=> $a['match_percent'];
                });
                ?>

                <div class="mt-4">
                    <h4>Результаты оценки для пользователя: <?php echo htmlspecialchars($user['username']); ?></h4>

                    <!-- Блок с показателями пульса -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Физиологические показатели</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <p><strong>Пульс до теста:</strong> <?php echo $pulse_before; ?> уд/мин</p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Пульс во время теста:</strong> <?php echo $pulse_during; ?> уд/мин</p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Пульс после теста:</strong> <?php echo $pulse_after; ?> уд/мин</p>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <p><strong>Уровень стресса:</strong> <?php echo ucfirst($stress_level); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Способность к восстановлению:</strong> <?php echo ucfirst($recovery_ability); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($pvk_scores)): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Оценка ПВК</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php
                                    arsort($pvk_scores);
                                    foreach ($pvk_scores as $pvk_id => $score):
                                        $width = min(100, max(20, $score * 10));
                                    ?>
                                        <div class="col-md-6 mb-2">
                                            <label><?php echo htmlspecialchars($pvk_names[$pvk_id]); ?></label>
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" style="width: <?php echo $width; ?>%"
                                                     aria-valuenow="<?php echo $score; ?>" aria-valuemin="0" aria-valuemax="10">
                                                    <?php echo $score; ?> баллов
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5>Рекомендуемые профессии</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($profession_matches)): ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Профессия</th>
                                                    <th>Совпадение</th>
                                                    <th>Соответствие ПВК</th>
                                                    <th>Рекомендация</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($profession_matches as $prof):
                                                    $recommendation = "";
                                                    $class = "";

                                                    if ($prof['match_percent'] >= 80) {
                                                        $recommendation = "Сильно рекомендуется";
                                                        $class = "table-success";
                                                    } elseif ($prof['match_percent'] >= 50) {
                                                        $recommendation = "Рекомендуется";
                                                        $class = "table-info";
                                                    } else {
                                                        $recommendation = "Не рекомендуется";
                                                        $class = "table-light";
                                                    }
                                                ?>
                                                    <tr class="<?php echo $class; ?>">
                                                        <td><?php echo htmlspecialchars($prof['name']); ?></td>
                                                        <td><?php echo $prof['match_percent']; ?>%</td>
                                                        <td><?php echo $prof['matched_pvks']; ?> из <?php echo $prof['required_pvks']; ?></td>
                                                        <td><?php echo $recommendation; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p>Не удалось определить подходящие профессии.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Недостаточно данных для оценки ПВК. Пользователь должен пройти больше тестов.
                        </div>
                    <?php endif; ?>
                </div>
            <?php } ?>
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
                <?php if (isset($_GET['user_id'])): ?>
                    <h3>Чат с пользователем</h3>
                    <div class="chat-box">
                        <?php
                        $selected_user_id = $_GET['user_id'];
                        $sql = "SELECT m.*, u1.username as from_username, u2.username as to_username
                                FROM messagescon m
                                JOIN users u1 ON m.from_user_id = u1.id
                                JOIN users u2 ON m.to_user_id = u2.id
                                WHERE (from_user_id = ? AND to_user_id = ?)
                                OR (from_user_id = ? AND to_user_id = ?)
                                ORDER BY created_at";
                        $stmt = $conn->prepare($sql);
                        if (!$stmt) {
                            die("Ошибка подготовки запроса: " . $conn->error);
                        }
                        $stmt->bind_param("iiii", $_SESSION['user_id'], $selected_user_id, $selected_user_id, $_SESSION['user_id']);
                        $stmt->execute();
                        $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                        $stmt->close();

                        foreach ($messages as $message): ?>
                            <div class="message <?php echo $message['from_user_id'] == $_SESSION['user_id'] ? 'text-right' : 'text-left'; ?>">
                                <p><strong><?php echo $message['from_user_id'] == $_SESSION['user_id'] ? 'Вы' : htmlspecialchars($message['from_username']); ?>:</strong></p>
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

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('testsContainer');
            const addButton = document.getElementById('addTest');

            // Инициализация Sortable для перетаскивания
            new Sortable(container, {
                animation: 150,
                handle: '.form-control',
                chosenClass: 'sortable-chosen'
            });

            // Добавление нового теста
            addButton.addEventListener('click', function() {
                const newItem = document.createElement('div');
                newItem.className = 'test-item mb-2 d-flex align-items-center';
                newItem.innerHTML = `
                    <select name="tests[]" class="form-control mr-2" required>
                        <option value="simple_reaction">Простая сенсомоторная реакция</option>
                        <option value="complex_reaction">Сложная сенсомоторная реакция</option>
                        <option value="circle_reaction_test">Тест на круги</option>
                        <option value="addition_parity_test">Тест на сложение и четность</option>
                        <option value="visual_memory_test">Тест на зрительную память</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-danger remove-test">×</button>
                `;
                container.appendChild(newItem);
            });

            // Удаление теста
            container.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-test')) {
                    if (container.querySelectorAll('.test-item').length > 1) {
                        e.target.closest('.test-item').remove();
                    } else {
                        alert('Должен остаться хотя бы один тест');
                    }
                }
            });

            // Пометить уведомление как прочитанное
            $('.notification-item').click(function() {
                const notificationId = $(this).data('id');
                const item = $(this);

                if (item.hasClass('unread')) {
                    $.post('mark_notification_read.php', {id: notificationId}, function() {
                        item.removeClass('unread');
                        updateNotificationBadge();
                    });
                }
            });

            // Обновление счетчика непрочитанных уведомлений
            function updateNotificationBadge() {
                $.get('get_unread_notifications.php', function(count) {
                    const badge = $('.notification-badge');
                    if (count > 0) {
                        badge.text(count).show();
                    } else {
                        badge.hide();
                    }
                });
            }

            // Проверка новых уведомлений каждые 30 секунд
            setInterval(updateNotificationBadge, 30000);
        });
    </script>
</body>
</html>