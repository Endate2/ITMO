<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Назначение тестов</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 50px;
        }
        .form-group {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Назначить тест респонденту</h2>
        <form action="assign_test.php" method="POST">
            <div class="form-group">
                <label for="respondent_id">Выберите респондента:</label>
                <select name="respondent_id" id="respondent_id" class="form-control" required>
                    <?php
                    // Подключение к базе данных
                    require_once 'db.php';
                    // Получение списка респондентов
                    $sql = "SELECT id, username FROM users WHERE role = 'respondent'";
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
</body>
</html>