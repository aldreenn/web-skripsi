# 📘 Dokumentasi Fitur Aplikasi — ReadQuest

> **ReadQuest** adalah aplikasi web berbasis PHP (XAMPP/MySQL) yang dirancang sebagai platform pembelajaran Reading Comprehension bergaya gamifikasi untuk persiapan ujian TOEFL.
>
> Dokumen ini menjelaskan **seluruh fitur** yang telah dibangun beserta **cara kerja teknis** di balik setiap fitur.

---

## 📂 Struktur Folder Proyek

```
aplikasi_skripsi/
├── index.php                 # Landing Page (Halaman Utama)
├── .htaccess                 # Konfigurasi server Apache
├── 403.php                   # Halaman error akses ditolak
│
├── config/
│   └── koneksi.php           # Konfigurasi koneksi database MySQL + Timezone Sync
│
├── auth/
│   ├── login.php             # Logika proses login
│   ├── signup.php            # Logika proses registrasi
│   └── logout.php            # Logika proses logout
│
├── pages/
│   ├── loginpage.html        # Halaman form login (UI)
│   ├── signup.html           # Halaman form registrasi (UI)
│   ├── dashboard.php         # Dashboard utama user (Home + Leaderboard)
│   ├── practice.php          # Halaman Practice Path (daftar materi per level)
│   ├── reading-detail.php    # Halaman baca artikel + kuis interaktif
│   ├── save_score.php        # API endpoint untuk menyimpan skor practice
│   ├── test.php              # Halaman Test Path (daftar paket ujian simulasi)
│   ├── test-exam.php         # Halaman ujian simulasi TOEFL (timer 55 menit)
│   ├── submit_test.php       # Logika pemeriksa jawaban ujian + konversi skor TOEFL
│   ├── test-result.php       # Halaman hasil ujian TOEFL
│   ├── score_history.php     # Halaman riwayat skor (Practice + Test)
│   └── manage_account.php    # Halaman pengaturan akun pengguna
│
├── admin_pages/
│   ├── admin_dashboard.php   # Controller utama admin (routing + semua logika CRUD)
│   ├── export_scores.php     # Ekspor data nilai ke file Excel (.xls)
│   ├── components/
│   │   ├── header.php        # Komponen header admin
│   │   ├── sidebar.php       # Komponen sidebar navigasi admin
│   │   └── footer.php        # Komponen footer admin (termasuk alert)
│   └── views/
│       ├── overview.php              # Dashboard ringkasan statistik admin
│       ├── manage_article.php        # Tabel kelola artikel/materi
│       ├── add_article.php           # Form tambah artikel baru
│       ├── edit_article.php          # Form edit artikel
│       ├── manage_questions.php      # Tabel kelola soal kuis practice
│       ├── add_question.php          # Form tambah soal kuis
│       ├── edit_question.php         # Form edit soal kuis
│       ├── manage_topics.php         # Kelola folder topik per level CEFR
│       ├── manage_test_package.php   # Kelola paket ujian simulasi
│       ├── edit_test_package.php     # Form edit paket ujian
│       ├── manage_test_passage.php   # Tabel kelola passage/teks bacaan ujian
│       ├── add_test_passage.php      # Form tambah passage ujian
│       ├── edit_test_passage.php     # Form edit passage ujian
│       ├── manage_test_questions.php # Tabel kelola soal ujian simulasi
│       ├── add_test_question.php     # Form tambah soal ujian
│       ├── edit_test_question.php    # Form edit soal ujian
│       └── view_scores.php          # Lihat & filter semua nilai peserta
│
├── admin_css/
│   └── admin_dashboard.css   # Stylesheet khusus halaman admin
│
├── desain/
│   ├── index.css             # CSS Landing Page
│   ├── loginpage.css         # CSS Halaman Login
│   ├── signup.css            # CSS Halaman Sign Up
│   ├── dashboard.css         # CSS Dashboard User
│   ├── practice.css          # CSS Practice Path
│   ├── readingdetail.css     # CSS Reading Detail + Kuis
│   ├── test.css              # CSS Test Path
│   ├── test-exam.css         # CSS Halaman Ujian Simulasi
│   ├── test-result.css       # CSS Halaman Hasil Ujian
│   ├── score_history.css     # CSS Riwayat Nilai
│   └── manage_account.css    # CSS Pengaturan Akun
│
├── assets/                   # Gambar statis (hero image, SVG background, favicon)
├── data/
│   └── reading-repository.js # Repository data bacaan (JavaScript)
└── uploads/
    ├── materials/            # Upload gambar untuk materi & passage ujian
    └── questions/            # Upload gambar untuk soal
```

---

## 🏗️ Arsitektur Aplikasi

```
┌─────────────────────────────────────────────────────────────┐
│                        FRONTEND                             │
│  HTML + TailwindCSS (Landing) / Vanilla CSS + JavaScript    │
└──────────────────────────┬──────────────────────────────────┘
                           │ HTTP Request (Form POST / Fetch API)
┌──────────────────────────▼──────────────────────────────────┐
│                     BACKEND (PHP)                           │
│  Session Management, Routing, Business Logic, CRUD          │
└──────────────────────────┬──────────────────────────────────┘
                           │ MySQLi Query
┌──────────────────────────▼──────────────────────────────────┐
│                  DATABASE (MySQL/MariaDB)                    │
│  Database: aplikasi_skripsi                                 │
│  Tabel: users, materials, questions, topics,                │
│         practice_scores, test_packets, test_passages,       │
│         test_questions, test_scores                         │
└─────────────────────────────────────────────────────────────┘
```

- **Stack**: PHP Native + MySQL (XAMPP), JavaScript Vanilla, TailwindCSS (khusus landing page), Vanilla CSS (semua halaman lainnya)
- **Session Management**: PHP `$_SESSION` untuk autentikasi dan penyimpanan state user
- **Database Driver**: `mysqli` (procedural dan OOP)
- **Timezone Sync**: PHP `date_default_timezone_set('Asia/Jakarta')` + MySQL `SET time_zone = '+07:00'` pada setiap koneksi untuk sinkronisasi zona waktu WIB
- **Favicon**: Custom favicon (`assets/favicon.png`) diterapkan di seluruh halaman (user & admin)

---

## 🔐 1. Fitur Autentikasi (Login, Sign Up, Logout)

### 1.1 Registrasi (Sign Up)

