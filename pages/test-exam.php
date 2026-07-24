<?php
session_start();
include '../config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/loginpage.html');
    exit;
}

$paket = isset($_GET['paket']) ? strtoupper(htmlspecialchars($_GET['paket'])) : 'A';
$user_id = (int)$_SESSION['user_id'];

// ==========================================
// PENJAGA LAPIS 2 (ANTI-BOCOR URL)
// ==========================================
$cek_skor = mysqli_query($conn, "SELECT id FROM test_scores WHERE user_id = '$user_id' AND test_packet = '$paket'");
if (mysqli_num_rows($cek_skor) > 0) {
    // Jika di database sudah ada nilai untuk user ini dan paket ini, tendang keluar!
    echo "<script>
            alert('Access Denied! You have already completed Exam Packet {$paket} and are not allowed to retake it.');
            window.location.href = 'test.php';
          </script>";
    exit;
}

// === LOGIKA TIMER SERVER ===
$durasi_ujian = 40 * 60; 
$session_timer_name = 'exam_end_time_' . $paket; 

if (!isset($_SESSION[$session_timer_name])) {
    $_SESSION[$session_timer_name] = time() + $durasi_ujian;
}
$sisa_waktu = $_SESSION[$session_timer_name] - time();
if ($sisa_waktu <= 0) { $sisa_waktu = 0; }

// 1. Ambil SEMUA teks bacaan
$query_passages = mysqli_query($conn, "SELECT * FROM test_passages WHERE packet_id = '$paket' ORDER BY passage_number ASC");
$passages = [];
while ($row = mysqli_fetch_assoc($query_passages)) {
    $passages[] = $row;
}

if (count($passages) == 0) {
    die("<div style='padding: 50px; color: white; background: #0f172a; height: 100vh; text-align: center; font-family: sans-serif;'><h2>Questions are not available in the database.</h2></div>");
}

// 2. Ambil soal dan kelompokkan
$query_questions = mysqli_query($conn, "
    SELECT q.* FROM test_questions q
    JOIN test_passages p ON q.passage_id = p.id
    WHERE p.packet_id = '$paket'
    ORDER BY q.id ASC
");

$urutan_fase = ['predicting', 'clarifying', 'questioning', 'summarizing'];
$soal_reciprocal = [];
while ($q = mysqli_fetch_assoc($query_questions)) {
    $soal_reciprocal[$q['passage_id']][$q['reciprocal_phase']][] = $q;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TOEFL Simulation - Packet <?php echo $paket; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../desain/test-exam.css?v=<?= time(); ?>">
    <style>
        .tab-btn.tab-completed {
            background-color: #22c55e !important;
            color: white !important;
        }
        .tab-btn.tab-incomplete {
            background-color: #ef4444 !important;
            color: white !important;
        }
        .tab-btn.tab-completed.active {
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.4) !important;
        }
        .tab-btn.tab-incomplete.active {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.4) !important;
        }
    </style>
