<div class="content-header">
    <h1>Add New Reading Article</h1>
    <p>Please fill out the form below to add a new article.</p>
</div>
<div class="admin-card">
    <form action="admin_dashboard.php?page=add_article" method="POST" class="add-material-form" enctype="multipart/form-data">
        <input type="hidden" name="submit_material" value="1">
        <div class="form-row">
            <div class="form-group">
                <label>Level (CEFR)</label>
                <select name="level" id="levelDropdown" required>
                    <option value="A1">A1 (Starter)</option>
                    <option value="A2">A2 (Beginner)</option>
                    <option value="B1">B1 (Intermediate)</option>
                    <option value="B2">B2 (Upper Intermediate)</option>
                    <option value="C1">C1 (Advanced)</option>
                    <option value="C2">C2 (Proficient)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Topic Folder</label>
                <select name="topic" id="topicDropdown" required>
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
            <label>Estimated Reading Time</label>
            <input type="text" name="reading_time" placeholder="E.g.: 5 Min Read" required>
        </div>

        <div class="form-group" style="background: #0b1322; padding: 15px; border-radius: 8px; border: 1px dashed #cbd5e1;">
            <label>Material / Visual Question Image (Optional)</label>
            <input type="file" name="material_image" accept="image/*" style="display: block; margin-top: 5px;">
            <small style="color: #757575; font-size: 12px;">*Upload if the material requires an image (e.g.: clock, schedule, etc.).</small>
        </div>

        <div class="form-group">
            <label>Article Title</label>
            <input type="text" name="title" placeholder="Enter title..." required>
        </div>
        <div class="form-group">
            <label>Short Description</label>
            <textarea name="desc" rows="2" placeholder="Write a short description about the article..." required></textarea>
        </div>
        <div class="form-group">
            <label>Reading Text Content (Optional if visual question only)</label>
            <textarea name="fullContent" rows="10" placeholder="Paste full text here..."></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="submit-btn">Save Material</button>
        </div>
    </form>
</div>