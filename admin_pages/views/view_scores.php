<style>
    /* CSS Kustom untuk Tab Bar */
    .score-tabs {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        border-bottom: 2px solid #1e293b;
    }
    .score-tab {
        padding: 10px 25px;
        color: #64748b;
        text-decoration: none;
        font-weight: 600;
        font-size: 15px;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
    }
    .score-tab:hover {
        color: #cbd5e1;
    }
    .score-tab.active {
        color: #3b82f6; /* Warna biru saat aktif */
        border-bottom: 3px solid #3b82f6;
    }
</style>

<div class="content-header">
    <h1>View User Score</h1>
    <p>Monitor the progress of participants' practice and test scores here.</p>
</div>
<div class="admin-card">
    <?php
        // Menangkap status Tab yang aktif (default ke practice)
        $tab = isset($_GET['tab']) && $_GET['tab'] == 'test' ? 'test' : 'practice';
        
        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
        $level_filter = isset($_GET['level_filter']) ? mysqli_real_escape_string($conn, $_GET['level_filter']) : '';
        $packet_filter = isset($_GET['packet_filter']) ? mysqli_real_escape_string($conn, $_GET['packet_filter']) : '';
    ?>

    <div class="score-tabs">
        <a href="admin_dashboard.php?page=view_scores&tab=practice" class="score-tab <?= $tab == 'practice' ? 'active' : ''; ?>">Practice Scores</a>
        <a href="admin_dashboard.php?page=view_scores&tab=test" class="score-tab <?= $tab == 'test' ? 'active' : ''; ?>">Test Scores</a>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">

    <form method="GET" action="admin_dashboard.php" class="table-toolbar" style="display: flex; align-items: center; gap: 10px; margin: 0; flex-grow: 1;">
        <input type="hidden" name="page" value="view_scores">
        <input type="hidden" name="tab" value="<?= $tab; ?>">
            
        <div class="search-box" style="flex-grow: 1; max-width: 400px;">
            <input type="text" name="search" placeholder="<?= $tab == 'practice' ? 'Search participant name or article title...' : 'Search participant name or test packet...'; ?>" value="<?= htmlspecialchars($search); ?>" style="padding: 10px; border: 1px solid #334155; background: #0b1322; color: #f8fafc; border-radius: 6px; width: 100%; box-sizing: border-box; height: 40px;">
        </div>
            
        <div class="filter-box">
            <?php if ($tab == 'practice'): ?>
                <select name="level_filter" style="padding: 10px; border: 1px solid #334155; background: #0b1322; color: #f8fafc; border-radius: 6px; height: 40px; box-sizing: border-box;">
                    <option value="">All Levels</option>
                    <option value="A1" <?= ($level_filter == 'A1') ? 'selected' : ''; ?>>Level A1</option>
                    <option value="A2" <?= ($level_filter == 'A2') ? 'selected' : ''; ?>>Level A2</option>
                    <option value="B1" <?= ($level_filter == 'B1') ? 'selected' : ''; ?>>Level B1</option>
                    <option value="B2" <?= ($level_filter == 'B2') ? 'selected' : ''; ?>>Level B2</option>
                    <option value="C1" <?= ($level_filter == 'C1') ? 'selected' : ''; ?>>Level C1</option>
                    <option value="C2" <?= ($level_filter == 'C2') ? 'selected' : ''; ?>>Level C2</option>
                </select>
            <?php else: ?>
                <select name="packet_filter" style="padding: 10px; border: 1px solid #334155; background: #0b1322; color: #f8fafc; border-radius: 6px; height: 40px; box-sizing: border-box;">
                    <option value="">All Packets</option>
                    <option value="A" <?= ($packet_filter == 'A') ? 'selected' : ''; ?>>Packet A</option>
                    <option value="B" <?= ($packet_filter == 'B') ? 'selected' : ''; ?>>Packet B</option>
                    <option value="C" <?= ($packet_filter == 'C') ? 'selected' : ''; ?>>Packet C</option>
                </select>
            <?php endif; ?>
        </div>
            
        <button type="submit" style="padding: 0 20px; background-color: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; height: 40px;">Search</button>
            
        <?php if($search != '' || $level_filter != '' || $packet_filter != ''): ?>
            <a href="admin_dashboard.php?page=view_scores&tab=<?= $tab; ?>" style="padding: 0 20px; background-color: #ef4444; color: white; border: none; border-radius: 6px; font-weight: bold; text-decoration: none; font-size: 14px; height: 40px; display: flex; align-items: center;">Reset</a>
        <?php endif; ?>
    </form>
        
    <a href="export_scores.php?tab=<?= $tab; ?>&search=<?= urlencode($search); ?>&level_filter=<?= urlencode($level_filter); ?>&packet_filter=<?= urlencode($packet_filter); ?>" 
       style="padding: 0 20px; background-color: #a3e635; color: #0b1322; border-radius: 6px; font-weight: bold; text-decoration: none; display: flex; align-items: center; height: 40px; box-sizing: border-box; white-space: nowrap;">
       Export to Excel
    </a>
        
