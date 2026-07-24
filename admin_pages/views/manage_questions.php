<style>
    /* thead diubah menjadi block dengan padding-right 8px untuk mengisi celah kosong (gap) akibat scrollbar */
    #questionsTable thead {
        display: block;
        width: 100%;
        padding-right: 8px;
        box-sizing: border-box;
        background-color: #1e293b;
        border-bottom: 1px solid #26354a;
    }

    #questionsTable thead tr, #questionsTable tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }

    /* Hilangkan border th karena sudah digantikan oleh border thead agar tidak dobel */
    #questionsTable th {
        border-bottom: none !important;
    }

    .admin-card .table-container {
        overflow-y: hidden !important;
        max-height: none !important;
    }

    #questionsTable tbody {
        display: block;
        max-height: 400px;
        overflow-y: scroll;
    }

    #questionsTable tbody::-webkit-scrollbar-track {
        background: #152238;
    }

    /* Menyesuaikan lebar kolom untuk thead dan tbody */
    #questionsTable th:nth-child(1), #questionsTable td:nth-child(1) { width: 5%; }
    #questionsTable th:nth-child(2), #questionsTable td:nth-child(2) { width: 25%; }
    #questionsTable th:nth-child(3), #questionsTable td:nth-child(3) { width: 20%; }
    #questionsTable th:nth-child(4), #questionsTable td:nth-child(4) { width: 35%; }
    #questionsTable th:nth-child(5), #questionsTable td:nth-child(5) { width: 15%; text-align: center; }
</style>

<div class="content-header">
    <h1>Manage Quiz Questions</h1>
    <p>Here is the entire list of quiz questions connected to your materials.</p>
</div>
<div class="admin-card">
    <!-- Fitur Pencarian Realtime -->
    <div class="table-toolbar">
        <div class="search-box">
            <input type="text" id="searchQuestionInput" placeholder="Search Question, Level, Folder, or Material...">
        </div>
    </div>

    <div class="table-container">
        <table class="score-table" id="questionsTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Related Material</th>
                    <th>Level & Folder</th>
                    <th>Quiz Question</th>
                    <th style="text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q_list_query = "SELECT q.id, q.question_text, m.title, m.level, m.topic 
                                 FROM questions q 
                                 JOIN materials m ON q.material_id = m.id 
                                 ORDER BY m.level ASC, m.topic ASC, q.id ASC";
                                 
                $q_list_res = mysqli_query($conn, $q_list_query);
                $no = 1;

                if (mysqli_num_rows($q_list_res) > 0) {
                    while ($q_row = mysqli_fetch_assoc($q_list_res)) {
                        echo "<tr>";
                        echo "<td>{$no}</td>";
                        echo "<td class='font-semibold'>{$q_row['title']}</td>";
                        echo "<td><span class='badge' style='background:#f3f4f6; color:#1f2937; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:bold;'>{$q_row['level']}</span> <span style='color:#6b7280; font-size:13px;'>/ {$q_row['topic']}</span></td>";
                        echo "<td>" . htmlspecialchars($q_row['question_text']) . "</td>";
                        echo "<td style='text-align: center; vertical-align: middle;'>
                                <div style='display: flex; justify-content: center; gap: 8px;'>
                                    <a href='admin_dashboard.php?page=edit_question&id={$q_row['id']}' style='display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #3b82f6; border-radius: 6px; font-size: 13px; font-weight: bold; text-decoration: none; color: #ffffff; width: max-content;'>
                                        <span class='material-symbols-outlined' style='font-size: 16px;'>edit</span>
                                    </a>
                                    <a href='javascript:void(0);' style='display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #ef4444; border-radius: 6px; font-size: 13px; font-weight: bold; text-decoration: none; color: #ffffff; width: max-content;' onclick=\"rqConfirm('Are you sure you want to permanently delete this question?', 'admin_dashboard.php?page=manage_questions&delete_question={$q_row['id']}');\">
                                        <span class='material-symbols-outlined' style='font-size: 16px;'>delete_outline</span>
                                    </a>
                                </div>
                              </td>";
                        echo "</tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center; padding:20px; color:#6b7280;'>No quiz questions inputted yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Logika pencarian realtime
    document.getElementById('searchQuestionInput').addEventListener('input', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#questionsTable tbody tr');
        
        rows.forEach(row => {
            // Mengambil kolom ke-2 (Materi), ke-3 (Level/Folder), ke-4 (Pertanyaan)
            let materiCell = row.querySelector('td:nth-child(2)');
            let levelCell = row.querySelector('td:nth-child(3)');
            let pertanyaanCell = row.querySelector('td:nth-child(4)');
            
            if (materiCell && levelCell && pertanyaanCell) {
                let materiText = materiCell.textContent.toLowerCase();
                let levelText = levelCell.textContent.toLowerCase();
                let pertanyaanText = pertanyaanCell.textContent.toLowerCase();
                
                // Cek apakah kata kunci ada di salah satu dari ketiga kolom tersebut
                if (materiText.includes(filter) || levelText.includes(filter) || pertanyaanText.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    });
</script>