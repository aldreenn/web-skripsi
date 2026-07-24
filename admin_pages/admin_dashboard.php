<?php
include '../config/koneksi.php'; 
session_start();

// ========================================================
// PROTEKSI KEAMANAN
// ========================================================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Khusus ini biarkan alert bawaan karena header/footer belum dimuat saat login gagal
    echo "<script>
            alert('Akses Ditolak! Halaman ini khusus untuk Admin.');
            window.location.href = '/aplikasi_skripsi/pages/loginpage.html';
          </script>";
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'overview';

// ========================================================
// LOGIKA SIMPAN DATA MATERIAL (ADD ARTICLE)
// ========================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_material'])) {
    $level = trim($_POST['level']);
    $topic = trim($_POST['topic']); 
    $reading_time = trim($_POST['reading_time']);
    $title = trim($_POST['title']);
    $desc = trim($_POST['desc']);
    $fullContent = trim($_POST['fullContent']);

    if (empty($title) || empty($desc) || empty($topic)) {
        $_SESSION['alert'] = ['title' => 'Gagal!', 'msg' => 'Kolom Judul, Folder, dan Deskripsi WAJIB diisi!', 'type' => 'error'];
        header("Location: admin_dashboard.php?page=add_article");
        exit;
    }

    $cover_image_db = NULL;
    if (isset($_FILES['material_image']) && $_FILES['material_image']['error'] === 0) {
        $file_name = time() . '_' . basename($_FILES['material_image']['name']);
        $target_dir = "../uploads/materials/"; 
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES['material_image']['tmp_name'], $target_file)) {
            $cover_image_db = $file_name;
        }
    }

    $title = mysqli_real_escape_string($conn, $title);
    $desc = mysqli_real_escape_string($conn, $desc);
    $fullContent = mysqli_real_escape_string($conn, $fullContent);

    if ($cover_image_db != NULL) {
        $sql = "INSERT INTO materials (level, topic, reading_time, cover_image, title, description, full_content) 
                VALUES ('$level', '$topic', '$reading_time', '$cover_image_db', '$title', '$desc', '$fullContent')";
    } else {
        $sql = "INSERT INTO materials (level, topic, reading_time, title, description, full_content) 
                VALUES ('$level', '$topic', '$reading_time', '$title', '$desc', '$fullContent')";
    }

    if (mysqli_query($conn, $sql)) {
        $_SESSION['alert'] = ['title' => 'Berhasil!', 'msg' => 'Material/Artikel Berhasil Ditambahkan!', 'type' => 'success'];
        header("Location: admin_dashboard.php?page=manage_article");
        exit;
    } else {
        $_SESSION['alert'] = ['title' => 'Terjadi Kesalahan!', 'msg' => 'Gagal menyimpan: ' . mysqli_error($conn), 'type' => 'error'];
        header("Location: admin_dashboard.php?page=add_article");
        exit;
    }
}