</head>
<body>

    <header class="exam-header">
        <h2>Exam Packet <?php echo $paket; ?></h2>
        
        <div class="header-stepper-container">
            <?php foreach ($passages as $index => $p): $pid = $p['id']; ?>
                <div class="stepper-header stepper-passage-<?php echo $pid; ?>" style="display: <?php echo $index == 0 ? 'flex' : 'none'; ?>;">
                    <div class="step-indicator active" id="ind_<?php echo $pid; ?>_0">Predicting</div>
                    <div class="step-indicator" id="ind_<?php echo $pid; ?>_1">Clarifying</div>
                    <div class="step-indicator" id="ind_<?php echo $pid; ?>_2">Questioning</div>
                    <div class="step-indicator" id="ind_<?php echo $pid; ?>_3">Summarizing</div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="timer-wrapper">
            <div class="timer-box" id="timerDisplay">40:00</div>
        </div>
    </header>

    <div class="exam-container">
        
        <div class="passage-panel">
            <div class="tabs-container">
                <?php foreach ($passages as $index => $p): ?>
                    <button type="button" class="tab-btn <?php echo $index == 0 ? 'active' : ''; ?>" onclick="switchPassage('passage_<?php echo $p['id']; ?>', this)">
                        Passage <?php echo $p['passage_number']; ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <?php foreach ($passages as $index => $p): ?>
                <div id="content_passage_<?php echo $p['id']; ?>" class="passage-content <?php echo $index == 0 ? 'active' : ''; ?>">
                    <h3 style="color: #3b82f6; margin-top: 0;"><?php echo htmlspecialchars($p['title']); ?></h3>
                    
                    <?php if (!empty($p['cover_image'])): ?>
                        <div style="margin-top: 15px; text-align: center;">
                            <img src="../uploads/materials/<?php echo $p['cover_image']; ?>" alt="Exam Illustration" style="max-width: 100%; border-radius: 8px; border: 1px solid #334155; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                        </div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 20px;" class="passage-text-container" id="text_passage_<?php echo $p['id']; ?>" data-original="<?php echo htmlspecialchars($p['content']); ?>">
                        <?php echo nl2br(htmlspecialchars($p['content'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="question-panel">
            <form id="examForm" action="submit_test.php" method="POST">
                <input type="hidden" name="paket" value="<?php echo $paket; ?>">
                
                <?php foreach ($passages as $index => $p): 
                    $pid = $p['id'];
                    $fase = isset($soal_reciprocal[$pid]) ? $soal_reciprocal[$pid] : [];
                ?>
                    <div id="questions_passage_<?php echo $pid; ?>" class="questions-content <?php echo $index == 0 ? 'active' : ''; ?>">
                        
                        <?php 
                        $nomor_global = 1; 
                        foreach ($urutan_fase as $step_idx => $nama_fase): 
                        ?>
                            <div class="step-content <?php echo $step_idx == 0 ? 'active' : ''; ?>" id="step_<?php echo $pid; ?>_<?php echo $step_idx; ?>">
                                <?php 
                                if (isset($fase[$nama_fase]) && count($fase[$nama_fase]) > 0): 
                                    foreach ($fase[$nama_fase] as $q): 
                                ?>
                                        <div class="question-card" style="margin-bottom: 25px;">
                                            <div class="question-text"><?php echo $nomor_global; ?>. <?php echo htmlspecialchars($q['question_text']); ?></div>
                                            
                                            <label class="option-label">
                                                <input type="radio" name="q_<?php echo $q['id']; ?>" value="A"> 
                                                <span><?php echo htmlspecialchars($q['option_a']); ?></span>
                                            </label>
                                            <label class="option-label">
                                                <input type="radio" name="q_<?php echo $q['id']; ?>" value="B"> 
                                                <span><?php echo htmlspecialchars($q['option_b']); ?></span>
                                            </label>
                                            <label class="option-label">
                                                <input type="radio" name="q_<?php echo $q['id']; ?>" value="C"> 
                                                <span><?php echo htmlspecialchars($q['option_c']); ?></span>
                                            </label>
                                            <label class="option-label">
                                                <input type="radio" name="q_<?php echo $q['id']; ?>" value="D"> 
                                                <span><?php echo htmlspecialchars($q['option_d']); ?></span>
                                            </label>
                                        </div>
                                <?php 
                                        $nomor_global++;
                                    endforeach; 
                                else: 
                                ?>
                                    <p style='color: #94a3b8;'>Questions for this phase are not available.</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <div class="step-navigation">
                            <button type="button" class="btn-nav btn-prev" id="btn_prev_<?php echo $pid; ?>" onclick="changeStep(<?php echo $pid; ?>, -1)" style="display:none;">Previous Step</button>
                            <button type="button" class="btn-nav btn-next" id="btn_next_<?php echo $pid; ?>" onclick="changeStep(<?php echo $pid; ?>, 1)">Next Step</button>
                            
                            <?php if ($index < count($passages) - 1): ?>
                                <?php $next_pid = $passages[$index + 1]['id']; ?>
                                <button type="button" class="btn-nav btn-next" id="btn_next_passage_<?php echo $pid; ?>" onclick="pindahKePassage(<?php echo $next_pid; ?>)" style="display:none; background-color: #8b5cf6;">Next Passage </button>
                            <?php else: ?>
                                <button type="button" class="btn-nav btn-next" id="btn_submit_<?php echo $pid; ?>" onclick="konfirmasiSubmit()" style="display:none; background-color: #22c55e;">Submit Exam</button>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            </form>
        </div>
    </div>

    <div id="customModal" class="modal-overlay">
        <div class="modal-box">
            <h3 id="modalTitle" class="modal-title">Confirmation</h3>
            <p id="modalMessage" class="modal-message">Message here...</p>
            <div class="modal-actions">
                <button id="btnModalCancel" class="btn-cancel">Cancel</button>
                <button id="btnModalConfirm" class="btn-confirm-modal">Yes, Submit</button>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('customModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const btnCancel = document.getElementById('btnModalCancel');
        const btnConfirm = document.getElementById('btnModalConfirm');

        btnCancel.addEventListener('click', () => {
            modal.classList.remove('show');
            setTimeout(() => { modal.style.display = 'none'; }, 300);
        });

        // === 1. TIMER ===
        let totalWaktu = <?php echo $sisa_waktu; ?>; 
        const timerDisplay = document.getElementById('timerDisplay');
        const examForm = document.getElementById('examForm');
        let hitungMundur; 

        function perbaruiTimer() {
            let menit = Math.floor(totalWaktu / 60);
            let detik = totalWaktu % 60;
            menit = menit < 10 ? '0' + menit : menit;
            detik = detik < 10 ? '0' + detik : detik;
            timerDisplay.textContent = menit + ':' + detik;

            if (totalWaktu <= 0) {
                clearInterval(hitungMundur);
                modalTitle.innerText = "⏰ Time's Up!";
                modalTitle.style.color = "#ef4444"; 
                modalMessage.innerHTML = "Your exam time has expired!<br>The system is saving your answers to the database...";
                btnCancel.style.display = 'none'; 
                btnConfirm.style.display = 'none'; 
                modal.style.display = 'flex';
                setTimeout(() => { modal.classList.add('show'); }, 50);
                setTimeout(() => { bersihkanJawabanLokal(); examForm.submit(); }, 2500);
            }
            totalWaktu--;
        }
        perbaruiTimer();
        hitungMundur = setInterval(perbaruiTimer, 1000);

        // === 2. AUTO-SAVE ===
        const paketUjian = "<?php echo $paket; ?>";
        const semuaRadio = document.querySelectorAll('input[type="radio"]');

        semuaRadio.forEach(radio => {
            const namaSoal = radio.name; 
            const jawabanTersimpan = localStorage.getItem('jawaban_' + paketUjian + '_' + namaSoal);
            if (jawabanTersimpan && radio.value === jawabanTersimpan) { radio.checked = true; }
            radio.addEventListener('change', function() {
                localStorage.setItem('jawaban_' + paketUjian + '_' + this.name, this.value);
                
                const passageContent = this.closest('.questions-content');
                if (passageContent) {
                    const pid = passageContent.id.replace('questions_passage_', '');
                    checkPassageCompletion(pid);
                    if (typeof updateStepIndicators === 'function') {
                        updateStepIndicators(pid);
                    }
                }
            });
        });

        function bersihkanJawabanLokal() {
            semuaRadio.forEach(radio => { localStorage.removeItem('jawaban_' + paketUjian + '_' + radio.name); });
            <?php foreach ($passages as $p): ?>
            localStorage.removeItem('visited_' + paketUjian + '_<?php echo $p['id']; ?>');
            localStorage.removeItem('step_' + paketUjian + '_<?php echo $p['id']; ?>');
            <?php endforeach; ?>
        }

        // === CEK KELENGKAPAN PASSAGE ===
        function checkPassageCompletion(passageId) {
            const questionsContent = document.getElementById('questions_passage_' + passageId);
            if (!questionsContent) return;
            
            const radiosInPassage = questionsContent.querySelectorAll('input[type="radio"]');
            const totalQuestions = radiosInPassage.length / 4;
            const answered = questionsContent.querySelectorAll('input[type="radio"]:checked').length;
            
            const tabBtn = document.querySelector(`button[onclick*="passage_${passageId}"]`);
            if (!tabBtn) return;
            
            if (totalQuestions > 0 && answered === totalQuestions) {
                tabBtn.classList.remove('tab-incomplete');
                tabBtn.classList.add('tab-completed');
            } else {
                if (tabBtn.getAttribute('data-visited') === 'true') {
                    tabBtn.classList.remove('tab-completed');
                    tabBtn.classList.add('tab-incomplete');
                }
            }
        }

        function updateAllTabs() {
            <?php foreach ($passages as $p): ?>
                checkPassageCompletion(<?php echo $p['id']; ?>);
            <?php endforeach; ?>
        }

        // Restore status visited dari localStorage
        document.querySelectorAll('.tab-btn').forEach(btn => {
            const match = btn.getAttribute('onclick').match(/passage_(\d+)/);
            if (match) {
                const passageId = match[1];
                if (localStorage.getItem('visited_' + paketUjian + '_' + passageId) === 'true') {
                    btn.setAttribute('data-visited', 'true');
                }
            }
        });

        // Inisialisasi tab aktif pertama kali
        const activeTab = document.querySelector('.tab-btn.active');
        if (activeTab) {
            activeTab.setAttribute('data-visited', 'true');
            const match = activeTab.getAttribute('onclick').match(/passage_(\d+)/);
            if (match) {
                localStorage.setItem('visited_' + paketUjian + '_' + match[1], 'true');
            }
        }
        
        updateAllTabs();

        // === 3. SUBMIT ===
        function konfirmasiSubmit() {
            const totalSoal = semuaRadio.length / 4;
            const soalTerisi = document.querySelectorAll('input[type="radio"]:checked').length;
            const soalKosong = totalSoal - soalTerisi;

            if (soalKosong > 0) {
                modalTitle.innerText = "⚠️ Warning!";
                modalTitle.style.color = "#f59e0b"; 
                modalMessage.innerHTML = `You still have <strong>${soalKosong} unanswered questions</strong>.<br><br>It is highly recommended to answer all questions (Reciprocal Reading).`;
                btnConfirm.innerText = "Submit Anyway";
                btnConfirm.className = "btn-confirm-modal warning";
            } else {
                modalTitle.innerText = "✅ Confirmation";
                modalTitle.style.color = "#3b82f6"; 
                modalMessage.innerHTML = "Excellent! You have completed all 4 Phases of Reciprocal Reading.<br><br>Are you sure you want to submit?";
                btnConfirm.innerText = "Yes, Submit";
                btnConfirm.className = "btn-confirm-modal";
            }

            btnConfirm.onclick = function() {
                btnConfirm.innerText = "Processing...";
                btnConfirm.disabled = true;
                btnCancel.style.display = 'none';
                clearInterval(hitungMundur); 
                bersihkanJawabanLokal();
                examForm.submit();
            };

            btnCancel.style.display = 'block';
            btnConfirm.style.display = 'block';
            modal.style.display = 'flex';
            setTimeout(() => { modal.classList.add('show'); }, 50);
        }

        // === 4. STEPPER LOGIC ===
        let currentStep = {}; 
        <?php foreach ($passages as $p): ?>
            currentStep[<?php echo $p['id']; ?>] = 0; 
        <?php endforeach; ?>

        function updateStepIndicators(passageId) {
            const current = currentStep[passageId];
            
            for (let i = 0; i <= 3; i++) {
                const ind = document.getElementById('ind_' + passageId + '_' + i);
                const stepDiv = document.getElementById('step_' + passageId + '_' + i);
                if (!ind || !stepDiv) continue;
                
                if (i === current) {
                    ind.classList.add('active');
                    ind.classList.remove('completed', 'incomplete');
                } else {
                    ind.classList.remove('active');
                    
                    const radios = stepDiv.querySelectorAll('input[type="radio"]');
                    const total = radios.length / 4;
                    const answered = stepDiv.querySelectorAll('input[type="radio"]:checked').length;
                    
                    if (total > 0 && answered === total) {
                        ind.classList.add('completed');
                        ind.classList.remove('incomplete');
                    } else if (i < current || answered > 0) {
                        ind.classList.add('incomplete');
                        ind.classList.remove('completed');
                    } else {
                        ind.classList.remove('completed', 'incomplete');
                    }
                }
            }
        }

        function changeStep(passageId, direction) {
            let step = currentStep[passageId];
            
            const currentStepDiv = document.getElementById('step_' + passageId + '_' + step);
            currentStepDiv.classList.remove('active');
            
            step += direction;
            currentStep[passageId] = step;
            localStorage.setItem('step_' + paketUjian + '_' + passageId, step);

            const nextStepDiv = document.getElementById('step_' + passageId + '_' + step);
            nextStepDiv.classList.add('active');
            
            updateStepIndicators(passageId);

            const btnPrev = document.getElementById('btn_prev_' + passageId);
            const btnNext = document.getElementById('btn_next_' + passageId);
            const btnSubmit = document.getElementById('btn_submit_' + passageId);
            const btnNextPassage = document.getElementById('btn_next_passage_' + passageId); // Tangkap tombol baru

            btnPrev.style.display = (step === 0) ? 'none' : 'block';
            
            if (step === 3) {
                // Saat berada di tahap Summarizing (Tahap 4)
                btnNext.style.display = 'none';
                if(btnSubmit) btnSubmit.style.display = 'block';
                if(btnNextPassage) btnNextPassage.style.display = 'block';
            } else {
                // Saat berada di tahap Predicting, Clarifying, atau Questioning
                btnNext.style.display = 'block';
                if(btnSubmit) btnSubmit.style.display = 'none';
                if(btnNextPassage) btnNextPassage.style.display = 'none';
            }

            const textContainer = document.getElementById('text_passage_' + passageId);
            const originalText = textContainer.getAttribute('data-original');
            
            if (step === 1) {
                const highlightedText = originalText.replace(/\bubiquitous\b/gi, '<span class="highlight-vocab">ubiquitous</span>');
                textContainer.innerHTML = highlightedText.replace(/\n/g, "<br>");
            } else {
                textContainer.innerHTML = originalText.replace(/\n/g, "<br>");
            }

            // Kembalikan scroll bagian soal ke paling atas
            const questionPanel = document.querySelector('.question-panel');
            if (questionPanel) {
                questionPanel.scrollTop = 0;
            }
        }

        // === FUNGSI PINDAH PASSAGE OTOMATIS ===
        function pindahKePassage(nextPassageId) {
            // Mencari elemen tombol tab yang sesuai dan mengekliknya secara programatik
            const nextTab = document.querySelector(`button[onclick*="passage_${nextPassageId}"]`);
            if (nextTab) {
                nextTab.click();
            }
        }

        // === 5. TAB LOGIC (DIPERBARUI) ===
        function switchPassage(targetId, btnElement) {
            // Sembunyikan konten & soal
            document.querySelectorAll('.passage-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.questions-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            
            // Sembunyikan semua stepper di header
            document.querySelectorAll('.stepper-header').forEach(el => el.style.display = 'none');

            // Aktifkan yang baru
            document.getElementById('content_' + targetId).classList.add('active');
            document.getElementById('questions_' + targetId).classList.add('active');
            
            btnElement.classList.add('active');
            btnElement.setAttribute('data-visited', 'true');
            
            // Tampilkan stepper yang sesuai di header
            const pid = targetId.split('_')[1];
            localStorage.setItem('visited_' + paketUjian + '_' + pid, 'true');
            document.querySelector('.stepper-passage-' + pid).style.display = 'flex';
            
            document.querySelector('.question-panel').scrollTop = 0;
            
            updateAllTabs();
        }

        // Restore fase/step reciprocal agar kebal refresh
        window.addEventListener('DOMContentLoaded', () => {
            <?php foreach ($passages as $p): ?>
                let targetStep_<?php echo $p['id']; ?> = parseInt(localStorage.getItem('step_' + paketUjian + '_<?php echo $p['id']; ?>')) || 0;
                for (let i = 0; i < targetStep_<?php echo $p['id']; ?>; i++) {
                    changeStep(<?php echo $p['id']; ?>, 1);
                }
                updateStepIndicators(<?php echo $p['id']; ?>);
            <?php endforeach; ?>
        });
    </script>
</body>
</html>