<?php
    $m_id = (int)$_GET['id'];
    $m_data_res = mysqli_query($conn, "SELECT * FROM materials WHERE id = $m_id");
    $m_data = mysqli_fetch_assoc($m_data_res);
    if (!$m_data) {
        echo "<div class='content-header'><h1>Article not found!</h1></div>";
    } else {
?>
<div class="content-header">
    <h1>Edit Reading Article</h1>
    <p>Update article details, folders, and images below.</p>
</div>
<div class="admin-card">
    <form action="admin_dashboard.php?page=edit_article&id=<?= $m_id; ?>" method="POST" class="add-material-form" enctype="multipart/form-data">
        <input type="hidden" name="material_id" value="<?= $m_data['id']; ?>">
        
        <div class="form-row">
            <div class="form-group">
                <label>Level (CEFR)</label>
                <select name="level" id="levelDropdown" required>
                    <option value="A1" <?= ($m_data['level'] == 'A1') ? 'selected' : ''; ?>>A1 (Starter)</option>
                    <option value="A2" <?= ($m_data['level'] == 'A2') ? 'selected' : ''; ?>>A2 (Beginner)</option>
                    <option value="B1" <?= ($m_data['level'] == 'B1') ? 'selected' : ''; ?>>B1 (Intermediate)</option>
                    <option value="B2" <?= ($m_data['level'] == 'B2') ? 'selected' : ''; ?>>B2 (Upper Intermediate)</option>
                    <option value="C1" <?= ($m_data['level'] == 'C1') ? 'selected' : ''; ?>>C1 (Advanced)</option>
                    <option value="C2" <?= ($m_data['level'] == 'C2') ? 'selected' : ''; ?>>C2 (Proficient)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Topic Folder</label>
                <select name="topic" id="topicDropdown" data-selected-topic="<?= htmlspecialchars($m_data['topic']); ?>" required>
                    <option value="" data-level="all">-- Select Topic Folder --</option>
                    <?php
                    $t_res = mysqli_query($conn, "SELECT * FROM topics ORDER BY level ASC, topic_name ASC");
                    while($t_row = mysqli_fetch_assoc($t_res)) {
                        echo "<option value='".$t_row['topic_name']."' data-level='".$t_row['level']."'>[".$t_row['level']."] - ".$t_row['topic_name']."</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label>Reading Time</label>
            <input type="text" name="reading_time" value="<?= htmlspecialchars($m_data['reading_time']); ?>" required>
        </div>

        <div class="form-group" style="background: #0b1322; padding: 15px; border-radius: 8px; border: 1px dashed #cbd5e1;">
            <label>Current Material / Visual Question Image</label><br>
            <?php if ($m_data['cover_image']): ?>
                <div style="margin-bottom: 10px;">
                    <img src="../uploads/materials/<?= $m_data['cover_image']; ?>" style="max-height: 150px; border-radius: 6px; border: 1px solid #26354a;"><br>
                    <label style="display:inline-block; margin-top:5px; cursor:pointer; color:#ef4444;">
                        <input type="checkbox" name="remove_image_material" value="yes"> ❌ Delete this image
                    </label>
                </div>
            <?php else: ?>
                <p style="color: #94a3b8; font-size: 13px; margin: 5px 0;">No image for this article.</p>
            <?php endif; ?>
            
            <label style="margin-top: 10px; display:block;">Change/Upload New Image</label>
            <input type="file" name="material_image" accept="image/*" style="display: block; margin-top: 5px;">
        </div>

        <div class="form-group">
            <label>Article Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($m_data['title']); ?>" required>
        </div>
        <div class="form-group">
            <label>Short Description</label>
            <textarea name="desc" rows="2" required><?= htmlspecialchars($m_data['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label>Full Text Content (Optional)</label>
            <textarea name="fullContent" rows="10"><?= htmlspecialchars($m_data['full_content']); ?></textarea>
        </div>
        
        <div class="form-actions" style="display: flex; gap: 15px;">
            <a href="admin_dashboard.php?page=manage_article" class="submit-btn" style="background: transparent; border: 1px solid #26354a; color: #ffffff; text-decoration: none; flex: 1; text-align: center; margin: 0; padding: 12px; box-sizing: border-box; display: block;">Cancel</a>
            <button type="submit" name="update_material" class="submit-btn" style="flex: 1; margin: 0; padding: 12px; box-sizing: border-box; border: none; cursor: pointer; display: block; width: 100%;">Update Article</button>
        </div>
    </form>
</div>
<?php } ?>