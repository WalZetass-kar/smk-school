# 09 — Actionable Priority Fix List (Execution Record)

**Audit Date:** 2026-11-11  
**Project:** Website Profil & CMS Sekolah (SMK Negeri Contoh Indonesia)  
**Status Eksekusi:** **SELESAI (ALL P1, P2, & KEY P3 FIXED)**

---

## 1. Status Penyelesaian Berdasarkan Prioritas

| Prioritas | Kategori | Status Eksekusi | Keterangan Verifikasi |
| :--- | :--- | :---: | :--- |
| **P1-01** | Skema Database Master `database/database.sql` | ✅ FIXED | DDL tabel `site_modules`, `wa_gateway_settings`, dan `idx_ppdb_nisn` telah disatukan. |
| **P1-02** | Path Traversal pada Unduhan Dokumen | ✅ FIXED | Validasi diperketat ke `UPLOADS_PATH` di `pages/unduhan-download.php`. |
| **P1-03** | Kebocoran File Backup & .env pada CLI Server | ✅ FIXED | Pola regex blokir setara `.htaccess` ditambahkan di `router.php`. |
| **P2-01** | Izin Superadmin pada WhatsApp Gateway | ✅ FIXED | Ditambahkan `requireSuperAdmin()` di `admin/whatsapp/index.php`. |
| **P2-02** | Paparan Data NIK pada Cetak Bukti PPDB | ✅ FIXED | Ditambahkan verifikasi sesi pendaftar dan masking NIK untuk publik di `pages/ppdb-cetak.php`. |
| **P2-03** | CSP Memblokir Iframe Google Maps | ✅ FIXED | Ditambahkan `https://www.google.com` dan `https://maps.google.com` pada direktif `frame-src`. |
| **P2-04** | Active Menu Navbar pada Subdirektori | ✅ FIXED | Normalisasi `$currentPath` memotong prefix `BASE_URL` di `includes/navbar.php`. |
| **P2-05** | Browser Caching Berkas Statis di .htaccess | ✅ FIXED | Ditambahkan konfigurasi `mod_expires` (1 bulan CSS/JS, 1 tahun gambar/font). |
| **P2-06** | Tombol Pintas "Skip to Content" A11y | ✅ FIXED | Ditambahkan `<a href="#main-content">` di `includes/header.php`. |
| **P3-01** | Default Lisensi Jenjang Sekolah | ✅ FIXED | Konstanta `APP_SCHOOL_LEVEL` disesuaikan ke `'SMK'` di `config/constants.php`. |
| **P3-02** | Kunci Rahasia Verifikasi SKL | ✅ FIXED | `pages/kelulusan-cetak.php` kini mendukung `getenv('SKL_SECRET_KEY')`. |
| **P3-03** | Normalisasi URL Tombol Banner Beranda | ✅ FIXED | Mendukung URL eksternal dan relatif tanpa leading slash di `pages/home.php`. |
| **P3-04** | Penghapusan Gambar Sampah (*Orphan Files*) | ✅ FIXED | Dilengkapi pemanggilan `@unlink` pada skrip delete berita, banner, guru, galeri, partner. |
| **P3-05** | Lazy Loading Gambar Below-the-Fold | ✅ FIXED | Atribut `loading="lazy"` terpasang di gambar kartu jurusan, berita, alumni, sambutan. |
| **P3-06** | Asosiasi Label dan Input Form Kontak | ✅ FIXED | Atribut `for` dan `id` dipasangkan pada seluruh field di `pages/kontak.php`. |
| **P3-07** | Indeks Database Kolom `nisn` PPDB | ✅ FIXED | Indeks `idx_ppdb_nisn` telah aktif pada database langsung dan master SQL. |
