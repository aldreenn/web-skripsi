<?php
session_start();

// Security Protection: If user tries to access without new test history, kick to dashboard
if (!isset($_SESSION['last_test_result'])) {
    header('Location: dashboard.php');
    exit;
}

// Get test result data from session
$result = $_SESSION['last_test_result'];

$paket = htmlspecialchars($result['paket']);
$raw_score = (int)$result['raw_score'];
$total_questions = (int)$result['total_questions'];
$toefl_score = (int)$result['toefl_score'];
$cefr_level = htmlspecialchars($result['cefr_level']);
$wrong_score = $total_questions - $raw_score;
$diagnostic = isset($result['diagnostic']) ? $result['diagnostic'] : [];

// After data is fetched, delete the session so refreshing the page won't cause error/duplicate
unset($_SESSION['last_test_result']);

// Colors per CEFR level for badge and progress bar
$cefr_colors = [
    'A2' => '#22c55e',  // Green
    'B1' => '#3b82f6',  // Blue
    'B2' => '#f59e0b',  // Yellow
    'C1' => '#a855f7'   // Purple
];

$cefr_labels = [
    'A2' => 'Basic (A2)',
    'B1' => 'Intermediate (B1)',
    'B2' => 'Upper Intermediate (B2)',
    'C1' => 'Advanced (C1)'
];

$badge_color = isset($cefr_colors[$cefr_level]) ? $cefr_colors[$cefr_level] : '#94a3b8';
$badge_label = isset($cefr_labels[$cefr_level]) ? $cefr_labels[$cefr_level] : $cefr_level;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Completed | ReadQuest</title>
    <link rel="icon" type="image/png" href="/assets/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../desain/test-result.css?v=<?= time(); ?>">
</head>
<body>

    <div class="result-container">
        <div class="result-card">
            <h1>Simulation Completed!</h1>
            <p class="result-subtitle">TOEFL Reading Score Evaluation Result - Package <?php echo $paket; ?></p>
            
            <div class="score-circle">
                <div class="number"><?php echo $toefl_score; ?></div>
                <div class="label">TOEFL Score</div>
            </div>

            <!-- ============================================ -->
            <!-- BADGE CEFR LEVEL (Result Predicate) -->
            <!-- ============================================ -->
            <div class="cefr-badge-container">
                <div class="cefr-badge" style="background-color: <?php echo $badge_color; ?>20; border: 2px solid <?php echo $badge_color; ?>; color: <?php echo $badge_color; ?>;">
                    <span class="cefr-badge-icon">🎯</span>
                    <span class="cefr-badge-text"><?php echo $badge_label; ?></span>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stats-box">
                    <div class="stats-label">Correct Answer</div>
                    <div class="stats-value" style="color: #22c55e;"><?php echo $raw_score; ?> <span style="font-size: 12px; color: #64748b;">Questions</span></div>
                </div>
                <div class="stats-box">
                    <div class="stats-label">Wrong Answer</div>
                    <div class="stats-value" style="color: #ef4444;"><?php echo $wrong_score; ?> <span style="font-size: 12px; color: #64748b;">Questions</span></div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- DIAGNOSTIC REPORT: Breakdown per CEFR Level -->
            <!-- ============================================ -->
            <?php if (!empty($diagnostic)): ?>
            <div class="diagnostic-section">
                <h3 class="diagnostic-title">📊 Diagnostic Report</h3>
                <p class="diagnostic-subtitle">Correct answers performance per CEFR level</p>

                <div class="diagnostic-grid">
                    <?php foreach ($diagnostic as $level => $data): 
                        $color = isset($cefr_colors[$level]) ? $cefr_colors[$level] : '#94a3b8';
                        $pct = $data['percentage'];
                        $total_q = $data['total'];
                        $correct_q = $data['correct'];
                        
                        // Skip levels that have no questions
                        if ($total_q == 0) continue;
                    ?>
                    <div class="diagnostic-row">
                        <div class="diagnostic-level">
                            <span class="level-dot" style="background-color: <?php echo $color; ?>;"></span>
                            <span class="level-name"><?php echo $level; ?></span>
                        </div>
                        <div class="diagnostic-bar-wrapper">
                            <div class="diagnostic-bar">
                                <div class="diagnostic-bar-fill" style="width: <?php echo $pct; ?>%; background-color: <?php echo $color; ?>;"></div>
                            </div>
                        </div>
                        <div class="diagnostic-info">
                            <span class="diagnostic-percent" style="color: <?php echo $color; ?>;"><?php echo $pct; ?>%</span>
                            <span class="diagnostic-detail"><?php echo $correct_q; ?>/<?php echo $total_q; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <a href="test.php?paket=<?php echo urlencode($paket); ?>" class="btn-back-dashboard">Finish & Back to Menu</a>
        </div>
    </div>

</body>
</html>