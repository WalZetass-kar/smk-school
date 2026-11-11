# 01 — Project Discovery Audit

**Audit Date:** 2026-11-11  
**Project:** Website Profil & CMS Sekolah (SMK Negeri Contoh Indonesia)  
**Auditor Role:** Senior Software Engineer, Application Security Engineer, QA Engineer, UI/UX Designer, Performance Engineer  
**Status:** Pre-Production Assessment  

---

## 1. Executive Overview

Proyek ini adalah Content Management System (CMS) dan portal web profil sekolah berbasis **PHP Native (tanpa framework PHP)** dengan antarmuka berbasis **Bootstrap 5**, JavaScript vanilla, Chart.js, Summernote, serta penyimpanan relasional menggunakan **MySQL/MariaDB via PHP Data Objects (PDO)**.

Sistem dirancang untuk mendukung multi-jenjang sekolah (SMK, SMA, SMP, SD) dengan fitur toggle modul dinamis, manajemen konten komprehensif, pendaftaran peserta didik baru (PPDB Online), pengumuman kelulusan & SKL online dengan QR Code, direktori guru, bursa kerja khusus (BKK), direktori jurusan, dan integrasi WhatsApp Gateway.

---

## 2. Framework & Tech Stack Discovery

| Layer | Teknologi & Versi | Catatan Arsitektur |
| :--- | :--- | :--- |
| **Backend Runtime** | PHP 8.x Native (Procedural + OOP Helper Class) | Menggunakan PDO dengan prepared statements untuk akses database |
| **Database** | MySQL 8.x / MariaDB 10.x | 32 tabel pada live instance; InnoDB engine, UTF8MB4 |
| **Web Server** | Apache (HTTP Server) / PHP CLI Built-in | Dilengkapi `.htaccess` untuk rewrite URL & security headers; fallback `router.php` untuk dev CLI |
| **Frontend Framework**| Bootstrap 5.3.3 (CSS & Bundle JS lokal) | Disimpan lokal di `assets/vendor/bootstrap/` |
| **Iconography** | Bootstrap Icons v1.11.3 | Disimpan lokal di `assets/vendor/bootstrap-icons/` |
| **Visual Charts** | Chart.js v4.4.1 (UMD minified) | Disimpan lokal di `assets/vendor/chartjs/` |
| **DOM Utilities** | jQuery v3.7.1 | Disimpan lokal di `assets/vendor/jquery/` (digunakan Summernote) |
| **Rich Text Editor** | Summernote Lite v0.8.18 | Disimpan lokal di `assets/vendor/summernote/` |
| **Styling Engine** | Custom CSS Variables + Dynamic PHP CSS Injector | `assets/css/style.css`, `assets/css/admin.css`, `renderDynamicThemeStyles()` |

---

## 3. Directory Structure Analysis

