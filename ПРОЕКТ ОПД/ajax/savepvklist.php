<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'expert') {
    die("Доступ запрещен.");
}

require '../includes/db.php';

// Включение вывода ошибок MySQL
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Отладка: выводим данные из POST
echo "<pre>";
print_r($_POST);
echo "</pre>";

$profession_id = $_POST['profession_id'];
$priorities = $_POST['priority'];

// Фильтруем приоритеты: оставляем только те, которые были указаны
$filtered_priorities = array_filter($priorities, function ($priority) {
    return !empty($priority); // Оставляем только непустые значения
});

// Проверяем, что все указанные приоритеты уникальны
$unique_priorities = array_unique(array_values($filtered_priorities));
if (count($filtered_priorities) !== count($unique_priorities)) {
    die("Приоритеты не должны повторяться.");
}

// Удаляем старый список эксперта (если есть)
$stmt = $conn->prepare("DELETE FROM expert_pvk_lists WHERE expert_id = ? AND profession_id = ?");
$stmt->bind_param("ii", $_SESSION['user_id'], $profession_id);
$stmt->execute();
$stmt->close();

// Сохраняем новый список (только для ПВК с указанными приоритетами)
foreach ($filtered_priorities as $pvk_id => $priority) {
    $stmt = $conn->prepare("INSERT INTO expert_pvk_lists (expert_id, profession_id, pvk_id, priority) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiii", $_SESSION['user_id'], $profession_id, $pvk_id, $priority);
    $stmt->execute();
    $stmt->close();
}

// Редирект на страницу ПВК
header("Location: ../pages/pvk.php?profession_id=" . $profession_id);
exit();
?>