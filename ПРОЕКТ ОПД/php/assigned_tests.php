<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Назначенные тесты</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 50px;
        }
        table {
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Назначенные тесты</h2>
        <table>
            <thead>
                <tr>
                    <th>Респондент</th>
                    <th>Тест</th>
                    <th>Дата назначения</th>
                </tr>
            </thead>
            <tbody>
                <?php
                require_once 'db.php';
                // Получение списка назначенных тестов
                $sql = "SELECT u.username, a.test_name, a.assigned_at 
                        FROM assigned_tests a 
                        JOIN users u ON a.respondent_id = u.id 
                        WHERE a.expert_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['username']}</td>
                            <td>{$row['test_name']}</td>
                            <td>{$row['assigned_at']}</td>
                          </tr>";
                }
                $stmt->close();
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>