<style>
    /* Memisahkan scrollbar agar hanya ada pada tbody dan tidak menembus thead */
    .admin-card .table-container {
        overflow-y: hidden !important;
        max-height: none !important;
    }

    #topicsTable tbody {
        display: block;
        max-height: 400px;
        overflow-y: scroll;
    }

    /* thead diubah menjadi block dengan padding-right 8px untuk mengisi celah kosong (gap) akibat scrollbar */
    #topicsTable thead {
        display: block;
        width: 100%;
        padding-right: 8px;
        box-sizing: border-box;
        background-color: #1e293b;
        border-bottom: 1px solid #26354a;
    }

    #topicsTable thead tr, #topicsTable tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }

    /* Hilangkan border th karena sudah digantikan oleh border thead agar tidak dobel */
    #topicsTable th {
        border-bottom: none !important;
    }

    #topicsTable tbody::-webkit-scrollbar-track {
        background: #152238;
    }

    /* Menyesuaikan lebar kolom untuk thead dan tbody karena sekarang terpisah layout-nya */
    #topicsTable th:nth-child(1), #topicsTable td:nth-child(1) { width: 5%; }
    #topicsTable th:nth-child(2), #topicsTable td:nth-child(2) { width: 15%; }
    #topicsTable th:nth-child(3), #topicsTable td:nth-child(3) { width: 60%; }
    #topicsTable th:nth-child(4), #topicsTable td:nth-child(4) { width: 20%; }
</style>

<div class="content-header">
    <h1>Manage Topics Folder </h1>
    <p>Create and manage reading category folders to display in the application.</p>
</div>
<div class="admin-card" style="margin-bottom: 20px;">
    <h3 style="color: #a3e635; margin-top: 0; margin-bottom: 15px; font-size: 18px;">Create a New Folder</h3>
    <form action="admin_dashboard.php?page=manage_topics" method="POST" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
        <div class="form-group" style="flex: 1; min-width: 120px; margin: 0;">
            <label style="display: block; margin-bottom: 5px;">Select Level</label>
            <select name="level_topic" required style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box;">
                <option value="A1">Level A1</option>
                <option value="A2">Level A2</option>
                <option value="B1">Level B1</option>
                <option value="B2">Level B2</option>
                <option value="C1">Level C1</option>
                <option value="C2">Level C2</option>
            </select>
        </div>
        <div class="form-group" style="flex: 2; min-width: 200px; margin: 0;">
            <label style="display: block; margin-bottom: 5px;">Topic Folder Name</label>
            <input type="text" name="topic_name" placeholder="E.g.: Family, Science, History..." required style="padding: 10px; width: 100%; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box;">
        </div>
        <button type="submit" name="submit_topic" class="submit-btn" style="width: auto; flex-shrink: 0; padding: 0 30px; height: 42px; margin: 0;">+ Create Folder</button>
    </form>
</div>

