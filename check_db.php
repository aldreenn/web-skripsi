<?php
include 'config/koneksi.php';
$res = mysqli_query($conn, "SELECT ps.id, ps.user_id, ps.material_id, ps.score, ps.created_at, m.title FROM practice_scores ps LEFT JOIN materials m ON ps.material_id = m.id");
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
?>
