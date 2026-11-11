# 03 — UI & UX Design Audit

**Audit Date:** 2026-11-11  
**Project:** Website Profil & CMS Sekolah (SMK Negeri Contoh Indonesia)  
**Standard Reference:** Nielsen Norman Group Usability Heuristics, WCAG 2.2 Design Standards, Mobile-First UX  
**Auditor Role:** UI/UX Designer & Frontend Quality Engineer  
**Status:** Pre-Production Assessment  

---

## 1. Executive Summary

Audit antarmuka dan pengalaman pengguna (UI/UX) difokuskan pada hirarki visual, layout dan jarak (*spacing*), konsistensi *design system*, kontras warna, navigasi responsif, status interaksi tombol/form, serta *empty/loading/error states*.

Secara visual, website mengusung gaya modern, bersih, dan institusional yang sangat solid:
- Pemilihan palet warna konsisten: Primary Deep Navy (`#0F2C59`), Accent Amber (`#F59E0B`), dan Clean Slate Off-White (`#F8FAFC`).
- Tipografi profesional memadukan display font **Sora** untuk judul dan **Inter** untuk keterbacaan teks isi.
- Seluruh tabel master data admin dibungkus dalam container responsif (`.table-responsive`).
- Halaman publik dilengkapi *empty states* yang ramah pengguna dengan ikon dan arahan yang jelas.

Namun, terdapat sejumlah temuan UX kritis pada sistem navigasi, umpan balik validasi formulir, dan penanganan layout tumpuk (*stacking*) pada layar perangkat bergerak (*mobile*).

---

## 2. UI/UX Findings Catalog

### Finding UI-01: Indikator Menu Aktif (Active State) Navbar Publik Gagal Berfungsi pada Subdirektori
- **Category:** Navigation & Information Architecture
- **Severity:** Medium
- **File & Lokasi:** `includes/navbar.php:3-11, 43, 60, 78`
- **Bukti Temuan:**
  ```php
  $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
  ...
  <li class="nav-item"><a class="nav-link<?= navActive('/', $currentPath) ?>" href="<?= e(BASE_URL) ?>/">Beranda</a></li>
  ...
  <a class="nav-link dropdown-toggle<?= $currentPath === '/jurusan' ? ' active' : '' ?>" ...>
  ```
- **Dampak:** Variabel `$currentPath` mengambil URI langsung tanpa menghapus prefix subdirektori proyek (`BASE_URL`, misalnya `/smk-school`). Akibatnya, pada instalasi lokal XAMPP atau direktori hosting bertingkat, perbandingan `$currentPath === '/jurusan'` atau `navActive('/', $currentPath)` selalu bernilai **FALSE** karena nilai `$currentPath` yang sebenarnya adalah `/smk-school/jurusan`. Indikator visual halaman yang sedang dibuka pengguna tidak pernah menyala (*active class* hilang).
- **Rekomendasi Solusi:** Normalisasi `$currentPath` dengan memotong prefix `BASE_URL` sebagaimana dilakukan pada front controller `index.php`:
  ```php
  $urlPrefix = parse_url(BASE_URL, PHP_URL_PATH);
  if ($urlPrefix && $urlPrefix !== '/' && str_starts_with($currentPath, $urlPrefix)) {
      $currentPath = substr($currentPath, strlen($urlPrefix));
  }
  $currentPath = rtrim($currentPath, '/') ?: '/';
  ```
- **Verification Method:** Akses halaman `/smk-school/berita` pada browser dan periksa atribut class pada link navbar Berita.
- **Status:** Confirmed

---

### Finding UI-02: Potensi Tumpang-Tindih (Overlapping) Fixed Navbar dan Announcement Ticker di Layar Mobile
- **Category:** Layout & Mobile Responsiveness
- **Severity:** Medium
- **File & Lokasi:** `includes/navbar.php:15-28`, `assets/css/style.css:120-165`
- **Bukti Temuan:**
  Ketika pengumuman berjalan aktif (`$hasTicker = true`), bar pengumuman `.top-announcement-bar` dan `.navbar-smk` keduanya menggunakan posisi `fixed-top`. CSS mengatur offset navbar melalui kelas `.navbar-smk.has-ticker { top: 38px; }`.
- **Dampak:** Pada viewport layar sangat kecil (lebar < 400px), jika teks info badge *"INFO"* dan teks berjalan bertumpuk atau jika tinggi pengumuman membesar, navbar tetap tertahan pada offset kaku `top: 38px`, mengakibatkan bagian atas navigasi atau header hero tertutup sebagian oleh ticker.
- **Rekomendasi Solusi:** Gunakan CSS variable dinamis (`--ticker-height`) atau posisikan ticker dalam aliran dokumen normal (`sticky-top`) bersama header, atau pastikan tinggi ticker terkunci tepat satu baris (`white-space: nowrap; height: 38px; overflow: hidden;`) pada semua ukuran layar.
- **Verification Method:** Buka viewport simulator Chrome DevTools ukuran 360x640px dengan fitur running text aktif.
- **Status:** Confirmed

