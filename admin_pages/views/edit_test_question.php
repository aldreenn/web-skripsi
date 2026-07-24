<?php
// Mengambil ID soal dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Menarik data soal yang akan diedit
$query = mysqli_query($conn, "SELECT * FROM test_questions WHERE id = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<div class='content-header'><h1>Data Not Found</h1></div>";
    exit;
}
?>

<div class="content-header">
    <h1>Edit Test Question</h1>
    <p>Update information or content of this Reciprocal Reading test question.</p>
</div>
<div class="admin-card">
    <form action="admin_dashboard.php?page=manage_test_questions" method="POST" class="add-material-form">
        <input type="hidden" name="update_test_question" value="1">
        <input type="hidden" name="question_id" value="<?php echo $data['id']; ?>">
        
        <div class="form-group">
            <label>Select Reading Text (Passage)</label>
            <select name="passage_id" id="passageSelect" required>
                <option value="">-- Select Packet and Text --</option>
                <?php
                // Menarik data teks dan menyiapkannya untuk JS Preview
                $passages_query = mysqli_query($conn, "SELECT * FROM test_passages ORDER BY packet_id ASC, passage_number ASC");
                $preview_data = [];
                
                while($p_row = mysqli_fetch_assoc($passages_query)) {
                    $p_id = $p_row['id'];
                    $preview_text = substr($p_row['content'], 0, 300) . "..."; 
                    $preview_data[$p_id] = htmlspecialchars($preview_text);
                    
                    // Tandai teks yang sebelumnya dipilih
                    $selected = ($data['passage_id'] == $p_id) ? "selected" : "";
                    echo "<option value='{$p_id}' {$selected}>[Packet {$p_row['packet_id']} - Passage {$p_row['passage_number']}] {$p_row['title']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group" id="previewBox" style="display: none;">
            <label style="color: #a3e635;">Text Preview (Snippet):</label>
            <div id="previewText" style="padding: 15px; background: #0b1322; border: 1px dashed #334155; border-radius: 8px; color: #94a3b8; font-style: italic; font-size: 13px; line-height: 1.6;">
                </div>
        </div>

        <div class="form-group">
            <label>Reciprocal Reading Phase</label>
            <select name="reciprocal_phase" required>
                <option value="predicting" <?php echo ($data['reciprocal_phase'] == 'predicting') ? 'selected' : ''; ?>>1. Predicting</option>
                <option value="clarifying" <?php echo ($data['reciprocal_phase'] == 'clarifying') ? 'selected' : ''; ?>>2. Clarifying</option>
                <option value="questioning" <?php echo ($data['reciprocal_phase'] == 'questioning') ? 'selected' : ''; ?>>3. Questioning</option>
                <option value="summarizing" <?php echo ($data['reciprocal_phase'] == 'summarizing') ? 'selected' : ''; ?>>4. Summarizing</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Question</label>
            <textarea name="question_text" rows="3" required><?php echo htmlspecialchars($data['question_text']); ?></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group"><label>Option A</label><input type="text" name="option_a" value="<?php echo htmlspecialchars($data['option_a']); ?>" required></div>
            <div class="form-group"><label>Option B</label><input type="text" name="option_b" value="<?php echo htmlspecialchars($data['option_b']); ?>" required></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Option C</label><input type="text" name="option_c" value="<?php echo htmlspecialchars($data['option_c']); ?>" required></div>
            <div class="form-group"><label>Option D</label><input type="text" name="option_d" value="<?php echo htmlspecialchars($data['option_d']); ?>" required></div>
        </div>
        
        <div class="form-group">
            <label>Answer Key</label>
            <select name="correct_answer" required>
                <option value="A" <?php echo ($data['correct_answer'] == 'A') ? 'selected' : ''; ?>>A</option>
                <option value="B" <?php echo ($data['correct_answer'] == 'B') ? 'selected' : ''; ?>>B</option>
                <option value="C" <?php echo ($data['correct_answer'] == 'C') ? 'selected' : ''; ?>>C</option>
                <option value="D" <?php echo ($data['correct_answer'] == 'D') ? 'selected' : ''; ?>>D</option>
            </select>
        </div>

        <div class="form-actions" style="display: flex; justify-content: flex-end; gap: 10px;">
            <a href="admin_dashboard.php?page=manage_test_questions" class="submit-btn" style="background-color: #334155; color: #ffffff; text-decoration: none; width: auto; display: inline-block; text-align: center;">Cancel</a>
            <button type="submit" class="submit-btn" style="width: auto;">Save Changes</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const passageSelect = document.getElementById('passageSelect');
        const previewBox = document.getElementById('previewBox');
        const previewText = document.getElementById('previewText');
        const passagePreviews = <?php echo json_encode($preview_data); ?>;
        
        // Fungsi untuk mengupdate preview
        function updatePreview() {
            const selectedId = passageSelect.value;
            if (selectedId && passagePreviews[selectedId]) {
                previewText.textContent = '"' + passagePreviews[selectedId] + '"';
                previewBox.style.display = 'block';
            } else {
                previewBox.style.display = 'none';
                previewText.textContent = '';
            }
        }

        // Panggil saat halaman pertama kali dimuat (untuk menampilkan preview otomatis)
        updatePreview();

        // Panggil setiap kali dropdown diubah
        passageSelect.addEventListener('change', updatePreview);
    });
</script>