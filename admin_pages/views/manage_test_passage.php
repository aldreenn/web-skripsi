<style>
    /* thead diubah menjadi block dengan padding-right 8px untuk mengisi celah kosong (gap) akibat scrollbar */
    #testPassageTable thead {
        display: block;
        width: 100%;
        padding-right: 8px;
        box-sizing: border-box;
        background-color: #1e293b;
        border-bottom: 1px solid #26354a;
    }

    #testPassageTable thead tr, #testPassageTable tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }

    /* Hilangkan border th karena sudah digantikan oleh border thead agar tidak dobel */
    #testPassageTable th {
        border-bottom: none !important;
    }

    .admin-card .table-container {
        overflow-y: hidden !important;
        max-height: none !important;
    }

    #testPassageTable tbody {
        display: block;
        max-height: 400px;
        overflow-y: scroll;
    }

    #testPassageTable tbody::-webkit-scrollbar-track {
        background: #152238;
    }

    /* Menyesuaikan lebar kolom untuk thead dan tbody */
    #testPassageTable th:nth-child(1), #testPassageTable td:nth-child(1) { width: 5%; }
    #testPassageTable th:nth-child(2), #testPassageTable td:nth-child(2) { width: 15%; }
    #testPassageTable th:nth-child(3), #testPassageTable td:nth-child(3) { width: 15%; }
    #testPassageTable th:nth-child(4), #testPassageTable td:nth-child(4) { width: 35%; }
    #testPassageTable th:nth-child(5), #testPassageTable td:nth-child(5) { width: 15%; }
    #testPassageTable th:nth-child(6), #testPassageTable td:nth-child(6) { width: 15%; text-align: center; }
</style>

<div class="content-header">
    <h1>Manage Test Passages</h1>
    <p>Here is the list of reading texts used for the Reciprocal Reading Test mode.</p>
</div>
<div class="admin-card">
    <!-- Fitur Pencarian Realtime -->
    <div class="table-toolbar">
        <div class="search-box">
            <input type="text" id="searchTestPassageInput" placeholder="Search Packet, Order, or Text Title...">
        </div>
    </div>

    <div class="table-container">
        <table class="score-table" id="testPassageTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Test Packet</th>
                    <th>Order</th>
                    <th>Text Title</th>
                    <th>Total Questions</th>
                    <th style="text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Memanggil data dari tabel test_passages dan mengurutkannya berdasarkan Paket lalu Nomor urut
                $mat_query = mysqli_query($conn, "SELECT * FROM test_passages ORDER BY packet_id ASC, passage_number ASC");
                $no = 1;
                
                if (mysqli_num_rows($mat_query) > 0) {
                    while ($m_row = mysqli_fetch_assoc($mat_query)) {
                        $current_id = $m_row['id'];
                        
                        // Menghitung jumlah soal dari tabel test_questions
                        $count_query = mysqli_query($conn, "SELECT COUNT(*) AS total_soal FROM test_questions WHERE passage_id = '$current_id'");
                        $count_data = mysqli_fetch_assoc($count_query);
                        $jumlah_soal = $count_data['total_soal'] ? $count_data['total_soal'] : 0;

                        echo "<tr>";
                        echo "<td>{$no}</td>";
                        // Desain badge khusus untuk Paket Ujian
                        echo "<td><span style='background:#1e293b; color:#a3e635; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:bold; border: 1px solid #334155;'>Packet {$m_row['packet_id']}</span></td>";
                        echo "<td>Passage {$m_row['passage_number']}</td>";
                        echo "<td class='font-semibold'>{$m_row['title']}</td>";
                        echo "<td><strong>{$jumlah_soal} Questions</strong></td>"; 
                        echo "<td style='text-align: center; vertical-align: middle;'>
                                <div style='display: flex; justify-content: center; gap: 8px;'>
                                    <a href='admin_dashboard.php?page=edit_test_passage&id={$m_row['id']}' style='display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #3b82f6; border-radius: 6px; font-size: 13px; font-weight: bold; text-decoration: none; color: #ffffff; width: max-content;'>
                                        <span class='material-symbols-outlined' style='font-size: 16px;'>edit</span>
                                    </a>
                                    <a href='javascript:void(0);' style='display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #ef4444; border-radius: 6px; font-size: 13px; font-weight: bold; text-decoration: none; color: #ffffff; width: max-content;' onclick=\"rqConfirm('Deleting this passage will remove all related test questions. Continue?', 'admin_dashboard.php?page=manage_test_materials&delete_test_material={$m_row['id']}');\">
                                        <span class='material-symbols-outlined' style='font-size: 16px;'>delete_outline</span>
                                    </a>
                                </div>
                              </td>";
                        echo "</tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center; padding: 20px; color: #64748b;'>No test passages inputted yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Logika pencarian realtime untuk 3 kolom
    document.getElementById('searchTestPassageInput').addEventListener('input', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#testPassageTable tbody tr');
        
        rows.forEach(row => {
            // Kolom Paket Ujian (2), Urutan (3), Judul Teks (4)
            let paketCell = row.querySelector('td:nth-child(2)');
            let urutanCell = row.querySelector('td:nth-child(3)');
            let judulCell = row.querySelector('td:nth-child(4)');
            
            if (paketCell && urutanCell && judulCell) {
                let paketText = paketCell.textContent.toLowerCase();
                let urutanText = urutanCell.textContent.toLowerCase();
                let judulText = judulCell.textContent.toLowerCase();
                
                // Cek apakah kata kunci ada di salah satu dari ketiga kolom tersebut
                if (paketText.includes(filter) || urutanText.includes(filter) || judulText.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    });
</script>