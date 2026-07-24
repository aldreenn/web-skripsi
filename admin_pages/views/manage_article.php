<style>
    /* thead diubah menjadi block dengan padding-right 8px untuk mengisi celah kosong (gap) akibat scrollbar */
    #articleTable thead {
        display: block;
        width: 100%;
        padding-right: 8px;
        box-sizing: border-box;
        background-color: #1e293b;
        border-bottom: 1px solid #26354a;
    }

    #articleTable thead tr, #articleTable tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }

    /* Hilangkan border th karena sudah digantikan oleh border thead agar tidak dobel */
    #articleTable th {
        border-bottom: none !important;
    }

    .admin-card .table-container {
        overflow-y: hidden !important;
        max-height: none !important;
    }

    #articleTable tbody {
        display: block;
        max-height: 400px;
        overflow-y: scroll;
    }

    #articleTable tbody::-webkit-scrollbar-track {
        background: #152238;
    }

    /* Menyesuaikan lebar kolom untuk thead dan tbody */
    #articleTable th:nth-child(1), #articleTable td:nth-child(1) { width: 5%; }
    #articleTable th:nth-child(2), #articleTable td:nth-child(2) { width: 33%; }
    #articleTable th:nth-child(3), #articleTable td:nth-child(3) { width: 20%; }
    #articleTable th:nth-child(4), #articleTable td:nth-child(4) { width: 13%; }
    #articleTable th:nth-child(5), #articleTable td:nth-child(5) { width: 14%; }
    #articleTable th:nth-child(6), #articleTable td:nth-child(6) { width: 15%; text-align: center; }
</style>

<div class="content-header">
    <h1>Manage Existing Articles</h1>
    <p>Here is the list of active reading articles/visual questions in the system. You can edit or delete them.</p>
</div>
<div class="admin-card">
    <!-- Fitur Pencarian Realtime -->
    <div class="table-toolbar">
        <div class="search-box">
            <input type="text" id="searchArticleInput" placeholder="Search Article Title, Level, or Folder...">
        </div>
    </div>

    <div class="table-container">
        <table class="score-table" id="articleTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Article Title</th>
                    <th>Level & Folder</th>
                    <th>Estimated Time</th>
                    <th>Total Questions</th>
                    <th style="text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $mat_query = mysqli_query($conn, "SELECT * FROM materials ORDER BY level ASC, topic ASC, id DESC");
                $no = 1;
                if (mysqli_num_rows($mat_query) > 0) {
                    while ($m_row = mysqli_fetch_assoc($mat_query)) {
                        $current_id = $m_row['id'];
                        $count_query = mysqli_query($conn, "SELECT COUNT(*) AS total_soal FROM questions WHERE material_id = '$current_id'");
                        $count_data = mysqli_fetch_assoc($count_query);
                        $jumlah_soal = $count_data['total_soal'];

                        echo "<tr>";
                        echo "<td>{$no}</td>";
                        echo "<td class='font-semibold'>{$m_row['title']}</td>";
                        echo "<td><span class='badge' style='background:#f3f4f6; color:#1f2937; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:bold;'>{$m_row['level']}</span> <span style='color:#6b7280; font-size:13px;'>/ {$m_row['topic']}</span></td>";
                        echo "<td>{$m_row['reading_time']}</td>";
                        echo "<td><strong>{$jumlah_soal} Questions</strong></td>"; 
                        echo "<td style='text-align: center; vertical-align: middle;'>
                                <div style='display: flex; justify-content: center; gap: 8px;'>
                                    <a href='admin_dashboard.php?page=edit_article&id={$m_row['id']}' style='display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #3b82f6; border-radius: 6px; font-size: 13px; font-weight: bold; text-decoration: none; color: #ffffff; width: max-content;'>
                                        <span class='material-symbols-outlined' style='font-size: 16px;'>edit</span>
                                    </a>
                                    <a href='javascript:void(0);' style='display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #ef4444; border-radius: 6px; font-size: 13px; font-weight: bold; text-decoration: none; color: #ffffff; width: max-content;' onclick=\"rqConfirm('Deleting this article will remove all quizzes and score history inside it. Continue?', 'admin_dashboard.php?page=manage_article&delete_material={$m_row['id']}');\">
                                        <span class='material-symbols-outlined' style='font-size: 16px;'>delete_outline</span>
                                    </a>
                                </div>
                              </td>";
                        echo "</tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center; padding: 20px; color: #6b7280;'>No articles/materials inputted yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Logika pencarian realtime
    document.getElementById('searchArticleInput').addEventListener('input', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#articleTable tbody tr');
        
        rows.forEach(row => {
            let titleCell = row.querySelector('td:nth-child(2)');
            let levelCell = row.querySelector('td:nth-child(3)');
            
            if (titleCell && levelCell) {
                let titleText = titleCell.textContent.toLowerCase();
                let levelText = levelCell.textContent.toLowerCase();
                
                if (titleText.includes(filter) || levelText.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    });
</script>