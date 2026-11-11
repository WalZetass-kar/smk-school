# 02 — Application Security Audit

**Audit Date:** 2026-11-11  
**Project:** Website Profil & CMS Sekolah (SMK Negeri Contoh Indonesia)  
**Standard Reference:** OWASP Top 10 (2021) & Pre-Production Security Hardening  
**Auditor Role:** Application Security Engineer  
**Status:** Pre-Production Assessment  

---

## 1. Executive Summary

Audit keamanan menyeluruh dilakukan terhadap autentikasi, manajemen sesi, kontrol akses berbasis peran (RBAC), pencegahan injeksi SQL, Cross-Site Scripting (XSS), Cross-Site Request Forgery (CSRF), penanganan berkas unggahan, proteksi brute-force, dan paparan informasi sensitif.

Aplikasi telah mengimplementasikan pertahanan berlapis (*defense-in-depth*) yang sangat baik pada beberapa komponen kritis, antara lain:
- Dual-layer rate limiting (kombinasi per-IP dan per-identitas akun).
- Session fixation protection via `session_regenerate_id(true)`.
- Validasi Magic Bytes, verifikasi MIME asli, dan konversi paksa gambar ke format WebP via GD.
- Proteksi CSRF token di hampir seluruh operasi mutasi state (POST).
- Sanitasi HTML khusus Summernote melalui fungsi `cleanHtml()`.
- Pemblokiran eksekusi skrip PHP di folder `assets/uploads/` via `.htaccess`.

Namun, ditemukan beberapa celah keamanan dan inkonsistensi yang perlu ditangani sebelum rilis produksi:

---

## 2. Security Findings Catalog

### Finding SEC-01: Potensi Arbitrary File Read / Path Traversal pada Handler Unduhan Dokumen
- **Category:** OWASP A01:2021 — Broken Access Control / Path Traversal
- **Severity:** High
- **File & Lokasi:** `pages/unduhan-download.php:26-30`
- **Bukti Temuan:**
  ```php
  $filePath = BASE_PATH . '/' . ltrim($doc['file_path'], '/\\');
  // Anti Path Traversal Validation (Base Directory: BASE_PATH, Target: $filePath)
  if (file_exists($filePath) && is_file($filePath) && isSafePath(BASE_PATH, $filePath)) {
      $mime = mime_content_type($filePath) ?: 'application/octet-stream';
      ...
      readfile($filePath);
  ```
- **Dampak:** Validasi `isSafePath()` menggunakan `BASE_PATH` (root folder aplikasi) sebagai direktori basis. Jika record `file_path` di tabel `downloads` dimanipulasi atau diarahkan ke `config/config.php`, `.env`, atau `database/database.sql`, validasi `isSafePath` akan bernilai **TRUE** karena berkas-berkas tersebut memang berada di dalam `BASE_PATH`. Pengunjung publik dapat mengunduh berkas konfigurasi atau source code server.
- **Rekomendasi Solusi:** Ubah direktori basis validasi menjadi folder unggahan yang sah, yaitu `UPLOADS_PATH` (`BASE_PATH . '/assets/uploads'`), dan pastikan file target wajib berada di dalam subdirektori unduhan:
  ```php
  $uploadsDir = realpath(UPLOADS_PATH);
  $realTarget = realpath($filePath);
  if ($realTarget && str_starts_with($realTarget, $uploadsDir) && is_file($realTarget)) {
      ...
  }
  ```
- **Verification Method:** Simulasi record database dengan `file_path = 'config/config.php'` lalu akses endpoint `/unduhan/download/{id}`.
- **Status:** Confirmed

---

### Finding SEC-02: Kebocoran Berkas Sensitif & Backup SQL saat Menjalankan Server Dev CLI
- **Category:** OWASP A05:2021 — Security Misconfiguration
- **Severity:** High
- **File & Lokasi:** `router.php:10-33`
- **Bukti Temuan:**
  ```php
  // Block direct access to sensitive folders (matching .htaccess rules)
  if (str_starts_with($uri, '/config/') || str_starts_with($uri, '/includes/')) {
      http_response_code(403);
      echo 'Access forbidden';
      exit;
  }
  ...
  // Let static files and existing admin PHP files be handled directly
  if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
      return false;
  }
  ```