<div class="admin-card">
    <!-- Fitur Pencarian Realtime -->
    <div class="table-toolbar">
        <div class="search-box">
            <input type="text" id="searchTopicInput" placeholder="Search Level or Topic Folder Name...">
        </div>
    </div>

    <div class="table-container">
        <table class="score-table" id="topicsTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Level</th>
                    <th>Topic Folder Name</th>
                    <th style="text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $topic_query = mysqli_query($conn, "SELECT * FROM topics ORDER BY level ASC, topic_name ASC");
                $no = 1;
                if (mysqli_num_rows($topic_query) > 0) {
                    while ($row = mysqli_fetch_assoc($topic_query)) {
                        echo "<tr>";
                        echo "<td>{$no}</td>";
                        echo "<td><strong>{$row['level']}</strong></td>";
                        echo "<td>{$row['topic_name']}</td>";
                        $safe_topic_name = htmlspecialchars($row['topic_name'], ENT_QUOTES);
                        echo "<td style='text-align: center; vertical-align: middle;'>
                                <div style='display: flex; justify-content: center; gap: 8px;'>
                                    <a href='javascript:void(0);' style='display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #3b82f6; border-radius: 6px; font-size: 13px; font-weight: bold; text-decoration: none; color: #ffffff; width: max-content;' onclick=\"showEditTopicModal('{$row['id']}', '{$row['level']}', '{$safe_topic_name}');\">
                                        <span class='material-symbols-outlined' style='font-size: 16px;'>edit</span>
                                    </a>
                                    <a href='javascript:void(0);' style='display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #ef4444; border-radius: 6px; font-size: 13px; font-weight: bold; text-decoration: none; color: #ffffff; width: max-content;' onclick=\"rqConfirm('Are you sure you want to delete this folder and all materials inside it?', 'admin_dashboard.php?page=manage_topics&delete_topic={$row['id']}');\">
                                        <span class='material-symbols-outlined' style='font-size: 16px;'>delete_outline</span>
                                    </a>
                                </div>
                              </td>";
                        echo "</tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center;'>No topics created yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit Topik -->
<div id="edit-topic-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(11, 19, 34, 0.8); z-index: 9999; justify-content: center; align-items: center; backdrop-filter: blur(4px); opacity: 0; transition: opacity 0.3s ease;">
    <div style="background: #152238; border: 1px solid #26354a; border-radius: 16px; padding: 30px; width: 90%; max-width: 400px; text-align: left; box-shadow: 0 10px 30px rgba(0,0,0,0.5); transform: translateY(-20px); transition: transform 0.3s ease;" id="edit-topic-box">
        <h3 style="color: #ffffff; margin-bottom: 20px; font-size: 20px; text-align: center;">Edit Topic Folder</h3>
        <form action="admin_dashboard.php?page=manage_topics" method="POST">
            <input type="hidden" name="edit_topic_id" id="edit_topic_id">
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; color: #94a3b8;">Level</label>
                <select name="edit_level" id="edit_level" required style="padding: 10px; width: 100%; border: 1px solid #26354a; background: #0b1322; color: #ffffff; border-radius: 6px; box-sizing: border-box; font-family: inherit;">
                    <option value="A1">Level A1</option>
                    <option value="A2">Level A2</option>
                    <option value="B1">Level B1</option>
                    <option value="B2">Level B2</option>
                    <option value="C1">Level C1</option>
                    <option value="C2">Level C2</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 25px;">
                <label style="display: block; margin-bottom: 5px; color: #94a3b8;">Topic Folder Name</label>
                <input type="text" name="edit_topic_name" id="edit_topic_name" required style="padding: 10px; width: 100%; border: 1px solid #26354a; background: #0b1322; color: #ffffff; border-radius: 6px; box-sizing: border-box; font-family: inherit;">
            </div>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <button type="button" onclick="closeEditTopicModal()" class="submit-btn" style="background: transparent; border: 1px solid #26354a; color: #ffffff; flex: 1; margin: 0; padding: 12px; box-sizing: border-box; cursor: pointer; width: 100%;">Cancel</button>
                <button type="submit" name="update_topic" class="submit-btn" style="flex: 1; margin: 0; padding: 12px; box-sizing: border-box; border: none; cursor: pointer; display: block; width: 100%;">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    function showEditTopicModal(id, level, name) {
        document.getElementById('edit_topic_id').value = id;
        document.getElementById('edit_level').value = level;
        document.getElementById('edit_topic_name').value = name;
        
        const modal = document.getElementById('edit-topic-modal');
        const box = document.getElementById('edit-topic-box');
        
        modal.style.display = 'flex';
        setTimeout(() => {
            modal.style.opacity = '1';
            box.style.transform = 'translateY(0)';
        }, 10);
    }

    function closeEditTopicModal() {
        const modal = document.getElementById('edit-topic-modal');
        const box = document.getElementById('edit-topic-box');
        
        modal.style.opacity = '0';
        box.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }

    // Logika pencarian realtime
    document.getElementById('searchTopicInput').addEventListener('input', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#topicsTable tbody tr');
        
        rows.forEach(row => {
            let levelCell = row.querySelector('td:nth-child(2)');
            let nameCell = row.querySelector('td:nth-child(3)');
            
            if (levelCell && nameCell) {
                let levelText = levelCell.textContent.toLowerCase();
                let nameText = nameCell.textContent.toLowerCase();
                
                if (levelText.includes(filter) || nameText.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    });
</script>