// ========================================================
// LOGIKA SIMPAN DATA TEST MATERIAL (ADD TEST PASSAGE)
// ========================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_test_material'])) {
    $packet_id = trim($_POST['packet_id']);
    $passage_number = (int)$_POST['passage_number']; 
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (empty($packet_id) || empty($passage_number) || empty($title) || empty($content)) {
        $_SESSION['alert'] = ['title' => 'Gagal!', 'msg' => 'Semua kolom WAJIB diisi!', 'type' => 'error'];
        header("Location: admin_dashboard.php?page=add_test_passage");
        exit;
    }

    // Proses unggah file gambar jika ada yang di-submit
    $cover_image_db = NULL;
    if (isset($_FILES['test_passage_image']) && $_FILES['test_passage_image']['error'] === 0) {
        // Membuat nama unik file: timestamp + prefix _test_ + nama asli
        $file_name = time() . '_test_' . basename($_FILES['test_passage_image']['name']);
        $target_dir = "../uploads/materials/"; // Disatukan ke folder upload materials agar praktis
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES['test_passage_image']['tmp_name'], $target_file)) {
            $cover_image_db = $file_name;
        }
    }

    $title = mysqli_real_escape_string($conn, $title);
    $content = mysqli_real_escape_string($conn, $content);

    // Menyimpan data ke database (membedakan query jika memakai gambar atau tidak)
    if ($cover_image_db != NULL) {
        $sql = "INSERT INTO test_passages (packet_id, passage_number, title, content, cover_image) 
                VALUES ('$packet_id', '$passage_number', '$title', '$content', '$cover_image_db')";
    } else {
        $sql = "INSERT INTO test_passages (packet_id, passage_number, title, content) 
                VALUES ('$packet_id', '$passage_number', '$title', '$content')";
    }

    if (mysqli_query($conn, $sql)) {
        $_SESSION['alert'] = ['title' => 'Berhasil!', 'msg' => 'Passage Ujian Berhasil Ditambahkan!', 'type' => 'success'];
        header("Location: admin_dashboard.php?page=manage_test_passage");
        exit;
    } else {
        $_SESSION['alert'] = ['title' => 'Terjadi Kesalahan!', 'msg' => 'Gagal menyimpan: ' . mysqli_error($conn), 'type' => 'error'];
        header("Location: admin_dashboard.php?page=add_test_passage");
        exit;
    }
}

// ========================================================
// LOGIKA HAPUS DATA TEST MATERIAL
// ========================================================
if (isset($_GET['delete_test_material'])) {
    $id_material = (int)$_GET['delete_test_material'];
    
    // TAMBAHAN: Hapus file fisik gambar dari folder uploads jika ada sebelum data dihapus dari DB
    $cek_mat = mysqli_query($conn, "SELECT cover_image FROM test_passages WHERE id = $id_material");
    if ($data_mat = mysqli_fetch_assoc($cek_mat)) {
        if (!empty($data_mat['cover_image'])) {
            $file_img = "../uploads/materials/" . $data_mat['cover_image'];
            if (file_exists($file_img)) { unlink($file_img); }
        }
    }
    
    // Hapus soal ujian yang terhubung terlebih dahulu (menggunakan passage_id)
    mysqli_query($conn, "DELETE FROM test_questions WHERE passage_id = $id_material");
    
    // Eksekusi hapus teks passage ujian
    if (mysqli_query($conn, "DELETE FROM test_passages WHERE id = $id_material")) {
        $_SESSION['alert'] = ['title' => 'Terhapus!', 'msg' => 'Passage ujian beserta soal terkait berhasil dihapus permanen!', 'type' => 'success'];
        header("Location: admin_dashboard.php?page=manage_test_passage");
        exit;
    }
}

