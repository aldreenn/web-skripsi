<div class="content-header">
    <h1>Add Test Passage</h1>
    <p>Add a reading passage based on the Test Package, passage order, and supporting image.</p>
</div>
<div class="admin-card">
    <form action="admin_dashboard.php?page=add_test_passage" method="POST" class="add-material-form" enctype="multipart/form-data">
        <input type="hidden" name="submit_test_material" value="1">
        
        <div class="form-row">
            <div class="form-group">
                <label>Test Package (Package ID)</label>
                <select name="packet_id" required>
                    <option value="">-- Select Test Package --</option>
                    <?php
                    if (isset($conn)) {
                        $req_query = mysqli_query($conn, "SELECT packet_code, title FROM test_packets ORDER BY packet_code ASC");
                        while ($req_row = mysqli_fetch_assoc($req_query)) {
                            echo "<option value='{$req_row['packet_code']}'>{$req_row['title']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Passage Order (Passage Number)</label>
                <input type="number" name="passage_number" min="1" placeholder="E.g.: 1" required>
            </div>
        </div>

        <div class="form-group option-box" style="padding: 15px; background: #0b1322; border: 1px dashed #26354a; border-radius: 8px; margin-bottom: 20px;">
            <label style="color: #a3e635;">Test Illustration Image (Optional)</label>
            <input type="file" name="test_passage_image" accept="image/*" style="display: block; margin-top: 8px; border: none; padding: 0; background: transparent; color: #94a3b8;">
            <small style="color: #64748b; font-size: 12px; display: block; margin-top: 4px;">Upload an image or diagram if this test article requires visual support.</small>
        </div>
        
        <div class="form-group">
            <label>Text Title (Title)</label>
            <input type="text" name="title" placeholder="E.g.: The Impact of Microplastics on Marine Ecosystems" required>
        </div>
        
        <div class="form-group">
            <label>Text Content (Content)</label>
            <textarea name="content" rows="12" placeholder="Paste full reading text here..." required></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="submit-btn">Save Test Passage</button>
        </div>
    </form>
</div>