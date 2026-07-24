<style>
    /* thead diubah menjadi block dengan padding-right 8px untuk mengisi celah kosong (gap) akibat scrollbar */
    #testQuestionsTable thead {
        display: block;
        width: 100%;
        padding-right: 8px;
        box-sizing: border-box;
        background-color: #1e293b;
        border-bottom: 1px solid #26354a;
    }

    #testQuestionsTable thead tr, #testQuestionsTable tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }

    /* Hilangkan border th karena sudah digantikan oleh border thead agar tidak dobel */
    #testQuestionsTable th {
        border-bottom: none !important;
    }

    .admin-card .table-container {
        overflow-y: hidden !important;
        max-height: none !important;
    }

    #testQuestionsTable tbody {
        display: block;
        max-height: 400px;
        overflow-y: scroll;
    }

    #testQuestionsTable tbody::-webkit-scrollbar-track {
        background: #152238;
    }

    /* Menyesuaikan lebar kolom untuk thead dan tbody */
    #testQuestionsTable th:nth-child(1), #testQuestionsTable td:nth-child(1) { width: 5%; }
    #testQuestionsTable th:nth-child(2), #testQuestionsTable td:nth-child(2) { width: 20%; }
    #testQuestionsTable th:nth-child(3), #testQuestionsTable td:nth-child(3) { width: 18%; }
    #testQuestionsTable th:nth-child(4), #testQuestionsTable td:nth-child(4) { width: 32%; }
    #testQuestionsTable th:nth-child(5), #testQuestionsTable td:nth-child(5) { width: 10%; }
    #testQuestionsTable th:nth-child(6), #testQuestionsTable td:nth-child(6) { width: 15%; text-align: center; }
</style>

<div class="content-header">
    <h1>Manage Test Questions</h1>
    <p>List of all Reciprocal Reading test questions available in the system.</p>