// ========================================================
// LOGIKA UPDATE / EDIT DATA TEST MATERIAL (PASSAGE)
// ========================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_test_material'])) {
    $passage_id = (int)$_POST['passage_id'];
    $packet_id = trim($_POST['packet_id']);
    $passage_number = (int)$_POST['passage_number']; 
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (empty($packet_id) || empty($passage_number) || empty($title) || empty($content)) {
        $_SESSION['alert'] = ['title' => 'Gagal!', 'msg' => 'Semua kolom teks WAJIB diisi!', 'type' => 'error'];
        header("Location: admin_dashboard.php?page=edit_test_passage&id=$passage_id");
        exit;
    }

    $image_update_sql = "";
    
    // 1. Jika ada file gambar baru yang diunggah
    if (isset($_FILES['test_passage_image']) && $_FILES['test_passage_image']['error'] === 0) {
        $cek_img = mysqli_query($conn, "SELECT cover_image FROM test_passages WHERE id = '$passage_id'");
        $data_img = mysqli_fetch_assoc($cek_img);
        if ($data_img['cover_image']) {
            $old_path = "../uploads/materials/" . $data_img['cover_image'];
            if (file_exists($old_path)) unlink($old_path);
        }

        $file_name = time() . '_test_' . basename($_FILES['test_passage_image']['name']);
        $target_file = "../uploads/materials/" . $file_name;
        if (move_uploaded_file($_FILES['test_passage_image']['tmp_name'], $target_file)) {
            $image_update_sql = ", cover_image = '$file_name' ";
        }
    }

    // 2. Jika tombol "Hapus Gambar" dicentang
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == 'yes') {
        $cek_img = mysqli_query($conn, "SELECT cover_image FROM test_passages WHERE id = '$passage_id'");
        $data_img = mysqli_fetch_assoc($cek_img);
        if ($data_img['cover_image']) {
            $old_path = "../uploads/materials/" . $data_img['cover_image'];
            if (file_exists($old_path)) unlink($old_path);
        }
        $image_update_sql = ", cover_image = NULL ";
    }

    $title = mysqli_real_escape_string($conn, $title);
    $content = mysqli_real_escape_string($conn, $content);

    // Update data dengan atau tanpa tambahan query gambar
    $update_sql = "UPDATE test_passages SET 
                    packet_id = '$packet_id', passage_number = '$passage_number', 
                    title = '$title', content = '$content' 
                    $image_update_sql 
                   WHERE id = '$passage_id'";

    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['alert'] = ['title' => 'Tersimpan!', 'msg' => 'Passage Ujian Berhasil Diperbarui!', 'type' => 'success'];
        header("Location: admin_dashboard.php?page=manage_test_passage");
        exit;
    }
}

// ========================================================
// LOGIKA SIMPAN DATA SOAL UJIAN (ADD TEST QUESTION)
// ========================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_test_question'])) {
    $passage_id = (int)$_POST['passage_id'];
    $reciprocal_phase = mysqli_real_escape_string($conn, trim($_POST['reciprocal_phase']));
    $question_text = mysqli_real_escape_string($conn, trim($_POST['question_text']));
    $option_a = mysqli_real_escape_string($conn, trim($_POST['option_a']));
    $option_b = mysqli_real_escape_string($conn, trim($_POST['option_b']));
    $option_c = mysqli_real_escape_string($conn, trim($_POST['option_c']));
    $option_d = mysqli_real_escape_string($conn, trim($_POST['option_d']));
    $correct_answer = mysqli_real_escape_string($conn, $_POST['correct_answer']);

    if (empty($passage_id) || empty($reciprocal_phase) || empty($question_text)) {
        $_SESSION['alert'] = ['title' => 'Gagal!', 'msg' => 'Teks, Fase, dan Pertanyaan WAJIB diisi!', 'type' => 'error'];
        header("Location: admin_dashboard.php?page=add_test_question");
        exit;
    }

    $sql_q = "INSERT INTO test_questions (passage_id, reciprocal_phase, question_text, option_a, option_b, option_c, option_d, correct_answer) 
              VALUES ('$passage_id', '$reciprocal_phase', '$question_text', '$option_a', '$option_b', '$option_c', '$option_d', '$correct_answer')";

    if (mysqli_query($conn, $sql_q)) {
        $_SESSION['alert'] = ['title' => 'Berhasil!', 'msg' => 'Soal Ujian Baru Berhasil Ditambahkan!', 'type' => 'success'];
        // Kita arahkan kembali ke halaman ini agar Admin bisa langsung lanjut buat soal berikutnya
        header("Location: admin_dashboard.php?page=add_test_question"); 
        exit;
    } else {
        $_SESSION['alert'] = ['title' => 'Terjadi Kesalahan!', 'msg' => 'Gagal menyimpan: ' . mysqli_error($conn), 'type' => 'error'];
        header("Location: admin_dashboard.php?page=add_test_question");
        exit;
    }
}