- **Dampak:** Apache memblokir berkas `.sql`, `.env`, `.log` dan folder `database/` melalui `.htaccess`. Namun jika developer/penguji menjalankan aplikasi menggunakan server built-in PHP (`php -S localhost:8000 router.php`), `router.php` hanya memblokir `/config/` dan `/includes/`. Permintaan HTTP langsung ke `http://localhost:8000/assets/backups/backup_auto_smk_school_20261026_234313.sql` atau `http://localhost:8000/.env` akan dilayani langsung oleh server built-in karena `file_exists` mengembalikan true.
- **Rekomendasi Solusi:** Selaraskan filter penolakan di `router.php` agar identik dengan aturan `.htaccess`:
  ```php
  if (preg_match('#^/(config|includes|database|pages|scratch|assets/backups)/#i', $uri) ||
      preg_match('/\.(sql|env|md|log|git|gitignore|lock|json|bak|swp|dist)$/i', $uri)) {
      http_response_code(403);
      exit('Access forbidden');
  }
  ```
- **Verification Method:** Jalankan server built-in PHP dan lakukan curl ke berkas SQL di `assets/backups/`.
- **Status:** Confirmed

---

### Finding SEC-03: Pengecekan Izin Super Admin Tidak Diterapkan pada Pengaturan WhatsApp Gateway
- **Category:** OWASP A01:2021 — Broken Access Control / Missing Function Level Access Control
- **Severity:** Medium
- **File & Lokasi:** `admin/whatsapp/index.php:6-8`
- **Bukti Temuan:**
  ```php
  require_once __DIR__ . '/../../config/config.php';
  require_once __DIR__ . '/../../includes/auth.php';
  // Tidak ada pemanggilan requireSuperAdmin();
  ```
- **Dampak:** Pengguna admin biasa (`role = 'admin'`) dapat mengakses halaman ini, melihat API Token pihak ketiga (Fonnte/Wablas), mengubah nomor dan endpoint gateway, serta memicu fitur `test_send` untuk mengirim pesan sewenang-wenang menggunakan kuota sekolah. Modul sensitif tingkat sistem seharusnya dibatasi hanya untuk `superadmin`.
- **Rekomendasi Solusi:** Tambahkan `requireSuperAdmin();` di baris ke-8 pada `admin/whatsapp/index.php`.
- **Verification Method:** Login sebagai akun `admin` biasa lalu navigasikan ke `http://localhost/smk-school/admin/whatsapp/index.php`.
- **Status:** Confirmed

---

### Finding SEC-04: Paparan Data Pribadi Calon Siswa (PII) melalui Link Cetak Bukti PPDB
- **Category:** OWASP A01:2021 — Broken Object Level Authorization (BOLA) & Privacy Exposure
- **Severity:** Medium
- **File & Lokasi:** `pages/ppdb-cek.php:303` & `pages/ppdb-cetak.php:9, 217`
- **Bukti Temuan:**
  Pada `pages/ppdb-cek.php`, pencarian hanya membutuhkan NISN siswa (data semi-publik di lingkungan sekolah):
  ```php
  WHERE r.registration_no = ? OR r.nisn = ?
  ```
  Di halaman hasil, terdapat tautan langsung ke halaman cetak kartu bukti pendaftaran:
  ```html
  <a href="<?= e(BASE_URL) ?>/ppdb/cetak?reg=<?= urlencode($result['registration_no']) ?>" target="_blank">
  ```
  Pada `pages/ppdb-cetak.php:217`, data pribadi ditampilkan secara telanjang tanpa masking:
  ```php
  <tr><th>NISN / NIK</th><td><?= e($data['nisn']) ?> / <?= e($data['nik']) ?></td></tr>
  ```
  Serta menampilkan nama lengkap orang tua, nomor telepon orang tua, dan alamat lengkap.
