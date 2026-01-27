<?php
require_once 'db.php';

function getAverageResults($gender, $age) {
    global $conn;
    $minAge = $age - 5;
    $maxAge = $age + 5;

    $sql = "SELECT AVG(reaction_time) as avg_reaction_time, AVG(correct_answers) as avg_correct_answers
            FROM test_results
            WHERE gender = ? AND age BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $gender, $minAge, $maxAge);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Пример использования
$gender = 'male';
$age = 25;
$averageResults = getAverageResults($gender, $age);
echo json_encode($averageResults);
?>