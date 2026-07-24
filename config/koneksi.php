<?php
// Konfigurasi Database (Default bawaan XAMPP)
$host = "localhost";
$user = "root";       // Username default phpMyAdmin adalah root
$pass = "";           // Password default phpMyAdmin biasanya kosong
$db   = "aplikasi_skripsi"; // Nama database yang sudah Anda buat

// Membuat koneksi ke database
$conn = mysqli_connect($host, $user, $pass, $db);

// Mengecek apakah koneksi berhasil atau gagal
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
} else {
    // Baris ini sengaja dikosongkan agar tidak mengganggu tampilan UI.
    // Jika ingin mengetes, Anda bisa menghapus tanda // di bawah ini:
    // echo "Koneksi Berhasil!";
}
?>