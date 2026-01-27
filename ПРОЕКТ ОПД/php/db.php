<?php
$host = 'localhost'; // Хост
$dbname = 'it_portal'; // Имя базы данных
$username = 'root'; // Имя пользователя (по умолчанию root)
$password = ''; // Пароль (по умолчанию пустой)

// Подключение к базе данных
try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Ошибка подключения к базе данных: " . $conn->connect_error);
    }
    echo "Подключение к базе данных успешно!"; // Отладочное сообщение
} catch (Exception $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>