// ========================================================
// LOGIKA HAPUS DATA SOAL UJIAN
// ========================================================
if (isset($_GET['delete_test_question'])) {
    $delete_tq_id = (int)$_GET['delete_test_question'];
    if (mysqli_query($conn, "DELETE FROM test_questions WHERE id = '$delete_tq_id'")) {
        $_SESSION['alert'] = ['title' => 'Terhapus!', 'msg' => 'Soal ujian berhasil dihapus!', 'type' => 'success'];
        header("Location: admin_dashboard.php?page=manage_test_questions");
        exit;
    }
}

// ========================================================
// LOGIKA UPDATE / EDIT DATA SOAL UJIAN
// ========================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_test_question'])) {
    $question_id = (int)$_POST['question_id'];
    $passage_id = (int)$_POST['passage_id'];
    $reciprocal_phase = mysqli_real_escape_string($conn, trim($_POST['reciprocal_phase']));
    $question_text = mysqli_real_escape_string($conn, trim($_POST['question_text']));
    $option_a = mysqli_real_escape_string($conn, trim($_POST['option_a']));
    $option_b = mysqli_real_escape_string($conn, trim($_POST['option_b']));
    $option_c = mysqli_real_escape_string($conn, trim($_POST['option_c']));
    $option_d = mysqli_real_escape_string($conn, trim($_POST['option_d']));
    $correct_answer = mysqli_real_escape_string($conn, $_POST['correct_answer']);

    if (empty($passage_id) || empty($reciprocal_phase) || empty($question_text)) {
        $_SESSION['alert'] = ['title' => 'Gagal!', 'msg' => 'Teks, Fase, dan Pertanyaan WAJIB diisi!', 'type' => 'error'];
        header("Location: admin_dashboard.php?page=edit_test_question&id=$question_id");
        exit;
    }

    $update_sql = "UPDATE test_questions SET 
                    passage_id = '$passage_id', reciprocal_phase = '$reciprocal_phase', 
                    question_text = '$question_text', option_a = '$option_a', 
                    option_b = '$option_b', option_c = '$option_c', option_d = '$option_d', 
                    correct_answer = '$correct_answer' 
                   WHERE id = '$question_id'";

    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['alert'] = ['title' => 'Tersimpan!', 'msg' => 'Soal Ujian Berhasil Diperbarui!', 'type' => 'success'];
        header("Location: admin_dashboard.php?page=manage_test_questions");
        exit;
    }
}

// ========================================================
// LOGIKA UPDATE / EDIT DATA MATERIAL 
// ========================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_material'])) {
    $material_id = (int)$_POST['material_id'];
    $level = trim($_POST['level']);
    $topic = trim($_POST['topic']); 
    $reading_time = trim($_POST['reading_time']);
    $title = trim($_POST['title']);
    $desc = trim($_POST['desc']);
    $fullContent = trim($_POST['fullContent']);

    if (empty($title) || empty($desc) || empty($topic)) {
        $_SESSION['alert'] = ['title' => 'Gagal!', 'msg' => 'Kolom Judul, Folder, dan Deskripsi WAJIB diisi!', 'type' => 'error'];
        header("Location: admin_dashboard.php?page=edit_article&id=$material_id");
        exit;
    }

    $image_update_sql = "";
    
    if (isset($_FILES['material_image']) && $_FILES['material_image']['error'] === 0) {
        $cek_img = mysqli_query($conn, "SELECT cover_image FROM materials WHERE id = '$material_id'");
        $data_img = mysqli_fetch_assoc($cek_img);
        if ($data_img['cover_image']) {
            $old_path = "../uploads/materials/" . $data_img['cover_image'];
            if (file_exists($old_path)) unlink($old_path);
        }

        $file_name = time() . '_' . basename($_FILES['material_image']['name']);
        $target_file = "../uploads/materials/" . $file_name;
        if (move_uploaded_file($_FILES['material_image']['tmp_name'], $target_file)) {
            $image_update_sql = ", cover_image = '$file_name' ";
        }
    }

    if (isset($_POST['remove_image_material']) && $_POST['remove_image_material'] == 'yes') {
        $cek_img = mysqli_query($conn, "SELECT cover_image FROM materials WHERE id = '$material_id'");
        $data_img = mysqli_fetch_assoc($cek_img);
        if ($data_img['cover_image']) {
            $old_path = "../uploads/materials/" . $data_img['cover_image'];
            if (file_exists($old_path)) unlink($old_path);
        }
        $image_update_sql = ", cover_image = NULL ";
    }

    $title = mysqli_real_escape_string($conn, $title);
    $desc = mysqli_real_escape_string($conn, $desc);
    $fullContent = mysqli_real_escape_string($conn, $fullContent);

    $update_sql = "UPDATE materials SET 
                    level = '$level', topic = '$topic', reading_time = '$reading_time', 
                    title = '$title', description = '$desc', full_content = '$fullContent' 
                    $image_update_sql WHERE id = '$material_id'";

    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['alert'] = ['title' => 'Tersimpan!', 'msg' => 'Artikel Berhasil Diperbarui!', 'type' => 'success'];
        header("Location: admin_dashboard.php?page=manage_article");
        exit;
    }
}

