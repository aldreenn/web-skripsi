<?php
session_start();
include '../config/koneksi.php';

// Pastikan hanya admin yang bisa mengunduh
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

// Menangkap filter jika Admin sedang melakukan pencarian
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$level_filter = isset($_GET['level_filter']) ? mysqli_real_escape_string($conn, $_GET['level_filter']) : '';

$where_clauses = [];
if ($search != '') { $where_clauses[] = "(u.username LIKE '%$search%' OR m.title LIKE '%$search%')"; }
if ($level_filter != '') { $where_clauses[] = "m.level = '$level_filter'"; }
$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Query data nilai
$score_query = "SELECT s.id, u.username AS nama_user, m.title, m.level, s.score, s.created_at 
                FROM practice_scores s 
                JOIN users u ON s.user_id = u.id 
                JOIN materials m ON s.material_id = m.id 
                $where_sql ORDER BY s.created_at DESC";
$score_result = mysqli_query($conn, $score_query);

// Mengatur Header agar browser langsung mendownloadnya sebagai file Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Nilai_Peserta.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <table border="1">
        <thead>
            <tr>
                <th colspan="6" style="font-size: 18px; font-weight: bold; text-align: center; padding: 10px;">Laporan Nilai Kuis - ReadQuest</th>
            </tr>
            <tr>
                <th style="background-color: #f3f4f6;">No</th>
                <th style="background-color: #f3f4f6;">Nama Peserta</th>
                <th style="background-color: #f3f4f6;">Judul Artikel</th>
                <th style="background-color: #f3f4f6;">Level</th>
                <th style="background-color: #f3f4f6;">Skor</th>
                <th style="background-color: #f3f4f6;">Tanggal Selesai</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            if (mysqli_num_rows($score_result) > 0) {
                while ($row = mysqli_fetch_assoc($score_result)) {
                    $tanggal = date("d M Y H:i", strtotime($row['created_at']));
                    echo "<tr>
                            <td>{$no}</td>
                            <td>{$row['nama_user']}</td>
                            <td>{$row['title']}</td>
                            <td>{$row['level']}</td>
                            <td>{$row['score']}</td>
                            <td>{$tanggal}</td>
                          </tr>";
                    $no++;
                }
            } else {
                echo "<tr><td colspan='6'>Tidak ada data.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>