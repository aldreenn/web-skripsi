<?php
session_start();
include '../config/koneksi.php';

// Proteksi Keamanan
if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/loginpage.html');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// 1. Ambil riwayat skor user untuk mengatur gembok
$query_scores = mysqli_query($conn, "SELECT test_packet, MAX(toefl_score) as best_score FROM test_scores WHERE user_id = '$user_id' GROUP BY test_packet");

$user_scores = [];
if ($query_scores) {
    while ($row = mysqli_fetch_assoc($query_scores)) {
        $pkt = strtoupper($row['test_packet']);
        $user_scores[$pkt] = (int)$row['best_score'];
    }
}

// 2. Ambil Definisi Paket dari Database (DINAMIS)
$test_packets = [];
$query_master = mysqli_query($conn, "SELECT * FROM test_packets ORDER BY id ASC");

if ($query_master) {
    while ($row = mysqli_fetch_assoc($query_master)) {
        $pkt_code = $row['packet_code'];
        $req = $row['requirement'];
        
        // Logika Gembok
        $is_locked = false;
        if (!empty($req) && !isset($user_scores[$req])) {
            $is_locked = true;
        }
        
        // Deskripsi default
        $desc = "Exam simulation to measure your reading proficiency.";
        if ($pkt_code === 'A') {
             $desc = 'First stage exam simulation to comprehensively measure your basic reading skills. The timer will run continuously.';
        } elseif ($pkt_code === 'B') {
             $desc = 'Intermediate difficulty level. Unlocks after you complete Test Package A.';
        } elseif ($pkt_code === 'C') {
             $desc = 'Final stage exam simulation (Final Boss). Unlocks after you complete Test Package B.';
        }

        $test_packets[$pkt_code] = [
            'title' => str_replace(['Packet', 'Packets'], ['Package', 'Packages'], $row['title']),
            'desc' => $desc,
            'locked' => $is_locked,
            'req_msg' => 'Please complete Package ' . $req . ' first to unlock this test'
        ];
    }
}

// 3. Menangkap Parameter Paket dari URL (misal: test.php?paket=B)
// Jika tidak ada di URL, arahkan ke paket pertama (biasanya A) yang ditemukan di database
$active_packet = '';
if (isset($_GET['paket']) && array_key_exists(strtoupper($_GET['paket']), $test_packets)) {
    $active_packet = strtoupper($_GET['paket']);
} else {
    // Ambil kunci pertama dari array test_packets jika tidak ada parameter
    if (!empty($test_packets)) {
        reset($test_packets);
        $active_packet = key($test_packets);
    }
}

