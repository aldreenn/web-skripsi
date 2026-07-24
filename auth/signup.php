<?php
// Konfigurasi database - sesuaikan dengan kredensial InfinityFree
$dbHost = 'sql304.infinityfree.com';
$dbPort = 3306;
$dbUser = 'if0_42484122';
$dbPass = 'unpredicted11';
$dbName = 'if0_42484122_readquest';

$tableName   = 'users';

// Helper untuk kirim pesan error dan kembali ke halaman signup
function redirectWithError(string $message, int $statusCode = 400, string $redirect = '/pages/signup.html'): void
{
    http_response_code($statusCode);
    $encodedMessage = urlencode($message);
    header("Location: {$redirect}?error={$encodedMessage}");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithError('Metode harus POST.', 405);
}

// 1. Menangkap SEMUA input dari form
$username   = isset($_POST['username']) ? trim($_POST['username']) : '';
$password   = isset($_POST['password']) ? $_POST['password'] : '';
$confirm    = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

$first_name_raw = isset($_POST['first_name']) ? trim($_POST['first_name']) : ''; 
$last_name_raw  = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';   

// 2. Merapikan format nama menjadi Huruf Kapital di Awal (Title Case)
$first_name = ucwords(strtolower($first_name_raw));
$last_name  = ucwords(strtolower($last_name_raw));

// 3. Validasi: Pastikan tidak ada yang kosong
if ($username === '' || $first_name === '' || $last_name === '' || $password === '' || $confirm === '') {
    redirectWithError('Semua field wajib diisi.');
}

// 4. Validasi: Pastikan password dan konfirmasi cocok
if ($password !== $confirm) {
    redirectWithError('Konfirmasi password tidak cocok.');
}

// Hash password untuk disimpan aman
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
if ($mysqli->connect_error) {
    redirectWithError('Gagal koneksi database: ' . $mysqli->connect_error, 500);
}

// Cek duplikasi username
$checkSql = "SELECT 1 FROM {$tableName} WHERE username = ? LIMIT 1";
$checkStmt = $mysqli->prepare($checkSql);
if (!$checkStmt) {
    redirectWithError('Gagal menyiapkan query: ' . $mysqli->error, 500);
}
$checkStmt->bind_param('s', $username);
$checkStmt->execute();
$exists = $checkStmt->get_result()->fetch_row();
$checkStmt->close();

if ($exists) {
    $mysqli->close();
    redirectWithError('Username sudah digunakan, silakan pilih yang lain.', 409);
}

// Insert user baru dengan First Name dan Last Name yang sudah rapi
$insertSql = "INSERT INTO {$tableName} (username, first_name, last_name, password) VALUES (?, ?, ?, ?)";
$insertStmt = $mysqli->prepare($insertSql);
if (!$insertStmt) {
    redirectWithError('Gagal menyiapkan insert: ' . $mysqli->error, 500);
}

// Bind parameter (s s s s = string, string, string, string)
$insertStmt->bind_param('ssss', $username, $first_name, $last_name, $passwordHash);
$ok = $insertStmt->execute();
$insertStmt->close();
$mysqli->close();

if (!$ok) {
    redirectWithError('Gagal menyimpan data pengguna.', 500);
}

// Jika sukses, redirect ke halaman login
header('Location: ../pages/loginpage.html');
exit;