| Item | Detail |
|------|--------|
| **Halaman UI** | `pages/signup.html` |
| **Backend** | `auth/signup.php` |
| **CSS** | `desain/signup.css` |

**Cara Kerja:**
1. User mengisi form berisi **Username**, **First Name**, **Last Name**, **Password**, dan **Confirm Password**.
2. Data dikirim via `POST` ke `auth/signup.php`.
3. Server melakukan validasi:
   - Semua field wajib terisi.
   - Password dan konfirmasi harus cocok.
   - Username tidak boleh duplikat (dicek via query `SELECT`).
4. Nama depan dan belakang di-format menjadi **Title Case** (`ucwords(strtolower(...))`).
5. Password di-hash menggunakan `password_hash($password, PASSWORD_DEFAULT)` sebelum disimpan ke database.
6. Jika berhasil → redirect ke halaman login. Jika gagal → redirect kembali ke signup dengan pesan error via query string.

### 1.2 Login

| Item | Detail |
|------|--------|
| **Halaman UI** | `pages/loginpage.html` |
| **Backend** | `auth/login.php` |
| **CSS** | `desain/loginpage.css` |

**Cara Kerja:**
1. User memasukkan **Username** dan **Password** lalu submit form.
2. Server mengambil data user dari tabel `users` berdasarkan username (menggunakan **Prepared Statement**).
3. Password diverifikasi menggunakan `password_verify()` (mendukung fallback plain-text untuk kompatibilitas).
4. Jika valid:
   - Session dibuat: `user_id`, `username`, `first_name`, `last_name`, `role`.
   - **Role-Based Redirect**: Jika role = `admin` → ke Admin Dashboard, jika role = `user` → ke User Dashboard.
5. Cache control header diterapkan untuk mencegah back-button setelah logout.

### 1.3 Logout

| Item | Detail |
|------|--------|
| **Backend** | `auth/logout.php` |

**Cara Kerja:**
1. `session_unset()` → Menghapus semua variabel session.
2. `session_destroy()` → Menghancurkan session secara permanen.
3. Header anti-cache diterapkan.
4. Redirect ke `index.php` (Landing Page).

---

## 🏠 2. Landing Page

| Item | Detail |
|------|--------|
| **File** | `index.php` |
| **CSS** | `desain/index.css` + TailwindCSS (via CDN) |

### Fitur Utama:
- **Session-Aware Navigation**: Tombol di navbar dan CTA berubah secara dinamis berdasarkan status login user (`Start Learning Now` vs `Go to Dashboard`).
- **Typewriter Effect**: Headline utama ditampilkan dengan efek pengetikan otomatis menggunakan JavaScript.
- **Horizontal Scroll on Vertical Scroll**: Section fitur menggunakan teknik *horizontal scrolling* yang dipicu oleh scroll vertikal (sticky container + translateX).
- **Animated Timeline**: Section "How It Works" memiliki animasi timeline yang mengisi garis progres dan mengubah warna indikator saat user scroll.
- **Scroll Reveal Animation**: Elemen-elemen dengan class `.fade-in-up` muncul dengan animasi saat masuk viewport menggunakan `IntersectionObserver`.
- **Ambient Glow Effects**: Background menggunakan elemen blur besar yang memberi kesan *ambient glow*.
- **Material Design 3 Color System**: Menggunakan skema warna Material Design yang di-extend ke TailwindCSS.

---

## 📊 3. Dashboard User

| Item | Detail |
|------|--------|
| **File** | `pages/dashboard.php` |
| **CSS** | `desain/dashboard.css` |

Dashboard adalah pusat kontrol utama user setelah login, dengan dua halaman internal: **Home** dan **Leaderboard**.

### 3.1 Home — My Learning Panel

Panel utama menampilkan dua jalur pembelajaran yang bisa di-toggle:

#### Practice Path
- Menampilkan daftar **artikel/materi bacaan** yang dikelompokkan berdasarkan **Level CEFR** dan **Topik**.
- Progress bar per topik dihitung secara dinamis (`completed / total × 100%`).
- **Navigasi level tanpa refresh** menggunakan JavaScript (`switchToLevel()`) yang me-render ulang konten secara client-side dan memperbarui URL via `history.replaceState()`.
- Setiap artikel menampilkan status (✅ selesai / 📖 belum) berdasarkan data dari tabel `practice_scores`.
- Tombol **Previous Level** dan **Next Level** tersedia, dengan next level terkunci jika user belum melewati level rekomendasi.

#### Test Path
- Menampilkan daftar **paket ujian simulasi** (dinamis dari tabel `test_packets`).
- Setiap paket menampilkan status: **🔒 Locked**, **⏱ Ready**, atau **✅ Completed**.
- Sistem gembok: Paket B memerlukan penyelesaian Paket A, Paket C memerlukan B, dst (field `requirement` di tabel `test_packets`).

### 3.2 Sidebar Dashboard

- **Progress Bar** (Practice / Test) — Menampilkan persentase total penyelesaian.
- **Articles/Tests Completed** — Statistik angka `X / Total`.
- **Longest Perfect Streak** — Menghitung jumlah skor 100 berturut-turut terpanjang (dihitung secara iteratif dari database). **Minimum 2 skor 100 berturut-turut** diperlukan agar streak ditampilkan (nilai `< 2` ditampilkan sebagai `0`).
- **Activity Feed** — 10 aktivitas terbaru user (Congratulations setelah menyelesaikan artikel/test), dilengkapi time-ago relatif (`X minutes ago`, `X days ago`).
- **View History Details** — Link ke halaman `score_history.php` yang otomatis menyesuaikan tab aktif.
- **CEFR Badge di Profile Dropdown** — Setelah menyelesaikan minimal satu paket ujian, badge level CEFR bergaya gamifikasi (misal: `B2: The Vanguard`) ditampilkan di dropdown profil navbar. Badge ditentukan berdasarkan skor TOEFL tertinggi yang pernah diraih user di seluruh paket.

### 3.3 Performance Graph (Grafik Performa)

- **Line Chart SVG** custom tanpa library eksternal.
- Dua mode grafik: **Practice** (hijau, per level aktif) dan **Test** (biru).
- Setiap titik data memiliki **tooltip** yang menampilkan judul artikel/paket dan skornya.
- Grafik responsif terhadap resize window (`window.addEventListener('resize', drawAllGraphs)`).
- Data practice graph berubah saat user berpindah level (client-side rendering).

### 3.4 Global Leaderboard

