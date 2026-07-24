<div class="content-header">
    <h1>Add Test Question</h1>
    <p>Add test questions based on the Reciprocal Reading phase for a specific text.</p>
</div>
<div class="admin-card">
    <form action="admin_dashboard.php?page=add_test_question" method="POST" class="add-material-form">
        <input type="hidden" name="submit_test_question" value="1">
        
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
                    // Mengambil 300 karakter pertama sebagai cuplikan/preview
                    $preview_text = substr($p_row['content'], 0, 300) . "..."; 
                    $preview_data[$p_id] = htmlspecialchars($preview_text);
                    
                    echo "<option value='{$p_id}'>[Packet {$p_row['packet_id']} - Passage {$p_row['passage_number']}] {$p_row['title']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group" id="previewBox" style="display: none;">
            <label style="color: #a3e635;">Text Preview:</label>
            <div id="previewText" style="padding: 15px; background: #0b1322; border: 1px dashed #334155; border-radius: 8px; color: #94a3b8; font-style: italic; font-size: 13px; line-height: 1.6;">
                </div>
        </div>

        <div class="form-group">
            <label>Reciprocal Reading Phase</label>
            <select name="reciprocal_phase" required>
                <option value="">-- Select Tested Phase --</option>
                <option value="predicting">1. Predicting</option>
                <option value="clarifying">2. Clarifying</option>
                <option value="questioning">3. Questioning</option>
                <option value="summarizing">4. Summarizing</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Question</label>
            <textarea name="question_text" rows="3" placeholder="Enter test question here..." required></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group"><label>Option A</label><input type="text" name="option_a" required></div>
            <div class="form-group"><label>Option B</label><input type="text" name="option_b" required></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Option C</label><input type="text" name="option_c" required></div>
            <div class="form-group"><label>Option D</label><input type="text" name="option_d" required></div>
        </div>
        
        <div class="form-group">
            <label>Answer Key</label>
            <select name="correct_answer" required>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="submit-btn">Save Test Question</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const passageSelect = document.getElementById('passageSelect');
        const previewBox = document.getElementById('previewBox');
        const previewText = document.getElementById('previewText');
        
        // Memindahkan data preview dari PHP ke format JSON agar bisa dibaca JS
        const passagePreviews = <?php echo json_encode($preview_data); ?>;
        
        passageSelect.addEventListener('change', function() {
            const selectedId = this.value;
            if (selectedId && passagePreviews[selectedId]) {
                // Tampilkan kotak dan isikan teksnya jika ada yang dipilih
                previewText.textContent = '"' + passagePreviews[selectedId] + '"';
                previewBox.style.display = 'block';
            } else {
                // Sembunyikan kotak jika Admin kembali memilih "-- Pilih Paket --"
                previewBox.style.display = 'none';
                previewText.textContent = '';
            }
        });
    });
</script>