```text
smk-school/
├── .agents/                    # Konfigurasi subagent & AI skill tools
├── .env.example                # Template konfigurasi environment database & debug
├── .gitignore                  # Definisi file yang diabaikan Git (.env, uploads, backup)
├── .htaccess                   # Hardening Apache, routing URL rewrite, blocking file rahasia
├── audit.md                    # Master prompt & panduan audit pre-production
├── audit/                      # [BARU] Laporan audit komprehensif 9 modul
├── index.php                   # Front controller (routing semua rute publik)
├── manifest.json               # Manifest Progressive Web App (PWA)
├── README.md                   # Dokumentasi instalasi, fitur, dan akun demo
├── robots.php / robots.txt     # Pengaturan dynamic robots crawling
├── router.php                  # Web server router script untuk PHP Built-in Server
├── scurity.md                  # Checklist security hardening
├── sitemap.php                 # Sitemap XML dinamis (berita & jurusan)
├── skills-lock.json            # Lockfile skill agent
├── admin/                      # Dashboard & CMS Panel
│   ├── login.php / logout.php  # Autentikasi admin
│   ├── dashboard.php           # Ringkasan analitik, statistik, & grafik aktivitas
│   ├── profile-admin.php       # Update identitas admin yang sedang aktif
│   ├── change-password.php     # Fitur ganti password admin
│   ├── backup.php              # Manual database backup & download
│   ├── cron-backup.php         # Script backup terjadwal via CLI/cron
│   ├── includes/               # Komponen template admin (header, sidebar, topbar, footer)
│   ├── achievements/           # CRUD Prestasi Siswa & Sekolah
│   ├── activity-logs/          # Log audit aktivitas admin (view, export CSV, delete)
│   ├── agendas/                # CRUD Agenda & Kalender Kegiatan
│   ├── alumni/                 # CRUD Testimoni Alumni
│   ├── banners/                # CRUD Banner Slider Hero Beranda + toggle status
│   ├── bkk/                    # CRUD Bursa Kerja Khusus (Lowongan Kerja)
│   ├── contacts/               # Manajemen Pesan Masuk Buku Tamu/Kontak
│   ├── downloads/              # CRUD Pusat Unduhan Dokumen
│   ├── extracurriculars/       # CRUD Ekstrakurikuler Siswa
│   ├── galleries/              # CRUD Galeri Foto & Kategori Galeri
│   ├── graduations/            # Manajemen Kelulusan, SKL, Import CSV, Template
│   ├── guestbook/              # Manajemen Buku Tamu Digital & Saran
│   ├── history/                # Manajemen Narasi Sejarah Sekolah
│   ├── majors/                 # CRUD Jurusan / Kompetensi Keahlian
│   ├── modules/                # Pengaturan Fitur & Modul Halaman (Preset Multi-Jenjang)
│   ├── news/                   # CRUD Berita, Artikel, & Kategori Berita
│   ├── organization/           # Manajemen Struktur Organisasi & Bagan
│   ├── partners/               # CRUD Mitra Kerjasama DU/DI
│   ├── ppdb/                   # Pengelolaan PPDB (Pendaftar, Export, Setting)
│   ├── principal/              # Manajemen Foto & Sambutan Kepala Sekolah
│   ├── profile/                # Pengaturan Profil Utama Sekolah, Kontak, & Tema Warna
│   ├── social-media/           # CRUD Tautan Media Sosial
│   ├── statistics/             # CRUD Indikator Statistik Beranda
│   ├── teachers/               # CRUD Direktori Guru & Tenaga Kependidikan
│   ├── users/                  # Manajemen Akun Administrator (Superadmin only)
│   ├── vision-mission/         # Manajemen Visi, Misi, & Tujuan Sekolah
│   └── whatsapp/               # Konfigurasi WhatsApp Gateway (Fonnte/Wablas)
├── assets/
│   ├── backups/                # Direktori penyimpanan file dump SQL backup
│   ├── css/                    # Stylesheet publik (`style.css`) & admin (`admin.css`)
│   ├── images/                 # Gambar demo, favicon, placeholder aset
│   ├── js/                     # Skrip interaktif publik (`main.js`)
│   ├── uploads/                # Direktori berkas unggahan publik/admin
│   └── vendor/                 # Pustaka third-party offline (Bootstrap, Chart.js, dll.)
├── config/
│   ├── config.php              # Inisialisasi bootstrap, load .env, konfigurasi sesi
│   ├── constants.php           # Definisi konstanta global, URL path, lisensi jenjang
│   └── database.php            # Singleton koneksi database PDO
├── database/
│   ├── .htaccess               # Proteksi blokir akses langsung folder database
│   └── database.sql            # Skrip DDL & seed data inisialisasi awal
├── includes/
│   ├── auth.php                # Middleware autentikasi admin & proteksi session timeout
│   ├── footer.php              # Komponen footer publik & skrip JS
│   ├── functions.php           # Helper inti (sanitasi, upload, CSRF, rate-limit, format)
│   ├── header.php              # Komponen head HTML, CSS, dynamic meta tag
│   └── navbar.php              # Komponen navigasi menu utama responsif
└── pages/                      # 30 skrip view halaman publik
```

---

## 4. Dependencies & Version Catalog

Semua dependensi front-end disimpan secara lokal (self-hosted / zero external CDN dependency). Tidak ditemukan `composer.json` atau dependensi PHP pihak ketiga (murni PHP Standard Library).

| Package | Tipe | Lokasi | Versi Terdeteksi | Keperluan |
| :--- | :--- | :--- | :--- | :--- |
| **Bootstrap** | CSS / JS | `assets/vendor/bootstrap/` | v5.3.3 | Grid system, form styling, modal, utility classes |
| **Bootstrap Icons** | Icon Font / CSS | `assets/vendor/bootstrap-icons/` | v1.11.3 | Ikon antarmuka publik dan dashboard admin |
| **Chart.js** | JS Library | `assets/vendor/chartjs/` | v4.4.1 | Visualisasi grafik batang berita bulanan di dashboard |
| **jQuery** | JS Library | `assets/vendor/jquery/` | v3.7.1 | Dependensi wajib editor teks Summernote |
| **Summernote Lite** | WYSIWYG Editor | `assets/vendor/summernote/` | v0.8.18 | Rich text editor berita, profil, sambutan |

---

## 5. Route, Page, and API Inventory

