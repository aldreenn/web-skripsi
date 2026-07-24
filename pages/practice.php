<?php
session_start();
include '../config/koneksi.php';

// ========================================================
// PROTEKSI KEAMANAN: Cek apakah user sudah login
// ========================================================
if (!isset($_SESSION['user_id'])) {
    header('Location: /aplikasi_skripsi/pages/loginpage.html');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// 1. Ambil SEMUA LEVEL DAN TOPIK
$topics_query = mysqli_query($conn, "SELECT * FROM topics ORDER BY level ASC, topic_name ASC");
$all_topics = [];
while ($row = mysqli_fetch_assoc($topics_query)) {
    $all_topics[$row['level']][] = $row['topic_name'];
}

// 2. Menarik semua data artikel/materi
$query = "SELECT * FROM materials ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

$readingData = array();
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $cefr_class = "badge-" . strtolower($row['level']); 

        $readingData[] = array(
            'id' => (int)$row['id'],
            'level' => strtoupper($row['level']),
            'cefr_class' => $cefr_class,
            'topic' => $row['topic'],
            'readingTime' => $row['reading_time'],
            'title' => $row['title'],
            'desc' => $row['description']
        );
    }
}

// 3. Ambil jumlah soal per materi untuk perhitungan X/Y
$q_count_query = mysqli_query($conn, "SELECT material_id, COUNT(*) as total FROM questions GROUP BY material_id");
$question_counts = [];
while ($row = mysqli_fetch_assoc($q_count_query)) {
    $question_counts[$row['material_id']] = (int)$row['total'];
}

// 4. Ambil skor TERtinggi per materi (Termasuk yang gagal agar muncul warna merah)
$user_scores = [];
$completed_per_level = array("A1" => 0, "A2" => 0, "B1" => 0, "B2" => 0, "C1" => 0, "C2" => 0);
$completed_ids = [];