</div>

    <div class="table-container">
        <table class="score-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Participant Name</th>
                    <?php if ($tab == 'practice'): ?>
                        <th>Article Title</th>
                        <th>Level</th>
                    <?php else: ?>
                        <th>Test Packet</th>
                    <?php endif; ?>
                        <th>Score</th>
                        <th>Date</th>
                        <th style="text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($tab == 'practice') {
                    // ==========================================
                    // LOGIKA TAB PRACTICE
                    // ==========================================
                    $where_clauses = [];
                    if ($search != '') { 
                        $where_clauses[] = "(u.username LIKE '%$search%' OR u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%' OR m.title LIKE '%$search%')"; 
                    }
                    if ($level_filter != '') { 
                        $where_clauses[] = "m.level = '$level_filter'"; 
                    }
                    $where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

                    $score_query = "SELECT s.id, u.username AS nama_user, u.first_name, u.last_name, m.title, m.level, s.score, s.created_at 
                                    FROM practice_scores s 
                                    JOIN users u ON s.user_id = u.id 
                                    JOIN materials m ON s.material_id = m.id 
                                    $where_sql ORDER BY s.created_at DESC";
                } else {

                    // ==========================================
                    // LOGIKA TAB TEST
                    // ==========================================
                    $where_clauses = [];
                    if ($search != '') { 
                        // MENGGANTI packet_id MENJADI test_packet
                        $where_clauses[] = "(u.username LIKE '%$search%' OR u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%' OR s.test_packet LIKE '%$search%')"; 
                    }
                    if ($packet_filter != '') { 
                        // MENGGANTI packet_id MENJADI test_packet
                        $where_clauses[] = "s.test_packet = '$packet_filter'"; 
                    }
                    $where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

                    // MENGAMBIL raw_score DAN toefl_score, BUKAN score
                    $score_query = "SELECT s.id, u.username AS nama_user, u.first_name, u.last_name, s.test_packet, s.raw_score, s.toefl_score, s.created_at 
                                    FROM test_scores s 
                                    JOIN users u ON s.user_id = u.id 
                                    $where_sql ORDER BY s.created_at DESC";
                }

                $score_result = mysqli_query($conn, $score_query);
                $no = 1;
                
                if (mysqli_num_rows($score_result) > 0) {
                    while ($row = mysqli_fetch_assoc($score_result)) {
                        $tanggal = date("d M Y H:i", strtotime($row['created_at']));
                        
                        $fn = isset($row['first_name']) ? trim($row['first_name']) : '';
                        $ln = isset($row['last_name']) ? trim($row['last_name']) : '';
                        $display_name = (!empty($fn) || !empty($ln)) ? ucfirst($fn) . ' ' . ucfirst($ln) : ucfirst($row['nama_user']);

                        $delete_param = ($tab == 'practice') ? "delete_score" : "delete_test_score";

                        echo "<tr>";
                        echo "<td>{$no}</td>";
                        echo "<td class='font-semibold'>".htmlspecialchars($display_name)."</td>";
                        
                        // Menampilkan data tengah dan Skor sesuai tab
                        if ($tab == 'practice') {
                            $badge_class = "badge-" . strtolower($row['level']);
                            $score_class = ($row['score'] == 100) ? "score-perfect" : (($row['score'] >= 70) ? "score-good" : "score-bad");
                            
                            echo "<td>{$row['title']}</td>";
                            echo "<td><span class='badge {$badge_class}'>{$row['level']}</span></td>";
                            echo "<td class='{$score_class}'>{$row['score']}/100</td>";
                        } else {
                            // Tampilan khusus untuk Tab Test Score (Menyesuaikan dengan format skor TOEFL Reading)
                            $score_class = ($row['toefl_score'] >= 55) ? "score-perfect" : (($row['toefl_score'] >= 45) ? "score-good" : "score-bad");
                            
                            echo "<td><span style='background:#1e293b; color:#a3e635; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:bold;'>Packet {$row['test_packet']}</span></td>";
                            echo "<td class='{$score_class}'><strong>{$row['toefl_score']}</strong> <small style='color: #64748b; font-size: 11px;'>(Raw: {$row['raw_score']})</small></td>";
                        }

                        echo "<td>{$tanggal}</td>";
                        echo "<td style='text-align: center; vertical-align: middle;'>
                                <div style='display: flex; justify-content: center;'>
                                    <a href='javascript:void(0);' style='display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #ef4444; border-radius: 6px; font-size: 13px; font-weight: bold; text-decoration: none; color: #ffffff; width: max-content;' onclick=\"rqConfirm('Are you sure you want to delete this score history?', 'admin_dashboard.php?page=view_scores&tab={$tab}&{$delete_param}={$row['id']}');\">
                                        <span class='material-symbols-outlined' style='font-size: 16px;'>delete_outline</span>
                                    </a>
                                </div>
                              </td>";
                        echo "</tr>";
                        $no++;
                    }
                } else {
                    $colspan = ($tab == 'practice') ? 7 : 6;
                    echo "<tr><td colspan='{$colspan}' style='text-align:center; padding: 30px; color: #94a3b8;'>Data not found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>