<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /aplikasi_skripsi/pages/loginpage.html');
    exit;
}

// Panggil koneksi database
include '../config/koneksi.php';

$user_id = (int)$_SESSION['user_id'];
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') : 'Pengguna';
$first_name = isset($_SESSION['first_name']) ? htmlspecialchars($_SESSION['first_name'], ENT_QUOTES, 'UTF-8') : '';
$last_name = isset($_SESSION['last_name']) ? htmlspecialchars($_SESSION['last_name'], ENT_QUOTES, 'UTF-8') : '';

$full_name = (!empty($first_name) && !empty($last_name)) ? ucfirst($first_name) . ' ' . ucfirst($last_name) : ucfirst($username);

// Inisial untuk Avatar
if (!empty($first_name) && !empty($last_name)) {
    $initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
} elseif (!empty($first_name)) {
    $initials = strtoupper(substr($first_name, 0, 1));
} else {
    $initials = strtoupper(substr($username, 0, 1));
}

// ==========================================
// QUERY 1: RIWAYAT PRACTICE (Ditambahkan duration_seconds)
// ==========================================
$query_practice = "SELECT s.score, s.itp_score, s.duration_seconds, s.created_at, m.title, m.topic, m.level 
                   FROM practice_scores s 
                   JOIN materials m ON s.material_id = m.id 
                   WHERE s.user_id = ? 
                   ORDER BY s.created_at DESC";
$stmt_prac = $conn->prepare($query_practice);
$stmt_prac->bind_param("i", $user_id);
$stmt_prac->execute();
$result_practice = $stmt_prac->get_result();

// ==========================================
// QUERY 2: RIWAYAT TEST TOEFL
// ==========================================
$query_test = "SELECT test_packet, toefl_score, created_at 
               FROM test_scores 
               WHERE user_id = ? 
               ORDER BY created_at DESC";
$stmt_test = $conn->prepare($query_test);
$stmt_test->bind_param("i", $user_id);
$stmt_test->execute();
$result_test = $stmt_test->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> Score History | ReadQuest</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link rel="stylesheet" href="/aplikasi_skripsi/desain/dashboard.css?v=<?= time(); ?>">
  <link rel="stylesheet" href="/aplikasi_skripsi/desain/score_history.css?v=<?= time(); ?>">
