<?php
include 'config/koneksi.php';
$res = mysqli_query($conn, "SELECT id, question_text, option_a, option_b, option_c, option_d, correct_answer FROM questions WHERE material_id = 36");
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
?>
