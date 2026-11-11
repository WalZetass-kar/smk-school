# 06 — Performance & Asset Optimization Audit

**Audit Date:** 2026-11-11  
**Project:** Website Profil & CMS Sekolah (SMK Negeri Contoh Indonesia)  
**Standard Reference:** Core Web Vitals (LCP, CLS, INP), Google PageSpeed Guidelines, Database Indexing Standards  
**Auditor Role:** Performance Engineer & Web Optimization Specialist  
**Status:** Pre-Production Assessment  

---

## 1. Executive Summary

Audit performa difokuskan pada efisiensi pemuatan berkas statis, ukuran bundle aset (*bundle size*), optimasi gambar, penerapan *lazy loading*, efisiensi query database (indeks dan query time), serta kebijakan caching peramban (*browser caching*).

Arsitektur sistem memiliki sejumlah keunggulan performa:
- Seluruh pustaka CSS/JS eksternal (Bootstrap, Chart.js, jQuery, Summernote) di-host secara lokal tanpa round-trip DNS eksternal tambahan.
- Fungsi upload otomatis mengompresi gambar dan mengonversinya ke format modern **WebP** dengan batas dimensi maksimal 1920px.
- Pustaka berat seperti Chart.js dan Summernote hanya dimuat di halaman admin yang relevan, menjaga halaman publik tetap ringan.
- Kecepatan response time PHP Native sangat cepat (< 30ms TTFB pada server lokal).

Namun, ditemukan beberapa peluang optimasi penting pada kebijakan caching berkas statis, atribut lazy loading, dan indeks database pada tabel berkapasitas besar.

---

## 2. Performance Findings Catalog

### Finding PERF-01: Ketiadaan Aturan Browser Caching (Cache-Control / Expires) pada Berkas Statis
- **Category:** Caching & Network Optimization
- **Severity:** Medium
- **File & Lokasi:** `.htaccess:1-38`
- **Bukti Temuan:**
  Berkas `.htaccess` tidak mengonfigurasi modul `mod_expires.c` atau header `Cache-Control` untuk file statis (`.css`, `.js`, `.webp`, `.png`, `.jpg`, `.woff2`, `.svg`).
- **Dampak:** Setiap kali pengguna berpindah antar halaman atau kembali berkunjung, browser akan melakukan HTTP request ulang untuk seluruh aset statis (Bootstrap CSS, main.js, logo, favicon, font). Hal ini meningkatkan konsumsi bandwidth server sekolah dan memperlambat First Contentful Paint (FCP) pada kunjungan berulang (*repeat visits*).
- **Rekomendasi Solusi:** Tambahkan konfigurasi `mod_expires` dan `mod_headers` di `.htaccess` untuk memberikan instruksi cache berdurasi 1 tahun pada aset dengan versi (`?v=...`) dan 1 bulan pada gambar:
  ```apache
  <IfModule mod_expires.c>
      ExpiresActive On
      ExpiresByType text/css "access plus 1 month"
      ExpiresByType application/javascript "access plus 1 month"
      ExpiresByType image/webp "access plus 1 year"
      ExpiresByType image/jpeg "access plus 1 year"
      ExpiresByType image/png "access plus 1 year"
      ExpiresByType font/woff2 "access plus 1 year"
  </IfModule>
  ```
- **Verification Method:** Periksa header response aset CSS/gambar menggunakan `curl -I` pada server Apache lokal.
- **Status:** Confirmed

---

### Finding PERF-02: Sebagian Gambar di Bawah Lipatan (Below-the-Fold) Belum Menggunakan `loading="lazy"`
- **Category:** Core Web Vitals — Largest Contentful Paint (LCP) & Network Payload
- **Severity:** Low
- **File & Lokasi:** `pages/home.php:108, 202, 318, 354` & `pages/berita.php:291`
- **Bukti Temuan:**
  Elemen gambar pada seksi Sambutan Kepala Sekolah, Kartu Jurusan, Kartu Berita Terbaru, dan Foto Testimoni Alumni di `home.php` tidak memiliki atribut `loading="lazy"`:
  ```html
  <img src="<?= e(BASE_URL . '/' . $m['image']) ?>" alt="<?= e($m['name']) ?>" class="jurusan-card-img">
  ```