---

### Finding UI-03: Ketiadaan Umpan Balik Validasi Form Inline (Field-Level Error Feedback)
- **Category:** Form Usability & Error Prevention
- **Severity:** Medium
- **File & Lokasi:** `admin/news/form.php`, `admin/banners/form.php`, `admin/teachers/form.php`, `pages/kontak.php`
- **Bukti Temuan:**
  Validasi formulir yang gagal hanya memunculkan daftar error di bagian paling atas halaman (`<div class="alert alert-danger">`), sementara input field yang bersangkutan tidak ditandai dengan kelas Bootstrap `.is-invalid` maupun teks umpan balik langsung di bawah input.
- **Dampak:** Pada formulir yang panjang di perangkat mobile, pengguna harus scroll kembali ke puncak halaman untuk membaca pesan kesalahan, kemudian menebak letak input yang keliru karena tidak ada indikator visual berwarna merah (*inline feedback*) pada input yang bermasalah.
- **Rekomendasi Solusi:** Tambahkan kelas `.is-invalid` secara kondisional pada elemen input yang gagal validasi dan sertakan kontainer `<div class="invalid-feedback">` di bawah masing-masing input.
- **Verification Method:** Submit formulir berita admin dengan field judul kosong dan periksa visual highlight pada form control.
- **Status:** Confirmed

---

### Finding UI-04: Kontras Warna Subtitle Sidebar Admin di Bawah Ambang Batas WCAG AA
- **Category:** Accessibility & Color Contrast
- **Severity:** Low
- **File & Lokasi:** `admin/includes/sidebar.php:28` & `assets/css/admin.css`
- **Bukti Temuan:**
  ```html
  <span class="sidebar-brand-subtitle text-nowrap text-white-50 d-block text-truncate" style="font-size: 11px;">
  ```
  Warna teks `text-white-50` (`rgba(255, 255, 255, 0.5)`) di atas latar belakang sidebar `--color-primary-dark` (`#081830`) menghasilkan rasio kontras ~3.8:1.
- **Dampak:** Teks ukuran kecil (11px) dengan rasio di bawah 4.5:1 sulit dibaca oleh staf admin atau pengguna dengan gangguan penglihatan parsial atau saat monitor berada di bawah pencahayaan terang.
- **Rekomendasi Solusi:** Tingkatkan kepekatan warna menjadi `text-white-75` atau `rgba(255, 255, 255, 0.75)` (rasio kontras > 6.5:1).
- **Verification Method:** Ukur nilai heksadesimal dengan Color Contrast Analyzer DevTools.
- **Status:** Confirmed

---

### Finding UI-05: Dialog Konfirmasi Penghapusan Seluruh Log Aktivitas Belum Menggunakan Modal Peringatan Khusus
- **Category:** User Error Prevention & Destructive Action UX
- **Severity:** Low
- **File & Lokasi:** `admin/activity-logs/index.php:55-63`
- **Bukti Temuan:**
  Tombol "Bersihkan Semua Catatan Log" menggunakan dialog browser JavaScript standar `confirm()`:
  ```html
  <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Peringatan: Seluruh riwayat catatan aktivitas akan dihapus permanen. Lanjutkan?');">
  ```
- **Dampak:** Dialog `confirm()` bawaan browser terlihat usang (*jarring*), tidak selaras dengan tema modern Bootstrap 5, dan dapat ditekan secara tidak sengaja (*accidental click*) tanpa rincian risiko yang jelas.
- **Rekomendasi Solusi:** Ganti dengan Bootstrap Modal bertema merah (*Destructive Confirmation Modal*) yang menampilkan jumlah baris data yang akan dihapus secara jelas.
- **Verification Method:** Klik tombol bersihkan log di dashboard admin.
- **Status:** Confirmed

---

## 3. Matriks Komponen Design System

| Komponen | Status Konsistensi | Catatan UX |
| :--- | :---: | :--- |
| **Buttons** | ✅ Sangat Baik | Menggunakan kelas `.btn-primary`, `.btn-accent`, dan `.btn-outline-*` dengan hover/active state jelas. |
| **Cards & Containers** | ✅ Sangat Baik | Menggunakan sudut membulat seragam (`rounded-4`), border halus (`border-0 shadow-sm`), padding konsisten. |
| **Modals** | ✅ Baik | Global Search Modal dan Image Lightbox berfungsi dengan backdrop dan tombol close yang jelas. |
| **Badges & Tags** | ✅ Sangat Baik | Status Publish/Draft, kategori, dan akreditasi memiliki diferensiasi warna yang intuitif. |
| **Form Inputs** | ⚠️ Perlu Peningkatan | Styling input konsisten, namun umpan balik validasi inline masih kurang. |
| **Responsive Grid** | ✅ Sangat Baik | Sistem grid Bootstrap 5 bekerja rapi pada desktop, tablet, dan smartphone. |