</div>
<div class="admin-card">
    <!-- Fitur Pencarian Realtime -->
    <div class="table-toolbar">
        <div class="search-box">
            <input type="text" id="searchTestQuestionInput" placeholder="Search Packet, Phase, or Question...">
        </div>
    </div>

    <div class="table-container">
        <table class="score-table" id="testQuestionsTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th style="white-space: nowrap;">
                        Text & Packet
                        <span style="font-size: 11px; margin-left: 8px;">
                            <a href="admin_dashboard.php?page=manage_test_questions&sort=paket_asc" style="text-decoration: none; color: #a3e635;" title="Sort Ascending">▲</a>
                            <a href="admin_dashboard.php?page=manage_test_questions&sort=paket_desc" style="text-decoration: none; color: #ef4444;" title="Sort Descending">▼</a>
                        </span>
                    </th>
                    <th style="white-space: nowrap;">
                        Reciprocal Phase
                        <span style="font-size: 11px; margin-left: 8px;">
                            <a href="admin_dashboard.php?page=manage_test_questions&sort=fase_asc" style="text-decoration: none; color: #a3e635;" title="Sort A-Z">▲</a>
                            <a href="admin_dashboard.php?page=manage_test_questions&sort=fase_desc" style="text-decoration: none; color: #ef4444;" title="Sort Z-A">▼</a>
                        </span>
                    </th>
                    <th>Question</th>
                    <th>Key</th>
                    <th style="text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // 1. Menentukan urutan default (Paket A -> Z)
                $order_by = "ORDER BY tp.packet_id ASC, tp.passage_number ASC, tq.id ASC";
                
                // 2. Menangkap parameter 'sort' dari URL jika tombol diklik
                if (isset($_GET['sort'])) {
                    $sort = $_GET['sort'];
                    if ($sort == 'paket_asc') {
                        $order_by = "ORDER BY tp.packet_id ASC, tp.passage_number ASC, tq.id ASC";
                    } elseif ($sort == 'paket_desc') {
                        $order_by = "ORDER BY tp.packet_id DESC, tp.passage_number DESC, tq.id DESC";
                    } elseif ($sort == 'fase_asc') {
                        // Mengurutkan berdasarkan fase alfabetis (C, P, Q, S)
                        $order_by = "ORDER BY tq.reciprocal_phase ASC, tp.packet_id ASC, tp.passage_number ASC";
                    } elseif ($sort == 'fase_desc') {
                        $order_by = "ORDER BY tq.reciprocal_phase DESC, tp.packet_id ASC, tp.passage_number ASC";
                    }
                }

                // 3. Menarik data dengan query yang urutannya sudah dinamis
                $q_sql = "SELECT tq.*, tp.packet_id, tp.passage_number, tp.title 
                          FROM test_questions tq 
                          JOIN test_passages tp ON tq.passage_id = tp.id 
                          $order_by";
                
                $q_query = mysqli_query($conn, $q_sql);
                $no = 1;

                if (mysqli_num_rows($q_query) > 0) {
                    while ($row = mysqli_fetch_assoc($q_query)) {
                        $short_question = strlen($row['question_text']) > 50 ? substr($row['question_text'], 0, 50) . "..." : $row['question_text'];
                        
                        $phase_color = "#94a3b8";
                        if ($row['reciprocal_phase'] == 'predicting') $phase_color = "#3b82f6"; 
                        if ($row['reciprocal_phase'] == 'clarifying') $phase_color = "#a855f7"; 
                        if ($row['reciprocal_phase'] == 'questioning') $phase_color = "#eab308"; 
                        if ($row['reciprocal_phase'] == 'summarizing') $phase_color = "#10b981"; 

                        echo "<tr>";
                        echo "<td>{$no}</td>";
                        echo "<td>
                                <span style='background:#1e293b; color:#a3e635; padding:2px 6px; border-radius:4px; font-size:11px; font-weight:bold;'>Packet {$row['packet_id']} - P{$row['passage_number']}</span><br>
                                <small style='color:#94a3b8;'>{$row['title']}</small>
                              </td>";
                        echo "<td><span style='color: {$phase_color}; font-weight: bold; text-transform: uppercase; font-size: 12px;'>{$row['reciprocal_phase']}</span></td>";
                        echo "<td>{$short_question}</td>";
                        echo "<td><strong style='color:#a3e635;'>{$row['correct_answer']}</strong></td>";
                        echo "<td style='text-align: center; vertical-align: middle;'>
                                <div style='display: flex; justify-content: center; gap: 8px;'>
                                    <a href='admin_dashboard.php?page=edit_test_question&id={$row['id']}' style='display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #3b82f6; border-radius: 6px; font-size: 13px; font-weight: bold; text-decoration: none; color: #ffffff; width: max-content;'>
                                        <span class='material-symbols-outlined' style='font-size: 16px;'>edit</span>
                                    </a>
                                    <a href='javascript:void(0);' style='display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #ef4444; border-radius: 6px; font-size: 13px; font-weight: bold; text-decoration: none; color: #ffffff; width: max-content;' onclick=\"rqConfirm('Are you sure you want to delete this test question?', 'admin_dashboard.php?page=manage_test_questions&delete_test_question={$row['id']}');\">
                                        <span class='material-symbols-outlined' style='font-size: 16px;'>delete_outline</span>
                                    </a>
                                </div>
                              </td>";
                        echo "</tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center; padding: 20px; color: #64748b;'>No test questions inputted yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Logika pencarian realtime untuk 3 kolom
    document.getElementById('searchTestQuestionInput').addEventListener('input', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#testQuestionsTable tbody tr');
        
        rows.forEach(row => {
            // Kolom Teks & Paket (2), Fase Reciprocal (3), Pertanyaan (4)
            let paketCell = row.querySelector('td:nth-child(2)');
            let faseCell = row.querySelector('td:nth-child(3)');
            let pertanyaanCell = row.querySelector('td:nth-child(4)');
            
            if (paketCell && faseCell && pertanyaanCell) {
                let paketText = paketCell.textContent.toLowerCase();
                let faseText = faseCell.textContent.toLowerCase();
                let pertanyaanText = pertanyaanCell.textContent.toLowerCase();
                
                // Cek apakah kata kunci ada di salah satu dari ketiga kolom tersebut
                if (paketText.includes(filter) || faseText.includes(filter) || pertanyaanText.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    });
</script>