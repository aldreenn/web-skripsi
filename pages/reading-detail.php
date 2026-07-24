<?php
// Wajib diletakkan paling atas agar PHP bisa membaca KTP/Session user
session_start();

// ========================================================
// PROTEKSI KEAMANAN: Cek apakah user sudah login
// ========================================================
if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/loginpage.html');
    exit;
}

include '../config/koneksi.php';

// Mengambil ID materi dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Cari data MATERI berdasarkan ID tersebut
$query = "SELECT * FROM materials WHERE id = '$id'";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

// Jika ID materi tidak ditemukan, kembalikan ke halaman daftar
if (!$data) {
    header("Location: practice.php");
    exit;
}

// 2. Cari data SOAL KUIS berdasarkan material_id
$q_query = "SELECT * FROM questions WHERE material_id = '$id'";
$q_result = mysqli_query($conn, $q_query);
$questions = array();

if (mysqli_num_rows($q_result) > 0) {
    while ($row = mysqli_fetch_assoc($q_result)) {
        $questions[] = array(
            'id' => $row['id'],
            'q' => $row['question_text'],
            'options' => array($row['option_a'], $row['option_b'], $row['option_c'], $row['option_d']),
            'answer' => (int)$row['correct_answer'], 
            'explanation' => $row['explanation']
        );
    }
}

// Konversi data PHP ke JSON agar bisa digunakan oleh JavaScript (Timer & Kuis)
$json_data = json_encode($data);
$json_questions = json_encode($questions);
?>