### A. Public Routes (Disalurkan melalui Front Controller `index.php`)

| Route Path | Script Target | Tipe Request | Deskripsi |
| :--- | :--- | :--- | :--- |
| `/` | `pages/home.php` | GET | Beranda publik, slider, sambutan, jurusan, berita, partner, testimoni |
| `/profil` | `pages/profil.php` | GET | Ikhtisar profil sekolah |
| `/profil/sambutan` | `pages/sambutan.php` | GET | Halaman penuh sambutan Kepala Sekolah |
| `/profil/sejarah` | `pages/sejarah.php` | GET | Halaman sejarah berdirinya sekolah |
| `/profil/visi-misi` | `pages/visi-misi.php` | GET | Halaman visi, misi, dan tujuan institusi |
| `/profil/struktur-organisasi` | `pages/organisasi.php` | GET | Struktur bagan organisasi sekolah |
| `/profil/guru-tendik` | `pages/guru-tendik.php` | GET | Direktori guru & staf dengan filter bidang |
| `/jurusan` | `pages/jurusan.php` | GET | Daftar kompetensi keahlian/jurusan |
| `/jurusan/{slug}` | `pages/jurusan-detail.php` | GET | Informasi detail kompetensi keahlian |
| `/berita` | `pages/berita.php` | GET | Portal berita, pencarian kata kunci, filter kategori, pagination |
| `/berita/{slug}` | `pages/berita-detail.php` | GET | Detail berita, view counter, tombol share, berita terkait |
| `/galeri` | `pages/galeri.php` | GET | Galeri dokumentasi dengan filter kategori & lightbox modal |
| `/kontak` | `pages/kontak.php` | GET / POST | Informasi kontak sekolah & formulir kirim pesan ke admin |
| `/ppdb` | `pages/ppdb.php` | GET | Landing page informasi pendaftaran murid baru |
| `/ppdb/daftar` | `pages/ppdb-daftar.php` | GET / POST | Formulir pendaftaran siswa baru (multi-step / validasi berkas) |
| `/ppdb/sukses` | `pages/ppdb-sukses.php` | GET | Konfirmasi berhasil mendaftar + nomor registrasi |
| `/ppdb/cek-status` | `pages/ppdb-cek.php` | GET / POST | Pencarian status seleksi PPDB via NISN & nomor registrasi |
| `/ppdb/cetak` | `pages/ppdb-cetak.php` | GET | Halaman cetak bukti formulir PPDB (format cetak browser/PDF) |
| `/bkk` | `pages/bkk.php` | GET | Portal info bursa kerja khusus dan lowongan alumni |
| `/bkk/{slug}` | `pages/bkk-detail.php` | GET | Detail informasi lowongan kerja industri |
| `/ekstrakurikuler` | `pages/ekstrakurikuler.php`| GET | Direktori kegiatan ekstrakurikuler siswa |
| `/prestasi` | `pages/prestasi.php` | GET | Daftar rekam jejak prestasi akademik & non-akademik |
| `/unduhan` | `pages/unduhan.php` | GET | Katalog unduhan berkas formulir/silabus |
| `/unduhan/download/{id}` | `pages/unduhan-download.php`| GET | Handler stream unduhan file dengan hitungan download counter |
| `/kelulusan` | `pages/kelulusan.php` | GET / POST | Cek kelulusan siswa via NISN / No. Ujian |
| `/kelulusan/cetak` | `pages/kelulusan-cetak.php` | GET | Cetak Surat Keterangan Lulus (SKL) resmi dengan QR verification |
| `/agenda` | `pages/agenda.php` | GET | Kalender & daftar kegiatan sekolah mendatang |
| `/agenda/{slug}` | `pages/agenda-detail.php` | GET | Detail informasi agenda kegiatan |
| `/buku-tamu` | `pages/buku-tamu.php` | GET / POST | Form buku tamu digital dan aspirasi publik |
| `/api/search` | `pages/search-api.php` | GET | Live search endpoint JSON untuk berita, jurusan, dan agenda |
| `/sitemap.xml` | `sitemap.php` | GET | Peta situs XML SEO dinamis |
| `/robots.txt` | `robots.php` | GET | Konfigurasi robots SEO dinamis |

### B. Admin Panel Routes (Akses Langsung via File Script)

