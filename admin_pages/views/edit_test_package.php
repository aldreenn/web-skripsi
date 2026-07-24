<?php
// Mengambil ID paket dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Menarik data paket yang akan diedit
$query = mysqli_query($conn, "SELECT * FROM test_packets WHERE id = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<div class='content-header'><h1>Data Not Found</h1></div>";
    exit;
}
?>

<div class="content-header">
    <h1>Edit Test Package</h1>
    <p>Update information or settings for this simulation test packet.</p>
</div>
<div class="admin-card">
    <form action="admin_dashboard.php?page=manage_test_package" method="POST" class="add-material-form">
        <input type="hidden" name="update_test_packet" value="1">
        <input type="hidden" name="packet_id" value="<?php echo $data['id']; ?>">
        
        <div class="form-group">
            <label for="packet_code">Packet Code (E.g.: D, E, F)</label>
            <input type="text" name="packet_code" id="packet_code" class="form-control input-uppercase" required maxlength="10" value="<?php echo htmlspecialchars($data['packet_code']); ?>">
        </div>

        <div class="form-group">
            <label for="title">Packet Name</label>
            <input type="text" name="title" id="title" class="form-control" placeholder="E.g.: Test Packet D" required value="<?php echo htmlspecialchars($data['title']); ?>">
        </div>

        <div class="form-group">
            <label for="requirement">Lock Requirement (Must Pass Which Packet?)</label>
            <select name="requirement" id="requirement" class="form-control">
                <option value="">-- No Requirement (Automatically Open) --</option>
                <?php
                // Ambil daftar paket yang sudah ada untuk dijadikan pilihan syarat (kecuali paket ini sendiri)
                $req_query = mysqli_query($conn, "SELECT packet_code, title FROM test_packets WHERE id != '$id' ORDER BY id ASC");
                while ($req_row = mysqli_fetch_assoc($req_query)) {
                    $selected = ($data['requirement'] == $req_row['packet_code']) ? 'selected' : '';
                    echo "<option value='{$req_row['packet_code']}' {$selected}>Packet {$req_row['packet_code']} ({$req_row['title']})</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-actions" style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
            <a href="admin_dashboard.php?page=manage_test_package" class="submit-btn" style="background-color: #334155; color: #ffffff; text-decoration: none; width: auto; display: inline-block; text-align: center;">Cancel</a>
            <button type="submit" class="submit-btn" style="width: auto;">Save Changes</button>
        </div>
    </form>
</div>
