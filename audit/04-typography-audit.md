# 04 — Typography & Readability Audit

**Audit Date:** 2026-11-11  
**Project:** Website Profil & CMS Sekolah (SMK Negeri Contoh Indonesia)  
**Standard Reference:** Web Typography Best Practices, WCAG 2.2 Text Spacing (SC 1.4.12), Semantic Heading Hierarchy  
**Auditor Role:** UI Designer & Typography Quality Engineer  
**Status:** Pre-Production Assessment  

---

## 1. Executive Summary

Audit tipografi mengevaluasi pemilihan keluarga font (*font families*), skala ukuran font, bobot (*font weight*), tinggi baris (*line height*), spasi huruf (*letter spacing*), hirarki heading semantik, serta strategi pemuatan berkas font (*font loading & fallback*).

Sistem tipografi aplikasi dibangun dengan fondasi yang solid:
- Display font **Sora** memberikan kesan institusional modern, tegas, dan berwibawa pada judul-judul utama.
- Body font **Inter** menjamin keterbacaan tinggi (*high legibility*) pada teks deskripsi, tabel data, dan artikel berita.
- Monospace font **JetBrains Mono** diaplikasikan dengan tepat pada kode jurusan, nomor pendaftaran PPDB, dan nomor SKL.
- Penggunaan fungsi CSS `clamp()` pada judul hero dan section title memastikan tipografi bersifat responsif dan dinamis (*fluid typography*).

Namun, ditemukan beberapa isu struktural terkait semantik heading dan dependensi pemuatan font eksternal.

---

## 2. Typography Findings Catalog

### Finding TYPO-01: Multi-H1 dan Pelompatan Level Hirarki Heading pada Halaman Beranda
- **Category:** Semantic Heading Hierarchy & SEO Structure
- **Severity:** Medium
- **File & Lokasi:** `pages/home.php:48, 83, 115-119, 346-356`
- **Bukti Temuan:**
  1. Pada carousel hero banner:
     ```php
     <?php foreach ($banners as $index => $b): ?>
       <h1><?= e($b['title']) ?></h1>
     <?php endforeach; ?>
     ```
     Jika sekolah memiliki 3 banner slider aktif, halaman beranda akan menghasilkan **3 buah elemen `<h1>` terpisah** di DOM.
  2. Pada seksi Sambutan Kepala Sekolah:
     ```html
     <h2 class="section-title">Sambutan Kepala Sekolah</h2>
     ...
     <h5 class="mb-0 text-primary fw-bold"><?= e($principal['principal_name']) ?></h5>
     ```
     Terdapat lompatan tingkat heading dari `<h2>` langsung menuju `<h5>` tanpa melewati `<h3>` dan `<h4>`.
  3. Pada seksi Testimoni Alumni:
     ```html
     <h2 class="section-title">Apa Kata Alumni?</h2>
     ...
     <h6 class="testimoni-name"><?= e($t['name']) ?></h6>
     ```
     Heading melompat langsung dari `<h2>` ke `<h6>`.
- **Dampak:** Melanggar standar aksesibilitas WCAG 2.2 (SC 1.3.1 Info and Relationships) dan membingungkan pengguna alat pembaca layar (*screen reader*) yang menavigasi halaman berdasarkan struktur pohon heading. Struktur heading yang melompat juga menurunkan skor audit SEO Lighthouse.
- **Rekomendasi Solusi:** 
  1. Tetapkan satu elemen `<h1>` tunggal pada beranda (misalnya nama resmi sekolah pada hero), dan jadikan judul slider banner sebagai `h2` atau `p.display-title`.
  2. Gunakan urutan semantik berurutan: `<h2>` untuk judul seksi, `<h3>` untuk kartu/nama sub-komponen, dan gunakan kelas styling visual Bootstrap (seperti `.h5` atau `.h6`) untuk mengatur ukuran visual tanpa merusak semantik HTML.
- **Verification Method:** Jalankan pemeriksaan struktur dokumen dengan ekstensi HTML5 Outliner atau Lighthouse Accessibility Audit.
- **Status:** Confirmed

---

### Finding TYPO-02: Ketergantungan CDN Google Fonts Eksternal di Lingkungan Offline
- **Category:** Font Loading, Resilience, & Performance
- **Severity:** Low
- **File & Lokasi:** `includes/header.php:70-71` & `admin/includes/header.php:28-29`
- **Bukti Temuan:**
  ```html
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
  ```
