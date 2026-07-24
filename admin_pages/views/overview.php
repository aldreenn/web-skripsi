<?php
// ==========================================
// 1. QUERY KOTAK STATISTIK ATAS (DIPERBARUI)
// ==========================================
$query_users = mysqli_query($conn, "SELECT COUNT(id) as total FROM users WHERE role != 'admin'");
$total_users = mysqli_fetch_assoc($query_users)['total'] ?? 0;

// Topik (Practice)
$query_topics = mysqli_query($conn, "SELECT COUNT(id) as total FROM topics");
$total_topics = mysqli_fetch_assoc($query_topics)['total'] ?? 0;

// Artikel (Practice)
$query_mat_prac = mysqli_query($conn, "SELECT COUNT(id) as total FROM materials");
$total_artikel = mysqli_fetch_assoc($query_mat_prac)['total'] ?? 0;

// Soal (Practice) - BARU DIPISAH
$query_ques_prac = mysqli_query($conn, "SELECT COUNT(id) as total FROM questions");
$total_ques_prac = mysqli_fetch_assoc($query_ques_prac)['total'] ?? 0;

// Rata-rata Skor (Practice)
$query_avg_prac = mysqli_query($conn, "SELECT AVG(score) as avg_score FROM practice_scores");
$avg_prac_score = round(mysqli_fetch_assoc($query_avg_prac)['avg_score'] ?? 0);

// Passage (Test)
$query_mat_test = mysqli_query($conn, "SELECT COUNT(id) as total FROM test_passages");
$total_passage = mysqli_fetch_assoc($query_mat_test)['total'] ?? 0;

// Soal (Test) - BARU DIPISAH
$query_ques_test = mysqli_query($conn, "SELECT COUNT(id) as total FROM test_questions");
$total_ques_test = mysqli_fetch_assoc($query_ques_test)['total'] ?? 0;

// Rata-rata TOEFL
$query_toefl = mysqli_query($conn, "SELECT AVG(toefl_score) as avg_score FROM test_scores");
$avg_toefl = round(mysqli_fetch_assoc($query_toefl)['avg_score'] ?? 0);