// Kirim data ke JS
$json_packets = json_encode($test_packets);
$json_scores = json_encode($user_scores);
$json_active_packet = json_encode($active_packet);
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Test | ReadQuest</title>
    <link rel="icon" type="image/png" href="/assets/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="../desain/test.css?v=<?= time(); ?>" />
    <style>
        /* Visual Level Terkunci sama persis dengan Practice */
        .topic-btn.locked {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: #1e293b;
            border: 1px dashed #475569;
        }
        .topic-btn.locked:hover {
            background-color: #1e293b;
            transform: none;
        }
        .topic-btn.locked .icon {
            color: #64748b;
        }
    </style>
  </head>
  <body>
    <div class="app-container">
      
      <aside class="sidebar" id="test-sidebar">
        <div class="sidebar-section" style="display: flex; justify-content: space-between; align-items: center;">
          <p class="section-title" style="margin: 0;">Test Packages</p>
          <span class="material-symbols-outlined mobile-sidebar-toggle" style="display: none; cursor: pointer; color: #94a3b8;" onclick="toggleTestSidebar()">close</span>
        </div>

        <div class="sidebar-menu" id="sidebar-menu"></div>

        <div class="back-button-container">
            <button onclick="goBack()" class="back-btn">
                <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span> Back
            </button>
        </div>
      </aside>

      <main class="main-content">
        <div id="dynamic-content" class="reading-content active">
          
          <div class="content-header">
            <div class="mobile-top-actions" style="display: none; flex-direction: row; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 20px; width: 100%; min-height: 46px;">
                <div id="mobile-back-btn-container" style="display: flex;">
                    <button id="mobile-back-home-btn" onclick="goBack()" class="back-btn" style="padding: 10px 15px; border-radius: 8px; width: max-content; background-color: #3b82f6; border: 1px solid #3b82f6; color: white;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span> Back
                    </button>
                </div>
                <div id="mobile-sidebar-toggle-btn" class="mobile-sidebar-toggle" style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #a3e635; font-weight: bold; border: 1px solid #334155; padding: 10px 12px; border-radius: 8px; background: #1e293b; white-space: nowrap;" onclick="toggleTestSidebar()">
                    <span class="material-symbols-outlined">menu</span> Select Packages
                </div>
            </div>
            <div id="header-text-area"></div>
          </div>

          <div id="packet-view">
             </div>

        </div>
      </main>
    </div>

    <script>
      const testPackets = <?php echo $json_packets; ?>;
      const userScores = <?php echo $json_scores; ?>;

      const activePacketCode = <?php echo $json_active_packet; ?>; // Tangkap dari PHP

      window.onload = () => {
          renderSidebar();
          // Buka paket sesuai dengan yang diklik user dari dashboard
          if (activePacketCode) {
              changePacket(activePacketCode);
          }
      };

      function renderSidebar() {
          const sidebar = document.getElementById("sidebar-menu");
          sidebar.innerHTML = "";

          Object.keys(testPackets).forEach((pktId) => {
              const data = testPackets[pktId];
              let buttonClass = "topic-btn";
              let clickAction = `onclick="changePacket('${pktId}')"`;
              let lockIcon = `<span class="material-symbols-outlined icon" style="font-size: 18px; vertical-align: middle; margin-right: 6px;">assignment</span>`;
              
              let barColor = data.locked ? '#475569' : '#f59e0b';
              let isDone = userScores[pktId] !== undefined;
              let percentage = isDone ? 100 : 0;
              
              if (data.locked) {
                  buttonClass += " locked";
                  clickAction = `onclick="alert('${data.req_msg}')"`;
                  lockIcon = `<span class="material-symbols-outlined icon" style="font-size: 18px; vertical-align: middle; margin-right: 6px;">lock</span>`;
              }

              sidebar.innerHTML += `
                  <button ${clickAction} class="${buttonClass}" id="btn-${pktId}" style="display:flex; flex-direction:column; align-items:stretch; width:100%;">
                      <div class="btn-top" style="display:flex; align-items:center;">
                          ${lockIcon} <span style="margin-left: 6px;">${data.title}</span>
                      </div>
                      
                      <div style="display: flex; align-items: center; gap: 10px; margin-top: 12px; padding-left: 28px;">
                          <div style="flex-grow: 1; height: 6px; background-color: #0f172a; border-radius: 4px; overflow: hidden;">
                              <div style="height: 100%; background-color: ${barColor}; width: ${percentage}%; border-radius: 4px; transition: width 0.3s ease;"></div>
                          </div>
                          <span style="font-size: 11px; color: #94a3b8; font-weight: bold; width: 30px; text-align: right;">${percentage}%</span>
                      </div>
                  </button>
              `;
          });
      }

      function changePacket(pktId) {
          const data = testPackets[pktId];
          
          // Tutup sidebar jika diakses dari mobile
          const sidebar = document.getElementById("test-sidebar");
          if (sidebar && sidebar.classList.contains("show")) {
              sidebar.classList.remove("show");
          }
          
          document.querySelectorAll(".topic-btn:not(.locked)").forEach((btn) => btn.classList.remove("active"));
          const activeBtn = document.getElementById("btn-" + pktId);
          if (activeBtn) activeBtn.classList.add("active");

          const headerArea = document.getElementById("header-text-area");
          
          let isDone = userScores[pktId] !== undefined;
          
          let infoSyarat = isDone 
              ? `<span style="font-size:14px; background:rgba(163, 230, 53, 0.2); padding:4px 10px; border-radius:12px; color:#a3e635; margin-left:10px;">Your Score: ${userScores[pktId]}</span>` 
              : '';
          
          headerArea.innerHTML = `<h2 style="display:flex; align-items:center;">${data.title} ${infoSyarat}</h2><p class="header-desc"> Reading Exam Simulation Mode</p>`;

          const packetView = document.getElementById("packet-view");
          
          // ==========================================
          // LOGIKA PENGUNCIAN TOMBOL (LAPIS 1)
          // ==========================================
          let btnText = isDone ? "Completed" : "Start Exam";
          let btnStyle = isDone 
              ? "background-color: #334155; color: #94a3b8; cursor: not-allowed; border: 1px solid #475569;" 
              : "background-color: #f59e0b; cursor: pointer; color: white;";
          let btnAction = isDone ? "disabled" : `onclick="startTest('${pktId}')"`;
          let infoText = isDone 
              ? "<strong> Done:</strong> <span>You have already taken this exam. Exams can only be taken once.</span>"
              : "<strong> Attention:</strong> <span>Ensure your internet connection is stable. The timer will run continuously for 40 minutes.</span>";
          let infoBorder = isDone ? "#a3e635" : "#f59e0b";
          
          packetView.innerHTML = `
            <div class="quick-info" style="border-left: 4px solid ${infoBorder};">
              <div class="vocab-focus">
                ${infoText}
              </div>
            </div>

            <div class="reading-card" style="max-width: 600px;">
                <div class="card-badges">
                    <span class="card-badge" style="background-color: #f59e0b;">Full Simulation</span>
                </div>
                <h3 style="font-size: 22px; margin-bottom: 15px;">${data.title}</h3>
                <div class="card-meta" style="display: flex; gap: 20px; font-size: 14px; margin-bottom: 20px;">
                    <span style="display: flex; align-items: center; gap: 5px;"><span class="material-symbols-outlined" style="font-size: 18px;">schedule</span> 40 Minutes</span>
                    <span style="display: flex; align-items: center; gap: 5px;"><span class="material-symbols-outlined" style="font-size: 18px;">quiz</span> 32 Questions</span>
                </div>
                <p class="card-desc" style="font-size: 15px;">${data.desc}</p>
                
                <button class="read-btn" style="font-size: 16px; padding: 15px; border-radius: 8px; width: 100%; transition: 0.3s; font-weight: bold; ${btnStyle}" ${btnAction}>${btnText}</button>
            </div>
          `;
      }

      function startTest(pktId) {
          window.location.href = `test-exam.php?paket=${pktId}`;
      }

      function goBack() {
          window.location.href = "dashboard.php";
      }

      function toggleTestSidebar() {
          const sidebar = document.getElementById("test-sidebar");
          if(sidebar) {
              sidebar.classList.toggle("show");
          }
      }
    </script>
  </body>
</html>