</head>
<body>
  <nav class="navbar" aria-label="Primary">
    <div class="navbar-left">
      <a href="/aplikasi_skripsi/pages/dashboard.php#home" class="navbar-logo">ReadQuest</a>
    </div>
    <ul class="navbar-center navbar-links">
      <li><a href="/aplikasi_skripsi/pages/dashboard.php#home">Home</a></li>
      <li><a href="/aplikasi_skripsi/pages/practice.php">Practice</a></li>
      <li><a href="/aplikasi_skripsi/pages/test.php">Test</a></li>
      <li><a href="/aplikasi_skripsi/pages/dashboard.php#leaderboard">Leaderboard</a></li>
    </ul>
    <div class="navbar-right">
      <div class="profile-dropdown">
        <div class="avatar-circle" onclick="toggleProfileMenu()" id="avatarBtn">
          <?php echo $initials; ?>
        </div>
        
        <div class="dropdown-content" id="profileMenu">
          <div class="dropdown-header">
            <span class="user-name-drop"><?php echo $full_name; ?></span>
          </div>
          <a href="/aplikasi_skripsi/pages/manage_account.php">
              <span class="material-symbols-outlined">settings</span> Manage Account
          </a>
          <div class="dropdown-divider"></div>
          <a href="/aplikasi_skripsi/auth/logout.php" class="logout-text">
              <span class="material-symbols-outlined">logout</span> Log Out
          </a>
        </div>
        
      </div>
    </div>
  </nav>

  <main>
    <div class="dashboard-container">
        
        <div style="margin-bottom: 20px;">
            <a href="/aplikasi_skripsi/pages/dashboard.php#home" class="btn-primary" style="display: inline-flex; align-items: center; gap: 8px; width: fit-content;">
                <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span> Back to Home
            </a>
        </div>

        <div class="dash-header" style="background: linear-gradient(135deg, #0f172a, #1e293b);">
            <div class="welcome-text">
                <h2>Score History & Progress Report</h2>
                <p>Monitor your learning progress and evaluation history here, <?php echo $full_name; ?>.</p>
            </div>
        </div>

        <div class="card-panel">
            <div class="panel-header">
                <div class="panel-tabs" style="margin-left: 0;">
                    <span class="active" id="tab-hist-practice" onclick="toggleHistory('practice')" style="margin-left: 0; margin-right: 20px;">Practice History</span>
                    <span id="tab-hist-test" onclick="toggleHistory('test')">Test History</span>
                </div>
            </div>

            <div id="content-hist-practice" style="display: block; overflow-x: auto;">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th style="width: 180px;">Completion Date</th>
                            <th style="width: 80px;">Level</th>
                            <th style="width: 200px;">Topic</th>
                            <th>Practice Article Title</th>
                            <th>Time Taken</th>
                            <th style="text-align: center; width: 100px;">ITP Score</th>
                            <th style="text-align: right; width: 120px;">Score (XP)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_practice->num_rows > 0): ?>
                            <?php while($row = $result_practice->fetch_assoc()): ?>
                                <tr>
                                    <td class="date-col"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></td>
                                    
                                    <td style="color: #cbd5e1; font-weight: 600;">
                                        <span class="level-badge"><?= htmlspecialchars($row['level'] ?? 'A1') ?></span>
                                    </td>
                                    
                                    <td style="color: #94a3b8; font-weight: 500;">
                                        <?= htmlspecialchars($row['topic']) ?> 
                                    </td>
                                    
                                    <td style="color: #e2e8f0; font-weight: 500;">
                                        <?= htmlspecialchars($row['title']) ?>
                                    </td>

                                    <td style="color: #94a3b8;">
                                        <?php 
                                            // Memastikan nilai default adalah 0 jika kosong
                                            $waktu_detik = isset($row['duration_seconds']) ? (int)$row['duration_seconds'] : 0;
                                            $menit = floor($waktu_detik / 60);
                                            $detik = $waktu_detik % 60;
                                            echo sprintf("%02d:%02d Mins", $menit, $detik);
                                        ?>
                                    </td>
                                    
                                    <td style="text-align: center; font-weight: 600; color: #e2e8f0;">
                                        <?php if(in_array($row['level'], ['A2', 'B1', 'B2', 'C1'])): ?>
                                            <?= htmlspecialchars($row['itp_score'] ?? '-') ?>
                                        <?php else: ?>
                                            <span style="color: #64748b;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td style="text-align: right;">
                                        <?php if($row['score'] >= 70): ?>
                                            <span class="score-badge badge-green"><?= $row['score'] ?></span>
                                        <?php else: ?>
                                            <span class="score-badge badge-yellow"><?= $row['score'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="empty-state">No practice history yet. Let's start learning!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div id="content-hist-test" style="display: none; overflow-x: auto;">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th style="width: 200px;">Test Date</th>
                            <th>Test Packet</th>
                            <th style="text-align: right; width: 150px;">TEST Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_test->num_rows > 0): ?>
                            <?php while($row = $result_test->fetch_assoc()): ?>
                                <tr>
                                    <td class="date-col"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></td>
                                    <td style="color: #e2e8f0; font-weight: 500;">Simulation Packet <?= htmlspecialchars($row['test_packet']) ?></td>
                                    <td style="text-align: right;">
                                        <span class="score-badge badge-blue"><?= $row['toefl_score'] ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="empty-state">No simulation test history yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
  </main>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('tab')) {
            const tab = urlParams.get('tab');
            if (tab === 'test' || tab === 'practice') {
                toggleHistory(tab);
            }
        }
    });

    function toggleHistory(type) {
        document.getElementById('tab-hist-practice').classList.remove('active');
        document.getElementById('tab-hist-test').classList.remove('active');
        
        document.getElementById('content-hist-practice').style.display = 'none';
        document.getElementById('content-hist-test').style.display = 'none';
        
        document.getElementById('tab-hist-' + type).classList.add('active');
        document.getElementById('content-hist-' + type).style.display = 'block';
    }

    function toggleProfileMenu() {
        document.getElementById("profileMenu").classList.toggle("show");
    }

    // Menutup dropdown jika user mengklik sembarang tempat di luar avatar
    window.onclick = function(event) {
        if (!event.target.matches('.avatar-circle')) {
            var dropdowns = document.getElementsByClassName("dropdown-content");
            for (var i = 0; i < dropdowns.length; i++) {
                if (dropdowns[i].classList.contains('show')) {
                    dropdowns[i].classList.remove('show');
                }
            }
        }
    }
  </script>
</body>
</html>