- **Auth:** `admin/login.php`, `admin/logout.php`, `admin/change-password.php`, `admin/profile-admin.php`
- **Dashboard:** `admin/dashboard.php`
- **Content & Master Data:**
  - `admin/profile/index.php` (Pengaturan Sekolah, Kontak, Dynamic Theme Color)
  - `admin/banners/` (`index.php`, `form.php`, `delete.php`, `toggle.php`)
  - `admin/principal/index.php`
  - `admin/history/index.php`
  - `admin/vision-mission/index.php`
  - `admin/organization/index.php`
  - `admin/teachers/` (`index.php`, `form.php`, `delete.php`)
  - `admin/majors/` (`index.php`, `form.php`, `delete.php`)
  - `admin/news/` (`index.php`, `form.php`, `delete.php`, `categories.php`)
  - `admin/galleries/` (`index.php`, `form.php`, `delete.php`, `categories.php`)
  - `admin/alumni/` (`index.php`, `form.php`, `delete.php`)
  - `admin/partners/` (`index.php`, `form.php`, `delete.php`)
  - `admin/statistics/` (`index.php`, `form.php`, `delete.php`)
  - `admin/contacts/` (`index.php`)
  - `admin/social-media/` (`index.php`, `form.php`, `delete.php`)
  - `admin/ppdb/` (`index.php`, `detail.php`, `export.php`, `delete.php`, `settings.php`)
  - `admin/bkk/` (`index.php`, `form.php`, `delete.php`)
  - `admin/extracurriculars/` (`index.php`, `form.php`, `delete.php`)
  - `admin/achievements/` (`index.php`, `form.php`, `delete.php`)
  - `admin/downloads/` (`index.php`, `form.php`, `delete.php`)
  - `admin/graduations/` (`index.php`, `form.php`, `delete.php`, `import.php`, `export.php`, `template.php`, `settings.php`)
  - `admin/agendas/` (`index.php`, `form.php`, `delete.php`)
  - `admin/guestbook/` (`index.php`, `delete.php`, `export.php`)
  - `admin/whatsapp/` (`index.php`)
- **Superadmin Only:**
  - `admin/users/` (`index.php`, `form.php`, `delete.php`)
  - `admin/activity-logs/` (`index.php`, `delete.php`, `export.php`)
  - `admin/backup.php` (Manual DB backup & trigger)
  - `admin/modules/` (`index.php`, `preset.php`, `toggle.php` - subject to license flag)

---

## 6. Database Schema & State Discovery

Live database `smk_school` memiliki **32 tabel**. Namun terdapat diskrepansi antara database berjalan dengan file inisialisasi `database/database.sql`.

### Diskrepansi Skema:
1. Tabel **`site_modules`** (digunakan oleh fungsi `isModuleActive()` dan manajemen modul multi-jenjang) sudah ada di database live dan backup, tetapi **TIDAK TERDAPAT** di file instalasi awal `database/database.sql`.
2. Tabel **`wa_gateway_settings`** (digunakan oleh integrasi WhatsApp notifikasi) sudah ada di database live, tetapi **TIDAK TERDAPAT** di file `database/database.sql`.

Daftar 32 Tabel Aktif:
1. `achievements` (prestasi sekolah/siswa)
2. `activity_logs` (audit log aktivitas pengguna admin)
3. `agendas` (kalender kegiatan akademik)
4. `alumni_testimonials` (testimoni alumni)
5. `banners` (hero carousel beranda)
6. `bkk_jobs` (lowongan kerja bursa kerja khusus)
7. `contacts` (pesan kontak masuk)
8. `downloads` (berkas unduhan)
9. `extracurriculars` (kegiatan ekskul)
10. `galleries` (item foto galeri)
11. `gallery_categories` (kategori galeri)
12. `graduation_settings` (konfigurasi tahun & pesan kelulusan)
13. `graduations` (data kelulusan siswa & nilai)
14. `guestbooks` (buku tamu publik)
15. `history` (sejarah sekolah)
16. `majors` (jurusan keahlian)
17. `news` (berita & artikel)
18. `news_categories` (kategori artikel)
19. `organization` (bagan struktur organisasi)
20. `partners` (mitra kerjasama industri/DU-DI)
21. `ppdb_registrations` (data registrasi calon siswa)
22. `ppdb_settings` (konfigurasi kuota, gelombang, status PPDB)
23. `principal_message` (sambutan kepala sekolah)
24. `rate_limits` (pencatatan upaya brute-force & limitasi request)
25. `school_profile` (profil umum, kontak, warna tema)
26. `site_modules` (status aktivasi fitur modular sekolah)
27. `social_media` (link sosial media sekolah)
28. `statistics` (indikator angka beranda)
29. `teachers` (guru dan staf kependidikan)
30. `users` (akun admin CMS)
31. `vision_mission` (visi, misi, tujuan)
32. `wa_gateway_settings` (konfigurasi provider API WhatsApp)