- **Dampak:** Semua pustaka CSS, JS, dan ikon (Bootstrap, Bootstrap Icons, Chart.js, jQuery, Summernote) telah disimpan secara lokal di folder `assets/vendor/` agar mandiri. Namun, keluarga font Sora dan Inter masih mengandalkan CDN Google Fonts. Pada server lokal sekolah yang terisolasi (*intranet / air-gapped lab*) atau saat jaringan mengalami kendala koneksi ke Google, halaman akan menggunakan font fallback sistem (`Segoe UI` / `Roboto`) yang memiliki metrik lebar karakter berbeda, menyebabkan layout teks bergeser (*Cumulative Layout Shift / FOIT*).
- **Rekomendasi Solusi:** Unduh berkas font web WOFF2 untuk Sora dan Inter, letakkan di `assets/fonts/` atau `assets/vendor/fonts/`, dan muat melalui `@font-face` lokal di `style.css`.
- **Verification Method:** Uji akses halaman dengan mematikan koneksi internet (mode offline di browser DevTools).
- **Status:** Confirmed

---

### Finding TYPO-03: Inkonsistensi Level Heading pada Widget Sidebar Detail Halaman
- **Category:** Consistency & Semantic Markup
- **Severity:** Low
- **File & Lokasi:** `pages/jurusan-detail.php:248, 296, 322`
- **Bukti Temuan:**
  Pada sidebar halaman detail jurusan:
  - Baris 248: `<h3 class="h6 fw-bold text-primary mb-3 ...">Informasi Ringkas</h3>`
  - Baris 296: `<h2 class="h6 fw-bold text-primary mb-3">Jurusan Lainnya</h2>`
  - Baris 322: `<h2 class="h6 fw-bold text-primary mb-2">Punya Pertanyaan?</h2>`
- **Dampak:** Terjadi inkonsistensi level heading untuk elemen-elemen setara (*peer widgets*) di dalam sidebar yang sama (bercampur antara `<h2>` dan `<h3>`).
- **Rekomendasi Solusi:** Standarkan seluruh judul widget sidebar menjadi `<h3>` konsisten di seluruh halaman detail publik.
- **Verification Method:** Tinjau tag markup pada `pages/jurusan-detail.php`.
- **Status:** Confirmed

---

### Finding TYPO-04: Line-Height dan Reading Ergonomics pada Teks Panjang Berita
- **Category:** Text Readability & Typography Metrics
- **Severity:** Informational
- **File & Lokasi:** `assets/css/style.css:60-65, 850-860`
- **Bukti Temuan:**
  Line-height global heading diatur pada `line-height: 1.25`, dan body text artikel berita menggunakan `line-height: 1.7`.
- **Dampak:** Teks artikel berita sangat nyaman dibaca dengan line-height 1.7. Namun pada judul berita yang panjang (lebih dari 3 baris di perangkat mobile), line-height 1.25 terasa agak terlalu rapat dan berpotensi saling menabrak huruf ascender/descender (seperti 'g', 'j', 'y' dengan huruf kapital di bawahnya).
- **Rekomendasi Solusi:** Berikan `line-height: 1.35` khusus untuk judul artikel berita pada breakpoint mobile (`@media (max-width: 767.98px)`).
- **Verification Method:** Render artikel dengan judul 4 baris pada viewport mobile 375px.
- **Status:** Confirmed

---

## 3. Matriks Spesifikasi Tipografi

| Token Tipografi | Font Family | Bobot (Weight) | Ukuran (Size) | Kegunaan |
| :--- | :--- | :---: | :---: | :--- |
| `--font-display` | **Sora**, sans-serif | 700 / 800 | 1.4rem – 3.2rem | Judul hero, section title, kartu utama |
| `--font-body` | **Inter**, sans-serif | 400 / 500 / 600 | 0.875rem – 1.05rem | Paragraf, deskripsi, navigasi menu, form |
| `--font-mono` | **JetBrains Mono**, monospace | 500 / 700 | 0.85rem – 1.1rem | Nomor registrasi PPDB, NISN, kode jurusan |
| `h1.display` | Sora | 800 | `clamp(2.1rem, 4vw, 3.2rem)` | Hero title banner beranda |
| `h2.section-title` | Sora | 800 | `clamp(1.4rem, 5vw, 2.15rem)` | Judul seksi halaman publik |
| `body` | Inter | 400 | 1rem (16px) base | Teks dasar seluruh halaman |
| `.badge / small` | Inter | 600 / 700 | 0.75rem – 0.8125rem | Label status, tanggal, kategori |