- **Dampak:** Pihak yang hanya mengetahui NISN seorang siswa dapat mengetahui NIK lengkap (16 digit), nomor kontak orang tua, dan alamat rumah, melanggar prinsip kepatuhan privasi data pribadi (UU PDP).
- **Rekomendasi Solusi:** 
  1. Halaman cetak bukti pendaftaran publik sebaiknya memerlukan kombinasi NISN + Tanggal Lahir (atau token unik yang hanya diberikan saat pertama kali berhasil submit formulir pendaftaran).
  2. Lakukan masking parsial pada NIK di lembar cetak umum, atau simpan token verifikasi cetak ke dalam session pendaftar.
- **Verification Method:** Akses `/ppdb/cek-status` menggunakan NISN publik, lalu klik cetak kartu.
- **Status:** Confirmed

---

### Finding SEC-05: Kebijakan Content-Security-Policy (CSP) Memblokir Iframe Google Maps
- **Category:** OWASP A05:2021 — Security Misconfiguration
- **Severity:** Medium
- **File & Lokasi:** `includes/header.php:40` & `admin/includes/header.php:16`
- **Bukti Temuan:**
  Header CSP yang dikirimkan:
  ```http
  Content-Security-Policy: ... frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://api.qrserver.com;
  ```
  Namun pada `pages/kontak.php:222`:
  ```html
  <iframe src="<?= e($embedMapUrl) ?>" style="border:0;" loading="lazy" ...></iframe>
  ```
  Di mana `$embedMapUrl` mengarah ke domain `https://www.google.com/maps/...` atau `https://maps.google.com/...`.
- **Dampak:** Browser modern yang mematuhi CSP akan memblokir rendering elemen iframe peta Google Maps di halaman Kontak dengan pesan error di konsol browser: *"Refused to frame 'https://www.google.com/' because it violates the following Content Security Policy directive: frame-src 'self' ..."*.
- **Rekomendasi Solusi:** Tambahkan `https://www.google.com` dan `https://maps.google.com` ke dalam direktif `frame-src` pada header CSP.
- **Verification Method:** Buka halaman `/kontak` pada browser dan periksa tab Console DevTools untuk CSP error.
- **Status:** Confirmed

---

### Finding SEC-06: Kunci Rahasia Verifikasi SKL Bersifat Hardcoded
- **Category:** OWASP A02:2021 — Cryptographic Failures / Hardcoded Secret
- **Severity:** Low
- **File & Lokasi:** `pages/kelulusan-cetak.php:58`
- **Bukti Temuan:**
  ```php
  $docRef = strtoupper(substr(hash('sha256', $student['nisn'] . $student['exam_number'] . 'SMK_SKL_SECRET'), 0, 10));
  ```
- **Dampak:** String garam kriptografi `'SMK_SKL_SECRET'` tertanam langsung dalam source code. Siapa pun yang memiliki akses ke repositori dapat mereplikasi atau memalsukan nomor referensi verifikasi SKL secara deterministik tanpa kontrol rotasi rahasia.
- **Rekomendasi Solusi:** Pindahkan secret key ke variabel environment `.env` (misalnya `APP_KEY` atau `SKL_SECRET_KEY`) dan berikan fallback default yang aman.
- **Verification Method:** Tinjau source code `pages/kelulusan-cetak.php:58`.
- **Status:** Confirmed

---

### Finding SEC-07: Interpolasi Variabel Numerik pada Pemanggilan `->query()` di Operasi Hapus Admin
- **Category:** OWASP A03:2021 — Injection (Code Quality & Robustness)
- **Severity:** Low
- **File & Lokasi:** 
  - `admin/achievements/delete.php:25`
  - `admin/agendas/delete.php:15`
  - `admin/bkk/delete.php:30`
  - `admin/downloads/delete.php:25`
  - `admin/extracurriculars/delete.php:25`
  - `admin/graduations/delete.php:15`
  - `admin/guestbook/delete.php:15`
  - `admin/ppdb/delete.php:26`