- **Dampak:** Browser akan langsung mengunduh puluhan gambar kartu jurusan dan berita secara serentak di awal pemuatan halaman beranda, bersaing dengan rendering hero banner dan CSS kritis, sehingga memperlambat LCP pada koneksi internet seluler yang lambat.
- **Rekomendasi Solusi:** Tambahkan atribut `loading="lazy"` dan `decoding="async"` pada semua elemen gambar yang berada di bawah viewport awal (below-the-fold).
- **Verification Method:** Inspeksi elemen gambar di browser DevTools tab Network.
- **Status:** Confirmed

---

### Finding PERF-03: Ketiadaan Indeks Database pada Kolom `nisn` di Tabel `ppdb_registrations`
- **Category:** Database Query Efficiency
- **Severity:** Low
- **File & Lokasi:** `database/database.sql:307-339`
- **Bukti Temuan:**
  Tabel `ppdb_registrations` memiliki indeks untuk `registration_no` dan `status`, tetapi kolom `nisn` tidak memiliki indeks:
  ```sql
  WHERE r.registration_no = ? OR r.nisn = ?
  ```
- **Dampak:** Ketika ratusan atau ribuan calon siswa secara serentak memeriksa status PPDB di hari pengumuman menggunakan nomor NISN, MySQL terpaksa melakukan pemindaian tabel penuh (*Full Table Scan*) untuk mencocokkan `r.nisn`, meningkatkan utilisasi CPU database secara drastis saat traffic puncak (*traffic spike*).
- **Rekomendasi Solusi:** Tambahkan indeks komposit atau indeks tunggal pada kolom `nisn`:
  ```sql
  ALTER TABLE ppdb_registrations ADD INDEX idx_ppdb_nisn (nisn);
  ```
- **Verification Method:** Jalankan query `EXPLAIN SELECT * FROM ppdb_registrations WHERE registration_no = 'x' OR nisn = 'y';`.
- **Status:** Confirmed

---

### Finding PERF-04: Berkas CSS & JS Kustom (`style.css`, `admin.css`, `main.js`) Belum Diminifikasi
- **Category:** Asset Delivery & Payload Size
- **Severity:** Low
- **File & Lokasi:** `assets/css/style.css` (48 KB), `assets/css/admin.css` (16 KB), `assets/js/main.js` (22 KB)
- **Bukti Temuan:** Berkas stylesheet dan JavaScript kustom masih berupa kode mentah (*unminified*) dengan spasi dan komentar lengkap.
- **Dampak:** Total transfer payload CSS/JS bertambah sekitar ~35 KB lebih besar dari yang seharusnya jika diminifikasi (sekitar ~50% rasio kompresi).
- **Rekomendasi Solusi:** Sediakan berkas terkompresi `style.min.css` dan `main.min.js` untuk lingkungan produksi atau aktifkan kompresi gzip/brotli (`mod_deflate`) pada web server.
- **Verification Method:** Periksa ukuran file dan whitespace pada `assets/css/style.css`.
- **Status:** Confirmed

---

## 3. Matriks Ukuran Payload Aset (Aset Lokal)

| Berkas Aset | Ukuran Mentah | Estimasi Minified / Gzip | Catatan Delivery |
| :--- | :---: | :---: | :--- |
| `bootstrap.min.css` | 232 KB | ~35 KB (gzipped) | Dimuat di seluruh halaman |
| `style.css` | 48 KB | ~9 KB (gzipped) | Dimuat di seluruh halaman |
| `bootstrap-icons.css` | 100 KB | ~14 KB (gzipped) | Dimuat di seluruh halaman |
| `bootstrap.bundle.min.js` | 80 KB | ~23 KB (gzipped) | Dimuat di footer publik & admin |
| `main.js` | 22 KB | ~5 KB (gzipped) | Dimuat di footer publik |
| `chart.umd.min.js` | 205 KB | ~60 KB (gzipped) | **Khusus Admin Dashboard** (Publik Bebas) |
| `summernote-lite.min.js` | 164 KB | ~45 KB (gzipped) | **Khusus Admin Editor** (Publik Bebas) |
| `jquery.min.js` | 87 KB | ~30 KB (gzipped) | **Khusus Admin Editor** (Publik Bebas) |
