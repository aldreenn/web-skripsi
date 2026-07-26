<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/loginpage.html');
    exit;
}
// Cegah cache untuk mencegah back setelah logout
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Panggil koneksi database
include '../config/koneksi.php';
date_default_timezone_set('Asia/Jakarta');

$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') : 'User';
$username = ucfirst($username);
$user_id = (int)$_SESSION['user_id'];

// Mengambil First Name dan Last Name dari Session
$first_name = isset($_SESSION['first_name']) ? htmlspecialchars($_SESSION['first_name'], ENT_QUOTES, 'UTF-8') : '';
$last_name = isset($_SESSION['last_name']) ? htmlspecialchars($_SESSION['last_name'], ENT_QUOTES, 'UTF-8') : '';

$greeting_name = !empty($first_name) ? ucfirst($first_name) : $username;
$full_name = (!empty($first_name) && !empty($last_name)) ? ucfirst($first_name) . ' ' . ucfirst($last_name) : $username;

// Membuat inisial 2 huruf
if (!empty($first_name) && !empty($last_name)) {
    $initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
} elseif (!empty($first_name)) {
    $initials = strtoupper(substr($first_name, 0, 1));
} else {
    $initials = strtoupper(substr($username, 0, 1));
}

// ==========================================
// LOGIKA DINAMIS PRACTICE PATH (REVISI LEVEL AKTIF)
// ==========================================

// 1. Ambil semua ID artikel yang sudah dikerjakan user ini
$completed_materials = [];
$query_scores = "SELECT material_id FROM practice_scores WHERE user_id = '$user_id'";
$result_scores = mysqli_query($conn, $query_scores);
if ($result_scores) {
    while ($row = mysqli_fetch_assoc($result_scores)) {
        $completed_materials[] = $row['material_id'];
    }
}

// 2. Ambil semua materi dari database, urutkan berdasarkan Level lalu ID
$query_materials = "SELECT * FROM materials ORDER BY level ASC, id ASC";
$result_materials = mysqli_query($conn, $query_materials);

// Struktur Penyimpanan Data bersarang: Level -> Topik -> Materi
$levels_data = [];

while ($row = mysqli_fetch_assoc($result_materials)) {
    $level = $row['level'];
    $topic = $row['topic'];
    
    // Siapkan wadah untuk Level jika belum ada
    if (!isset($levels_data[$level])) {
        $levels_data[$level] = [
            'total' => 0,
            'completed' => 0,
            'topics' => []
        ];
    }
    
    // Siapkan wadah untuk Topik di dalam Level tersebut jika belum ada
    if (!isset($levels_data[$level]['topics'][$topic])) {
        $levels_data[$level]['topics'][$topic] = [
            'total' => 0,
            'completed' => 0,
            'materials' => []
        ];
    }
    
    // Tambahkan hitungan ke Level dan Topik
    $levels_data[$level]['total']++;
    $levels_data[$level]['topics'][$topic]['total']++;
    $levels_data[$level]['topics'][$topic]['materials'][] = $row;
    
    // Jika artikel sudah dikerjakan, catat progresnya
    if (in_array($row['id'], $completed_materials)) {
        $levels_data[$level]['completed']++;
        $levels_data[$level]['topics'][$topic]['completed']++;
    }
}

// 3. Tentukan "Level Aktif" (Level pertama yang belum lulus 5 artikel)
$recommended_level = null;
foreach ($levels_data as $level_name => $data) {
    $recommended_level = $level_name; // Set default sementara
    if ($data['completed'] < 5) {
        break; // Hentikan pencarian, ini adalah level yang harus dikerjakan user sekarang
    }
}

// Cek jika ada request level spesifik dari parameter GET
$all_levels = array_keys($levels_data);
if (isset($_GET['lvl']) && in_array($_GET['lvl'], $all_levels)) {
    $active_level = $_GET['lvl'];
} else {
    $active_level = $recommended_level;
}

// Menentukan Prev dan Next Level untuk tombol navigasi
$current_lvl_index = array_search($active_level, $all_levels);
$recommended_lvl_index = array_search($recommended_level, $all_levels);
$prev_level = ($current_lvl_index !== false && $current_lvl_index > 0) ? $all_levels[$current_lvl_index - 1] : null;
$next_level = ($current_lvl_index !== false && $current_lvl_index < count($all_levels) - 1 && $current_lvl_index < $recommended_lvl_index) ? $all_levels[$current_lvl_index + 1] : null;

// Ambil kumpulan topik khusus untuk level yang sedang aktif
$active_level_topics = $active_level ? $levels_data[$active_level]['topics'] : [];

// ==========================================
// DATA STATISTIK: PRACTICE PATH
// ==========================================
$q_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM materials");
$get_total = mysqli_fetch_assoc($q_total);
$total_articles = $get_total['total'] > 0 ? $get_total['total'] : 1; 

$q_completed = mysqli_query($conn, "SELECT COUNT(DISTINCT material_id) as selesai FROM practice_scores WHERE user_id = '$user_id' AND score >= 70");
$get_completed = mysqli_fetch_assoc($q_completed);
$completed_articles = $get_completed['selesai'];

$percent = round(($completed_articles / $total_articles) * 100);
$status_belajar = ($completed_articles > 0) ? "Active" : "Not Started";

// ==========================================
// DATA STATISTIK: TEST PATH (Dinamis dari Database)
// ==========================================
$test_scores = [];
$query_test_path = "SELECT test_packet, MAX(toefl_score) as max_score FROM test_scores WHERE user_id = '$user_id' GROUP BY test_packet";
$result_test_path = mysqli_query($conn, $query_test_path);

if ($result_test_path) {
    while ($row = mysqli_fetch_assoc($result_test_path)) {
        $test_scores[strtoupper($row['test_packet'])] = $row['max_score'];
    }
}

// ==========================================
// LOGIKA BADGE CEFR DI DROPDOWN
// ==========================================
$has_paket_a = isset($test_scores['A']);
$highest_cefr_badge = null;

if ($has_paket_a) {
    $max_toefl_all = 0;
    foreach ($test_scores as $pkt => $score) {
        if ($score > $max_toefl_all) {
            $max_toefl_all = $score;
        }
    }
    
    // Tentukan CEFR level berdasarkan skor toefl tertinggi
    if ($max_toefl_all >= 63) {
        $badge_label = 'C1: The Maestro';
        $badge_color = '#a855f7'; // Ungu
    } elseif ($max_toefl_all >= 56) {
        $badge_label = 'B2: The Vanguard';
        $badge_color = '#f59e0b'; // Kuning
    } elseif ($max_toefl_all >= 48) {
        $badge_label = 'B1: The Voyager';
        $badge_color = '#3b82f6'; // Biru
    } else {
        $badge_label = 'A2: The Conqueror';
        $badge_color = '#22c55e'; // Hijau
    }

    $highest_cefr_badge = [
        'label' => $badge_label,
        'color' => $badge_color,
        'bg' => $badge_color . '20' // 20% opacity
    ];
}

