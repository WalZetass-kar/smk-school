# 05 — Functional Quality Assurance (QA) Audit

**Audit Date:** 2026-11-11  
**Project:** Website Profil & CMS Sekolah (SMK Negeri Contoh Indonesia)  
**Testing Scope:** End-to-End Route Execution, CRUD Flows, Form Handling, Authentication Lifecycle, Document Generation  
**Auditor Role:** Senior QA Engineer & Backend Reliability Engineer  
**Status:** Pre-Production Assessment  

---

## 1. Executive Summary

Pengujian fungsional otomatis dan manual dijalankan terhadap seluruh 25 rute publik statis, 5 rute detail dinamis, 25 modul panel admin, skrip impor/ekspor data, serta API pencarian langsung (`search-api.php`).

Hasil pengujian sintaks (`php -l`) mengonfirmasi **100% berkas PHP bebas dari error sintaks/parse**.
Suite uji regresi keamanan internal (37 skenario) lulus **37/37 (100% Passed)**.
Seluruh rute halaman publik utama (`/`, `/profil`, `/jurusan`, `/berita`, `/galeri`, `/kontak`, `/ppdb`, `/kelulusan`, `/agenda`, dll.) berhasil di-render dengan data database lokal tanpa fatal error.

Namun, ditemukan beberapa cacat fungsional (*functional defects*) pada normalisasi tautan tombol dinamis, penanganan berkas sampah (*orphaned files*), dan redirect flow formulir.

---

## 2. Functional QA Findings Catalog

### Finding FUNC-01: Inkonsistensi Penanganan URL Tombol Banner Beranda
- **Category:** Routing & Navigation Integrity
- **Severity:** Medium
- **File & Lokasi:** `pages/home.php:51-54` & `admin/banners/form.php:84`
- **Bukti Temuan:**
  Pada template beranda:
  ```html
  <a href="<?= e(BASE_URL . $b['button_url']) ?>" class="btn btn-hero btn-hero-primary">
  ```
- **Dampak:** 
  1. Jika admin memasukkan tautan eksternal (misalnya `https://kemdikbud.go.id`), string `BASE_URL` tetap ditempelkan di depannya, menghasilkan URL rusak seperti `http://localhost/smk-schoolhttps://kemdikbud.go.id`.
  2. Jika admin memasukkan tautan internal tanpa garis miring depan (misalnya `profil`), URL yang dihasilkan menjadi `http://localhost/smk-schoolprofil` (tanpa tanda pemisah `/`), memicu error HTTP 404.
- **Rekomendasi Solusi:** Buat helper fungsi normalisasi URL:
  ```php
  function formatButtonUrl(string $url): string {
      $url = trim($url);
      if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '//')) {
          return $url;
      }
      return BASE_URL . '/' . ltrim($url, '/');
  }
  ```
- **Verification Method:** Masukkan URL eksternal pada banner admin lalu klik tombol slider di beranda.
- **Status:** Confirmed

---

### Finding FUNC-02: Berkas Fisik Gambar Tidak Dihapus Saat Data Dihapus pada Sejumlah Modul Admin
- **Category:** File Lifecycle Management & Storage Hygiene
- **Severity:** Low
- **File & Lokasi:** 
  - `admin/news/delete.php:13`
  - `admin/banners/delete.php:12`
  - `admin/teachers/delete.php:12`
  - `admin/galleries/delete.php:12`
  - `admin/partners/delete.php:17`
- **Bukti Temuan:**
  Beberapa modul (seperti `achievements`, `bkk`, `downloads`, `extracurriculars`) melakukan penghapusan berkas fisik dengan rapi menggunakan `@unlink()`. Namun, pada `admin/news/delete.php` dan `admin/teachers/delete.php`, kode hanya menghapus baris tabel di database:
  ```php
  getDB()->prepare("DELETE FROM news WHERE id = ?")->execute([$id]);
  ```
- **Dampak:** Berkas gambar lama di folder `assets/uploads/` tetap tersimpan selamanya sebagai *orphan files*, menyebabkan pembengkakan kapasitas media (*storage bloat*) secara akumulatif.
- **Rekomendasi Solusi:** Ambil path gambar lama dari database sebelum eksekusi `DELETE`, lalu panggil `@unlink(BASE_PATH . '/' . $imagePath)` jika file fisik ada dan berada di dalam `UPLOADS_PATH`.
- **Verification Method:** Hapus satu artikel berita yang memiliki gambar unggahan, lalu periksa apakah file gambar di `assets/uploads/` terhapus atau tertinggal.
- **Status:** Confirmed

---