// ========================================================
// LOGIKA HAPUS DATA MATERIAL
// ========================================================
if (isset($_GET['delete_material'])) {
    $id_material = (int)$_GET['delete_material'];
    
    $cek_mat = mysqli_query($conn, "SELECT cover_image FROM materials WHERE id = $id_material");
    if ($data_mat = mysqli_fetch_assoc($cek_mat)) {
        if (!empty($data_mat['cover_image'])) {
            $file_img = "../uploads/materials/" . $data_mat['cover_image'];
            if (file_exists($file_img)) { unlink($file_img); }
        }
    }
    
    mysqli_query($conn, "DELETE FROM questions WHERE material_id = $id_material");
    mysqli_query($conn, "DELETE FROM practice_scores WHERE material_id = $id_material");
    
    if (mysqli_query($conn, "DELETE FROM materials WHERE id = $id_material")) {
        $_SESSION['alert'] = ['title' => 'Terhapus!', 'msg' => 'Artikel beserta kuis & skor terkait berhasil dihapus permanen!', 'type' => 'success'];
        header("Location: admin_dashboard.php?page=manage_article");
        exit;
    }
}

// ========================================================
// LOGIKA SIMPAN, EDIT & HAPUS TOPIK (FOLDER)
// ========================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_topic'])) {
    $level_topic = trim($_POST['level_topic']);
    $topic_name = trim($_POST['topic_name']);
    if (!empty($level_topic) && !empty($topic_name)) {
        $topic_name = mysqli_real_escape_string($conn, $topic_name);
        $insert_topic = "INSERT INTO topics (level, topic_name) VALUES ('$level_topic', '$topic_name')";
        if (mysqli_query($conn, $insert_topic)) {
            $_SESSION['alert'] = ['title' => 'Folder Dibuat!', 'msg' => 'Folder Topik berhasil ditambahkan!', 'type' => 'success'];
            header("Location: admin_dashboard.php?page=manage_topics");
            exit;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_topic'])) {
    $edit_id = (int)$_POST['edit_topic_id'];
    $edit_level = trim($_POST['edit_level']);
    $edit_name = trim($_POST['edit_topic_name']);
    
    if (!empty($edit_level) && !empty($edit_name)) {
        $edit_name = mysqli_real_escape_string($conn, $edit_name);
        
        // Dapatkan nama topik lama
        $cek_old = mysqli_query($conn, "SELECT topic_name FROM topics WHERE id = $edit_id");
        if ($old_data = mysqli_fetch_assoc($cek_old)) {
            $old_topic_name = mysqli_real_escape_string($conn, $old_data['topic_name']);
            
            // Update nama topik di materials jika namanya berubah
            if ($old_topic_name !== $edit_name) {
                mysqli_query($conn, "UPDATE materials SET topic = '$edit_name' WHERE topic = '$old_topic_name'");
            }
        }
        
        $update_topic = "UPDATE topics SET level = '$edit_level', topic_name = '$edit_name' WHERE id = $edit_id";
        if (mysqli_query($conn, $update_topic)) {
            $_SESSION['alert'] = ['title' => 'Tersimpan!', 'msg' => 'Folder Topik berhasil diperbarui!', 'type' => 'success'];
            header("Location: admin_dashboard.php?page=manage_topics");
            exit;
        }
    }
}

if (isset($_GET['delete_topic'])) {
    $id_topic = (int)$_GET['delete_topic'];
    $cek_topik = mysqli_query($conn, "SELECT topic_name FROM topics WHERE id = $id_topic");
    if ($data_topik = mysqli_fetch_assoc($cek_topik)) {
        $nama_topik = mysqli_real_escape_string($conn, $data_topik['topic_name']);
        
        $cek_mat = mysqli_query($conn, "SELECT id, cover_image FROM materials WHERE topic = '$nama_topik'");
        while ($mat_data = mysqli_fetch_assoc($cek_mat)) {
            if (!empty($mat_data['cover_image'])) {
                $file_img = "../uploads/materials/" . $mat_data['cover_image'];
                if (file_exists($file_img)) unlink($file_img);
            }
            mysqli_query($conn, "DELETE FROM questions WHERE material_id = '{$mat_data['id']}'");
        }
        mysqli_query($conn, "DELETE FROM materials WHERE topic = '$nama_topik'");
    }
    if (mysqli_query($conn, "DELETE FROM topics WHERE id = $id_topic")) {
        $_SESSION['alert'] = ['title' => 'Terhapus!', 'msg' => 'Folder beserta semua materi di dalamnya berhasil dihapus!', 'type' => 'success'];
        header("Location: admin_dashboard.php?page=manage_topics");
        exit;
    }
}

// ========================================================
// LOGIKA SIMPAN, EDIT & HAPUS SOAL KUIS 
// ========================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_question'])) {
    $material_id = mysqli_real_escape_string($conn, trim($_POST['material_id']));
    $question_text = mysqli_real_escape_string($conn, trim($_POST['question_text']));
    $option_a = mysqli_real_escape_string($conn, trim($_POST['option_a']));
    $option_b = mysqli_real_escape_string($conn, trim($_POST['option_b']));
    $option_c = mysqli_real_escape_string($conn, trim($_POST['option_c']));
    $option_d = mysqli_real_escape_string($conn, trim($_POST['option_d']));
    $correct_answer = $_POST['correct_answer'];
    $explanation = mysqli_real_escape_string($conn, trim($_POST['explanation'])); 

    $q_sql = "INSERT INTO questions (material_id, question_text, option_a, option_b, option_c, option_d, correct_answer, explanation) 
              VALUES ('$material_id', '$question_text', '$option_a', '$option_b', '$option_c', '$option_d', '$correct_answer', '$explanation')";

    if (mysqli_query($conn, $q_sql)) {
        $_SESSION['alert'] = ['title' => 'Berhasil!', 'msg' => 'Soal Baru Berhasil Ditambahkan!', 'type' => 'success'];
        header("Location: admin_dashboard.php?page=manage_questions");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_question'])) {
    $question_id = (int)$_POST['question_id'];
    $material_id = mysqli_real_escape_string($conn, trim($_POST['material_id']));
    $question_text = mysqli_real_escape_string($conn, trim($_POST['question_text']));
    $option_a = mysqli_real_escape_string($conn, trim($_POST['option_a']));
    $option_b = mysqli_real_escape_string($conn, trim($_POST['option_b']));
    $option_c = mysqli_real_escape_string($conn, trim($_POST['option_c']));
    $option_d = mysqli_real_escape_string($conn, trim($_POST['option_d']));
    $correct_answer = $_POST['correct_answer'];
    $explanation = mysqli_real_escape_string($conn, trim($_POST['explanation']));

    $update_sql = "UPDATE questions SET 
                    material_id = '$material_id', question_text = '$question_text', 
                    option_a = '$option_a', option_b = '$option_b', option_c = '$option_c', option_d = '$option_d', 
                    correct_answer = '$correct_answer', explanation = '$explanation' 
                   WHERE id = '$question_id'";

    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['alert'] = ['title' => 'Tersimpan!', 'msg' => 'Soal Kuis Berhasil Diperbarui!', 'type' => 'success'];
        header("Location: admin_dashboard.php?page=manage_questions");
        exit;
    }
}

