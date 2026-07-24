<?php
// ==============================================================================
// FILE: admin_pages/components/header.php
// FUNGSI: Pembuka struktur HTML global, pemanggilan font, dan CSS untuk halaman admin.
// ==============================================================================
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Dashboard | ReadQuest'; ?></title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    
    <link rel="stylesheet" href="../admin_css/admin_dashboard.css?v=<?php echo time(); ?>">
</head>
<body>

    <div class="admin-container">