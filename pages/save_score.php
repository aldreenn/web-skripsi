<?php
session_start();
include '../config/koneksi.php';

// PROTEKSI KEAMANAN: Pastikan hanya yang sudah login yang bisa kirim nilai
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Anda belum login!"]);
    exit;
}

// Memastikan data dikirim melalui metode POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Menerima data JSON yang dikirim oleh JavaScript
    $data = json_decode(file_get_contents('php://input'), true);

    // Ambil ID User LANGSUNG dari Session
    $user_id = (int)$_SESSION['user_id']; 
    
    $material_id = isset($data['material_id']) ? (int)$data['material_id'] : 0;
    $score = isset($data['score']) ? (int)$data['score'] : 0;
    
    // ==========================================
    // TAMBAHAN: Tangkap durasi dan ITP Score dari JavaScript
    // ==========================================
    $duration = isset($data['duration_seconds']) ? (int)$data['duration_seconds'] : 0;
    $itp_score = isset($data['itp_score']) ? (int)$data['itp_score'] : 0;

    // Jika data valid, proses ke tabel scores
    if ($material_id > 0) {
        
        // LOGIKA PINTAR: Cek apakah user sudah punya nilai di artikel ini
        $cek_query = mysqli_query($conn, "SELECT id, score, itp_score FROM practice_scores WHERE user_id = '$user_id' AND material_id = '$material_id'");
        
        if (mysqli_num_rows($cek_query) > 0) {
            // Jika sudah ada, ambil nilai lamanya
            $row = mysqli_fetch_assoc($cek_query);
            $old_score = (int)$row['score'];
            $old_itp = (int)$row['itp_score'];
            $score_id = $row['id'];

            // Update JIKA nilai baru lebih BESAR atau SAMA DENGAN nilai lama
            // Ini memastikan activity feed (yang diurutkan berdasarkan created_at)
            // tetap memunculkan aktivitas terbaru jika user mendapat nilai yang sama tingginya.
            if ($score >= $old_score) {
                // Jika nilai sama, simpan durasi yang lebih cepat (terkecil)
                $best_duration = $duration;
                if ($score == $old_score) {
                    // Ambil durasi lama jika ada
                    $cek_durasi_query = mysqli_query($conn, "SELECT duration_seconds FROM practice_scores WHERE id = '$score_id'");
                    if ($row_dur = mysqli_fetch_assoc($cek_durasi_query)) {
                        $old_duration = (int)$row_dur['duration_seconds'];
                        if ($old_duration > 0 && $old_duration < $duration) {
                            $best_duration = $old_duration;
                        }
                    }
                }
                
                // Pilih ITP Score tertinggi (skor mastery lebih tinggi = ITP lebih tinggi)
                $best_itp = ($itp_score >= $old_itp) ? $itp_score : $old_itp;

                $update_sql = "UPDATE practice_scores SET score = '$score', itp_score = '$best_itp', duration_seconds = '$best_duration', created_at = CURRENT_TIMESTAMP WHERE id = '$score_id'";
                if (mysqli_query($conn, $update_sql)) {
                    echo json_encode(["status" => "success", "message" => "Skor dan waktu diperbarui"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Gagal update: " . mysqli_error($conn)]);
                }
            } else {
                echo json_encode(["status" => "success", "message" => "Skor tidak diupdate (nilai lama lebih tinggi)"]);
            }
        } else {
            // Jika belum pernah mengerjakan sama sekali, INSERT data baru beserta waktunya
            $insert_sql = "INSERT INTO practice_scores (user_id, material_id, score, itp_score, duration_seconds) VALUES ('$user_id', '$material_id', '$score', '$itp_score', '$duration')";
            if (mysqli_query($conn, $insert_sql)) {
                echo json_encode(["status" => "success", "message" => "Skor dan waktu berhasil disimpan"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Gagal menyimpan: " . mysqli_error($conn)]);
            }
        }
        
    } else {
        echo json_encode(["status" => "error", "message" => "Data artikel tidak valid"]);
    }
}
?>