- **Bukti Temuan:**
  ```php
  $id = (int)($_POST['id'] ?? 0);
  ...
  $title = $db->query("SELECT title FROM agendas WHERE id = $id")->fetchColumn() ?: "ID #$id";
  ```
- **Dampak:** Meskipun aman secara teknis saat ini karena `$id` telah di-cast ke tipe `int`, interpolasi variabel langsung ke dalam SQL string menyimpang dari standar arsitektur proyek (yang mewajibkan prepared statement konsisten di setiap query) dan rentan terhadap regresi kode jika tipe variabel diubah di masa depan.
- **Rekomendasi Solusi:** Ganti dengan PDO prepared statement standar:
  ```php
  $stmt = $db->prepare("SELECT title FROM agendas WHERE id = ?");
  $stmt->execute([$id]);
  $title = $stmt->fetchColumn() ?: "ID #$id";
  ```
- **Verification Method:** Tinjau pola `->query()` pada berkas `admin/**/delete.php`.
- **Status:** Confirmed

---

### Finding SEC-08: Kurangnya Header HSTS (HTTP Strict Transport Security)
- **Category:** OWASP A05:2021 — Security Misconfiguration
- **Severity:** Informational
- **File & Lokasi:** `.htaccess:19-26` dan `includes/header.php:34-41`
- **Bukti Temuan:** Header `Strict-Transport-Security` belum didefinisikan baik pada `.htaccess` maupun skrip PHP header.
- **Dampak:** Pengunjung yang pertama kali mengakses web via HTTP berpotensi rentan terhadap serangan SSL Stripping man-in-the-middle sebelum di-redirect ke HTTPS.
- **Rekomendasi Solusi:** Tambahkan header HSTS jika server mendeteksi protokol HTTPS:
  ```php
  if ($isHttps) {
      header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
  }
  ```
- **Verification Method:** Periksa HTTP response headers menggunakan curl `-I`.
- **Status:** Confirmed

---

## 3. Matriks Evaluasi OWASP Top 10

| OWASP Top 10 Category | Status Penilaian | Keterangan Evaluasi |
| :--- | :---: | :--- |
| **A01: Broken Access Control** | ⚠️ Needs Fix | Role check sudah ada pada `requireSuperAdmin()`, namun terlewat pada modul WhatsApp; download handler perlu pembatasan base path. |
| **A02: Cryptographic Failures** | ⚠️ Minor Issue | Password hashing menggunakan bcrypt (`PASSWORD_DEFAULT`), namun salt hash SKL hardcoded. |
| **A03: Injection** | ✅ Solid | Semua query input publik menggunakan PDO prepared statements. Seluruh delete script meng-cast ID integer. |
| **A04: Insecure Design** | ⚠️ Minor Issue | Pencarian data PPDB hanya berbasis NISN dapat mengungkap NIK lengkap via cetak formulir. |
| **A05: Security Misconfiguration** | ⚠️ Needs Fix | CSP `frame-src` memblokir Google Maps iframe; `router.php` tidak memblokir file backup SQL. |
| **A06: Vulnerable & Outdated Components** | ✅ Solid | Bootstrap 5.3.3, jQuery 3.7.1, Chart.js 4.4.1 — versi stabil terkini. |
| **A07: Identification & Auth Failures** | ✅ Solid | Dual-layer brute force rate limiting, session regeneration, session timeout 60 menit. |
| **A08: Software & Data Integrity Failures** | ✅ Solid | Upload divalidasi Magic Bytes, MIME, ekstensi whitelist, dan di-re-encode ke WebP via GD. |
| **A09: Security Logging & Monitoring** | ✅ Solid | Tabel `activity_logs` mencatat IP, user-agent, username, aksi, dan modul admin secara detail. |
| **A10: Server-Side Request Forgery (SSRF)** | ✅ Solid | Tidak ada fetching URL arbitrary dari sisi server berdasarkan input pengguna luar. |