<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($data['title']); ?> - ReadQuest</title>
    <link rel="stylesheet" href="../desain/readingdetail.css" />
  </head>
  <body>
    <header class="detail-header">
      <div class="back-button-container">
        <button onclick="window.location.href='practice.php'" class="back-btn">
          <span class="back-icon">←</span>
        </button>
      </div>
      <div class="header-info">
        <span id="level-badge" class="badge cefr-<?php echo strtolower($data['level']); ?>">
            CEFR <?php echo $data['level']; ?>
        </span>
        <span id="timer" class="timer">⏱ <?php echo $data['reading_time']; ?></span>
      </div>
    </header>

    <main class="split-layout">
      <section class="reading-pane">
        <h1 id="article-title"><?php echo htmlspecialchars($data['title']); ?></h1>
        <p class="article-meta" id="article-meta">
            Topic: <?php echo htmlspecialchars($data['topic']); ?> | Estimated Time: <?php echo htmlspecialchars($data['reading_time']); ?>
        </p>

        <?php if (!empty($data['cover_image'])): ?>
            <div class="cover-image-container">
                <img src="/uploads/materials/<?= $data['cover_image']; ?>" alt="Material Visual">
            </div>
        <?php endif; ?>
        
        <div class="article-content" id="article-content">
          <?php echo nl2br(htmlspecialchars($data['full_content'])); ?>
        </div>
      </section>

      <section class="quiz-pane">
        <div class="quiz-header">
          <h2>Questions</h2>
          <p>Choose the most appropriate answer based on the material provided.</p>
        </div>

        <div class="quiz-content" id="quiz-content"></div>

        <div class="quiz-footer">
          <button class="submit-btn" id="submit-btn-kuis" onclick="submitQuiz()">
            Submit Answer
          </button>
        </div>
      </section>
    </main>

    <script>
      const articleData = <?php echo $json_data; ?>;
      const quizQuestions = <?php echo $json_questions; ?>;
      
      let timerInterval; 

      document.addEventListener("DOMContentLoaded", () => {
        renderQuestions();
        startTimer();
      });

      function renderQuestions() {
        const quizContainer = document.getElementById("quiz-content");
        const submitBtn = document.getElementById("submit-btn-kuis");
        quizContainer.innerHTML = "";

        if (!quizQuestions || quizQuestions.length === 0) {
          quizContainer.innerHTML = "<p style='color: #94a3b8; padding: 20px;'>No quiz questions available for this article yet.</p>";
          submitBtn.style.display = "none";
          return;
        }

        quizQuestions.forEach((q, index) => {
          let optionsHTML = "";
          q.options.forEach((opt, optIndex) => {
            optionsHTML += `
                <label class="option-label">
                    <input type="radio" name="question_${index}" value="${optIndex}">
                    <span>${opt}</span>
                </label>
            `;
          });

          const questionBlock = `
            <div class="question-block" id="block_${index}">
                <p class="question-text"><strong>${index + 1}.</strong> ${q.q}</p>
                <div class="options-group">
                    ${optionsHTML}
                </div>
            </div>
          `;
          quizContainer.innerHTML += questionBlock;
        });
      }

      function startTimer() {
        let secondsElapsed = 0; // KEMBALI KE KODE ASLI ANDA
        const timerDisplay = document.getElementById("timer");

        function updateDisplay() {
          const m = Math.floor(secondsElapsed / 60);
          const s = secondsElapsed % 60;
          timerDisplay.innerText = `⏱ Learning Time: ${m.toString().padStart(2, "0")}:${s.toString().padStart(2, "0")}`;
        }

        updateDisplay(); 

        timerInterval = setInterval(() => {
          secondsElapsed++;
          updateDisplay();
        }, 1000);
      }

      // ========================================================
      // SCORING ENGINE: Konversi Skor Practice per Level CEFR
      // Menggunakan sistem Capping (batas maksimal ITP per level)
      // ========================================================
      function calculatePracticeScore(level, correctAnswers, totalQuestions) {
        const masteryRate = totalQuestions > 0 ? Math.round((correctAnswers / totalQuestions) * 100) : 0;
        const ratio = totalQuestions > 0 ? (correctAnswers / totalQuestions) : 0;

        // Definisi rentang ITP per level CEFR
        const itpRanges = {
          'A2': { min: 31, max: 47 },
          'B1': { min: 48, max: 55 },
          'B2': { min: 56, max: 62 },
          'C1': { min: 63, max: 67 }
        };

        const levelUpper = level.toUpperCase();

        // Level A1 & C2: Tidak menggunakan konversi ITP
        // Cukup return persentase jawaban benar (0-100%)
        if (levelUpper === 'A1' || levelUpper === 'C2') {
          return {
            itpScore: 0,       // Tidak ada skor ITP
            masteryRate: masteryRate,
            hasItp: false       // Flag: tidak pakai ITP
          };
        }

        // Level A2 - C1: Gunakan linear interpolation dalam rentang ITP yang di-cap
        const range = itpRanges[levelUpper];
        if (!range) {
          // Fallback jika level tidak dikenal
          return { itpScore: 0, masteryRate: masteryRate, hasItp: false };
        }

        // Linear interpolation: itpScore = min + ratio * (max - min)
        const itpScore = Math.round(range.min + ratio * (range.max - range.min));

        return {
          itpScore: itpScore,
          masteryRate: masteryRate,
          hasItp: true
        };
      }

      function submitQuiz() {
        clearInterval(timerInterval);

        // ========================================================
        // CARA PALING AMAN: Membaca teks waktu di layar tanpa merusak variabel
        // ========================================================
        const timerText = document.getElementById("timer").innerText;
        const timeMatch = timerText.match(/(\d{2}):(\d{2})/);
        let finalSeconds = 0;
        if (timeMatch) {
            const menit = parseInt(timeMatch[1], 10);
            const detik = parseInt(timeMatch[2], 10);
            finalSeconds = (menit * 60) + detik;
        }
        // ========================================================

        let correctAnswers = 0;
        const totalQuestions = quizQuestions.length;

        quizQuestions.forEach((q, index) => {
          const optionsElements = document.querySelectorAll(`input[name="question_${index}"]`);
          const selectedOption = document.querySelector(`input[name="question_${index}"]:checked`);
          let selectedValue = selectedOption ? parseInt(selectedOption.value) : -1;

          if (selectedValue === q.answer) {
            correctAnswers++;
          }

          optionsElements.forEach((radio, optIndex) => {
            radio.disabled = true; 
            let label = radio.parentElement;

            if (optIndex === q.answer) {
              label.classList.add("correct-answer"); 
            } else if (optIndex === selectedValue && selectedValue !== q.answer) {
              label.classList.add("wrong-answer"); 
            }
          });

          const block = document.getElementById(`block_${index}`);
          const expDiv = document.createElement("div");
          expDiv.className = "explanation-box";
          expDiv.innerHTML = `<strong>💡 Explanation:</strong> ${q.explanation || "No explanation provided."}`;
          block.appendChild(expDiv);
        });

        // ========================================================
        // SCORING ENGINE: Hitung skor menggunakan sistem capping CEFR
        // ========================================================
        const articleLevel = articleData.level.toUpperCase();
        const result = calculatePracticeScore(articleLevel, correctAnswers, totalQuestions);
        const masteryRate = result.masteryRate;
        const itpScore = result.itpScore;

        fetch('save_score.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                material_id: articleData.id,
                score: masteryRate,
                itp_score: itpScore,
                duration_seconds: finalSeconds
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log("Status Simpan Database:", data.message);
        })
        .catch(error => console.error('Error saat menyimpan skor:', error));

        const timerDisplay = document.getElementById("timer");
        timerDisplay.classList.remove("timer-warning");

        // ========================================================
        // TAMPILAN HASIL: Menampilkan ITP Score + Mastery Rate
        // ========================================================
        if (masteryRate >= 70) {
          if (result.hasItp) {
            timerDisplay.innerText = `PASSED — ITP: ${itpScore} | Mastery: ${masteryRate}%`;
          } else {
            timerDisplay.innerText = `PASSED — Score: ${masteryRate}%`;
          }
          timerDisplay.classList.add("timer-success");
        } else {
          if (result.hasItp) {
            timerDisplay.innerText = `FAILED — ITP: ${itpScore} | Mastery: ${masteryRate}%`;
          } else {
            timerDisplay.innerText = `FAILED — Score: ${masteryRate}%`;
          }
          timerDisplay.classList.add("timer-fail");
        }

        const submitBtn = document.getElementById("submit-btn-kuis");
        submitBtn.innerText = "Finish & Return to Menu";
        submitBtn.classList.add("btn-success");
        submitBtn.onclick = function () {
          window.location.href = "practice.php";
        };

        document.querySelector(".quiz-pane").scrollTo({ top: 0, behavior: "smooth" });
      }
    </script>
  </body>
</html>