$packets = [];
$query_master = mysqli_query($conn, "SELECT packet_code, title, requirement FROM test_packets ORDER BY id ASC");

if ($query_master) {
    while($row = mysqli_fetch_assoc($query_master)){
        $packets[$row['packet_code']] = [
            'title' => $row['title'],
            'req' => $row['requirement']
        ];
    }
}

$total_packets = count($packets);
$total_tests = $total_packets > 0 ? $total_packets : 1; 
$completed_tests = count($test_scores); 
$percent_test = round(($completed_tests / $total_tests) * 100);
$status_test = ($completed_tests > 0) ? "Active" : "Not Started";
$test_progress = $percent_test;

// ==========================================
// DATA FEED: PRACTICE (Artikel yang sudah diselesaikan)
// ==========================================
$practice_feed = [];
$query_practice_feed = "SELECT ps.score, ps.created_at, m.title 
                        FROM practice_scores ps 
                        JOIN materials m ON ps.material_id = m.id 
                        WHERE ps.user_id = '$user_id' AND ps.score >= 70
                        ORDER BY ps.created_at DESC 
                        LIMIT 10";
$result_practice_feed = mysqli_query($conn, $query_practice_feed);
if ($result_practice_feed) {
    while ($row = mysqli_fetch_assoc($result_practice_feed)) {
        $practice_feed[] = $row;
    }
}

// ==========================================
// DATA FEED: TEST (Paket test yang sudah diselesaikan)
// ==========================================
$test_feed = [];
$query_test_feed = "SELECT ts.toefl_score, ts.created_at, ts.test_packet, tp.title 
                    FROM test_scores ts 
                    JOIN test_packets tp ON ts.test_packet = tp.packet_code
                    WHERE ts.user_id = '$user_id'
                    ORDER BY ts.created_at DESC 
                    LIMIT 10";
$result_test_feed = mysqli_query($conn, $query_test_feed);
if ($result_test_feed) {
    while ($row = mysqli_fetch_assoc($result_test_feed)) {
        $test_feed[] = $row;
    }
}


// ==========================================
// DATA GRAFIK PERFORMA (PRACTICE & TEST)
// ==========================================
$graph_practice_by_level = [];
$query_gp = "SELECT s.score, m.title, m.level 
             FROM practice_scores s 
             JOIN materials m ON s.material_id = m.id 
             WHERE s.user_id = '$user_id' 
             ORDER BY s.created_at ASC";
$res_gp = mysqli_query($conn, $query_gp);
$max_streak = 0;
$current_streak = 0;

if($res_gp) {
    while($r = mysqli_fetch_assoc($res_gp)) {
        $lvl = $r['level'];
        $graph_practice_by_level[$lvl][] = [
            'score' => $r['score'],
            'title' => $r['title']
        ];

        // MENGHITUNG PERFECT STREAK (GLOBAL)
        if ($r['score'] == 100) {
            $current_streak++;
            if ($current_streak > $max_streak) {
                $max_streak = $current_streak;
            }
        } else {
            $current_streak = 0;
        }
    }
}
$graph_practice = $graph_practice_by_level[$active_level] ?? [];
$perfect_streak = ($max_streak >= 2) ? $max_streak : 0;

$graph_test = [];
$query_gt = "SELECT ts.toefl_score as score, tp.title 
             FROM test_scores ts
             JOIN test_packets tp ON ts.test_packet = tp.packet_code
             WHERE ts.user_id = '$user_id' 
             ORDER BY ts.created_at ASC";
$res_gt = mysqli_query($conn, $query_gt);
if($res_gt) {
    while($r = mysqli_fetch_assoc($res_gt)) {
        $graph_test[] = $r;
    }
}

// 4. Leaderboard Akumulasi (XP) Berdasarkan Level
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$level_filter = isset($_GET['level_filter']) ? mysqli_real_escape_string($conn, $_GET['level_filter']) : '';

