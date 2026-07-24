<div class="content-header">
    <h1>Manage Test Package</h1>
    <p>Manage simulation test Packages (e.g., Package A, Package B) and configure lock requirements.</p>
</div>

<div class="manage-packets-grid">
    
    <div class="card-panel">
        <h3 class="panel-title">Add New Package</h3>
        <form action="" method="POST">
            
            <div class="form-group">
                <label for="packet_code" class="form-label">Package Code (E.g.: D, E, F)</label>
                <input type="text" name="packet_code" id="packet_code" class="form-control input-uppercase" required maxlength="10">
            </div>

            <div class="form-group">
                <label for="title" class="form-label">Package Name</label>
                <input type="text" name="title" id="title" class="form-control" placeholder="E.g.: Test Packet D" required>
            </div>

            <div class="form-group mb-25">
                <label for="requirement" class="form-label">Lock Requirement (Must Pass Which Package?)</label>
                <select name="requirement" id="requirement" class="form-control">
                    <option value="">-- No Requirement (Automatically Open) --</option>
                    <?php
                    // Ambil daftar paket yang sudah ada untuk dijadikan pilihan syarat
                    $req_query = mysqli_query($conn, "SELECT packet_code, title FROM test_packets ORDER BY id ASC");
                    while ($req_row = mysqli_fetch_assoc($req_query)) {
                        echo "<option value='{$req_row['packet_code']}'>Packet {$req_row['packet_code']} ({$req_row['title']})</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit" name="submit_test_packet" class="btn-primary btn-full">
                Save Packet
            </button>
        </form>
    </div>

    <div class="card-panel">
        <h3 class="panel-title">Test Packages List</h3>
        <div class="table-responsive">
            <table class="score-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Package Name</th>
                        <th>Unlock Requirement</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query_packets = mysqli_query($conn, "SELECT * FROM test_packets ORDER BY packet_code ASC");
                    if (mysqli_num_rows($query_packets) > 0) {
                        while ($row = mysqli_fetch_assoc($query_packets)) {
                            // Cek teks gembok
                            $syarat_teks = empty($row['requirement']) 
                                ? '<span class="badge-green">No Requirement</span>' 
                                : "Passed Package <strong>" . htmlspecialchars($row['requirement']) . "</strong>";
                            
                            // PERBAIKAN: Menggunakan tanda kutip tunggal (') untuk atribut class HTML
                            echo "<tr>
                                    <td class='text-highlight fw-bold'>{$row['packet_code']}</td>
                                    <td>{$row['title']}</td>
                                    <td class='text-muted'>{$syarat_teks}</td>
                                    <td style='text-align: center; vertical-align: middle;'>
                                        <div style='display: flex; justify-content: center; gap: 8px;'>
                                            <a href='admin_dashboard.php?page=edit_test_package&id={$row['id']}' style='display: inline-flex; align-items: center; justify-content: center; padding: 6px 12px; background: #3b82f6; border-radius: 6px; color: #ffffff; text-decoration: none;'>
                                                <span class='material-symbols-outlined' style='font-size: 16px;'>edit</span>
                                            </a>
                                            <a href='admin_dashboard.php?page=manage_test_packets&delete_test_packet={$row['id']}' style='display: inline-flex; align-items: center; justify-content: center; padding: 6px 12px; background: #ef4444; border-radius: 6px; color: #ffffff; text-decoration: none;' onclick='return confirm(\"Are you sure you want to delete this packet? Make sure no questions are tied to it.\");'>
                                                <span class='material-symbols-outlined' style='font-size: 16px;'>delete_outline</span>
                                            </a>
                                        </div>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center text-muted' style='padding: 20px;'>No test packets yet. Please add a new packet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>