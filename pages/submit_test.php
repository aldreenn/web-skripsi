<?php
session_start();
include '../config/koneksi.php';

// Pastikan user login
if (!isset($_SESSION['user_id'])) {
    header('Location: /aplikasi_skripsi/pages/loginpage.html');
    exit;
}

// Pastikan data dikirim melalui metode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_SESSION['user_id'];
    $paket = isset($_POST['paket']) ? mysqli_real_escape_string($conn, $_POST['paket']) : '';

    if (empty($paket)) {
        die("Terjadi kesalahan: Paket ujian tidak valid.");
    }

    // ================================================================
    // 1. Tarik semua Kunci Jawaban beserta cefr_level untuk Paket ini
    // ================================================================
    $query_keys = mysqli_query($conn, "
        SELECT q.id, q.correct_answer, q.cefr_level 
        FROM test_questions q 
        JOIN test_passages p ON q.passage_id = p.id 
        WHERE p.packet_id = '$paket'
    ");

    $raw_score = 0;
    $total_questions = 0;

    // Array untuk Diagnostic Report: menyimpan data per level CEFR
    $diagnostic = [
        'A2' => ['total' => 0, 'correct' => 0],
        'B1' => ['total' => 0, 'correct' => 0],
        'B2' => ['total' => 0, 'correct' => 0],
        'C1' => ['total' => 0, 'correct' => 0]
    ];

    // ================================================================
    // 2. Mesin Pemeriksa: Cocokkan jawaban user dengan kunci jawaban
    //    Sekaligus hitung breakdown per level CEFR (Diagnostic)
    // ================================================================
    while ($row = mysqli_fetch_assoc($query_keys)) {
        $q_id = $row['id'];
        $kunci_jawaban = strtoupper($row['correct_answer']);
        $cefr_level = strtoupper($row['cefr_level']);
        $total_questions++;

        // Hitung total soal per level
        if (isset($diagnostic[$cefr_level])) {
            $diagnostic[$cefr_level]['total']++;
        }

        if (isset($_POST['q_' . $q_id])) {
            $jawaban_user = strtoupper($_POST['q_' . $q_id]);
            if ($jawaban_user === $kunci_jawaban) {
                $raw_score++;
                // Hitung jawaban benar per level
                if (isset($diagnostic[$cefr_level])) {
                    $diagnostic[$cefr_level]['correct']++;
                }
            }
        }
    }

    // ================================================================
    // 3. Konversi Raw Score → Skor ITP + Predikat CEFR
    //    Menggunakan Range-Based Linear Interpolation
    // ================================================================
    
    /**
     * convertRawToITP()
     * Mengkonversi raw score (0-50) ke skor ITP Reading dan predikat CEFR
     * menggunakan range-based mapping dengan linear interpolation.
     * 
     * @param int $raw Raw score (jumlah jawaban benar)
     * @return array ['itp_score' => int, 'cefr_level' => string]
     */
    function convertRawToITP($raw) {
        // Definisi mapping: [raw_min, raw_max, itp_min, itp_max, cefr_level]
        $ranges = [
            ['raw_min' => 45, 'raw_max' => 50, 'itp_min' => 63, 'itp_max' => 67, 'cefr' => 'C1'],
            ['raw_min' => 36, 'raw_max' => 44, 'itp_min' => 56, 'itp_max' => 62, 'cefr' => 'B2'],
            ['raw_min' => 23, 'raw_max' => 35, 'itp_min' => 48, 'itp_max' => 55, 'cefr' => 'B1'],
            ['raw_min' => 0,  'raw_max' => 22, 'itp_min' => 31, 'itp_max' => 47, 'cefr' => 'A2'],
        ];

        foreach ($ranges as $range) {
            if ($raw >= $range['raw_min'] && $raw <= $range['raw_max']) {
                // Linear interpolation di dalam range ini
                $raw_span = $range['raw_max'] - $range['raw_min'];
                if ($raw_span > 0) {
                    $ratio = ($raw - $range['raw_min']) / $raw_span;
                } else {
                    $ratio = 1; // Jika span = 0 (hanya 1 angka)
                }
                $itp_score = (int)round($range['itp_min'] + $ratio * ($range['itp_max'] - $range['itp_min']));
                
                return [
                    'itp_score' => $itp_score,
                    'cefr_level' => $range['cefr']
                ];
            }
        }

        // Fallback (seharusnya tidak tercapai)
        return ['itp_score' => 31, 'cefr_level' => 'A2'];
    }

    if ($total_questions > 0) {
        if ($total_questions == 50) {
            // Jika soal pas 50, langsung konversi
            $conversion = convertRawToITP($raw_score);
        } else {
            // Jika soal kurang dari 50 (misal saat testing), proyeksikan ke skala 50
            $projected_raw = (int)round(($raw_score / $total_questions) * 50);
            $conversion = convertRawToITP($projected_raw);
        }
        $toefl_score = $conversion['itp_score'];
        $cefr_level = $conversion['cefr_level'];
    } else {
        $toefl_score = 0;
        $cefr_level = 'A2';
    }

    // ================================================================
    // 4. Hitung Persentase Diagnostik per Level CEFR
    // ================================================================
    $diagnostic_result = [];
    foreach ($diagnostic as $level => $data) {
        if ($data['total'] > 0) {
            $percentage = round(($data['correct'] / $data['total']) * 100);
        } else {
            $percentage = 0;
        }
        $diagnostic_result[$level] = [
            'total'    => $data['total'],
            'correct'  => $data['correct'],
            'percentage' => $percentage
        ];
    }

    // ================================================================
    // 5. Simpan hasil ujian ke Database (termasuk cefr_level)
    // ================================================================
    $simpan = mysqli_query($conn, "
        INSERT INTO test_scores (user_id, test_packet, raw_score, toefl_score, cefr_level) 
        VALUES ('$user_id', '$paket', '$raw_score', '$toefl_score', '$cefr_level')
    ");

    if ($simpan) {
        $session_timer_name = 'exam_end_time_' . $paket;
        if (isset($_SESSION[$session_timer_name])) {
            unset($_SESSION[$session_timer_name]);
        }

        // ================================================================
        // 6. Simpan semua data ke Session untuk halaman Result
        // ================================================================
        $_SESSION['last_test_result'] = [
            'paket'           => $paket,
            'raw_score'       => $raw_score,
            'total_questions'  => $total_questions,
            'toefl_score'     => $toefl_score,
            'cefr_level'      => $cefr_level,
            'diagnostic'      => $diagnostic_result
        ];

        header('Location: test-result.php');
        exit;
    } else {
        echo "Gagal menyimpan skor ke database: " . mysqli_error($conn);
    }

} else {
    header('Location: dashboard.php');
    exit;
}
?>