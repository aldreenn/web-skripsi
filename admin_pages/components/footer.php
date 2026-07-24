<?php
// ==============================================================================
// FILE: admin_pages/components/footer.php
// FUNGSI: Penutup struktur tata letak, Script Bawaan, dan Sistem Custom Modal
// ==============================================================================
?>
        </main> <!-- Penutup tag <main class="main-content"> -->
    </div> <!-- Penutup tag <div class="admin-container"> -->

    <!-- ==========================================
         CUSTOM POP-UP MODAL (READQUEST THEME)
    =========================================== -->
    <div id="rq-modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(11, 19, 34, 0.8); z-index: 9999; justify-content: center; align-items: center; backdrop-filter: blur(4px); opacity: 0; transition: opacity 0.3s ease;">
        <div id="rq-modal-box" style="background: #152238; border: 1px solid #26354a; border-radius: 16px; padding: 30px; width: 90%; max-width: 400px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.5); transform: translateY(-20px); transition: transform 0.3s ease;">
            <!-- Ikon Pop-up -->
            <div id="rq-modal-icon" style="font-size: 48px; margin-bottom: 15px;"></div>
            <!-- Judul & Pesan -->
            <h3 id="rq-modal-title" style="color: #ffffff; margin-bottom: 10px; font-size: 22px;">Title</h3>
            <p id="rq-modal-desc" style="color: #94a3b8; font-size: 15px; margin-bottom: 25px; line-height: 1.5;"></p>
            <!-- Tombol Aksi -->
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button id="rq-btn-cancel" style="display: none; background: transparent; border: 1px solid #26354a; color: #ffffff; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; width: 100%;">Batal</button>
                <button id="rq-btn-ok" style="background: #a3e635; border: none; color: #0b1322; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 700; width: 100%;">OK</button>
            </div>
        </div>
    </div>

    <script>
        // --- LOGIKA DROPDOWN BAWAAN ---
        document.addEventListener("DOMContentLoaded", function() {
            const levelSelect = document.getElementById('levelDropdown');
            const topicSelect = document.getElementById('topicDropdown');
            if(levelSelect && topicSelect) {
                const allTopics = Array.from(topicSelect.querySelectorAll('option'));
                function updateTopics() {
                    const selectedLevel = levelSelect.value;
                    const savedTopic = topicSelect.getAttribute('data-selected-topic'); 
                    topicSelect.innerHTML = '';
                    topicSelect.appendChild(allTopics[0].cloneNode(true));
                    allTopics.forEach(option => {
                        if (option.getAttribute('data-level') === selectedLevel) {
                            let newOption = option.cloneNode(true);
                            if(savedTopic && newOption.value === savedTopic) newOption.selected = true;
                            topicSelect.appendChild(newOption);
                        }
                    });
                }
                updateTopics();
                levelSelect.addEventListener('change', updateTopics);
            }
        });

        // --- SISTEM CUSTOM POP-UP MODAL ---
        const modalOverlay = document.getElementById('rq-modal-overlay');
        const modalBox = document.getElementById('rq-modal-box');
        const modalIcon = document.getElementById('rq-modal-icon');
        const modalTitle = document.getElementById('rq-modal-title');
        const modalDesc = document.getElementById('rq-modal-desc');
        const btnOk = document.getElementById('rq-btn-ok');
        const btnCancel = document.getElementById('rq-btn-cancel');

        // Fungsi Menampilkan Modal Alert Biasa (Sukses/Gagal)
        function rqAlert(title, message, type, redirectUrl = null) {
            modalTitle.innerText = title;
            modalDesc.innerText = message;
            btnCancel.style.display = 'none'; // Sembunyikan tombol batal
            
            if (type === 'success') {
                modalIcon.innerHTML = '✅';
                btnOk.style.background = '#a3e635'; // Hijau
            } else if (type === 'error') {
                modalIcon.innerHTML = '❌';
                btnOk.style.background = '#ef4444'; // Merah
                btnOk.style.color = '#ffffff';
            }

            modalOverlay.style.display = 'flex';
            setTimeout(() => {
                modalOverlay.style.opacity = '1';
                modalBox.style.transform = 'translateY(0)';
            }, 10);

            btnOk.onclick = function() {
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                } else {
                    closeModal();
                }
            };
        }

        // Fungsi Menampilkan Modal Konfirmasi (Hapus)
        function rqConfirm(message, proceedUrl) {
            modalIcon.innerHTML = '⚠️';
            modalTitle.innerText = 'Konfirmasi Aksi';
            modalDesc.innerText = message;
            
            btnOk.style.background = '#ef4444'; // Merah untuk aksi hapus
            btnOk.style.color = '#ffffff';
            btnOk.innerText = 'Ya, Hapus';
            btnCancel.style.display = 'block'; // Tampilkan tombol batal
            btnCancel.innerText = 'Batal';

            modalOverlay.style.display = 'flex';
            setTimeout(() => {
                modalOverlay.style.opacity = '1';
                modalBox.style.transform = 'translateY(0)';
            }, 10);

            btnOk.onclick = function() { window.location.href = proceedUrl; };
            btnCancel.onclick = function() { closeModal(); };
        }

        function closeModal() {
            modalOverlay.style.opacity = '0';
            modalBox.style.transform = 'translateY(-20px)';
            setTimeout(() => { modalOverlay.style.display = 'none'; }, 300);
        }
    </script>
</body>
</html>