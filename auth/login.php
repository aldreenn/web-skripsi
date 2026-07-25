<?php
// Konfigurasi database - sesuaikan dengan kredensial InfinityFree
$dbHost = 'sql304.infinityfree.com';
$dbPort = 3306;
$dbUser = 'if0_42484122';
$dbPass = 'unpredicted11';
$dbName = 'if0_42484122_readquest';

// Nama tabel dan kolom (ubah sesuai skema Anda)
$tableName    = 'users';
$usernameCol  = 'username';
$passwordCol  = 'password';   // kolom yang berisi password

// Validasi nama tabel/kolom sederhana untuk menghindari injeksi pada identifier
foreach ([$tableName, $usernameCol, $passwordCol] as $identifier) {
    if (!preg_match('/^[A-Za-z0-9_]+$/', $identifier)) {
        http_response_code(500);
        exit('Konfigurasi nama tabel/kolom tidak valid.');
    }
}

// Helper untuk kirim pesan error lewat query string dan tetap di loginpage
function redirectWithError(string $message, int $statusCode = 400, string $redirect = '/pages/loginpage.html'): void
{
    http_response_code($statusCode);
    $encodedMessage = urlencode($message);
    header("Location: {$redirect}?error={$encodedMessage}");
    exit;
}

// Pastikan request berupa POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/loginpage.html');
    exit;
}

// Ambil input
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($username === '' || $password === '') {
    redirectWithError('Username dan password wajib diisi.');
}

// Koneksi ke database
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);

if ($mysqli->connect_error) {
    redirectWithError('Gagal koneksi database: ' . $mysqli->connect_error, 500);
}

// ========================================================
// UPDATE: Menambahkan first_name dan last_name ke dalam Query
// ========================================================
$sql = "SELECT id, {$usernameCol} AS username, first_name, last_name, {$passwordCol} AS password_plain, role
        FROM {$tableName}
        WHERE {$usernameCol} = ?
        LIMIT 1";

$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    redirectWithError('Gagal menyiapkan query: ' . $mysqli->error, 500);
}

$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$userRow = $result->fetch_assoc();

$stmt->close();
$mysqli->close();

// Cek username & password
$storedPassword = $userRow ? $userRow['password_plain'] : null;
$isValid = false;
if ($storedPassword !== null) {
    if (password_verify($password, $storedPassword) || $password === $storedPassword) {
        $isValid = true;
    }
}

if (!$userRow || !$isValid) {
    redirectWithError('Username atau password salah', 401);
}

// ========================================================
// UPDATE: Login berhasil, simpan nama ke dalam Session
// ========================================================
session_start();
$_SESSION['user_id'] = $userRow['id'];
$_SESSION['username'] = $userRow['username'];
$_SESSION['first_name'] = $userRow['first_name']; // Menyimpan First Name
$_SESSION['last_name'] = $userRow['last_name'];   // Menyimpan Last Name
$_SESSION['role'] = $userRow['role']; 

// Cegah cache agar halaman login tidak bisa di-back
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Arahkan ke halaman berdasarkan Role
if ($userRow['role'] === 'admin') {
    header('Location: ../admin_pages/admin_dashboard.php?page=overview');
} else {
    header('Location: ../pages/dashboard.php');
}
exit;
?>