$where_clauses = [];
if ($search != '') {
    $where_clauses[] = "u.username LIKE '%$search%'";
}
if ($level_filter != '') {
    $where_clauses[] = "m.level = '$level_filter'";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Menghitung TOTAL SKOR (XP)
$leaderboard_query = "SELECT u.username AS nama_user, 
                             u.first_name, 
                             u.last_name,
                             SUM(s.score) as total_score, 
                             COUNT(DISTINCT s.material_id) as artikel_lulus
                      FROM practice_scores s 
                      JOIN users u ON s.user_id = u.id 
                      JOIN materials m ON s.material_id = m.id 
                      $where_sql
                      GROUP BY s.user_id
                      ORDER BY total_score DESC, artikel_lulus DESC 
                      LIMIT 50"; 
$leaderboard_result = mysqli_query($conn, $leaderboard_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | ReadQuest</title>
    <link rel="icon" type="image/png" href="/assets/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="../desain/dashboard.css?v=<?= time(); ?>">
    <style>
        .panel-tabs span { cursor: pointer; transition: color 0.2s; }
        .panel-tabs span:hover { color: #ffffff; }
    </style>
</head>
<body>
    <nav class="navbar" aria-label="Primary">
        <div class="navbar-left">
            <span class="material-symbols-outlined mobile-menu-btn" onclick="toggleMobileMenu()" style="display: none; cursor: pointer; margin-right: 15px; font-size: 28px; user-select: none;">menu</span>
            <a href="#home" class="navbar-logo" onclick="switchPage('home')">ReadQuest</a>
        </div>

        <ul class="navbar-center navbar-links">
            <li><a href="/pages/dashboard.php" onclick="switchPage('dashboard'); return false;">Dashboard</a></li>
            <li><a href="/pages/practice.php">Practice</a></li>
            <li><a href="/pages/test.php">Test</a></li>
            <li><a href="#leaderboard" onclick="switchPage('leaderboard')">Leaderboard</a></li>
        </ul>

        <div class="navbar-right">
            <div class="profile-dropdown">
                <div class="avatar-circle" onclick="toggleProfileMenu()" id="avatarBtn">
                    <?php echo $initials; ?>
                </div>
                
                <div class="dropdown-content" id="profileMenu">
                    <div class="dropdown-header" style="display: flex; flex-direction: column;">
                        <span class="user-name-drop"><?php echo $full_name; ?></span>
                        <?php if ($highest_cefr_badge): ?>
                        <div class="user-badge-drop" style="background-color: <?= $highest_cefr_badge['bg'] ?>; border: 1px solid <?= $highest_cefr_badge['color'] ?>; color: <?= $highest_cefr_badge['color'] ?>;">
                            <span class="material-symbols-outlined" style="font-size: 14px; margin-right: 4px;">military_tech</span>
                            <?= $highest_cefr_badge['label'] ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <a href="/pages/manage_account.php">
                        <span class="material-symbols-outlined">settings</span> Manage Account
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="/auth/logout.php" class="logout-text">
                        <span class="material-symbols-outlined">logout</span> Log Out
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main id="page-home">
        <div class="dashboard-container">
            <div class="dash-header">
                <div class="welcome-text">
                    <h2 style="color:#a3e635">Hey <?php echo $greeting_name; ?>!</h2>
                    <p>Keep collecting XP and monitor your Practice and Test progress here.</p>
                </div>
                <a href="/pages/practice.php?lvl=<?= htmlspecialchars($recommended_level ?? 'A1') ?>" class="btn-primary">Continue Learning</a>
            </div>

            <div class="dash-grid">
                <div class="dash-main-content">
                    <div class="card-panel">
                        <div class="panel-header">
                            <h3 style="display:flex; align-items:center; gap:8px;">
                                <span class="material-symbols-outlined" style="color: #a3e635;">local_library</span>
                                My Learning
                            </h3>
                            <div class="panel-tabs">
                                <span class="active" id="tab-practice" onclick="togglePath('practice')">Practice Path</span>
                                <span id="tab-test" onclick="togglePath('test')">Test Path</span>
                            </div>
                        </div>
                        
                        <div id="content-practice" style="display: flex; flex-direction: column; padding: 20px;">
    
                            <?php if (!empty($active_level_topics)): ?>

                                <div class="current-level-header">
                                    <div class="level-info">
                                        <p class="level-label">Current Level</p>
                                        <h2 id="display-active-level" class="level-title">Level <?= htmlspecialchars($active_level) ?></h2>
                                    </div>
                                    <div class="level-nav-buttons">
                                        <button id="btn-prev-level" class="btn-level-nav <?= !$prev_level ? 'disabled' : '' ?>" <?= !$prev_level ? 'disabled' : '' ?>>
                                            <span class="material-symbols-outlined" style="font-size: 18px;">chevron_left</span> Previous Level
                                        </button>
                                        <button id="btn-next-level" class="btn-level-nav <?= !$next_level ? 'disabled' : '' ?>" <?= !$next_level ? 'disabled' : '' ?>>
                                            Next Level<span class="material-symbols-outlined" style="font-size: 18px;">chevron_right</span>
                                        </button>
                                    </div>
                                </div>
                            
                                <div class="scrollable-path" id="practice-scrollable-path">
                                    <?php foreach ($active_level_topics as $topic_name => $data): ?>
                                    <?php 
                                        // Hitung persentase per topik
                                        $progress_percentage = ($data['total'] > 0) ? round(($data['completed'] / $data['total']) * 100) : 0;
                                    ?>

                                    <div class="topic-section" style="margin-bottom: 40px;">

                                        <div class="thm-topic-header">
                                            <h3 class="thm-topic-title"><?= htmlspecialchars($topic_name) ?></h3>
                                            <div class="thm-topic-progress">
                                                <div class="thm-progress-track">
                                                    <div class="thm-progress-fill" style="width: <?= $progress_percentage ?>%;"></div>
                                                </div>
                                                <span class="thm-progress-text"><?= $progress_percentage ?>%</span>
                                            </div>
                                        </div>
                                    
                                        <div class="thm-article-list">
                                            <?php foreach ($data['materials'] as $mat): ?>
                                                <?php 
                                                    $is_completed = in_array($mat['id'], $completed_materials);

                                                    // Konfigurasi visual berdasarkan status selesai/belum
                                                    $card_class = $is_completed ? 'completed' : 'incomplete';
                                                    $icon = $is_completed ? 'check_circle' : 'menu_book';
                                                    $bg_class = $is_completed ? 'bg-blue' : 'bg-grey';
                                                ?>

                                                <div class="thm-article-card <?= $card_class ?>" onclick="window.location.href='reading-detail.php?id=<?= $mat['id'] ?>'">
                                                    <div class="thm-article-icon <?= $bg_class ?>">
                                                        <span class="material-symbols-outlined"><?= $icon ?></span>
                                                    </div>
                                                    <div class="thm-article-info">
                                                        <h4><?= htmlspecialchars($mat['title']) ?></h4>
                                                        <p><?= htmlspecialchars($mat['description']) ?></p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                            
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                            
                            <?php else: ?>
                                <div style="text-align: center; padding: 40px 20px;">
                                    <span class="material-symbols-outlined" style="font-size: 48px; color: #a3e635; margin-bottom: 10px;">social_leaderboard</span>
                                    <h3 style="color: #f8fafc; margin-bottom: 5px;">Amazing!</h3>
                                    <p style="color: #94a3b8;">You have completed all the available learning materials.</p>
                                </div>
                            <?php endif; ?>
                            
                        </div>

                        <div id="content-test" style="display: none; flex-direction: column; padding: 20px;">
    
                            <?php
                            // Note: Logika untuk Test Path (Query dan kalkulasi progress) 
                            // sekarang sudah dipindahkan ke bagian atas file (baris 117-147)
                            // agar variabel statistiknya bisa digunakan pada elemen sidebar (Test Selesai & Progress Test).
                            ?>

                            <div class="thm-topic-header">
                                <h3 class="thm-topic-title" style="color: #a3e635;">Test Progress</h3>
                                <div class="thm-topic-progress">
                                    <div class="thm-progress-track">
                                        <div class="thm-progress-fill" style="width: <?= $test_progress ?>%; background-color: #a3e635;"></div>
                                    </div>
                                    <span class="thm-progress-text"><?= $test_progress ?>%</span>
                                </div>
                            </div>
                        
                            <div class="scrollable-path">
                                <div class="thm-article-list">
                                    <?php foreach ($packets as $id_paket => $paket_data): ?>
                                        <?php
                                            // Logika Status Paket
                                            $is_completed = isset($test_scores[$id_paket]);
                                            $score = $is_completed ? $test_scores[$id_paket] : 0;

                                            // Cek apakah paket ini terkunci (syaratnya belum ada di history test_scores)
                                            $is_locked = false;
                                            if ($paket_data['req'] !== null && !isset($test_scores[$paket_data['req']])) {
                                                $is_locked = true;
                                            }
                                        
                                            $cefr_badge_html = '';
                                            // Penyesuaian Visual berdasarkan Status
                                            if ($is_locked) {
                                                $card_class = "incomplete locked-state";
                                                $icon_bg = "bg-grey";
                                                $icon = "lock";
                                                $onclick = "onclick=\"alert('Please complete " . $packets[$paket_data['req']]['title'] . " first to unlock this test.')\"";
                                                $desc = "Complete " . $packets[$paket_data['req']]['title'] . " first to unlock access.";
                                            } elseif ($is_completed) {
                                                $card_class = "completed";
                                                // Pakai inline style untuk warna gradien oranye/emas
                                                $icon_bg = "\" style=\"background: linear-gradient(135deg, #f59e0b, #b45309);\""; 
                                                $icon = "check_circle";
                                                $onclick = "onclick=\"window.location.href='test.php?paket=$id_paket'\"";
                                                $desc = "Highest Score: $score | Status: Completed";
                                                
                                                if ($score >= 63) {
                                                    $pkt_badge_label = 'C1: The Maestro';
                                                    $pkt_badge_color = '#a855f7';
                                                } elseif ($score >= 56) {
                                                    $pkt_badge_label = 'B2: The Vanguard';
                                                    $pkt_badge_color = '#f59e0b';
                                                } elseif ($score >= 48) {
                                                    $pkt_badge_label = 'B1: The Voyager';
                                                    $pkt_badge_color = '#3b82f6';
                                                } else {
                                                    $pkt_badge_label = 'A2: The Conqueror';
                                                    $pkt_badge_color = '#22c55e';
                                                }
                                                $pkt_badge_bg = $pkt_badge_color . '20';
                                                $cefr_badge_html = '<div class="user-badge-drop" style="background-color: '.$pkt_badge_bg.'; border: 1px solid '.$pkt_badge_color.'; color: '.$pkt_badge_color.'; margin-left: auto; margin-right: 15px; margin-top: 0;"><span class="material-symbols-outlined" style="font-size: 14px; margin-right: 4px;">military_tech</span>'.$pkt_badge_label.'</div>';
                                                
                                            } else {
                                                $card_class = "active";
                                                $icon_bg = "bg-blue";
                                                $icon = "timer";
                                                $onclick = "onclick=\"window.location.href='test.php?paket=$id_paket'\"";
                                                $desc = "Ready to start the test?";
                                            }
                                        ?>

                                        <div class="thm-article-card <?= $card_class ?>" <?= $onclick ?>>
                                            <div class="thm-article-icon <?= $icon_bg ?>">
                                                <span class="material-symbols-outlined"><?= $icon ?></span>
                                            </div>
                                            <div class="thm-article-info">
                                                <h4><?= $paket_data['title'] ?></h4>
                                                <p><?= $desc ?></p>
                                            </div>
                                            <?= $cefr_badge_html ?>
                                        </div>
                                        
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <!-- KARTU GRAFIK PERFORMA -->
                    <div class="card-panel performance-graph-panel" id="performance-graph-panel" style="margin-top: 25px;">
                        <div class="panel-header" style="border-bottom: none; padding-bottom: 0;">
                            <h3 style="display:flex; align-items:center; gap:8px;">
                                <span class="material-symbols-outlined" style="color: #a3e635;">monitoring</span>
                                Performance Graph <span style="color: #a3e635;">Level <span id="display-graph-level"><?= htmlspecialchars($active_level) ?></span></span>
                            </h3>
                        </div>
                        
                        <div id="graph-container-practice" class="graph-wrapper" style="display: block;">
                            <p id="empty-practice-msg" style="color: #94a3b8; text-align: center; margin: auto; padding-top: 50px; display: <?= empty($graph_practice) ? 'block' : 'none' ?>;">No practice data to display.</p>
                            <div class="line-chart-container" id="line-chart-practice" style="display: <?= empty($graph_practice) ? 'none' : 'flex' ?>;">
                                <div class="y-axis">
                                    <span>100</span>
                                    <span>75</span>
                                    <span>50</span>
                                    <span>25</span>
                                    <span>0</span>
                                </div>
                                <div class="chart-content">
                                    <div class="chart-area" id="chart-area-practice">
                                        <div class="grid-lines">
                                            <div class="grid-line"></div>
                                            <div class="grid-line"></div>
                                            <div class="grid-line"></div>
                                            <div class="grid-line"></div>
                                            <div class="grid-line" style="border: none; border-top: 1px solid #334155;"></div>
                                        </div>
                                        <svg class="chart-svg" preserveAspectRatio="none" id="svg-practice">
                                            <defs>
                                                <linearGradient id="gradient-practice" x1="0" x2="0" y1="0" y2="1">
                                                    <stop offset="0%" stop-color="#a3e635" stop-opacity="0.3"/>
                                                    <stop offset="100%" stop-color="#a3e635" stop-opacity="0"/>
                                                </linearGradient>
                                            </defs>
                                            <path class="chart-area-path" fill="url(#gradient-practice)" d=""></path>
                                            <path class="chart-line-path" fill="none" stroke="#a3e635" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" d=""></path>
                                        </svg>
                                        <div class="data-points-container" id="points-practice"></div>
                                    </div>
                                    <div class="x-axis" id="x-axis-practice"></div>
                                </div>
                            </div>
                        </div>

                        <div id="graph-container-test" class="graph-wrapper" style="display: none;">
                            <p id="empty-test-msg" style="color: #94a3b8; text-align: center; margin: auto; padding-top: 50px; display: <?= empty($graph_test) ? 'block' : 'none' ?>;">No test data to display.</p>
                            <div class="line-chart-container" id="line-chart-test" style="display: <?= empty($graph_test) ? 'none' : 'flex' ?>;">
                                <div class="y-axis">
                                    <span>100</span>
                                    <span>75</span>
                                    <span>50</span>
                                    <span>25</span>
                                    <span>0</span>
                                </div>
                                <div class="chart-content">
                                    <div class="chart-area" id="chart-area-test">
                                        <div class="grid-lines">
                                            <div class="grid-line"></div>
                                            <div class="grid-line"></div>
                                            <div class="grid-line"></div>
                                            <div class="grid-line"></div>
                                            <div class="grid-line" style="border: none; border-top: 1px solid #334155;"></div>
                                        </div>
                                        <svg class="chart-svg" preserveAspectRatio="none" id="svg-test">
                                            <defs>
                                                <linearGradient id="gradient-test" x1="0" x2="0" y1="0" y2="1">
                                                    <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.3"/>
                                                    <stop offset="100%" stop-color="#3b82f6" stop-opacity="0"/>
                                                </linearGradient>
                                            </defs>
                                            <path class="chart-area-path" fill="url(#gradient-test)" d=""></path>
                                            <path class="chart-line-path" fill="none" stroke="#3b82f6" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" d=""></path>
                                        </svg>
                                        <div class="data-points-container" id="points-test"></div>
                                    </div>
                                    <div class="x-axis" id="x-axis-test"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="dash-sidebar">
                    <div class="card-panel mission-panel">
                        <div id="sidebar-practice-progress" style="display: block;">
                            <p class="time-left">Status: <strong style="color: #a3e635;"><?php echo $status_belajar; ?></strong></p>
                            <h3 style="display:flex; align-items:center; gap:8px;">
                                <span class="material-symbols-outlined" style="color: #a3e635;">trending_up</span>
                                Practice Progress
                            </h3>
                            <p class="mission-desc">Complete articles to unlock the next level.</p>
                            
                            <div class="mission-item">
                                <div class="mission-info">
                                    <span>Total Reading Progress</span>
                                    <span><?php echo $percent; ?>%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $percent; ?>%;"></div>
                                </div>
                            </div>
                        </div>

                        <div id="sidebar-test-progress" style="display: none;">
                            <p class="time-left">Status: <strong style="color: #a3e635;"><?php echo $status_test; ?></strong></p>
                            <h3 style="display:flex; align-items:center; gap:8px;">
                                <span class="material-symbols-outlined" style="color: #a3e635;">assignment_turned_in</span>
                                Test Progress
                            </h3>
                            <p class="mission-desc">Complete test packages to see your predicted score.</p>
                            
                            <div class="mission-item">
                                <div class="mission-info">
                                    <span>Total Packets Completed</span>
                                    <span><?php echo $percent_test; ?>%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $percent_test; ?>%; background-color: #f59e0b;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-panel stats-panel">
                        <div id="sidebar-practice-stats" style="display: block;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <h3 style="margin: 0; font-size: 28px; color: #ffffff;"><?php echo $completed_articles; ?> <span style="font-size: 16px; color: #94a3b8;">/ <?php echo $total_articles; ?></span></h3>
                                    <p style="margin: 5px 0 0 0; color: #94a3b8; font-size: 14px;">Articles Completed</p>
                                </div>
                                
                                <!-- Perfect Streak -->
                                <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end;">
                                    <div style="display: flex; align-items: center; gap: 5px;">
                                        <h3 id="display-perfect-streak" style="margin: 0; font-size: 28px; color: #ffffff;"><?php echo $perfect_streak; ?></h3>
                                        <span class="material-symbols-outlined" style="color: #ef4444; font-size: 28px;">local_fire_department</span>
                                    </div>
                                    <p style="margin: 5px 0 0 0; color: #94a3b8; font-size: 14px;">Longest Perfect Streak</p>
                                </div>
                            </div>
                        </div>

                        <div id="sidebar-test-stats" style="display: none;">
                            <h3 style="margin: 0; font-size: 28px; color: #ffffff;"><?php echo $completed_tests; ?> <span style="font-size: 16px; color: #94a3b8;">/ <?php echo $total_tests; ?></span></h3>
                            <p style="margin: 5px 0 0 0; color: #94a3b8; font-size: 14px;">Tests Completed</p>
                        </div>

                        <div style="margin-top: 15px; border-top: 1px solid #334155; padding-top: 15px;">
                            <a href="score_history.php?tab=practice" id="history-details-link" style="display: inline-flex; align-items: center; gap: 5px; color: #3b82f6; text-decoration: none; font-size: 14px; font-weight: bold; transition: color 0.3s;" onmouseover="this.style.color='#60a5fa'" onmouseout="this.style.color='#3b82f6'">
                                View History Details
                                <span class="material-symbols-outlined" style="font-size: 16px;">arrow_forward</span>
                            </a>
                        </div>
                    </div>

                    <div class="card-panel feed-panel">
                        <div class="feed-header">
                            <span class="material-symbols-outlined" style="color: #a3e635; font-size: 20px;">notifications_active</span>
                            <h3>Activity Feed</h3>
                        </div>

                        <div id="sidebar-practice-feed" class="feed-list" style="display: block;">
                            <?php if (!empty($practice_feed)): ?>
                                <?php foreach ($practice_feed as $feed_item): ?>
                                    <?php
                                        $time_ago = '';
                                        if (!empty($feed_item['created_at'])) {
                                            $diff = time() - strtotime($feed_item['created_at']);
                                            if ($diff < 60) $time_ago = $diff . ' seconds ago';
                                            elseif ($diff < 3600) $time_ago = floor($diff / 60) . ' minutes ago';
                                            elseif ($diff < 86400) $time_ago = floor($diff / 3600) . ' hours ago';
                                            elseif ($diff < 604800) $time_ago = floor($diff / 86400) . ' days ago';
                                            else $time_ago = date('d M Y', strtotime($feed_item['created_at']));
                                        }
                                    ?>
                                    <div class="feed-item">
                                        <div class="feed-icon feed-icon-practice">
                                            <span class="material-symbols-outlined">emoji_events</span>
                                        </div>
                                        <div class="feed-content">
                                            <p class="feed-message">Congrats! 🎉 You successfully completed <strong><?= htmlspecialchars($feed_item['title']) ?></strong> with a score of <strong style="color: #a3e635;"><?= $feed_item['score'] ?></strong></p>
                                            <span class="feed-time"><?= $time_ago ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="feed-empty">
                                    <span class="material-symbols-outlined" style="font-size: 32px; color: #334155;">inbox</span>
                                    <p>No activity yet. Start learning to see your achievements!</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div id="sidebar-test-feed" class="feed-list" style="display: none;">
                            <?php if (!empty($test_feed)): ?>
                                <?php foreach ($test_feed as $feed_item): ?>
                                    <?php
                                        $time_ago = '';
                                        if (!empty($feed_item['created_at'])) {
                                            $diff = time() - strtotime($feed_item['created_at']);
                                            if ($diff < 60) $time_ago = $diff . ' seconds ago';
                                            elseif ($diff < 3600) $time_ago = floor($diff / 60) . ' minutes ago';
                                            elseif ($diff < 86400) $time_ago = floor($diff / 3600) . ' hours ago';
                                            elseif ($diff < 604800) $time_ago = floor($diff / 86400) . ' days ago';
                                            else $time_ago = date('d M Y', strtotime($feed_item['created_at']));
                                        }
                                    ?>
                                    <div class="feed-item">
                                        <div class="feed-icon feed-icon-test">
                                            <span class="material-symbols-outlined">military_tech</span>
                                        </div>
                                        <div class="feed-content">
                                            <p class="feed-message">Congrats! 🏆 You completed <strong><?= htmlspecialchars($feed_item['title']) ?></strong> with a TOEFL score of <strong style="color: #3b82f6;"><?= $feed_item['toefl_score'] ?></strong></p>
                                            <span class="feed-time"><?= $time_ago ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="feed-empty">
                                    <span class="material-symbols-outlined" style="font-size: 32px; color: #334155;">inbox</span>
                                    <p>No test activity yet. Start a test to see your achievements!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <main id="page-leaderboard" style="display: none;">
        <div class="dashboard-container">
            <div class="card-panel leaderboard-section">
                <h2 style="margin-bottom: 5px; color: #ffffff;">🏆 Global Leaderboard</h2>
                <p style="color: #94a3b8; font-size: 14px; margin-bottom: 20px;">
                    <?php 
                        if ($level_filter == '') {
                            echo "Displaying accumulated XP from all levels.";
                        } else {
                            echo "Displaying point competition specifically in <b style='color:#ffffff;'>Level " . htmlspecialchars($level_filter) . "</b>.";
                        }
                    ?>
                </p>
                
                <form class="leaderboard-filter-form" style="display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap; align-items: center;" onsubmit="return false;">
                    <input type="text" id="lb-search" name="search" placeholder="Search competitor's name..." value="<?= htmlspecialchars($search); ?>" style="flex-grow: 1; padding: 12px 15px; border: 1px solid #334155; border-radius: 8px; font-size: 14px; outline: none; font-family: inherit; background-color: #0f172a; color: white;" oninput="debounceFetchLeaderboard()">
                    
                    <select id="lb-level" name="level_filter" style="padding: 12px 15px; border: 1px solid #334155; border-radius: 8px; font-size: 14px; outline: none; background: #0f172a; color: white; font-family: inherit;" onchange="fetchLeaderboard()">
                        <option value="">🏆 All Leagues (Global)</option>
                        <option value="A1" <?= ($level_filter == 'A1') ? 'selected' : ''; ?>>📚 A1 League</option>
                        <option value="A2" <?= ($level_filter == 'A2') ? 'selected' : ''; ?>>📘 A2 League</option>
                        <option value="B1" <?= ($level_filter == 'B1') ? 'selected' : ''; ?>>🔥 B1 League</option>
                        <option value="B2" <?= ($level_filter == 'B2') ? 'selected' : ''; ?>>⚡ B2 League</option>
                        <option value="C1" <?= ($level_filter == 'C1') ? 'selected' : ''; ?>>🌟 C1 League</option>
                        <option value="C2" <?= ($level_filter == 'C2') ? 'selected' : ''; ?>>👑 C2 League</option>
                    </select>
                </form>
                
                <div style="overflow-x: auto;">
                    <table class="leaderboard-table" style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 80px; text-align: center;">Rank</th>
                                <th style="text-align: left; padding-right: 20px;">Participant Name</th>
                                <th class="hide-mobile" style="width: 150px; text-align: center; white-space: nowrap;">Articles Passed</th>
                                <th style="width: 160px; text-align: right; white-space: nowrap;">Total Score (XP)</th>
                            </tr>
                        </thead>
                        <tbody id="leaderboard-tbody">
                            <?php 
                            $rank = 1;
                            if (mysqli_num_rows($leaderboard_result) > 0) {
                                while ($row = mysqli_fetch_assoc($leaderboard_result)) {
                                    $row_class = "";
                                    if ($rank == 1) {
                                        $row_class = "rank-1";
                                    } elseif ($rank == 2) {
                                        $row_class = "rank-2";
                                    } elseif ($rank == 3) {
                                        $row_class = "rank-3";
                                    }
                                    
                                    $fn = isset($row['first_name']) ? trim($row['first_name']) : '';
                                    $ln = isset($row['last_name']) ? trim($row['last_name']) : '';

                                    if (!empty($fn) && !empty($ln)) {
                                        $display_name = ucfirst($fn) . ' ' . ucfirst($ln);
                                    } elseif (!empty($fn)) {
                                        $display_name = ucfirst($fn);
                                    } else {
                                        $display_name = ucfirst($row['nama_user']);
                                    }

                                    $user_highlight = (strtolower($row['nama_user']) == strtolower($_SESSION['username'])) ? " <small style='color:#3b82f6;'>(You)</small>" : "";

                                    echo "<tr class='{$row_class}'>\n";
                                    echo "    <td style='text-align: center;'><span class='badge-rank'>{$rank}</span></td>\n";
                                    echo "    <td style='font-weight: 500; text-align: left; color:#e2e8f0;'>" . htmlspecialchars($display_name) . "{$user_highlight}</td>\n";
                                    echo "    <td class='hide-mobile' style='text-align: center; color:#cbd5e1;'>{$row['artikel_lulus']}</td>\n";
                                    echo "    <td style='text-align: right; font-weight: bold; color: #a3e635; font-size: 16px;'>" . number_format($row['total_score']) . " XP</td>\n";
                                    echo "</tr>\n";
                                    $rank++;
                                }
                            } else {
                                echo "<tr><td colspan='4' style='text-align:center; color:#64748b; padding: 40px;'>No score records found for this level yet. Be the first!</td></tr>\n";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <script>
                    let lbTimer;
                    function debounceFetchLeaderboard() {
                        clearTimeout(lbTimer);
                        lbTimer = setTimeout(() => fetchLeaderboard(), 400);
                    }
                    
                    function fetchLeaderboard() {
                        const search = document.getElementById('lb-search').value;
                        const level = document.getElementById('lb-level').value;
                        const tbody = document.getElementById('leaderboard-tbody');
                        
                        let skeletonHTML = '';
                        for(let i=0; i<5; i++) {
                            skeletonHTML += `
                                <tr class="skeleton-row">
                                    <td style="text-align: center;"><div class="skeleton-box" style="width: 40px; margin: 0 auto; border-radius: 50%; height: 40px;"></div></td>
                                    <td><div class="skeleton-box" style="width: 70%;"></div></td>
                                    <td class="hide-mobile" style="text-align: center;"><div class="skeleton-box" style="width: 30px; margin: 0 auto;"></div></td>
                                    <td style="text-align: right;"><div class="skeleton-box" style="width: 80px; margin-left: auto;"></div></td>
                                </tr>
                            `;
                        }
                        tbody.innerHTML = skeletonHTML;
                        
                        // Update URL parameter tanpa refresh untuk maintain state jika di-refresh manual
                        const newUrl = new URL(window.location);
                        if (search) newUrl.searchParams.set('search', search);
                        else newUrl.searchParams.delete('search');
                        
                        if (level) newUrl.searchParams.set('level_filter', level);
                        else newUrl.searchParams.delete('level_filter');
                        
                        window.history.replaceState(null, null, newUrl);
                        
                        fetch('dashboard.php?search=' + encodeURIComponent(search) + '&level_filter=' + encodeURIComponent(level))
                            .then(response => response.text())
                            .then(html => {
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(html, 'text/html');
                                const newTbody = doc.querySelector('#leaderboard-tbody');
                                if (newTbody) {
                                    tbody.innerHTML = newTbody.innerHTML;
                                }
                            })
                            .catch(err => {
                                console.error('Error fetching leaderboard', err);
                            });
                    }
                </script>
            </div>
        </div>
    </main>

    <script>
        let activeLevel = <?= json_encode($active_level, JSON_INVALID_UTF8_SUBSTITUTE) ?: 'null' ?>;
        const recommendedLevel = <?= json_encode($recommended_level, JSON_INVALID_UTF8_SUBSTITUTE) ?: 'null' ?>;
        const allLevels = <?= json_encode($all_levels, JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]' ?>;
        const levelsData = <?= json_encode($levels_data, JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}' ?>;
        const completedMaterials = <?= json_encode($completed_materials, JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]' ?>;
        const graphPracticeByLevel = <?= json_encode($graph_practice_by_level, JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}' ?>;
        let practiceData = (graphPracticeByLevel[activeLevel] || []).slice(-5);
        const testData = (<?= json_encode($graph_test, JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]' ?>).slice(-5);

        function escapeHTML(str) {
            if (!str) return '';
            return str.replace(/[&<>'"]/g, 
                tag => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    "'": '&#39;',
                    '"': '&quot;'
                }[tag] || tag)
            );
        }

        function renderPracticePath(level) {
            const levelInfo = levelsData[level];
            const pathContainer = document.getElementById('practice-scrollable-path');
            if (!pathContainer || !levelInfo) return;

            let html = '';
            const topics = levelInfo.topics;
            
            for (const topicName in topics) {
                if (!topics.hasOwnProperty(topicName)) continue;
                const topicData = topics[topicName];
                const progressPercentage = topicData.total > 0 ? Math.round((topicData.completed / topicData.total) * 100) : 0;
                
                html += `
                    <div class="topic-section" style="margin-bottom: 40px;">
                        <div class="thm-topic-header">
                            <h3 class="thm-topic-title">${escapeHTML(topicName)}</h3>
                            <div class="thm-topic-progress">
                                <div class="thm-progress-track">
                                    <div class="thm-progress-fill" style="width: ${progressPercentage}%;"></div>
                                </div>
                                <span class="thm-progress-text">${progressPercentage}%</span>
                            </div>
                        </div>
                        
                        <div class="thm-article-list">
                `;
                
                topicData.materials.forEach(mat => {
                    const isCompleted = completedMaterials.includes(mat.id.toString()) || completedMaterials.includes(Number(mat.id));
                    const cardClass = isCompleted ? 'completed' : 'incomplete';
                    const icon = isCompleted ? 'check_circle' : 'menu_book';
                    const bgClass = isCompleted ? 'bg-blue' : 'bg-grey';
                    
                    html += `
                        <div class="thm-article-card ${cardClass}" onclick="window.location.href='reading-detail.php?id=${mat.id}'">
                            <div class="thm-article-icon ${bgClass}">
                                <span class="material-symbols-outlined">${icon}</span>
                            </div>
                            <div class="thm-article-info">
                                <h4>${escapeHTML(mat.title)}</h4>
                                <p>${escapeHTML(mat.description)}</p>
                            </div>
                        </div>
                    `;
                });
                
                html += `
                        </div>
                    </div>
                `;
            }
            
            pathContainer.innerHTML = html;
        }

        function updateLevelButtons(level) {
            const prevBtn = document.getElementById('btn-prev-level');
            const nextBtn = document.getElementById('btn-next-level');
            const displayLvl = document.getElementById('display-active-level');
            const displayGraphLvl = document.getElementById('display-graph-level');
            
            if (displayLvl) {
                displayLvl.textContent = `Level ${level}`;
            }
            if (displayGraphLvl) {
                displayGraphLvl.textContent = level;
            }
            
            const currentIdx = allLevels.indexOf(level);
            const recommendedIdx = allLevels.indexOf(recommendedLevel);
            
            if (prevBtn) {
                if (currentIdx > 0) {
                    prevBtn.disabled = false;
                    prevBtn.classList.remove('disabled');
                } else {
                    prevBtn.disabled = true;
                    prevBtn.classList.add('disabled');
                }
            }
            
            if (nextBtn) {
                if (currentIdx < allLevels.length - 1 && currentIdx < recommendedIdx) {
                    nextBtn.disabled = false;
                    nextBtn.classList.remove('disabled');
                } else {
                    nextBtn.disabled = true;
                    nextBtn.classList.add('disabled');
                }
            }
        }

        function switchToLevel(level) {
            if (!allLevels.includes(level)) return;
            activeLevel = level;
            
            // Update URL search param 'lvl' without refreshing
            const url = new URL(window.location);
            url.searchParams.set('lvl', level);
            window.history.replaceState({}, '', url);

            updateLevelButtons(level);
            renderPracticePath(level);
            
            practiceData = (graphPracticeByLevel[level] || []).slice(-5);
            drawAllGraphs();
        }

        function drawLineChart(containerId, data, isTest) {
            const container = document.getElementById(containerId);
            if (!container) return;

            const wrapper = container.closest('.graph-wrapper');
            if (wrapper.style.display === 'none') return;
            
            const emptyMsg = wrapper.querySelector(isTest ? '#empty-test-msg' : '#empty-practice-msg');
            
            if (!data || data.length === 0) {
                container.style.display = 'none';
                if (emptyMsg) emptyMsg.style.display = 'block';
                return;
            }
            
            container.style.display = 'flex';
            if (emptyMsg) emptyMsg.style.display = 'none';
            if (wrapper.style.display === 'none') return;

            const area = container.querySelector('.chart-area');
            const svgWidth = area.clientWidth;
            const svgHeight = area.clientHeight;

            const svg = container.querySelector('.chart-svg');
            const areaPath = svg.querySelector('.chart-area-path');
            const linePath = svg.querySelector('.chart-line-path');
            const pointsContainer = container.querySelector('.data-points-container');
            const xAxisContainer = container.querySelector('.x-axis');

            pointsContainer.innerHTML = '';
            xAxisContainer.innerHTML = '';

            let chartData = data;
            if (chartData.length === 1) {
                chartData = [chartData[0], chartData[0]]; // duplicate for a single line span
            }

            // Memberi jarak ekstra (80px) di kiri & kanan agar label panjang di awal/akhir tidak terpotong overflow container
            const paddingX = 80; 
            const usableWidth = svgWidth - (paddingX * 2);
            const stepX = usableWidth / Math.max(1, (chartData.length - 1));

            let dArea = `M ${paddingX} ${svgHeight} `;
            let dLine = '';
            let lastX = 0;

            chartData.forEach((item, index) => {
                let realScore = parseFloat(item.score);
                let score = isTest ? Math.min(100, Math.max(0, (realScore / 677) * 100)) : realScore;
                
                let x = paddingX + (index * stepX);
                lastX = x;
                let y = svgHeight - (score / 100) * svgHeight;
                y = Math.max(0, Math.min(svgHeight, y));

                if (index === 0) {
                    dLine += `M ${x} ${y}`;
                    dArea += `L ${x} ${y} `;
                } else {
                    dLine += ` L ${x} ${y}`;
                    dArea += `L ${x} ${y} `;
                }

                if (data.length === 1 && index === 1) return; // don't draw duplicate point/label

                let leftPercent = (data.length === 1) ? 50 : (x / svgWidth) * 100;
                let bottomPercent = (score / 100) * 100;
                let colorClass = isTest ? 'test' : 'practice';
                let colorHex = isTest ? '#3b82f6' : '#a3e635';

                pointsContainer.innerHTML += `
                    <div class="data-point ${colorClass}" style="left: ${leftPercent}%; bottom: ${bottomPercent}%;">
                        <div class="chart-tooltip">${item.title} <br><span style="color:${colorHex}">Score: ${realScore}</span></div>
                    </div>
                `;

                let labelMaxWidth = stepX > 0 ? stepX - 10 : 150;
                // Pastikan batas maksimal tidak terlalu kecil
                if (data.length <= 2) {
                    labelMaxWidth = 250; 
                } else if (labelMaxWidth < 60) {
                    labelMaxWidth = 60; 
                }

                xAxisContainer.innerHTML += `
                    <div class="x-label" style="left: ${leftPercent}%; max-width: ${labelMaxWidth}px;" title="${item.title}">${item.title}</div>
                `;
            });

            dArea += `L ${lastX} ${svgHeight} Z`;

            if(areaPath) areaPath.setAttribute('d', dArea);
            if(linePath) linePath.setAttribute('d', dLine);
        }

        function drawAllGraphs() {
            setTimeout(() => {
                drawLineChart('line-chart-practice', practiceData, false);
                drawLineChart('line-chart-test', testData, true);
            }, 50);
        }

        window.addEventListener('resize', drawAllGraphs);

        function togglePath(pathType) {
            // Update History Details link
            const historyLink = document.getElementById('history-details-link');
            if (historyLink) {
                historyLink.href = 'score_history.php?tab=' + pathType;
            }

            // Hapus class active dari tab
            document.getElementById('tab-practice').classList.remove('active');
            document.getElementById('tab-test').classList.remove('active');
            
            // Sembunyikan konten tengah
            document.getElementById('content-practice').style.display = 'none';
            document.getElementById('content-test').style.display = 'none';
            
            // Sembunyikan sidebar progres
            document.getElementById('sidebar-practice-progress').style.display = 'none';
            document.getElementById('sidebar-test-progress').style.display = 'none';
            
            // Sembunyikan sidebar statistik
            document.getElementById('sidebar-practice-stats').style.display = 'none';
            document.getElementById('sidebar-test-stats').style.display = 'none';

            // Sembunyikan sidebar feed
            document.getElementById('sidebar-practice-feed').style.display = 'none';
            document.getElementById('sidebar-test-feed').style.display = 'none';
            
            // Sembunyikan/Tampilkan keseluruhan panel grafik performa
            if(document.getElementById('performance-graph-panel')) {
                if (pathType === 'test') {
                    document.getElementById('performance-graph-panel').style.display = 'none';
                } else {
                    document.getElementById('performance-graph-panel').style.display = 'block';
                }
            }

            // Sembunyikan graph
            if(document.getElementById('graph-container-practice')) {
                document.getElementById('graph-container-practice').style.display = 'none';
                document.getElementById('graph-container-test').style.display = 'none';
            }

            // Tampilkan yang dipilih
            document.getElementById('tab-' + pathType).classList.add('active');
            document.getElementById('content-' + pathType).style.display = 'flex';
            document.getElementById('sidebar-' + pathType + '-progress').style.display = 'block';
            document.getElementById('sidebar-' + pathType + '-stats').style.display = 'block';
            document.getElementById('sidebar-' + pathType + '-feed').style.display = 'block';

            if(document.getElementById('graph-container-' + pathType) && pathType !== 'test') {
                document.getElementById('graph-container-' + pathType).style.display = 'block';
                drawAllGraphs();
            }
        }

        function switchPage(pageName) {
            const homePage = document.getElementById('page-home');
            const leaderboardPage = document.getElementById('page-leaderboard');
            
            if (pageName === 'dashboard' || pageName === 'home') {
                homePage.style.display = 'block';
                leaderboardPage.style.display = 'none';
                pageName = 'dashboard';
            } else if (pageName === 'leaderboard') {
                homePage.style.display = 'none';
                leaderboardPage.style.display = 'block';
            }

            localStorage.setItem('lastPage', pageName);
            
            if (pageName === 'dashboard') {
                history.pushState(null, null, window.location.pathname + window.location.search);
            } else if (window.location.hash !== '#' + pageName) {
                history.pushState(null, null, '#' + pageName);
            }
            
            // Tutup menu burger HP jika terbuka
            var mobileMenu = document.querySelector(".navbar-links");
            if (mobileMenu && mobileMenu.classList.contains('show')) {
                mobileMenu.classList.remove('show');
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            const hash = window.location.hash;
            const savedPage = localStorage.getItem('lastPage');

            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('search') || urlParams.has('level_filter')) {
                switchPage('leaderboard');
                return;
            }

            if (hash === '#leaderboard') {
                switchPage('leaderboard');
            } else if (hash === '#home') {
                switchPage('home');
            } else if (savedPage) {
                switchPage(savedPage);
            } else {
                switchPage('home');
            }
            drawAllGraphs();
            // Event listener untuk navigasi level tanpa refresh
            const prevBtn = document.getElementById('btn-prev-level');
            const nextBtn = document.getElementById('btn-next-level');
            
            if (prevBtn) {
                prevBtn.addEventListener('click', function() {
                    const currentIdx = allLevels.indexOf(activeLevel);
                    if (currentIdx > 0) {
                        switchToLevel(allLevels[currentIdx - 1]);
                    }
                });
            }
            
            if (nextBtn) {
                nextBtn.addEventListener('click', function() {
                    const currentIdx = allLevels.indexOf(activeLevel);
                    const recommendedIdx = allLevels.indexOf(recommendedLevel);
                    if (currentIdx < allLevels.length - 1 && currentIdx < recommendedIdx) {
                        switchToLevel(allLevels[currentIdx + 1]);
                    }
                });
            }
        });

        function toggleProfileMenu() {
            var menu = document.getElementById("profileMenu");
            menu.classList.toggle("show");
        }

        function toggleMobileMenu() {
            var links = document.querySelector(".navbar-links");
            links.classList.toggle("show");
        }

        window.onclick = function(event) {
            if (!event.target.matches('.avatar-circle')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
            if (!event.target.matches('.mobile-menu-btn') && !event.target.closest('.navbar-links')) {
                var mobileMenu = document.querySelector(".navbar-links");
                if (mobileMenu && mobileMenu.classList.contains('show')) {
                    mobileMenu.classList.remove('show');
                }
            }
        }
    </script>
</body>
</html>