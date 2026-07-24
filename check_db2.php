<?php
include 'config/koneksi.php';
$res = mysqli_query($conn, "SELECT id, title, level FROM materials WHERE title LIKE '%Playing Sports with Friends%'");
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
?>