---

## 7. Role & Access Control Matrix (RBAC)

Sistem mengimplementasikan 3 level peran:

| Fitur / Halaman | Public (Tamu) | Admin Biasa (`admin`) | Super Admin (`superadmin`) |
| :--- | :---: | :---: | :---: |
| Akses Halaman Publik (Beranda, Berita, Jurusan, dll.) | ✅ Akses | ✅ Akses | ✅ Akses |
| Kirim Pesan Kontak & Isi Buku Tamu | ✅ Akses | ✅ Akses | ✅ Akses |
| Mendaftar PPDB Online & Cek Kelulusan | ✅ Akses | ✅ Akses | ✅ Akses |
| Unduh Dokumen Publik | ✅ Akses | ✅ Akses | ✅ Akses |
| Akses Panel `/admin/login.php` | ✅ Form Login | ✅ Sesi Aktif | ✅ Sesi Aktif |
| Akses Dashboard & Statistik | ❌ Ditolak | ✅ Akses | ✅ Akses |
| CRUD Konten (Berita, Banner, Galeri, Guru, dll.) | ❌ Ditolak | ✅ Akses | ✅ Akses |
| Verifikasi PPDB & Kelulusan | ❌ Ditolak | ✅ Akses | ✅ Akses |
| Ganti Password & Profil Diri | ❌ Ditolak | ✅ Akses | ✅ Akses |
| Manajemen Admin (`admin/users/`) | ❌ Ditolak | ❌ Ditolak (403/Redirect) | ✅ Full CRUD |
| Audit Log Aktivitas (`admin/activity-logs/`) | ❌ Ditolak | ❌ Ditolak (403/Redirect) | ✅ Lihat, Export, Hapus |
| Database Backup (`admin/backup.php`) | ❌ Ditolak | ❌ Ditolak (403/Redirect) | ✅ Generate & Download |
| Manajemen Fitur Modul (`admin/modules/`) | ❌ Ditolak | ❌ Ditolak | ✅ Mengikuti konstanta `ALLOW_MODULE_MANAGEMENT` |

---

## 8. Project Discovery Findings

### Finding DISC-01: Skema Database Awal (`database/database.sql`) Tidak Lengkap
- **Category:** Architecture & Database Integrity
- **Severity:** High
- **File & Lokasi:** `database/database.sql`
- **Bukti:** Tabel `site_modules` dan `wa_gateway_settings` yang dibutuhkan oleh `includes/functions.php` dan modul admin WhatsApp tidak terdapat dalam berkas `database.sql`.
- **Dampak:** Pengguna yang melakukan instalasi baru sesuai petunjuk `README.md` akan mengalami fatal SQL error (`Table 'smk_school.site_modules' doesn't exist`) saat mengakses beranda atau navigasi.
- **Rekomendasi Solusi:** Salin DDL dan data inisialisasi tabel `site_modules` dan `wa_gateway_settings` dari dump backup ke dalam `database/database.sql`.
- **Verification Method:** Periksa isi `database/database.sql` dengan `grep / Select-String` dan uji impor di fresh database.
- **Status:** Confirmed

### Finding DISC-02: Konfigurasi Default Jenjang Terkunci pada `SMA` di File Konstanta
- **Category:** Configuration & Consistency
- **Severity:** Medium
- **File & Lokasi:** `config/constants.php:71`
- **Bukti:** 
  ```php
  define('APP_SCHOOL_LEVEL', 'SMA');
  define('ALLOW_MODULE_MANAGEMENT', false);
  ```
- **Dampak:** Meskipun nama proyek adalah "SMK Negeri Contoh Indonesia", konfigurasi ini secara default menyembunyikan modul spesifik SMK (BKK dan Mitra Kerjasama Industri) dari menu navigasi dan beranda publik.
- **Rekomendasi Solusi:** Sesuaikan nilai default konstanta menjadi `'SMK'` atau `'MULTI'` untuk repositori SMK, atau berikan penjelasan yang jelas di dokumentasi pengembang.
- **Verification Method:** Jalankan `getAllowedModulesForLevel(getSchoolLevel())` pada shell PHP.
- **Status:** Confirmed

---

## 9. Kesimpulan Discovery

Arsitektur aplikasi secara fundamental terstruktur rapi untuk aplikasi PHP Native. Sistem routing modular, front controller pada rute publik, pemisahan konstanta dan konfigurasi, serta self-hosting semua pustaka front-end membuat aplikasi independen dari koneksi internet luar. Namun, kelengkapan skema SQL master menjadi temuan prioritas untuk menjamin instalasi pre-production yang andal.