if (isset($_GET['delete_question'])) {
    $delete_q_id = (int)$_GET['delete_question'];
    if (mysqli_query($conn, "DELETE FROM questions WHERE id = '$delete_q_id'")) {
        $_SESSION['alert'] = ['title' => 'Terhapus!', 'msg' => 'Soal kuis berhasil dihapus!', 'type' => 'success'];
        header("Location: admin_dashboard.php?page=manage_questions");
        exit;
    }
}

// ========================================================
// LOGIKA HAPUS DATA SKOR (PRACTICE)
// ========================================================
if (isset($_GET['delete_score'])) {
    $delete_id = (int)$_GET['delete_score'];
    if (mysqli_query($conn, "DELETE FROM practice_scores WHERE id = '$delete_id'")) {
        $_SESSION['alert'] = ['title' => 'Terhapus!', 'msg' => 'Data skor latihan berhasil dihapus!', 'type' => 'success'];
        header("Location: admin_dashboard.php?page=view_scores&tab=practice");
        exit;
    }
}

// ========================================================
// LOGIKA HAPUS DATA SKOR (TEST)
// ========================================================
if (isset($_GET['delete_test_score'])) {
    $delete_test_id = (int)$_GET['delete_test_score'];
    if (mysqli_query($conn, "DELETE FROM test_scores WHERE id = '$delete_test_id'")) {
        $_SESSION['alert'] = ['title' => 'Terhapus!', 'msg' => 'Data skor ujian berhasil dihapus!', 'type' => 'success'];
        header("Location: admin_dashboard.php?page=view_scores&tab=test");
        exit;
    }
}