$q_done = mysqli_query($conn, "SELECT s.material_id, m.level, MAX(s.score) as best_score 
                               FROM practice_scores s 
                               JOIN materials m ON s.material_id = m.id 
                               WHERE s.user_id = '$user_id' 
                               GROUP BY s.material_id, m.level");

if (mysqli_num_rows($q_done) > 0) {
    while ($r = mysqli_fetch_assoc($q_done)) {
        $mat_id = (int)$r['material_id'];
        $b_score = (int)$r['best_score'];
        $lvl = strtoupper($r['level']);
        
        $user_scores[$mat_id] = $b_score;
        
        // Hanya yang skornya >= 70 yang dihitung lulus untuk gembok level
        if ($b_score >= 70) {
            $completed_ids[] = $mat_id;
            if (isset($completed_per_level[$lvl])) {
                $completed_per_level[$lvl]++;
            }
        }
    }
}

// JSON Encode untuk diproses di Frontend
$json_readingData = json_encode($readingData);
$json_completed = json_encode($completed_ids); 
$json_topics = json_encode($all_topics);
$json_completed_levels = json_encode($completed_per_level);
$json_q_counts = json_encode($question_counts);
$json_user_scores = json_encode($user_scores);
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Practice | ReadQuest</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="../desain/practice.css?v=<?= time(); ?>" />
    <style>
        /* Tambahan CSS untuk visual Level Terkunci */
        .topic-btn.locked {
            opacity: 0.5;
            cursor: not-allowed;
            background-color: #1e293b; /* Warna gelap */
            border: 1px dashed #475569;
        }
        .topic-btn.locked:hover {
            background-color: #1e293b; /* Hilangkan efek hover */
            transform: none;
        }
        .topic-btn.locked .icon {
            color: #64748b; /* Warna ikon abu-abu */
        }
    </style>
  </head>
  <body>
    <div class="app-container">
      <aside class="sidebar">
        <div class="sidebar-section">
          <p class="section-title">Level</p>
        </div>

        <div class="sidebar-menu" id="sidebar-menu"></div>

        <div class="back-button-container">
            <button onclick="goBack()" class="back-btn">
                <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span> Back to Home
            </button>
        </div>
      </aside>

      <main class="main-content">
        <div id="dynamic-content" class="reading-content active">
          
          <div class="content-header">
            <div id="header-text-area"></div>
          </div>

          <div id="topic-view">
            <p class="section-subtitle">Choose a Learning Topic:</p>
            <div class="card-grid" id="topic-grid"></div>
          </div>

          <div id="article-view" style="display: none;">

            <div class="quick-info">
              <div class="vocab-focus" id="vocab-display">
                <strong>💡 Info:</strong> <span>Answer the questions based on the provided material.</span>
              </div>
              <div class="progress-info">
                <strong>🎯 Passing Target:</strong> Score 70+
              </div>
            </div>

            <div class="action-bar">
              <input type="text" id="searchInput" placeholder="Search article title..." class="search-input" onkeyup="filterArticles()" />
            </div>

            <div class="card-grid" id="card-grid"></div>

          </div>
        </div>
      </main>
    </div>

    <div id="customModal" class="modal-overlay">
        <div class="modal-box">
            <h3 id="modalTitle" class="modal-title">Level Locked</h3>
            <p id="modalMessage" class="modal-message"></p>
            <div class="modal-actions">
                <button class="btn-confirm-modal" onclick="closeLockedModal()">OK</button>
            </div>
        </div>
    </div>

    <script>
      const readingData = <?php echo $json_readingData; ?>;
      const completedArticles = <?php echo $json_completed; ?>; 
      const databaseTopics = <?php echo $json_topics; ?>; 
      const completedPerLevel = <?php echo $json_completed_levels; ?>; // Data kelulusan per level
      const questionCounts = <?php echo $json_q_counts; ?>;
      const userScores = <?php echo $json_user_scores; ?>;
    </script>

    <script>
      let currentLevel = "";
      let currentTopic = "";

      const levelDescriptions = {
          "A1": { title: "Beginner (A1)", desc: "Very basic text and sentence understanding." },
          "A2": { title: "Elementary (A2)", desc: "Understanding of daily life texts." },
          "B1": { title: "Intermediate (B1)", desc: "Topics related to work, school, and recreation." },
          "B2": { title: "Upper Intermediate (B2)", desc: "Understanding complex and abstract texts." },
          "C1": { title: "Advanced (C1)", desc: "Long scientific texts with implicit meaning." },
          "C2": { title: "Proficient (C2)", desc: "Expert-level reading proficiency and literature." }
      };

      // Urutan hierarki level
      const levelHierarchy = ["A1", "A2", "B1", "B2", "C1", "C2"];

      window.onload = () => {
          renderSidebar();
      };

      function renderSidebar() {
          const sidebar = document.getElementById("sidebar-menu");
          sidebar.innerHTML = "";
          let firstLevel = null;

          Object.keys(levelDescriptions).forEach((levelCode, index) => {
              // Jika tidak ada data topik di database, lewati rendering tombol ini
              if(!databaseTopics[levelCode]) return;

              if(!firstLevel) firstLevel = levelCode;
              
              const levelTitle = levelDescriptions[levelCode].title;
              
              // =====================================
              // LOGIKA GEMBOK LEVEL
              // =====================================
              let isLocked = false;
              let lockMessage = "";
              let lockIcon = `<span class="icon">${levelCode} -</span>`;

              // Level A1 selalu terbuka (index 0)
              if (index > 0) {
                  let prevLevel = levelHierarchy[index - 1];
                  
                  // 1. Hitung total artikel yang ada di level sebelumnya
                  let totalInPrevLevel = readingData.filter(i => i.level === prevLevel).length;
                  // 2. Hitung berapa yang sudah lulus di level sebelumnya
                  let completedInPrevLevel = completedPerLevel[prevLevel] || 0;

                  // SETTING MINIMAL ARTIKEL DI SINI (Ubah angka 1 sesuai kebutuhan Anda)
                  let minRequired = 5;

                  // Terkunci jika artikel level sebelumnya ada, TAPI yang lulus kurang dari minimal
                  if (totalInPrevLevel > 0 && completedInPrevLevel < minRequired) {
                      isLocked = true;
                      lockIcon = `<span class="material-symbols-outlined icon" style="font-size: 18px; vertical-align: middle; margin-right: 6px;">lock</span>`;
                      lockMessage = `Pass at least ${minRequired} article(s) in ${prevLevel} to unlock this level.`;
                  } else if (totalInPrevLevel === 0) {
                      // Terkunci otomatis jika admin belum memasukkan materi sama sekali di level sebelumnya
                      isLocked = true;
                      lockIcon = `<span class="material-symbols-outlined icon" style="font-size: 18px; vertical-align: middle; margin-right: 6px;">lock</span>`;
                      lockMessage = `Materials for ${prevLevel} are not yet available; you cannot skip levels.`;
                  }
              }

              // Styling tombol bergantung status terkunci atau tidak
              let buttonClass = "topic-btn";
              let clickAction = `onclick="changeTopic('${levelCode}')"`;
              
              if (isLocked) {
                  buttonClass += " locked";
                  clickAction = `onclick="showLockedModal('${lockMessage}')"`;
              }

              // Hitung total artikel dan persentase khusus untuk level ini
              const totalArticlesInLevel = readingData.filter(i => i.level === levelCode).length;
              const completedInLevel = completedPerLevel[levelCode] || 0;
              const percentage = totalArticlesInLevel === 0 ? 0 : Math.round((completedInLevel / totalArticlesInLevel) * 100);

              // Warna bar abu-abu jika level terkunci, hijau jika terbuka
              const barColor = isLocked ? '#475569' : '#22c55e';

              sidebar.innerHTML += `
                  <button ${clickAction} class="${buttonClass}" id="btn-${levelCode}" style="display:flex; flex-direction:column; align-items:stretch; width:100%;">
                      <div class="btn-top" style="display:flex; align-items:center;">
                          ${lockIcon} <span>${levelTitle.replace(`(${levelCode})`, '').trim()}</span>
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

          const urlParams = new URLSearchParams(window.location.search);
          const urlLevel = urlParams.get('lvl');

          if(urlLevel && databaseTopics[urlLevel]) {
              changeTopic(urlLevel);
          } else if(firstLevel) {
              changeTopic(firstLevel); 
          } else {
              document.getElementById("topic-grid").innerHTML = "<p style='color: #94a3b8;'>No topic folders have been created by the Admin yet.</p>";
          }
      }

      function changeTopic(levelId) {
        currentLevel = levelId;
        
        // Update active class
        document.querySelectorAll(".topic-btn:not(.locked)").forEach((btn) => btn.classList.remove("active"));
        const activeBtn = document.getElementById("btn-" + levelId);
        if (activeBtn) activeBtn.classList.add("active");

        const headerArea = document.getElementById("header-text-area");
        const titleData = levelDescriptions[levelId] || { title: `Level ${levelId}`, desc: "Materi pembelajaran" };
        
        // Tampilkan info progres kelulusan level ini di Header
        let totalArticlesInCurrent = readingData.filter(i => i.level === levelId).length;
        let progressLulus = completedPerLevel[levelId] || 0;
        let infoSyarat = `<span style="font-size:14px; background:rgba(163, 230, 53, 0.2); padding:4px 10px; border-radius:12px; color:#a3e635; margin-left:10px;">Passed: ${progressLulus}/${totalArticlesInCurrent} Articles</span>`;
        
        headerArea.innerHTML = `<h2 style="display:flex; align-items:center;">${titleData.title} ${infoSyarat}</h2><p class="header-desc">${titleData.desc}</p>`;

        const activeTopics = databaseTopics[levelId] || [];
        const topicGrid = document.getElementById("topic-grid");
        topicGrid.innerHTML = "";

        if (activeTopics.length > 0) {
            activeTopics.forEach(topic => {
                const articlesInTopic = readingData.filter(i => i.level === levelId && i.topic === topic).length;
                
                topicGrid.innerHTML += `
                    <div class="topic-folder-card" onclick="openTopicArticles('${topic}')">
                        <h3 class="folder-title">${topic}</h3>
                        <p class="folder-meta">${articlesInTopic} Material Available</p>
                    </div>
                `;
            });
        } else {
            topicGrid.innerHTML = "<p style='color: #94a3b8; grid-column: 1 / -1;'>No materials available for this level yet.</p>";
        }

        document.getElementById("topic-view").style.display = "block";
        document.getElementById("article-view").style.display = "none";
      }

      function openTopicArticles(topicName) {
        currentTopic = topicName;

        document.getElementById("topic-view").style.display = "none";
        document.getElementById("article-view").style.display = "block";

        const headerArea = document.getElementById("header-text-area");
        headerArea.innerHTML = `
            <button onclick="backToTopicView()" style="background-color: #3b82f6; color: #ffffff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 20px; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#2563eb'" onmouseout="this.style.backgroundColor='#3b82f6'">
                <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span> Back to Topics List
            </button>
            <h2 style="display:flex; align-items:center;">Topic: ${topicName} <span class="header-meta" style="margin-left:10px;">(Level ${currentLevel})</span></h2>
        `;

        document.getElementById("searchInput").value = "";
        displayArticles(currentLevel, currentTopic, "");
      }

      function displayArticles(levelId, topicName, filterText = "") {
        const grid = document.getElementById("card-grid");
        grid.innerHTML = "";

        const baseData = readingData.filter(item => 
            item.level.toUpperCase() === levelId.toUpperCase() && 
            item.topic === topicName
        );
        
        const filteredData = baseData.filter(item => 
            item.title.toLowerCase().includes(filterText.toLowerCase())
        );

        let completedInThisTopic = 0;
        baseData.forEach((item) => {
          if (completedArticles.includes(item.id)) {
            completedInThisTopic++;
          }
        });

        const totalInThisTopic = baseData.length;
        const progressPercent = totalInThisTopic === 0 ? 0 : Math.round((completedInThisTopic / totalInThisTopic) * 100);

        if (filteredData.length > 0) {
          filteredData.forEach((item) => {
            const isDone = completedArticles.includes(item.id); // True jika lulus (>= 70)
            const hasAttempted = userScores[item.id] !== undefined; // True jika pernah mencoba (meski gagal)
            
            let statusBadge = "";
            let cardStatusClass = "";
            let buttonText = "Start Practice";

            if (hasAttempted) {
                const score = userScores[item.id];
                const totalQ = questionCounts[item.id] || 0;
                
                // Trik Matematika Terbalik: (Skor / 100) * Total Soal
                const correctQ = Math.round((score / 100) * totalQ);
                
                buttonText = "Try Again";

                if (score >= 70) {
                    // LULUS (Hijau)
                    statusBadge = `
                        <span class="badge-done" style="background:#22c55e; color:white; padding:2px 8px; border-radius:4px; font-size:12px; display:inline-flex; align-items:center; gap:4px; font-weight: bold;">
                            <span class="material-symbols-outlined" style="font-size: 14px;">check_circle</span> Passed
                        </span>
                        <span style="background:rgba(34, 197, 94, 0.2); color:#4ade80; padding:2px 8px; border-radius:4px; font-size:12px; font-weight: bold; margin-left: auto;">
                            Score: ${score}
                        </span>
                    `;
                    cardStatusClass = "card-done";
                } else {
                    // GAGAL (Merah)
                    statusBadge = `
                        <span class="badge-fail" style="background:#ef4444; color:white; padding:2px 8px; border-radius:4px; font-size:12px; display:inline-flex; align-items:center; gap:4px; font-weight: bold;">
                            <span class="material-symbols-outlined" style="font-size: 14px;">cancel</span> Failed
                        </span>
                        <span style="background:rgba(239, 68, 68, 0.2); color:#f87171; padding:2px 8px; border-radius:4px; font-size:12px; font-weight: bold; margin-left: auto;">
                            Score: ${score}
                        </span>
                    `;
                    cardStatusClass = "card-fail"; 
                }
            }

            grid.innerHTML += `
                <div class="reading-card ${cardStatusClass}">
                    <div class="card-badges">
                        <span class="card-badge cefr-${item.level.toLowerCase()}">CEFR ${item.level}</span>
                        ${statusBadge}
                    </div>
                    <h3>${item.title}</h3>
                    <p class="card-meta">⏱ ${item.readingTime}</p>
                    <p class="card-desc">${item.desc}</p>
                    <button class="read-btn" onclick="startReading(${item.id})">${buttonText}</button>
                </div>
            `;
          });
        } else {
          grid.innerHTML = "<p style='color:#94a3b8;'>Material not found or no articles have been added to this folder yet.</p>";
        }
      }

      function filterArticles() {
        const searchText = document.getElementById("searchInput").value;
        displayArticles(currentLevel, currentTopic, searchText);
      }

      function backToTopicView() {
        changeTopic(currentLevel);
      }

      function startReading(id) {
        window.location.href = `reading-detail.php?id=${id}`;
      }

      function goBack() {
        window.location.href = "dashboard.php";
      }

      function showLockedModal(message) {
          document.getElementById('modalMessage').innerText = message;
          const modal = document.getElementById('customModal');
          modal.style.display = 'flex';
          setTimeout(() => { modal.classList.add('show'); }, 50);
      }

      function closeLockedModal() {
          const modal = document.getElementById('customModal');
          modal.classList.remove('show');
          setTimeout(() => { modal.style.display = 'none'; }, 300);
      }

    </script>
  </body>
</html>