<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/loginpage.html');
    exit;
}

// Panggil koneksi database
include '../config/koneksi.php';

$user_id = (int)$_SESSION['user_id'];

// ========================================================
// LOGIKA RESET PROGRESS (Dipindahkan dari practice.php)
// ========================================================
if (isset($_GET['action']) && $_GET['action'] == 'reset') {
    $del_query = "DELETE FROM practice_scores WHERE user_id = '$user_id'";
    mysqli_query($conn, $del_query);
    header("Location: manage_account.php?tab=details&reset_success=1");
    exit;
}

$success_msg = '';
$error_msg = '';
if (isset($_SESSION['success_msg'])) {
    $success_msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}
if (isset($_SESSION['error_msg'])) {
    $error_msg = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}

// Menentukan tab mana yang sedang aktif (default: details)
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'details';

if (isset($_GET['reset_success'])) {
    $success_msg = "Your practice progress has been successfully reset!";
}

// ========================================================
// 1. PROSES UPDATE DATA JIKA TOMBOL SAVE DIKLIK
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_fn = trim($_POST['first_name']);
    $new_ln = trim($_POST['last_name']);
    $new_bio = trim($_POST['bio']);

    if (empty($new_fn) || empty($new_ln)) {
        $_SESSION['error_msg'] = "First Name and Last Name cannot be empty.";
    } else {
        // Rapikan format nama (Huruf Kapital di awal)
        $new_fn = ucwords(strtolower($new_fn));
        $new_ln = ucwords(strtolower($new_ln));

        // Update ke database
        $update_query = "UPDATE users SET first_name = ?, last_name = ?, bio = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "sssi", $new_fn, $new_ln, $new_bio, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_msg'] = "Your profile has been successfully updated!";
            // Update session agar nama di navbar langsung berubah
            $_SESSION['first_name'] = $new_fn;
            $_SESSION['last_name'] = $new_ln;
        } else {
            $_SESSION['error_msg'] = "An error occurred while saving your data.";
        }
        mysqli_stmt_close($stmt);
    }
    header("Location: manage_account.php?tab=details");
    exit;
}

// ========================================================
// 2. PROSES UPDATE PASSWORD (TAB PASSWORD MANAGER)
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error_msg'] = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error_msg'] = "New password confirmation does not match.";
    } elseif (strlen($new_password) < 6) {
        $_SESSION['error_msg'] = "New password must be at least 6 characters.";
    } elseif ($new_password === $old_password) {
        $_SESSION['error_msg'] = "New password cannot be the same as the old password.";
    } else {
        // Ambil password lama dari database untuk dicocokkan
        $pass_query = "SELECT password FROM users WHERE id = '$user_id'";
        $pass_result = mysqli_query($conn, $pass_query);
        $user_db = mysqli_fetch_assoc($pass_result);

        if (password_verify($old_password, $user_db['password']) || $old_password === $user_db['password']) {
            // Jika cocok, hash password baru dan simpan
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update_pass = "UPDATE users SET password = ? WHERE id = ?";
            $stmt_pass = mysqli_prepare($conn, $update_pass);
            mysqli_stmt_bind_param($stmt_pass, "si", $new_hashed, $user_id);
            
            if (mysqli_stmt_execute($stmt_pass)) {
                $_SESSION['success_msg'] = "Your password has been successfully changed!";
            } else {
                $_SESSION['error_msg'] = "A system error occurred while changing your password.";
            }
            mysqli_stmt_close($stmt_pass);
        } else {
            $_SESSION['error_msg'] = "The old password you entered is incorrect.";
        }
    }
    header("Location: manage_account.php?tab=password");
    exit;
}

// ========================================================
// AMBIL DATA TERBARU DARI DATABASE UNTUK DITAMPILKAN DI FORM
// ========================================================
$query = "SELECT username, first_name, last_name, bio FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user_data = mysqli_fetch_assoc($result);

$username = htmlspecialchars($user_data['username'], ENT_QUOTES, 'UTF-8');
$first_name = htmlspecialchars($user_data['first_name'] ?? '', ENT_QUOTES, 'UTF-8');
$last_name = htmlspecialchars($user_data['last_name'] ?? '', ENT_QUOTES, 'UTF-8');
$bio = htmlspecialchars($user_data['bio'] ?? '', ENT_QUOTES, 'UTF-8');

// Logika pembuatan Inisial Avatar
if (!empty($first_name) && !empty($last_name)) {
    $initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
    $full_name = ucfirst($first_name) . ' ' . ucfirst($last_name);
} elseif (!empty($first_name)) {
    $initials = strtoupper(substr($first_name, 0, 1));
    $full_name = ucfirst($first_name);
} else {
    $initials = strtoupper(substr($username, 0, 1));
    $full_name = ucfirst($username);
}