### Finding FUNC-03: Ketiadaan Parameter `exit;` Setelah Redirect di Sejumlah Titik Alur
- **Category:** Execution Flow Control
- **Severity:** Low
- **File & Lokasi:** `admin/change-password.php:5`
- **Bukti Temuan:**
  ```php
  require_once __DIR__ . '/../config/config.php';
  require_once __DIR__ . '/../includes/auth.php';

  redirect(BASE_URL . '/admin/profile-admin.php');
  ```
- **Dampak:** Fungsi `redirect()` di `includes/functions.php:17` memang memanggil `exit;`. Namun merupakan praktik pertahanan berlapis (*defensive programming*) untuk selalu menyertakan `exit;` tepat setelah fungsi redirect di berkas pemanggil guna mencegah potensi eksekusi instruksi berikutnya jika fungsi helper mengalami modifikasi di masa depan.
- **Rekomendasi Solusi:** Tambahkan `exit;` secara eksplisit setelah setiap pemanggilan `redirect(...)`.
- **Verification Method:** Inspeksi baris redirect di `admin/change-password.php`.
- **Status:** Confirmed

---

### Finding FUNC-04: Pengujian Impor Excel pada Data Kelulusan Memerlukan Ekstensi File Spesifik
- **Category:** Third-Party Data Import QA
- **Severity:** Low
- **File & Lokasi:** `admin/graduations/import.php:26`
- **Bukti Temuan:**
  Sistem hanya menerima ekstensi `.csv`, `.txt`, dan `.xls` (HTML Table format). Format modern `.xlsx` (OpenXML binary zip) ditolak dengan pesan error.
- **Dampak:** Staf tata usaha yang mengunggah berkas Excel bawaan Office modern (.xlsx) akan gagal mengimpor data dan harus melakukan `Save As` manual ke CSV atau XLS 97-2003 terlebih dahulu.
- **Rekomendasi Solusi:** Tambahkan petunjuk visual yang jelas di modal impor (misalnya: *"Simpan dokumen Anda sebagai format CSV UTF-8 atau Excel 97-2003 .xls sebelum mengunggah"*).
- **Verification Method:** Coba unggah file berekstensi `.xlsx` pada panel import kelulusan.
- **Status:** Confirmed

---

## 3. Matriks Hasil Pengujian Modul Aplikasi

| Modul Pengujian | Metode Verifikasi | Status | Catatan QA |
| :--- | :--- | :---: | :--- |
| **Public Homepage & Slider** | Automated Runner CLI & Browser | ✅ PASS | Banner slider, sambutan, jurusan, berita, alumni, partner berjalan mulus. |
| **Profil Sekolah (5 Submenu)** | Automated Runner CLI & Browser | ✅ PASS | Seluruh halaman profil merender konten statis & dinamis dengan benar. |
| **Katalog Jurusan & Detail** | Automated Runner CLI & Browser | ✅ PASS | Parameter slug regex rute bekerja, view counter dan share link aktif. |
| **Portal Berita & Search** | Automated Runner CLI & Browser | ✅ PASS | Pencarian kata kunci, pagination halaman, sidebar artikel populer normal. |
| **Galeri Foto & Lightbox** | Browser DOM Inspection | ✅ PASS | Filter kategori galeri bekerja, modal lightbox Bootstrap aktif. |
| **Formulir Kontak Publik** | Automated & Simulated POST | ✅ PASS | Honeypot bot trap aktif, captcha matematika tervalidasi, DB record tersimpan. |
| **Pendaftaran PPDB Online** | Automated & Simulated POST | ✅ PASS | Validasi input, upload foto/rapor, pembuatan nomor unik registrasi lulus. |
| **Cek Status & Cetak PPDB** | Simulated Query Parameter | ✅ PASS | Status seleksi tampil, QR Code verifikasi aktif, tampilan ramah cetak (print CSS). |
| **Cek Kelulusan & Cetak SKL**| Simulated Search POST | ✅ PASS | Pencarian NISN/No Ujian bekerja, SKL resmi tercetak dengan format multi-kertas. |
| **Buku Tamu Digital** | Simulated POST Form | ✅ PASS | Validasi captcha angka bekerja, data masuk ke panel admin dengan rapi. |
| **Live Search API** | JSON Request Endpoint | ✅ PASS | Return format JSON valid, multi-entitas (berita, jurusan, bkk, agenda). |
| **Admin Login & Logout** | Session & Cookie Assertion | ✅ PASS | Bcrypt verify sukses, session regenerated, rate limit login memblokir brute-force. |
| **Admin Dashboard & Charts** | PHP Ob-Start Capture | ✅ PASS | Stat card dan grafik Chart.js ter-render tanpa error. |
| **Database Backup & Dump** | SQL Dump Output Check | ✅ PASS | Struktur skema dan data ter-dump lengkap dengan format SQL valid. |
