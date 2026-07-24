<div class="content-header">
    <h1>Add Quiz Question</h1>
    <p>Select an article and add a multiple-choice question.</p>
</div>
<div class="admin-card">
    <form action="admin_dashboard.php?page=add_question" method="POST">
        <div class="form-group">
            <label>Select Material/Article</label>
            <select name="material_id" required>
                <option value="">-- Select Material --</option>
                <?php
                $res = mysqli_query($conn, "SELECT id, title FROM materials ORDER BY created_at DESC");
                while($row = mysqli_fetch_assoc($res)) {
                    echo "<option value='".$row['id']."'>".$row['title']."</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Question</label>
            <textarea name="question_text" rows="3" placeholder="Write the quiz question..." required></textarea>
        </div>

        <div class="option-box">
            <div class="form-row">
                <div class="form-group">
                    <label>Option A</label>
                    <input type="text" name="option_a" required>
                </div>
                <div class="form-group">
                    <label>Option B</label>
                    <input type="text" name="option_b" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Option C</label>
                    <input type="text" name="option_c" required>
                </div>
                <div class="form-group">
                    <label>Option D</label>
                    <input type="text" name="option_d" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Correct Answer</label>
            <select name="correct_answer" required>
                <option value="0">Option A</option>
                <option value="1">Option B</option>
                <option value="2">Option C</option>
                <option value="3">Option D</option>
            </select>
        </div>

        <div class="form-group">
            <label>Explanation (Optional)</label>
            <textarea name="explanation" rows="2" placeholder="Explain the reason for the correct answer..."></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" name="submit_question" class="submit-btn" style="background: #a3e635;">Save Quiz Question</button>
        </div>
    </form>
</div>