// ==========================================
// 2. QUERY AKTIVITAS TERBARU (5 Tes Terakhir)
// ==========================================
$query_recent = mysqli_query($conn, "
    SELECT u.username, CONCAT(u.first_name, ' ', u.last_name) AS nama_lengkap, ts.test_packet, ts.toefl_score 
    FROM test_scores ts 
    JOIN users u ON ts.user_id = u.id 
    ORDER BY ts.id DESC LIMIT 5
");

// ==========================================
// 3. QUERY PAPAN PERINGKAT (Top 5 Skor)
// ==========================================
$query_top = mysqli_query($conn, "
    SELECT CONCAT(u.first_name, ' ', u.last_name) AS nama_lengkap, MAX(ts.toefl_score) as best_score 
    FROM test_scores ts 
    JOIN users u ON ts.user_id = u.id 
    GROUP BY ts.user_id 
    ORDER BY best_score DESC LIMIT 5
");

// ==========================================
// 4. QUERY STATUS KESIAPAN UJIAN (Per Paket)
// ==========================================
$query_ready = mysqli_query($conn, "
    SELECT tp.packet_id, COUNT(tq.id) as total_q 
    FROM test_passages tp 
    LEFT JOIN test_questions tq ON tp.id = tq.passage_id 
    GROUP BY tp.packet_id
");
$packet_status = [];
$query_all_packets = mysqli_query($conn, "SELECT packet_code FROM test_packets");
if ($query_all_packets) {
    while ($row = mysqli_fetch_assoc($query_all_packets)) {
        $packet_status[strtoupper($row['packet_code'])] = 0;
    }
}

if ($query_ready) {
    while ($r = mysqli_fetch_assoc($query_ready)) {
        $pkt = strtoupper($r['packet_id']);
        $packet_status[$pkt] = $r['total_q'];
    }
}
ksort($packet_status);
?>

<div class="content-header" style="margin-bottom: 30px;">
    <h1>Admin Overview</h1>
    <p style="color: #94a3b8; margin-top: 5px;">Summary of statistics and activity on the ReadQuest platform.</p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
    
    <div style="background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid #334155; display: flex; align-items: center; gap: 15px;">
        <div style="background: rgba(59, 130, 246, 0.15); color: #3b82f6; padding: 15px; border-radius: 10px; display: flex;">
            <span class="material-symbols-outlined" style="font-size: 32px;">group</span>
        </div>
        <div>
            <p style="margin: 0; font-size: 13px; color: #94a3b8; font-weight: bold; text-transform: uppercase;">Total Users</p>
            <h3 style="margin: 5px 0 0; font-size: 26px; color: #f8fafc;"><?= $total_users ?></h3>
        </div>
    </div>

    <div style="background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid #334155; display: flex; align-items: center; gap: 15px;">
        <div style="background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 15px; border-radius: 10px; display: flex;">
            <span class="material-symbols-outlined" style="font-size: 32px;">folder_open</span>
        </div>
        <div>
            <p style="margin: 0; font-size: 13px; color: #94a3b8; font-weight: bold; text-transform: uppercase;">Total Topics (Practice)</p>
            <h3 style="margin: 5px 0 0; font-size: 26px; color: #f8fafc;"><?= $total_topics ?></h3>
        </div>
    </div>

    <div style="background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid #334155; display: flex; align-items: center; gap: 15px;">
        <div style="background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 15px; border-radius: 10px; display: flex;">
            <span class="material-symbols-outlined" style="font-size: 32px;">article</span>
        </div>
        <div>
            <p style="margin: 0; font-size: 13px; color: #94a3b8; font-weight: bold; text-transform: uppercase;">Total Articles (Practice)</p>
            <h3 style="margin: 5px 0 0; font-size: 26px; color: #f8fafc;"><?= $total_artikel ?></h3>
        </div>
    </div>

    <div style="background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid #334155; display: flex; align-items: center; gap: 15px;">
        <div style="background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 15px; border-radius: 10px; display: flex;">
            <span class="material-symbols-outlined" style="font-size: 32px;">psychology_alt</span>
        </div>
        <div>
            <p style="margin: 0; font-size: 13px; color: #94a3b8; font-weight: bold; text-transform: uppercase;">Total Questions (Practice)</p>
            <h3 style="margin: 5px 0 0; font-size: 26px; color: #f8fafc;"><?= $total_ques_prac ?></h3>
        </div>
    </div>

    <div style="background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid #334155; display: flex; align-items: center; gap: 15px;">
        <div style="background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 15px; border-radius: 10px; display: flex;">
            <span class="material-symbols-outlined" style="font-size: 32px;">trending_up</span>
        </div>
        <div>
            <p style="margin: 0; font-size: 13px; color: #94a3b8; font-weight: bold; text-transform: uppercase;">Average Score (Practice)</p>
            <h3 style="margin: 5px 0 0; font-size: 26px; color: #f8fafc;"><?= $avg_prac_score ?></h3>
        </div>
    </div>

    <div style="background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid #334155; display: flex; align-items: center; gap: 15px;">
        <div style="background: rgba(6, 182, 212, 0.15); color: #06b6d4; padding: 15px; border-radius: 10px; display: flex;">
            <span class="material-symbols-outlined" style="font-size: 32px;">description</span>
        </div>
        <div>
            <p style="margin: 0; font-size: 13px; color: #94a3b8; font-weight: bold; text-transform: uppercase;">Total Passages (Test)</p>
            <h3 style="margin: 5px 0 0; font-size: 26px; color: #f8fafc;"><?= $total_passage ?></h3>
        </div>
    </div>

    <div style="background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid #334155; display: flex; align-items: center; gap: 15px;">
        <div style="background: rgba(6, 182, 212, 0.15); color: #06b6d4; padding: 15px; border-radius: 10px; display: flex;">
            <span class="material-symbols-outlined" style="font-size: 32px;">rule</span>
        </div>
        <div>
            <p style="margin: 0; font-size: 13px; color: #94a3b8; font-weight: bold; text-transform: uppercase;">Total Questions (Test)</p>
            <h3 style="margin: 5px 0 0; font-size: 26px; color: #f8fafc;"><?= $total_ques_test ?></h3>
        </div>
    </div>

    <div style="background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid #334155; display: flex; align-items: center; gap: 15px;">
        <div style="background: rgba(6, 182, 212, 0.15); color: #06b6d4; padding: 15px; border-radius: 10px; display: flex;">
            <span class="material-symbols-outlined" style="font-size: 32px;">trending_up</span>
        </div>
        <div>
            <p style="margin: 0; font-size: 13px; color: #94a3b8; font-weight: bold; text-transform: uppercase;">Average Score (Test)</p>
            <h3 style="margin: 5px 0 0; font-size: 26px; color: #f8fafc;"><?= $avg_toefl ?></h3>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">

    <div style="background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid #334155;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid #334155; padding-bottom: 15px;">
            <span class="material-symbols-outlined" style="color: #3b82f6;">history</span>
            <h3 style="margin: 0; color: #f8fafc; font-size: 18px;">Recent Test Activity</h3>
        </div>
        
        <table style="width: 100%; border-collapse: collapse; text-align: left; color: #cbd5e1; font-size: 14px;">
            <thead>
                <tr style="border-bottom: 1px dashed #475569; color: #94a3b8;">
                    <th style="padding: 10px 5px;">Username</th>
                    <th style="padding: 10px 5px;">Full Name</th>
                    <th style="padding: 10px 5px;">Packet</th>
                    <th style="padding: 10px 5px; text-align: right;">ITP Score</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($query_recent && mysqli_num_rows($query_recent) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($query_recent)): ?>
                        <tr style="border-bottom: 1px solid #334155;">
                            <td style="padding: 12px 5px; font-weight: bold; color: #f8fafc;"><?= htmlspecialchars($row['username']) ?></td>
                            <td style="padding: 12px 5px; color: #cbd5e1;"><?= htmlspecialchars($row['nama_lengkap'] ?? '-') ?></td>
                            <td style="padding: 12px 5px;">
                                <span style="background: #334155; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Packet <?= htmlspecialchars($row['test_packet']) ?></span>
                            </td>
                            <td style="padding: 12px 5px; text-align: right; color: #10b981; font-weight: bold;"><?= $row['toefl_score'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="padding: 20px; text-align: center; color: #64748b;">No test activity yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="display: flex; flex-direction: column; gap: 20px;">
        
        <div style="background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid #334155;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                <span class="material-symbols-outlined" style="color: #f59e0b;">leaderboard</span>
                <h3 style="margin: 0; color: #f8fafc; font-size: 16px;">Top Scorers</h3>
            </div>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <?php $rank = 1; ?>
                <?php if ($query_top && mysqli_num_rows($query_top) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($query_top)): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; background: #0f172a; padding: 10px 15px; border-radius: 8px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-weight: bold; color: <?= $rank == 1 ? '#f59e0b' : '#94a3b8' ?>;">#<?= $rank++ ?></span>
                                <span style="color: #cbd5e1; font-size: 14px;"><?= htmlspecialchars($row['nama_lengkap'] ?? 'Unknown') ?></span>
                            </div>
                            <span style="font-weight: bold; color: #f8fafc;"><?= $row['best_score'] ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align: center; color: #64748b; font-size: 14px; padding: 10px;">No data available.</div>
                <?php endif; ?>
            </div>
        </div>

        <div style="background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid #334155;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                <span class="material-symbols-outlined" style="color: #10b981;">checklist</span>
                <h3 style="margin: 0; color: #f8fafc; font-size: 16px;">Test Readiness</h3>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <?php foreach ($packet_status as $pkt => $qty): ?>
                    <?php 
                        $percent = min(100, round(($qty / 32) * 100)); 
                        $bar_color = $percent == 100 ? '#10b981' : '#3b82f6';
                    ?>
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 13px; color: #cbd5e1; margin-bottom: 5px;">
                            <span>Packet <?= $pkt ?></span>
                            <span><?= $qty ?>/32 Questions</span>
                        </div>
                        <div style="width: 100%; height: 6px; background: #0f172a; border-radius: 4px; overflow: hidden;">
                            <div style="height: 100%; width: <?= $percent ?>%; background: <?= $bar_color ?>; border-radius: 4px;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</div>