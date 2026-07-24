<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = mysqli_query($conn, "SELECT * FROM test_passages WHERE id = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<div class='content-header'><h1>Data Not Found</h1></div>";
    exit;
}
?>

<div class="content-header">
    <h1>Edit Test Passage</h1>
    <p>Update information, text content, or supporting image for this Reciprocal Reading Test.</p>
</div>
<div class="admin-card">
    <form action="admin_dashboard.php?page=manage_test_passage" method="POST" class="add-material-form" enctype="multipart/form-data">
        <input type="hidden" name="update_test_material" value="1">
        <input type="hidden" name="passage_id" value="<?php echo $data['id']; ?>">
        
        <div class="form-row">
            <div class="form-group">
                <label>Test Packet (Packet ID)</label>
                <select name="packet_id" required>
                    <option value="A" <?php echo ($data['packet_id'] == 'A') ? 'selected' : ''; ?>>Packet A</option>
                    <option value="B" <?php echo ($data['packet_id'] == 'B') ? 'selected' : ''; ?>>Packet B</option>
                    <option value="C" <?php echo ($data['packet_id'] == 'C') ? 'selected' : ''; ?>>Packet C</option>
                </select>
            </div>
            <div class="form-group">
                <label>Passage Order (Passage Number)</label>
                <input type="number" name="passage_number" min="1" value="<?php echo $data['passage_number']; ?>" required>
            </div>
        </div>

        <div class="form-group option-box" style="padding: 15px; background: #0b1322; border: 1px dashed #26354a; border-radius: 8px; margin-bottom: 20px;">
            <label style="color: #a3e635;">Test Illustration Image</label>
            
            <?php if (!empty($data['cover_image'])): ?>
                <div style="margin: 10px 0;">
                    <img src="../uploads/materials/<?php echo $data['cover_image']; ?>" alt="Cover Image" style="max-height: 150px; border-radius: 6px; border: 1px solid #334155;">
                </div>
                <label style="color: #ef4444; font-size: 13px; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                    <input type="checkbox" name="remove_image" value="yes"> Check to delete current image
                </label>
            <?php else: ?>
                <span style="color: #64748b; font-size: 13px; display: block; margin-bottom: 10px;">No illustration image yet.</span>
            <?php endif; ?>

            <input type="file" name="test_passage_image" accept="image/*" style="display: block; margin-top: 15px; color: #94a3b8;">
            <small style="color: #64748b; font-size: 12px;">Leave blank if you don't want to change the image.</small>
        </div>
        
        <div class="form-group">
            <label>Text Title (Title)</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($data['title']); ?>" required>
        </div>
        
        <div class="form-group">
            <label>Text Content (Content)</label>
            <textarea name="content" rows="15" required><?php echo htmlspecialchars($data['content']); ?></textarea>
        </div>
        
        <div class="form-actions" style="display: flex; justify-content: flex-end; gap: 10px;">
            <a href="admin_dashboard.php?page=manage_test_passage" class="submit-btn" style="background-color: #334155; color: #ffffff; text-decoration: none; width: auto; display: inline-block; text-align: center;">Cancel</a>
            <button type="submit" class="submit-btn" style="width: auto;">Save Changes</button>
        </div>
    </form>
</div>