- Menampilkan peringkat **Top 50** peserta berdasarkan akumulasi **XP (Total Score)**.
  - **XP dihitung dari `SUM(score)`** di tabel `practice_scores`, di mana `score` adalah **Mastery Rate** (0–100%) per artikel. Bukan ITP Score.
  - Jika filter Level CEFR aktif, XP hanya dihitung dari artikel di level tersebut (`JOIN materials ... WHERE m.level = '...'`).
- Fitur **Search** berdasarkan nama dan **Filter** berdasarkan Level CEFR (A1–C2).
- **AJAX Live Reload**: Pencarian dan filter memuat data secara asinkron tanpa refresh halaman penuh. URL parameter (`search`, `level_filter`) diperbarui via `history.replaceState()` agar state tetap terjaga saat di-refresh manual.
- **Skeleton Loading Animation**: Saat data sedang dimuat melalui AJAX, baris tabel digantikan oleh animasi *shimmer* (skeleton placeholder) agar user tidak melihat tabel yang kosong atau bingung.
- **Responsif Mobile**: Kolom `Articles Passed` disembunyikan pada layar kecil (`hide-mobile` class) dan form filter Leaderboard menggunakan layout vertikal pada mobile. Header kolom menggunakan `white-space: nowrap` untuk mencegah pemotongan teks dan `table-layout: fixed` untuk stabilitas layout.
- Baris user yang login di-highlight dengan warna khusus dan badge "(You)".
- Peringkat top 3 mendapat emoji medali (🥇🥈🥉).
- Tombol **Reset** untuk menghapus filter.

---

## 📚 4. Practice Path (Jalur Latihan)

| Item | Detail |
|------|--------|
| **File** | `pages/practice.php` |
| **CSS** | `desain/practice.css` |

### Cara Kerja:

1. **Sidebar CEFR Level**: Menampilkan tombol per level (A1, A2, B1, B2, C1, C2) yang dirender secara dinamis dari data database.
2. **Sistem Gembok Level**: 
   - Level A1 selalu terbuka.
   - Level berikutnya terkunci sampai **minimal 5 artikel di level sebelumnya telah LULUS** (skor ≥ 70). Jumlah minimal dapat dikonfigurasi melalui variabel `minRequired` di JavaScript.
   - Jika level sebelumnya belum ada materinya di database → tetap terkunci.
3. **Custom Modal Pop-Up (Level Locked)**: Saat user mengklik level yang masih terkunci, muncul modal pop-up bergaya premium (glassmorphism, backdrop blur) dengan pesan persyaratan yang spesifik (misal: "Pass at least 5 article(s) in A2 to unlock this level."). Modal ini menggantikan `alert()` bawaan browser.
4. **Tampilan Folder Topik**: Setiap level menampilkan folder-folder topik (diambil dari tabel `topics`). Klik folder untuk melihat artikel di dalamnya.
5. **Daftar Artikel**: Setiap kartu artikel menampilkan:
   - Badge level CEFR
   - Status: **Passed** (hijau, skor ≥70), **Failed** (merah, skor <70), atau belum pernah dikerjakan.
   - Skor best attempt.
   - Tombol `Start Practice` atau `Try Again`.
6. **Search**: Filter artikel berdasarkan judul secara real-time.
7. **Progress Bar per Level**: Di sidebar, setiap level menampilkan bar progres kelulusan.

---

## 📖 5. Reading Detail + Interactive Quiz

| Item | Detail |
|------|--------|
| **File** | `pages/reading-detail.php` |
| **CSS** | `desain/readingdetail.css` |
| **API** | `pages/save_score.php` |

### Cara Kerja:

1. **Layout Split-Screen**: Halaman dibagi dua panel:
   - **Panel Kiri**: Teks bacaan lengkap (artikel), termasuk cover image jika ada.
   - **Panel Kanan**: Soal-soal kuis multiple choice (dari tabel `questions`).
2. **Timer Berjalan**: Timer count-up yang menghitung waktu belajar (dimulai dari 00:00).
3. **Submit Quiz**:
   - Timer dihentikan.
   - Jawaban diperiksa secara client-side.
   - Jawaban benar diberi highlight **hijau**, salah diberi highlight **merah**.
   - **Penjelasan (Explanation)** ditampilkan untuk setiap soal setelah submit.
4. **Scoring Engine — Capping System per Level CEFR**:
   - Skor dihitung menggunakan fungsi `calculatePracticeScore(level, correctAnswers, totalQuestions)` yang menghasilkan dua output: **ITP Score** dan **Mastery Rate**.
   - **Mastery Rate** = Persentase jawaban benar (0–100%). Digunakan sebagai dasar threshold lulus/gagal (≥ 70).
   - **ITP Score** = Konversi mastery rate ke skala TOEFL ITP Reading menggunakan **linear interpolation** dalam rentang yang di-cap per level.
   - Tabel Capping ITP per Level:

     | Level | Konversi ITP? | Rentang Skor ITP | Output di UI |
     |:-----:|:---:|:---:|---|
     | **A1** | ❌ | — | Persentase saja (`Score: XX%`) |
     | **A2** | ✅ | 31 – 47 | `ITP: XX \| Mastery: YY%` |
     | **B1** | ✅ | 48 – 55 | `ITP: XX \| Mastery: YY%` |
     | **B2** | ✅ | 56 – 62 | `ITP: XX \| Mastery: YY%` |
     | **C1** | ✅ | 63 – 67 | `ITP: XX \| Mastery: YY%` |
     | **C2** | ❌ | — | Persentase saja (`Score: XX%`) |

   - Rumus ITP: `itp_score = itp_min + (correctAnswers / totalQuestions) × (itp_max - itp_min)`
   - Level A1 dan C2 tidak menggunakan konversi ITP karena berada di luar cakupan standar TOEFL ITP.
5. **Penyimpanan Skor** (via `Fetch API` ke `save_score.php`):
   - Data yang dikirim: `material_id`, `score` (mastery rate), `itp_score`, `duration_seconds`.
   - Jika belum pernah mengerjakan → `INSERT` data baru.
   - Jika sudah ada → `UPDATE` hanya jika skor baru **≥ skor lama** (best score disimpan). Kolom `created_at` juga diperbarui ke `CURRENT_TIMESTAMP` agar entri terbaru muncul di Activity Feed.
   - Jika skor sama → `created_at` tetap diperbarui, dan simpan **durasi tercepat** (waktu tersingkat dari dua attempt).
   - ITP Score juga mengikuti logika "simpan yang tertinggi" — dari dua nilai ITP, yang lebih besar yang disimpan.
   - Durasi waktu belajar ikut disimpan ke kolom `duration_seconds`.