// ========================================================
// LOGIKA SIMPAN & HAPUS DATA PACKAGE TEST (PAKET UJIAN)
// ========================================================

// 1. Logika Simpan Paket Baru
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_test_packet'])) {
    $packet_code = strtoupper(trim($_POST['packet_code'])); // Pastikan selalu huruf besar
    $title = trim($_POST['title']);
    $req = trim($_POST['requirement']);
    
    // Jika tidak ada syarat, jadikan NULL di database
    $requirement = empty($req) ? "NULL" : "'$req'";

    if (empty($packet_code) || empty($title)) {
        $_SESSION['alert'] = ['title' => 'Gagal!', 'msg' => 'Kode Paket dan Judul Paket WAJIB diisi!', 'type' => 'error'];
    } else {
        // Cek apakah kode paket sudah ada (mencegah duplikat kode A, B, dsb)
        $cek_duplikat = mysqli_query($conn, "SELECT id FROM test_packets WHERE packet_code = '$packet_code'");
        if (mysqli_num_rows($cek_duplikat) > 0) {
            $_SESSION['alert'] = ['title' => 'Gagal!', 'msg' => 'Kode Paket sudah digunakan! Gunakan huruf lain.', 'type' => 'error'];
        } else {
            $sql = "INSERT INTO test_packets (packet_code, title, requirement) VALUES ('$packet_code', '$title', $requirement)";
            if (mysqli_query($conn, $sql)) {
                $_SESSION['alert'] = ['title' => 'Berhasil!', 'msg' => 'Paket Ujian Baru Berhasil Ditambahkan!', 'type' => 'success'];
            } else {
                $_SESSION['alert'] = ['title' => 'Terjadi Kesalahan!', 'msg' => 'Gagal menyimpan: ' . mysqli_error($conn), 'type' => 'error'];
            }
        }
    }
    header("Location: admin_dashboard.php?page=manage_test_package");
    exit;
}

