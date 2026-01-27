<form action="assign_test.php" method="POST">
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
        <label for="test_name">Выберите тест:</label>
        <select name="test_name" id="test_name" class="form-control" required>
            <option value="simple_reaction">Простая сенсомоторная реакция</option>
            <option value="complex_reaction">Сложная сенсомоторная реакция</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Назначить тест</button>
</form>