6. **Indikator Hasil**: Header berubah sesuai level:
   - Level A2–C1: `PASSED — ITP: XX | Mastery: YY%` (hijau) atau `FAILED — ITP: XX | Mastery: YY%` (merah).
   - Level A1/C2: `PASSED — Score: XX%` (hijau) atau `FAILED — Score: XX%` (merah).

---

## 🧪 6. Test Simulation (Simulasi Ujian TOEFL)

### 6.1 Halaman Daftar Test

| Item | Detail |
|------|--------|
| **File** | `pages/test.php` |
| **CSS** | `desain/test.css` |

- Menampilkan paket ujian dari tabel `test_packets` (saat ini mendukung **4 paket: A, B, C, D**).
- Setiap paket menampilkan: judul, deskripsi naratif unik, durasi (**40 menit**), jumlah soal (**32 soal**: 8 soal per level CEFR A2–C1).
- **Deskripsi Paket Naratif**: Setiap paket memiliki deskripsi unik yang menggambarkan fase perjalanan belajar:
  - **Package A**: *"The baseline assessment"* — Penilaian awal.
  - **Package B**: *"The consistency check"* — Uji konsistensi setelah latihan.
  - **Package C**: *"The mastery phase"* — Fase penguasaan di bawah tekanan.
  - **Package D**: *"The final milestone"* — Pencapaian akhir sebelum ujian sesungguhnya.
- **Sistem Gembok**: Paket terkunci jika requirement belum terpenuhi.
- **Custom Modal Pop-Up (Package Locked)**: Saat user mengklik paket yang masih terkunci, muncul modal pop-up bergaya premium (identik dengan Practice Path) menggantikan `alert()` bawaan browser. Pesan modal menyesuaikan secara dinamis (misal: "Please complete Package B first to unlock this test").
- **Tombol Completed**: Jika user sudah mengerjakan, tombol dinonaktifkan (ujian hanya bisa dilakukan 1x per paket).

### 6.2 Halaman Ujian Simulasi

| Item | Detail |
|------|--------|
| **File** | `pages/test-exam.php` |
| **CSS** | `desain/test-exam.css` |

**Cara Kerja:**
1. **Server-Side Timer**: Waktu ujian disimpan di `$_SESSION['exam_end_time_X']`. Sisa waktu dihitung di server (`$_SESSION[timer] - time()`), sehingga refresh halaman **tidak mereset timer**.
2. **Timer Countdown 40 Menit**: Ditampilkan di header dan terus berjalan. Jika habis → form otomatis di-submit.
3. **Multi-Passage Layout**: Panel kiri menampilkan teks bacaan dengan tab untuk berpindah antar passage. Panel kanan menampilkan soal.
4. **Reciprocal Reading 4 Phases**: Setiap passage memiliki 4 tahap soal yang harus dilalui berurutan:
   - **Predicting** → **Clarifying** → **Questioning** → **Summarizing**
   - Stepper indicator di header menunjukkan tahap saat ini (active/completed/incomplete).
5. **Auto-Save ke LocalStorage**: Setiap jawaban yang dipilih disimpan ke `localStorage`, sehingga jika browser ditutup/refresh, jawaban tidak hilang.
6. **Proteksi Anti-Bocor URL (Lapis 2)**: Sebelum menampilkan soal, server mengecek apakah user sudah pernah menyelesaikan paket ini. Jika ya → akses ditolak.
7. **Modal Konfirmasi**: Saat submit, muncul modal yang menampilkan jumlah soal yang belum diisi.

### 6.3 Pemeriksa Jawaban & Konversi Skor TOEFL ITP

| Item | Detail |
|------|--------|
| **File** | `pages/submit_test.php` |

**Cara Kerja:**
1. Kunci jawaban diambil dari database bersama `cefr_level` setiap soal (`test_questions.correct_answer`, `test_questions.cefr_level`).
2. Setiap jawaban user dicocokkan dengan kunci jawaban secara server-side.
3. **Raw Score** dihitung (jumlah jawaban benar dari 32 soal).
4. **Konversi ke Skor TOEFL ITP Reading + Predikat CEFR**: Menggunakan fungsi `convertRawToITP()` dengan **Range-Based Linear Interpolation**:

   | Raw Score | Rentang Skor ITP | Level CEFR |
   |:---------:|:----------------:|:----------:|
   | 45 – 50 | 63 – 67 | C1 (Advanced) |
   | 36 – 44 | 56 – 62 | B2 (Upper Intermediate) |
   | 23 – 35 | 48 – 55 | B1 (Intermediate) |
   | 0 – 22  | 31 – 47 | A2 (Basic) |

   - Rumus: `itp_score = itp_min + ((raw - raw_min) / (raw_max - raw_min)) × (itp_max - itp_min)`
   - Jika jumlah soal berbeda dari 50 (default), raw score diproyeksikan ke skala 50 secara proporsional untuk memastikan tabel konversi ITP tetap akurat.
5. **Analisis Diagnostik (Diagnostic Report)**: Bersamaan dengan pemeriksaan jawaban, sistem menghitung breakdown persentase jawaban benar **per level CEFR** (A2, B1, B2, C1) berdasarkan tag `cefr_level` pada setiap soal.
6. Hasil disimpan ke tabel `test_scores` (termasuk kolom `cefr_level`).
7. Session timer dihapus.
8. Data hasil (`raw_score`, `toefl_score`, `cefr_level`, dan data `diagnostic`) disimpan ke `$_SESSION['last_test_result']` lalu redirect ke halaman result.

### 6.4 Halaman Hasil Ujian + Diagnostic Report

| Item | Detail |
|------|--------|
| **File** | `pages/test-result.php` |
| **CSS** | `desain/test-result.css` |

Halaman ini menampilkan hasil evaluasi ujian dalam 4 section:

1. **Lingkaran Skor TOEFL ITP** — Menampilkan skor ITP (31–67) dalam lingkaran visual dengan gradient.
2. **Badge Predikat CEFR** — Badge berwarna yang menampilkan level CEFR hasil konversi (misal: "Upper Intermediate (B2)"). Warna badge sesuai level:
   - A2 = Hijau (#22c55e)
   - B1 = Biru (#3b82f6)
   - B2 = Kuning (#f59e0b)
   - C1 = Ungu (#a855f7)
3. **Stats Grid** — Jumlah jawaban benar dan salah/kosong.
4. **📊 Diagnostic Report** — Breakdown persentase jawaban benar per level CEFR dalam bentuk **progress bar animasi**. Setiap baris menampilkan:
   - Dot warna level
   - Nama level (A2, B1, B2, C1)
   - Progress bar dengan lebar sesuai persentase
   - Persentase dan rasio jawaban benar (misal: `80%` — `4/5`)
   - Level yang tidak memiliki soal otomatis di-skip

- Data diambil dari session lalu session dihapus (mencegah duplikasi saat refresh).

---

## 📈 7. Score History (Riwayat Nilai)

| Item | Detail |
|------|--------|
| **File** | `pages/score_history.php` |
| **CSS** | `desain/score_history.css` + `desain/dashboard.css` |

### Fitur:
- **Dua Tab**: Practice History dan Test History.
- **Practice History**: Tabel riwayat menampilkan 7 kolom: tanggal, level, topik, judul artikel, **waktu pengerjaan** (`duration_seconds` dikonversi ke format `MM:SS`), **ITP Score** (ditampilkan hanya untuk level A2–C1; level A1/C2 menampilkan tanda `-`), dan **Score (XP)** (mastery rate).
- **Test History**: Tabel menampilkan tanggal, paket ujian, dan skor TOEFL.
- Score (XP) ≥ 70 ditampilkan dengan badge **hijau**, di bawah 70 dengan badge **kuning**. Skor TOEFL (Test History) selalu ditampilkan dengan badge **biru**.
- Tab aktif default ditentukan oleh parameter URL (`?tab=practice` atau `?tab=test`).
- Menggunakan **Prepared Statement** untuk query database.

---

## ⚙️ 8. Manage Account (Pengaturan Akun)

| Item | Detail |
|------|--------|
| **File** | `pages/manage_account.php` |
| **CSS** | `desain/manage_account.css` + `desain/dashboard.css` |

### Fitur:

#### 8.1 Account Details
- Form untuk mengubah **First Name**, **Last Name**, dan **Biography**.
- Username ditampilkan sebagai read-only (tidak bisa diubah).
- Nama diformat ulang ke Title Case sebelum disimpan.
- Session langsung diperbarui setelah update agar nama di navbar berubah tanpa re-login.

#### 8.2 Password Manager
- Form untuk mengubah password.
- Validasi: password lama harus cocok, password baru minimal 6 karakter, konfirmasi harus sama.
- Password baru di-hash menggunakan `password_hash()` sebelum disimpan.

#### 8.3 Danger Zone — Reset Progress
- Tombol untuk **menghapus seluruh progress practice** (tabel `practice_scores`).
- Dilindungi oleh **modal konfirmasi** sebelum eksekusi.
- Setelah reset → semua level terkunci kembali kecuali A1.
- Success modal muncul untuk mengkonfirmasi penghapusan berhasil.

---

## 🛡️ 9. Panel Admin

| Item | Detail |
|------|--------|
| **File Utama** | `admin_pages/admin_dashboard.php` |
| **CSS** | `admin_css/admin_dashboard.css` |
| **Komponen** | `admin_pages/components/` (header, sidebar, footer) |
| **Views** | `admin_pages/views/` (17 file view) |

### Proteksi Keamanan:
- Setiap akses ke admin dashboard dicek: `$_SESSION['role'] === 'admin'`.
- Jika bukan admin → alert + redirect ke halaman login.

### 9.1 Overview Dashboard
- Ringkasan statistik: total user, total artikel, total soal, total paket ujian.
- Menampilkan data rangkuman dalam bentuk kartu informasi.

### 9.2 Kelola Materi/Artikel (CRUD)

| Aksi | Halaman |
|------|---------|
| Lihat Daftar | `views/manage_article.php` |
| Tambah | `views/add_article.php` |
| Edit | `views/edit_article.php` |
| Hapus | Via parameter `?delete_article=ID` |

- **Tambah Artikel**: Form berisi Level CEFR, Topik (folder), Waktu Baca, Judul, Deskripsi, Konten Lengkap, dan opsional **Cover Image** (upload file).
- **Edit**: Form pre-populated dengan data dari database. Mendukung upload gambar baru atau hapus gambar lama.
- **Hapus**: Menghapus artikel beserta soal-soal yang terkait dan file gambar fisik dari server.

### 9.3 Kelola Soal Kuis Practice (CRUD)

| Aksi | Halaman |
|------|---------|
| Lihat Daftar | `views/manage_questions.php` |
| Tambah | `views/add_question.php` |
| Edit | `views/edit_question.php` |
| Hapus | Via parameter `?delete_question=ID` |

- Setiap soal terhubung ke satu artikel (`material_id`).
- Format: Pertanyaan + 4 Opsi (A/B/C/D) + Jawaban Benar + Penjelasan.

### 9.4 Kelola Folder Topik

| Aksi | Halaman |
|------|---------|
| Kelola | `views/manage_topics.php` |

- Admin dapat membuat folder topik per level CEFR.
- Data disimpan di tabel `topics`.

### 9.5 Kelola Paket Ujian Simulasi

| Aksi | Halaman |
|------|---------|
| Lihat & Kelola | `views/manage_test_package.php` |
| Edit | `views/edit_test_package.php` |

- Data paket ujian (A, B, C, dll) disimpan di tabel `test_packets`.
- Setiap paket memiliki field `requirement` untuk mengatur urutan gembok.

### 9.6 Kelola Passage Ujian (CRUD)

| Aksi | Halaman |
|------|---------|
| Lihat Daftar | `views/manage_test_passage.php` |
| Tambah | `views/add_test_passage.php` |
| Edit | `views/edit_test_passage.php` |
| Hapus | Via parameter `?delete_test_material=ID` |

- Setiap passage terhubung ke satu paket (`packet_id`).
- Mendukung upload **cover image** opsional.
- Hapus passage otomatis menghapus soal yang terkait (cascade manual via query).

### 9.7 Kelola Soal Ujian Simulasi (CRUD)

| Aksi | Halaman |
|------|---------|
| Lihat Daftar | `views/manage_test_questions.php` |
| Tambah | `views/add_test_question.php` |
| Edit | `views/edit_test_question.php` |
| Hapus | Via parameter `?delete_test_question=ID` |

- Setiap soal terhubung ke satu passage (`passage_id`).
- Memiliki field tambahan `reciprocal_phase` (predicting/clarifying/questioning/summarizing) untuk mengelompokkan soal ke dalam 4 tahap Reciprocal Reading.

### 9.8 Lihat Nilai Peserta + Ekspor Excel

| Aksi | Halaman |
|------|---------|
| Lihat & Filter | `views/view_scores.php` |
| Ekspor | `export_scores.php` |

- Menampilkan semua nilai practice peserta dalam tabel.
- Fitur **search** (nama/judul) dan **filter** (level CEFR).
- Tombol **Export ke Excel** mengunduh file `.xls` yang berisi data nilai sesuai filter aktif.

### 9.9 Sistem Alert Admin
- Menggunakan `$_SESSION['alert']` untuk menampilkan notifikasi sukses/error di footer.
- Notifikasi ditampilkan dengan animasi dan otomatis hilang.

---

## 🗄️ Skema Database

### Tabel `users`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT (PK, AI) | ID unik user |
| username | VARCHAR | Username login (unik) |
| first_name | VARCHAR | Nama depan |
| last_name | VARCHAR | Nama belakang |
| password | VARCHAR | Password (hashed) |
| bio | TEXT | Biografi singkat |
| role | ENUM('user','admin') | Peran user |

### Tabel `materials`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT (PK, AI) | ID artikel |
| level | VARCHAR | Level CEFR (A1–C2) |
| topic | VARCHAR | Nama topik/folder |
| reading_time | VARCHAR | Estimasi waktu baca |
| cover_image | VARCHAR | Nama file gambar (opsional) |
| title | VARCHAR | Judul artikel |
| description | TEXT | Deskripsi singkat |
| full_content | TEXT | Konten lengkap artikel |
| created_at | TIMESTAMP | Waktu pembuatan |

### Tabel `questions`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT (PK, AI) | ID soal |
| material_id | INT (FK) | Referensi ke `materials.id` |
| question_text | TEXT | Teks pertanyaan |
| option_a–d | VARCHAR | 4 opsi jawaban |
| correct_answer | INT | Index jawaban benar (0–3) |
| explanation | TEXT | Penjelasan jawaban |

### Tabel `topics`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT (PK, AI) | ID topik |
| level | VARCHAR | Level CEFR |
| topic_name | VARCHAR | Nama topik/folder |

### Tabel `practice_scores`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT (PK, AI) | ID record |
| user_id | INT (FK) | Referensi ke `users.id` |
| material_id | INT (FK) | Referensi ke `materials.id` |
| score | INT | Mastery Rate / Persentase jawaban benar (0–100) |
| itp_score | INT | Skor konversi TOEFL ITP Reading (31–67), 0 jika level A1/C2 |
| duration_seconds | INT | Durasi pengerjaan (detik) |
| created_at | TIMESTAMP | Waktu pengerjaan |

### Tabel `test_packets`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT (PK, AI) | ID paket |
| packet_code | VARCHAR | Kode paket (A, B, C, ...) |
| title | VARCHAR | Judul paket ujian |
| requirement | VARCHAR | Kode paket prasyarat (nullable) |

### Tabel `test_passages`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT (PK, AI) | ID passage |
| packet_id | VARCHAR (FK) | Referensi ke `test_packets.packet_code` |
| passage_number | INT | Urutan passage dalam paket |
| title | VARCHAR | Judul teks bacaan |
| content | TEXT | Konten teks bacaan |
| cover_image | VARCHAR | Nama file gambar (opsional) |

### Tabel `test_questions`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT (PK, AI) | ID soal |
| passage_id | INT (FK) | Referensi ke `test_passages.id` |
| reciprocal_phase | ENUM | Fase: predicting/clarifying/questioning/summarizing |
| question_text | TEXT | Teks pertanyaan |
| option_a–d | VARCHAR | 4 opsi jawaban |
| correct_answer | CHAR(1) | Jawaban benar (A/B/C/D) |
| cefr_level | VARCHAR | Level CEFR soal (A2/B1/B2/C1) untuk Diagnostic Report |

### Tabel `test_scores`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT (PK, AI) | ID record |
| user_id | INT (FK) | Referensi ke `users.id` |
| test_packet | VARCHAR (FK) | Referensi ke `test_packets.packet_code` |
| raw_score | INT | Jumlah jawaban benar |
| toefl_score | INT | Skor konversi TOEFL ITP (31–67) |
| cefr_level | VARCHAR | Predikat level CEFR hasil konversi (A2/B1/B2/C1) |
| created_at | TIMESTAMP | Waktu pengerjaan |

---

## 🔄 Alur Kerja Utama (User Flow)

```
┌──────────────┐     ┌───────────┐     ┌─────────────┐
│ Landing Page │────▶│   Login   │────▶│  Dashboard  │
│  (index.php) │     │           │     │  (Home)     │
└──────────────┘     └───────────┘     └──────┬──────┘
                                              │
                    ┌─────────────────────────┼────────────────────────┐
                    ▼                         ▼                        ▼
          ┌──────────────┐          ┌──────────────┐        ┌──────────────┐
          │ Practice Path│          │  Test Path   │        │ Leaderboard  │
          │ (per Level)  │          │ (per Paket)  │        │ (Global XP)  │
          └──────┬───────┘          └──────┬───────┘        └──────────────┘
                 │                         │
                 ▼                         ▼
          ┌──────────────┐          ┌──────────────┐
          │Reading Detail│          │ Exam 55 Min  │
          │ + Quiz       │          │ 4 Phases RR  │
          └──────┬───────┘          └──────┬───────┘
                 │                         │
                 ▼                         ▼
          ┌──────────────┐          ┌──────────────┐
          │ Save Score   │          │ TOEFL Score  │
          │ (API)        │          │ Conversion   │
          └──────┬───────┘          └──────┬───────┘
                 │                         │
                 └──────────┬──────────────┘
                            ▼
                   ┌──────────────┐
                   │Score History │
                   │ + Dashboard  │
                   │   Update     │
                   └──────────────┘
```

---

## 📐 Sistem Scoring & Konversi TOEFL ITP

Sistem scoring pada ReadQuest dibagi menjadi dua modul utama yang masing-masing memiliki logika konversi berbeda:

### Prinsip Dasar

- **Standar Acuan**: TOEFL ITP (Institutional Testing Program) section Reading Comprehension dengan skala skor **31–67**.
- **Pemetaan CEFR**: Setiap rentang skor ITP dipetakan ke level CEFR (Common European Framework of Reference) untuk memberikan konteks kemampuan bahasa user.
- **Metode Konversi**: Linear Interpolation dalam setiap range (bukan lookup table statis).
- **Dua Jenis Skor Practice**: `score` (Mastery Rate, 0–100%) digunakan sebagai XP untuk Leaderboard & threshold lulus; `itp_score` (konversi ke skala TOEFL ITP) disimpan terpisah dan ditampilkan di Score History. Kedua nilai tersimpan di tabel `practice_scores`.

### A. Scoring Modul Practice (Latihan per Level)

Modul practice dipisah per level CEFR (A1–C2). Setiap level menggunakan **sistem capping** (batas nilai maksimal ITP) karena soal per level tidak mencakup seluruh spektrum kesulitan TOEFL ITP.

**Fungsi**: `calculatePracticeScore(level, correctAnswers, totalQuestions)` — JavaScript, client-side (`reading-detail.php`).

**Output** (objek dengan 3 field):
- `masteryRate` — Persentase jawaban benar (0–100%). Menjadi dasar lulus/gagal (threshold ≥ 70). Dikirim ke `save_score.php` sebagai field `score`.
- `itpScore` — Skor ITP dalam rentang yang di-cap sesuai level. Dikirim ke `save_score.php` sebagai field `itp_score`. Bernilai `0` untuk level A1 dan C2.
- `hasItp` — Boolean flag. `true` jika level menggunakan konversi ITP (A2–C1), `false` untuk A1/C2. Mengontrol format tampilan hasil di UI.

**Tabel Capping**:

| Level CEFR | Konversi ITP? | Rentang ITP Min–Max | Keterangan |
|:----------:|:---:|:---:|---|
| A1 (Starter) | ❌ | — | Hanya mengembalikan persentase jawaban benar (0–100%) |
| A2 (Basic) | ✅ | 31 – 47 | Batas maksimal skor ITP = 47 |
| B1 (Intermediate) | ✅ | 48 – 55 | Batas maksimal skor ITP = 55 |
| B2 (Upper Intermediate) | ✅ | 56 – 62 | Batas maksimal skor ITP = 62 |
| C1 (Advanced) | ✅ | 63 – 67 | Batas maksimal skor ITP = 67 |
| C2 (Proficient) | ❌ | — | Hanya mengembalikan persentase jawaban benar (0–100%) |

**Rumus Linear Interpolation**:
```
ratio = correctAnswers / totalQuestions
itpScore = itp_min + ratio × (itp_max - itp_min)
```

**Contoh**: User menjawab 8 dari 10 soal benar di level B1 (rentang 48–55):
- `ratio = 8/10 = 0.8`
- `itpScore = 48 + 0.8 × (55 - 48) = 48 + 5.6 = 54` (dibulatkan)
- `masteryRate = 80%`

**Penyimpanan**: `score` (mastery rate) dan `itp_score` disimpan ke tabel `practice_scores`. Logika best-score tetap berlaku (hanya update jika skor baru ≥ skor lama).

---

### B. Scoring Modul Test (Ujian Simulasi Paket A–D)

Setiap paket berisi **32 soal pilihan ganda campuran** (8 soal per level CEFR: A2, B1, B2, C1). Scoring menggunakan konversi dari raw score ke skor ITP dan predikat CEFR, dengan raw score diproyeksikan ke skala 50 untuk memastikan konsistensi tabel konversi.

**Fungsi**: `convertRawToITP($raw)` — PHP, server-side.

**Tabel Konversi**:

| Raw Score (Jawaban Benar) | Rentang Skor ITP | Predikat CEFR |
|:-------------------------:|:----------------:|:-------------:|
| 45 – 50 | 63 – 67 | C1 (Advanced) |
| 36 – 44 | 56 – 62 | B2 (Upper Intermediate) |
| 23 – 35 | 48 – 55 | B1 (Intermediate) |
| 0 – 22  | 31 – 47 | A2 (Basic) |

**Rumus Linear Interpolation**:
```
raw_span = raw_max - raw_min
ratio = (raw_score - raw_min) / raw_span
itp_score = itp_min + ratio × (itp_max - itp_min)
```

**Contoh**: User menjawab 40 dari 50 soal benar → masuk range B2 (36–44, ITP 56–62):
- `ratio = (40 - 36) / (44 - 36) = 4/8 = 0.5`
- `itpScore = 56 + 0.5 × (62 - 56) = 56 + 3 = 59`
- `cefrLevel = B2`

**Penyimpanan**: `raw_score`, `toefl_score`, dan `cefr_level` disimpan ke tabel `test_scores`.

---

### C. Analisis Diagnostik (Diagnostic Report)

Setiap soal ujian (tabel `test_questions`) memiliki tag `cefr_level` (A2/B1/B2/C1). Saat pemeriksaan jawaban, sistem secara paralel menghitung:

1. **Total soal per level CEFR** — Berapa soal A2, B1, B2, C1 dalam paket.
2. **Jawaban benar per level** — Berapa soal yang dijawab benar di setiap level.
3. **Persentase per level** — `(correct / total) × 100%`.

**Output Diagnostic** (ditampilkan di halaman hasil ujian):

```
📊 Diagnostic Report
┌──────────────────────────────────────────┐
│ A2: ████████████████████████████  100%    │
│ B1: ██████████████████████        80%     │
│ B2: ██████████                    30%     │
│ C1: ████████                      20%     │
└──────────────────────────────────────────┘
```

- Visualisasi menggunakan progress bar animasi dengan warna berbeda per level.
- Level tanpa soal otomatis di-skip (tidak ditampilkan).
- Tujuan: User bisa mengidentifikasi area kelemahan dan kekuatan mereka per tingkat kesulitan.

---

### D. Alur Data Scoring (Diagram)

```
┌─────────────────────────────────────────────────────────────────────┐
│                        PRACTICE MODE                                │
│                                                                     │
│  User Submit Quiz ──► calculatePracticeScore() ──► save_score.php   │
│                              │                          │           │
│                    ┌─────────┴─────────┐     ┌──────────┴────────┐  │
│                    │  Level A1/C2:     │     │ INSERT/UPDATE ke  │  │
│                    │  Mastery Rate     │     │ practice_scores:  │  │
│                    │  (0-100%)         │     │ score, itp_score, │  │
│                    │                   │     │ duration_seconds  │  │
│                    │  Level A2-C1:     │     └───────────────────┘  │
│                    │  ITP Score +      │                            │
│                    │  Mastery Rate     │                            │
│                    └───────────────────┘                            │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                          TEST MODE                                  │
│                                                                     │
│  User Submit Exam ──► submit_test.php ──► test-result.php           │
│                            │                    │                   │
│                  ┌─────────┴─────────┐   ┌──────┴───────────────┐   │
│                  │ convertRawToITP() │   │ Halaman Hasil:       │   │
│                  │ + Diagnostic Calc │   │ • Skor ITP (circle)  │   │
│                  │                   │   │ • Badge CEFR Level   │   │
│                  │ INSERT ke         │   │ • Stats Benar/Salah  │   │
│                  │ test_scores:      │   │ • Diagnostic Report  │   │
│                  │ raw_score,        │   │   (progress bar per  │   │
│                  │ toefl_score,      │   │    level CEFR)       │   │
│                  │ cefr_level        │   └──────────────────────┘   │
│                  └───────────────────┘                              │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 🎮 Elemen Gamifikasi

| Elemen | Implementasi |
|--------|-------------|
| **Level Progression** | 6 level CEFR (A1→C2) yang terkunci berurutan |
| **XP / Score System** | Mastery Rate (0–100%) dari setiap quiz practice diakumulasi sebagai XP di Leaderboard |
| **TOEFL ITP Scoring** | Konversi skor ke skala standar TOEFL ITP Reading (31–67) via linear interpolation per range |
| **CEFR Mapping** | Predikat level CEFR (A2–C1) berdasarkan skor ITP; badge bergaya gamifikasi di profil dropdown |
| **Diagnostic Report** | Analisis performa per level CEFR dengan progress bar visual animasi di halaman hasil ujian |
| **Leaderboard** | Peringkat global Top 50 berdasarkan total akumulasi XP (`SUM(mastery_rate)`) |
| **Perfect Streak** | Tracking jumlah skor 100 berturut-turut (minimum 2 untuk ditampilkan) |
| **Progress Tracking** | Progress bar per level, per topik, dan keseluruhan |
| **Achievement Feed** | Activity Feed yang menampilkan pencapaian terbaru (skor ≥ 70) |
| **Unlock System** | Level dan paket ujian terkunci, memberi motivasi untuk menyelesaikan yang sebelumnya |
| **Performance Graph** | Visualisasi tren performa dari waktu ke waktu (Line Chart SVG custom) |

---

## 🔒 Mekanisme Keamanan

| Aspek | Implementasi |
|-------|-------------|
| **Session Guard** | Setiap halaman mengecek `$_SESSION['user_id']` di awal |
| **Role-Based Access** | Admin dashboard hanya bisa diakses jika `$_SESSION['role'] === 'admin'` |
| **Password Hashing** | `password_hash()` + `password_verify()` |
| **Prepared Statement** | Digunakan di beberapa query sensitif (login, score history, manage account) |
| **Input Sanitization** | `htmlspecialchars()`, `mysqli_real_escape_string()`, `trim()` |
| **Cache Prevention** | Header anti-cache setelah login/logout (`no-store, no-cache`) |
| **Anti-Repeat Exam** | Double protection: client-side (tombol disabled) + server-side (cek database sebelum render soal) |
| **Server-Side Timer** | Timer ujian disimpan di session, tidak bisa dimanipulasi via browser |
| **Timezone Sync** | PHP + MySQL timezone disinkronkan ke WIB (`Asia/Jakarta` / `+07:00`) pada setiap koneksi untuk memastikan akurasi waktu pada tabel `TIMESTAMP` |

---

> 📌 **Catatan**: Dokumen ini terakhir diperbarui berdasarkan analisis kode sumber pada `28 Juli 2026`. Perubahan terbaru meliputi:
> - **Favicon**: Penambahan custom favicon (`assets/favicon.png`) di seluruh halaman (user + admin).
> - **Timezone Sync**: Sinkronisasi zona waktu PHP (`Asia/Jakarta`) dan MySQL (`+07:00`) pada `koneksi.php` untuk memperbaiki selisih waktu 14 jam pada server hosting InfinityFree.
> - **Leaderboard AJAX + Skeleton Loading**: Pencarian dan filter leaderboard menggunakan Fetch API dengan animasi skeleton shimmer sebagai placeholder saat memuat data.
> - **Leaderboard Responsif Mobile**: Kolom `Articles Passed` disembunyikan di mobile, layout filter vertikal, header kolom `white-space: nowrap`, dan `table-layout: fixed`.
> - **Custom Modal Pop-Up (Test Path)**: `alert()` bawaan browser pada paket ujian terkunci diganti dengan modal pop-up bergaya premium (glassmorphism + backdrop blur), identik dengan Practice Path.
> - **Deskripsi Paket Test A–D**: Deskripsi setiap paket diubah menjadi narasi progresif (baseline → consistency → mastery → final milestone) karena semua paket memiliki komposisi soal yang sama (8 soal × 4 level CEFR = 32 soal).
> - **Koreksi Spesifikasi Ujian**: Durasi ujian dikoreksi dari 55 menit menjadi **40 menit**, jumlah soal dari 50 menjadi **32 soal** per paket.
> - **Encoding Bug Fix**: Penambahan `mysqli_set_charset($conn, "utf8mb4")` pada `config/koneksi.php` untuk memperbaiki kemunculan simbol tanda tanya () pada teks bacaan akibat masalah *character encoding*.
> - **UI Test Result**: Penghapusan kata "Empty" pada keterangan hasil ujian "Wrong / Empty" karena sistem telah dirancang sedemikian rupa agar form soal ujian tidak bisa di-submit apabila ada jawaban kosong.
> - **Performance Graph (Test Path)**: Penambahan visualisasi grafik Line Chart interaktif untuk melacak tren performa (skala ITP maks 67) dari riwayat setiap Paket Ujian (Test) yang telah diselesaikan pada Dashboard pengguna.
> - **Form Resubmission Fix (PRG Pattern)**: Penerapan pola arsitektur *Post/Redirect/Get* pada halaman `manage_account.php` untuk mencegah munculnya *popup* peringatan *Confirm Form Resubmission* dari browser ketika pengguna menekan F5/Refresh.
> - **Password Validation & Visibility Toggle**: Penambahan validasi untuk menolak *password* baru jika sama dengan *password* lama, melengkapi form dengan fitur ikon mata (*show/hide password*) pada `manage_account.php` dan `signup.html`, serta standardisasi terjemahan pesan *error/success* ke dalam Bahasa Inggris secara utuh.
