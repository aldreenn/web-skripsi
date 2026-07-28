<?php
// ================================================================
// KONFIGURASI DATABASE - InfinityFree
// Ganti nilai di bawah ini dengan kredensial dari panel InfinityFree
// ================================================================
$host = 'sql304.infinityfree.com';
$user = 'if0_42484122';
$pass = 'unpredicted11'; // ← isi password hosting Anda
$db   = 'if0_42484122_readquest';

// Menetapkan Zona Waktu (Timezone) Default ke Waktu Indonesia Barat (WIB)
date_default_timezone_set('Asia/Jakarta');

// Membuat koneksi ke database
$conn = mysqli_connect($host, $user, $pass, $db);

// Mengecek apakah koneksi berhasil atau gagal
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
} else {
    // Sinkronisasi zona waktu MySQL dengan zona waktu PHP (WIB)
    mysqli_query($conn, "SET time_zone = '+07:00'");
    
    // Sinkronisasi encoding karakter ke UTF-8 agar karakter khusus (—, ", dll.) tampil benar
    mysqli_set_charset($conn, "utf8mb4");
    
    // Baris ini sengaja dikosongkan agar tidak mengganggu tampilan UI.
    // Jika ingin mengetes, Anda bisa menghapus tanda // di bawah ini:
    // echo "Koneksi Berhasil!";
}
?>