// ==========================================
// LOGIKA BADGE CEFR DI DROPDOWN
// ==========================================
$query_cefr = mysqli_query($conn, "SELECT test_packet, MAX(toefl_score) as max_score FROM test_scores WHERE user_id = '$user_id' GROUP BY test_packet");
$test_scores = [];
if ($query_cefr) {
    while ($row = mysqli_fetch_assoc($query_cefr)) {
        $test_scores[strtoupper($row['test_packet'])] = $row['max_score'];
    }
}

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Account | ReadQuest</title>
  <link rel="icon" type="image/png" href="/assets/favicon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link rel="stylesheet" href="../desain/dashboard.css?v=<?= time(); ?>">
  <link rel="stylesheet" href="../desain/manage_account.css?v=<?= time(); ?>">
</head>
<body>
  <nav class="navbar" aria-label="Primary">
    <div class="navbar-left">
      <span class="material-symbols-outlined mobile-menu-btn" onclick="toggleMobileMenu()" style="display: none; cursor: pointer; margin-right: 15px; font-size: 28px; user-select: none;">menu</span>
      <a href="../pages/dashboard.php" class="navbar-logo">ReadQuest</a>
    </div>
    <ul class="navbar-center navbar-links">
      <li><a href="dashboard.php">Dashboard</a></li>
      <li><a href="practice.php">Practice</a></li>
      <li><a href="test.php">Test</a></li>
      <li><a href="dashboard.php#leaderboard">Leaderboard</a></li>
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
            <?php else: ?>
            <small style="color: #64748b; font-size: 12px; display: block;">Student</small>
            <?php endif; ?>
          </div>
          <a href="manage_account.php">
            <span class="material-symbols-outlined">manage_accounts</span> Manage Account
          </a>
          <div class="dropdown-item">
            <a href="../auth/logout.php" class="logout-text">
                <span class="material-symbols-outlined">logout</span> Log Out
            </a>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <main class="settings-wrapper">
        <div class="left-column-wrapper">
          
          <aside class="settings-sidebar card-panel" style="margin-bottom: 25px;">
              <div class="sidebar-header">
                  <span class="material-symbols-outlined">settings</span> Manage account
              </div>
              <ul class="sidebar-menu">
                  <li class="<?= $active_tab == 'details' ? 'active' : '' ?>" onclick="window.location.href='?tab=details'">Account Details</li>
                  <li class="<?= $active_tab == 'password' ? 'active' : '' ?>" onclick="window.location.href='?tab=password'">Password Manager</li>
              </ul>
          </aside>

          <div class="danger-zone-sidebar card-panel">
              <h3 style="color: #ef4444; margin-top: 0; font-size: 16px; display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                  <span class="material-symbols-outlined" style="font-size: 20px;">warning</span>
                  Danger Zone
              </h3>
              <p style="color: #94a3b8; font-size: 12px; margin-bottom: 20px; line-height: 1.5;">
                  Delete your <strong>practice progress</strong> permanently. This will remove all accumulated XP and scores. This action cannot be undone.
              </p>
              
              <button onclick="confirmReset()" class="btn-danger-sidebar">
                  Reset Progress
              </button>
          </div>

      </div>

      <section class="settings-content card-panel">
          
          <?php if(!empty($success_msg)): ?>
              <div style="background-color: rgba(163, 230, 53, 0.1); border: 1px solid #a3e635; color: #a3e635; padding: 12px 15px; border-radius: 6px; margin-bottom: 25px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                  <span class="material-symbols-outlined" style="font-size: 20px; margin-top: 1px;">check_circle</span> 
                  <span><?php echo $success_msg; ?></span>
              </div>
          <?php endif; ?>

          <?php if(!empty($error_msg)): ?>
              <div style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; padding: 12px 15px; border-radius: 6px; margin-bottom: 25px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
                  <span class="material-symbols-outlined" style="font-size: 20px; margin-top: 1px;">error</span> 
                  <span><?php echo $error_msg; ?></span>
              </div>
          <?php endif; ?>

          <?php if($active_tab == 'details'): ?>
              <div class="settings-header">
                  <span class="material-symbols-outlined icon-green">manage_accounts</span>
                  <h2>General Information</h2>
              </div>

              <div class="avatar-section">
                  <div class="avatar-circle-large">
                      <?php echo $initials; ?>
                  </div>
              </div>

              <form class="settings-form" action="?tab=details" method="POST">
                  <div class="form-group">
                      <label>Username<span class="text-red">*</span></label>
                      <input type="text" value="<?php echo $username; ?>" disabled class="input-disabled">
                  </div>

                  <div class="form-row">
                      <div class="form-group">
                          <label>First Name<span class="text-red">*</span></label>
                          <input type="text" name="first_name" value="<?php echo $first_name; ?>" required>
                      </div>
                      <div class="form-group">
                          <label>Last Name<span class="text-red">*</span></label>
                          <input type="text" name="last_name" value="<?php echo $last_name; ?>" required>
                      </div>
                  </div>

                  <div class="form-group">
                      <label>Biography</label>
                      <textarea name="bio" rows="4" placeholder="Write a short summary about yourself..."><?php echo $bio; ?></textarea>
                  </div>

                  <div class="form-actions">
                      <button type="submit" name="update_profile" class="btn-save">Save Changes</button>
                  </div>
              </form>

          <?php elseif($active_tab == 'password'): ?>
              <div class="settings-header">
                  <span class="material-symbols-outlined icon-green">lock</span>
                  <h2>Password Manager</h2>
              </div>

              <p style="color: #94a3b8; font-size: 14px; margin-bottom: 25px;">Change your password regularly to keep your account secure.</p>

              <form class="settings-form" action="?tab=password" method="POST">
                  <div class="form-group">
                      <label>Old Password<span class="text-red">*</span></label>
                      <div style="position: relative; display: flex; align-items: center;">
                          <input type="password" id="old_password" name="old_password" required placeholder="Enter current password">
                          <span class="material-symbols-outlined toggle-password" data-target="old_password" style="position: absolute; right: 15px; color: #94a3b8; cursor: pointer; user-select: none;">visibility_off</span>
                      </div>
                  </div>
                  
                  <div class="form-group" style="margin-top: 10px;">
                      <label>New Password<span class="text-red">*</span></label>
                      <div style="position: relative; display: flex; align-items: center;">
                          <input type="password" id="new_password" name="new_password" required placeholder="New password (min. 6 characters)">
                          <span class="material-symbols-outlined toggle-password" data-target="new_password" style="position: absolute; right: 15px; color: #94a3b8; cursor: pointer; user-select: none;">visibility_off</span>
                      </div>
                  </div>
                  
                  <div class="form-group">
                      <label>Confirm New Password<span class="text-red">*</span></label>
                      <div style="position: relative; display: flex; align-items: center;">
                          <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repeat new password">
                          <span class="material-symbols-outlined toggle-password" data-target="confirm_password" style="position: absolute; right: 15px; color: #94a3b8; cursor: pointer; user-select: none;">visibility_off</span>
                      </div>
                  </div>

                  <div class="form-actions" style="margin-top: 20px;">
                      <button type="submit" name="update_password" class="btn-save">Update Password</button>
                  </div>
              </form>
          <?php endif; ?>

      </section>
  </main>

  <div id="customModal" class="modal-overlay">
      <div class="modal-box">
          <h3 class="modal-title">
              <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 5px;">warning</span>
              Confirm Reset
          </h3>
          <p class="modal-message">⚠️ WARNING:<br><br>This action will permanently delete your <strong>practice progress history</strong> and completion status. Are you sure you want to start over?</p>
          <div class="modal-actions">
              <button class="btn-cancel" onclick="closeModal()">Cancel</button>
              <button class="btn-confirm-modal" onclick="proceedReset()">Yes, Reset</button>
          </div>
      </div>
  </div>

  <div id="successModal" class="modal-overlay">
      <div class="modal-box" style="border-color: #a3e635;">
          <h3 class="modal-title" style="color: #a3e635;">
              <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 5px;">check_circle</span>
              Reset Successful
          </h3>
          <p class="modal-message">Progress successfully cleared! All levels are locked again except A1.</p>
          <div class="modal-actions">
              <button class="btn-save" style="width: 100%;" onclick="closeSuccessModal()">OK</button>
          </div>
      </div>
  </div>

  <script>
    function toggleProfileMenu() {
        document.getElementById("profileMenu").classList.toggle("show");
    }
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

    function toggleMobileMenu() {
        const navLinks = document.querySelector('.navbar-links');
        navLinks.classList.toggle('show');
    }

    function confirmReset() {
        const modal = document.getElementById('customModal');
        modal.style.display = 'flex';
        setTimeout(() => { modal.classList.add('show'); }, 50);
    }

    function closeModal() {
        const modal = document.getElementById('customModal');
        modal.classList.remove('show');
        setTimeout(() => { modal.style.display = 'none'; }, 300);
    }

    function proceedReset() {
        window.location.href = "manage_account.php?action=reset";
    }

    function closeSuccessModal() {
        const modal = document.getElementById('successModal');
        modal.classList.remove('show');
        setTimeout(() => { 
            modal.style.display = 'none'; 
            window.history.replaceState(null, null, 'manage_account.php?tab=details');
        }, 300);
    }

    <?php if(isset($_GET['reset_success']) && $_GET['reset_success'] == '1'): ?>
    window.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('successModal');
        modal.style.display = 'flex';
        setTimeout(() => { modal.classList.add('show'); }, 50);
    });
    <?php endif; ?>

    // Toggle Password Visibility Logic
    document.querySelectorAll('.toggle-password').forEach(icon => {
        // Cek isi awal saat pertama dimuat
        const targetId = icon.getAttribute('data-target');
        const input = document.getElementById(targetId);
        
        if(input) {
            // Tampilkan icon hanya jika ada isinya
            const toggleVisibility = () => {
                icon.style.display = input.value.length > 0 ? "block" : "none";
            };
            
            toggleVisibility();
            input.addEventListener("input", toggleVisibility);

            icon.addEventListener('click', function() {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.textContent = type === 'password' ? 'visibility_off' : 'visibility';
            });
            
            // Hover effect on icon
            icon.addEventListener('mouseover', function() {
                this.style.color = '#cbd5e1';
            });
            icon.addEventListener('mouseout', function() {
                this.style.color = '#94a3b8';
            });
        }
    });
  </script>
</body>
</html>