<?php
// ==============================================================================
// FILE: admin_pages/components/sidebar.php
// FUNGSI: Menampilkan menu navigasi sebelah kiri dengan section Test baru.
// ==============================================================================

// Pastikan variabel $page tersedia untuk menandai menu yang sedang aktif
$page = isset($_GET['page']) ? $_GET['page'] : 'add_article';
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <h1>Admin Dashboard</h1>
    </div>
    
    <ul class="sidebar-menu" id="sidebarScrollArea" style="overflow-y: auto; padding-bottom: 20px; opacity: 0; transition: opacity 0.2s ease-in;">
        <li>
            <a href="admin_dashboard.php?page=overview" class="sidebar-link <?= ($page == 'overview') ? 'active' : '' ?>" style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                <span class="material-symbols-outlined">dashboard</span> Overview
            </a>
        </li>
        <li style="padding: 0px 20px 5px 20px; font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 1px; margin-top: 0px;">
                Practice Section
        </li>
        <li>
            <a href="?page=manage_topics" class="<?= ($page == 'manage_topics') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined"> folder </span> Manage Topics Folder
            </a>
        </li>
        <li>
            <a href="?page=add_article" class="<?= ($page == 'add_article') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined"> add </span> Add Article
            </a>
        </li>
        <li>
            <a href="?page=manage_article" class="<?= ($page == 'manage_article' || $page == 'edit_article') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined"> settings </span> Manage Article
            </a>
        </li>
        <li>
            <a href="?page=add_question" class="<?= ($page == 'add_question') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined"> add </span> Add Question
            </a>
        </li>
        <li>
            <a href="?page=manage_questions" class="<?= ($page == 'manage_questions' || $page == 'edit_question') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined"> settings </span> Manage Questions
            </a>
        </li>

        <li style="padding: 15px 20px 5px 20px; font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 1px; border-top: 1px solid #f3f4f6; margin-top: 5px;">
            Test Section
        </li>
        <li>
            <a href="?page=manage_test_package" class="<?= ($page == 'manage_test_package') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined"> inventory_2 </span> Manage Test Package
            </a>
        </li>
        <li>
            <a href="?page=add_test_passage" class="<?= ($page == 'add_test_passage') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined"> post_add </span> Add Test Passage
            </a>
        </li>
        <li>
            <a href="?page=manage_test_passage" class="<?= ($page == 'manage_test_passage' || $page == 'edit_test_passage') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined"> article </span> Manage Test Passage
            </a>
        </li>
        <li>
            <a href="?page=add_test_question" class="<?= ($page == 'add_test_question') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined"> quiz </span> Add Test Question
            </a>
        </li>
        <li>
            <a href="?page=manage_test_questions" class="<?= ($page == 'manage_test_questions' || $page == 'edit_test_question') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined"> rule_folder </span> Manage Test Questions
            </a>
        </li>

        <li style="padding: 15px 20px 5px 20px; font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 1px; border-top: 1px solid #f3f4f6; margin-top: 5px;">
            Reports & Data
        </li>
        <li>
            <a href="?page=view_scores" class="<?= ($page == 'view_scores') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined"> table_eye </span> View User Score
            </a>
        </li>

    </ul>

    </ul> <script>
        (function() {
            var sidebar = document.getElementById('sidebarScrollArea');
            var savedScrollPos = sessionStorage.getItem('sidebarScrollPosition');
            if (sidebar && savedScrollPos) {
                // Set posisi instan sebelum browser menggambar layar
                sidebar.scrollTop = savedScrollPos;
            }
            
            // Simpan posisi saat akan pindah halaman
            window.addEventListener('beforeunload', function() {
                sessionStorage.setItem('sidebarScrollPosition', sidebar.scrollTop);
            });
        })();
    </script>

    <div class="sidebar-footer">
        <a href="/aplikasi_skripsi/auth/logout.php" class="logout-btn">Logout</a>
    </div>
</aside>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const sidebarMenu = document.getElementById('sidebarScrollArea');

        if (sidebarMenu) {
            const savedScrollPos = sessionStorage.getItem('sidebarScrollPosition');
            
            if (savedScrollPos) {
                // Atur posisi secara instan
                sidebarMenu.scrollTop = savedScrollPos;
            }

            // Setelah posisi diatur secara rahasia, munculkan sidebar dengan halus
            requestAnimationFrame(() => {
                sidebarMenu.style.opacity = '1';
            });

            // Simpan posisi saat di-scroll
            sidebarMenu.addEventListener('scroll', function() {
                sessionStorage.setItem('sidebarScrollPosition', sidebarMenu.scrollTop);
            });
        }
    });
</script>