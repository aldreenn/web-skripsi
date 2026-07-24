<?php
    $q_id = (int)$_GET['id'];
    $q_data_res = mysqli_query($conn, "SELECT * FROM questions WHERE id = $q_id");
    $q_data = mysqli_fetch_assoc($q_data_res);
    if (!$q_data) {
        echo "<div class='content-header'><h1>Question not found!</h1></div>";
    } else {
?>
<div class="content-header">
    <h1>Edit Quiz Question</h1>
    <p>Update the multiple-choice question details below.</p>
</div>
<div class="admin-card">
    <form action="admin_dashboard.php?page=edit_question&id=<?= $q_id; ?>" method="POST">
        <input type="hidden" name="question_id" value="<?= $q_data['id']; ?>">

        <div class="form-group">
            <label>Related Material</label>
            <select name="material_id" required>
                <?php
                $res = mysqli_query($conn, "SELECT id, title FROM materials ORDER BY created_at DESC");
                while($row = mysqli_fetch_assoc($res)) {
                    $selected = ($row['id'] == $q_data['material_id']) ? 'selected' : '';
                    echo "<option value='".$row['id']."' $selected>".$row['title']."</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Question</label>
            <textarea name="question_text" rows="3" required><?= htmlspecialchars($q_data['question_text']); ?></textarea>
        </div>

        <div class="option-box">
            <div class="form-row">
                <div class="form-group">
                    <label>Option A</label>
                    <input type="text" name="option_a" value="<?= htmlspecialchars($q_data['option_a']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Option B</label>
                    <input type="text" name="option_b" value="<?= htmlspecialchars($q_data['option_b']); ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Option C</label>
                    <input type="text" name="option_c" value="<?= htmlspecialchars($q_data['option_c']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Option D</label>
                    <input type="text" name="option_d" value="<?= htmlspecialchars($q_data['option_d']); ?>" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Correct Answer</label>
            <select name="correct_answer" required>
                <option value="0" <?= ($q_data['correct_answer'] == '0') ? 'selected' : ''; ?>>Option A</option>
                <option value="1" <?= ($q_data['correct_answer'] == '1') ? 'selected' : ''; ?>>Option B</option>
                <option value="2" <?= ($q_data['correct_answer'] == '2') ? 'selected' : ''; ?>>Option C</option>
                <option value="3" <?= ($q_data['correct_answer'] == '3') ? 'selected' : ''; ?>>Option D</option>
            </select>
        </div>

        <div class="form-group">
            <label>Explanation (Optional)</label>
            <textarea name="explanation" rows="2" placeholder="Explain the reason for the correct answer..."><?= htmlspecialchars($q_data['explanation']); ?></textarea>
        </div>

        <div class="form-actions" style="display: flex; gap: 15px;">
            <a href="admin_dashboard.php?page=manage_questions" class="submit-btn" style="background: transparent; border: 1px solid #26354a; color: #ffffff; text-decoration: none; flex: 1; text-align: center; margin: 0; padding: 12px; box-sizing: border-box; display: block;">Cancel</a>
            <button type="submit" name="update_question" class="submit-btn" style="flex: 1; margin: 0; padding: 12px; box-sizing: border-box; border: none; cursor: pointer; display: block; width: 100%;">Update Question</button>
        </div>
    </form>
</div>
<?php } ?>