// 2. Logika Edit Paket
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_test_packet'])) {
    $packet_id = (int)$_POST['packet_id'];
    $packet_code = strtoupper(trim($_POST['packet_code']));
    $title = trim($_POST['title']);
    $req = trim($_POST['requirement']);
    
    $requirement = empty($req) ? "NULL" : "'$req'";

    if (empty($packet_code) || empty($title)) {
        $_SESSION['alert'] = ['title' => 'Gagal!', 'msg' => 'Kode Paket dan Judul Paket WAJIB diisi!', 'type' => 'error'];
    } else {
        $cek_duplikat = mysqli_query($conn, "SELECT id FROM test_packets WHERE packet_code = '$packet_code' AND id != '$packet_id'");
        if (mysqli_num_rows($cek_duplikat) > 0) {
            $_SESSION['alert'] = ['title' => 'Gagal!', 'msg' => 'Kode Paket sudah digunakan! Gunakan huruf lain.', 'type' => 'error'];
        } else {
            $sql = "UPDATE test_packets SET packet_code = '$packet_code', title = '$title', requirement = $requirement WHERE id = '$packet_id'";
            if (mysqli_query($conn, $sql)) {
                $_SESSION['alert'] = ['title' => 'Berhasil!', 'msg' => 'Paket Ujian Berhasil Diperbarui!', 'type' => 'success'];
            } else {
                $_SESSION['alert'] = ['title' => 'Terjadi Kesalahan!', 'msg' => 'Gagal memperbarui: ' . mysqli_error($conn), 'type' => 'error'];
            }
        }
    }
    header("Location: admin_dashboard.php?page=manage_test_package");
    exit;
}

// 2. Logika Hapus Paket
if (isset($_GET['delete_test_packet'])) {
    $id_packet = (int)$_GET['delete_test_packet'];
    
    // Opsional: Anda bisa menambahkan logika cek di sini untuk tidak menghapus paket jika masih ada soal yang terhubung.
    // Tapi untuk sekarang kita eksekusi hapus langsung
    if (mysqli_query($conn, "DELETE FROM test_packets WHERE id = $id_packet")) {
        $_SESSION['alert'] = ['title' => 'Terhapus!', 'msg' => 'Data Paket Ujian berhasil dihapus!', 'type' => 'success'];
        header("Location: admin_dashboard.php?page=manage_test_package");
        exit;
    }
}

// ========================================================
// MEMANGGIL TATA LETAK & KONTEN 
// ========================================================
include 'components/header.php';
include 'components/sidebar.php';
?>

<main class="main-content">
    <?php
    // ========================================================
    // TRIGGER POP-UP JIKA ADA PESAN DI SESSION
    // ========================================================
    if (isset($_SESSION['alert'])) {
        $a_title = $_SESSION['alert']['title'];
        $a_msg = $_SESSION['alert']['msg'];
        $a_type = $_SESSION['alert']['type'];
        
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    rqAlert('$a_title', '$a_msg', '$a_type');
                });
              </script>";
        
        // Bersihkan session agar pop-up tidak muncul lagi saat halaman di-refresh
        unset($_SESSION['alert']);
    }

    // Memanggil file dari folder views/ berdasarkan nilai ?page=
    $view_file = "views/" . $page . ".php";
    
    if (file_exists($view_file)) {
        include $view_file;
    } else {
        echo "<div class='content-header'>
                <h1>404 - Halaman Tidak Ditemukan</h1>
                <p>Maaf, halaman yang Anda cari tidak tersedia di sistem.</p>
              </div>";
    }
    ?>
</main>

<?php
include 'components/footer.php';
?>