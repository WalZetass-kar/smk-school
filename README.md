# Website SMK Negeri Contoh Indonesia

CMS website sekolah lengkap — PHP Native + MySQL + Bootstrap 5 (tanpa framework PHP).

## Status: semua fase selesai

Proyek ini mengikuti urutan fase dari spesifikasi awal (Bagian 39) dan **semuanya
sudah selesai dan diuji langsung** (bukan cuma ditulis) — MariaDB + PHP CLI
dijalankan di lingkungan build, skema diimpor, server dinyalakan, dan setiap
halaman + setiap alur CRUD diuji lewat curl end-to-end (bukan cuma `php -l`).

**Halaman publik:**
- Beranda — hero slider, sambutan kepsek, profil ringkas, jurusan, berita,
  testimoni alumni, partner, galeri, statistik dengan animasi counter
- Profil + 5 submenu (sambutan, sejarah, visi & misi, struktur organisasi,
  guru & tendik dengan filter)
- Jurusan (list + halaman detail per slug)
- Berita (list dengan search, filter kategori, pagination, sidebar populer;
  detail dengan penghitung view, share, dan berita terkait)
- Galeri (grid dengan filter kategori + lightbox modal)
- Kontak (form dengan CSRF + captcha matematika sederhana, tersimpan ke DB)

**Dashboard admin (`/admin`):**
- Login dengan `password_verify()`, regenerasi session ID, proteksi CSRF
- Dashboard dengan 8 stat card + grafik berita per bulan (Chart.js)
- CRUD penuh untuk: Pengaturan Website (logo/favicon), Banner, Sambutan
  Kepsek, Sejarah, Visi & Misi, Struktur Organisasi, Guru & Tendik, Jurusan,
  Berita + Kategori Berita, Galeri + Kategori Galeri, Testimoni Alumni,
  Partner, Statistik, Social Media, Pesan Masuk (tandai dibaca/hapus),
  Manajemen Admin (khusus superadmin), Profil Admin sendiri, Ganti Password
- Middleware sederhana (`includes/auth.php`) memproteksi seluruh halaman admin

**SEO & keamanan:**
- `sitemap.php` dinamis (di-rewrite dari `/sitemap.xml`) — otomatis
  memasukkan jurusan aktif dan berita publish terbaru
- `robots.txt`, meta tag dinamis, Open Graph, Schema.org, slug SEO-friendly
- PDO prepared statement di **semua** query, `password_hash()`/`password_verify()`,
  CSRF token di semua form POST, validasi & sanitasi upload gambar (cek MIME
  asli, bukan cuma ekstensi), rename file upload acak, `.htaccess` yang
  memblokir eksekusi PHP di folder uploads dan akses langsung ke `config/`/`includes/`

## Instalasi (XAMPP / Laragon)

1. Salin folder project ke:
   ```
   htdocs/smk-school
   ```
2. Buat database lewat phpMyAdmin atau CLI:
   ```sql
   CREATE DATABASE smk_school;
   ```
3. Import skema + data demo: `database/database.sql`
   (lewat phpMyAdmin: Import → pilih file, atau `mysql -u root smk_school < database/database.sql`)
4. Atur koneksi di `config/database.php` — default sudah cocok untuk
   XAMPP/Laragon standar (`host: localhost`, `user: root`, `pass: ''`). Untuk
   hosting cPanel, ganti sesuai kredensial database di sana.
5. Pastikan `mod_rewrite` Apache aktif (biasanya sudah default di
   XAMPP/Laragon) supaya `.htaccess` bisa membuat URL bersih dan
   `/sitemap.xml` bisa di-rewrite ke `sitemap.php`.
6. Jalankan:
   ```
   http://localhost/smk-school/
   ```
   Admin: `http://localhost/smk-school/admin/login.php`

> Catatan: kalau kamu tes pakai `php -S` (built-in server PHP) alih-alih
> Apache, `.htaccess` tidak diproses sama sekali — jadi URL bersih dan
> rewrite `/sitemap.xml` hanya akan bekerja di Apache/Nginx sungguhan.
> `sitemap.php` sendiri tetap bisa diakses langsung untuk dicek.

## Kredensial admin demo

| Username     | Password  | Role       |
|--------------|-----------|------------|
| `superadmin` | `admin123`| superadmin |
| `admin`      | `admin123`| admin      |

**Ganti password ini lewat menu "Ganti Password" sebelum dipakai di luar localhost.**

## Struktur folder

```
smk-school/
├── index.php              # front controller / router (semua halaman publik)
├── sitemap.php             # sitemap XML dinamis
├── robots.txt
├── .htaccess
├── config/                 # koneksi DB, konstanta, bootstrap
├── includes/                # header, navbar, footer, functions, auth (proteksi admin)
├── assets/
│   ├── css/style.css        # design tokens (CSS variables) + semua komponen publik
│   ├── css/admin.css         # tema dashboard admin
│   ├── js/main.js
│   ├── images/                # gambar demo (placeholder, tinggal diganti admin)
│   └── uploads/                 # tujuan upload dari dashboard admin
├── pages/                    # 13 halaman publik
├── admin/
│   ├── login.php / logout.php / dashboard.php
│   ├── profile-admin.php / change-password.php
│   ├── includes/               # header, sidebar, topbar, footer khusus admin
│   └── <modul>/                # profile, banners, principal, history,
│                                  vision-mission, organization, teachers,
│                                  majors, news, galleries, alumni, partners,
│                                  statistics, contacts, social-media, users
│       ├── index.php (list)
│       ├── form.php (tambah/edit)
│       └── delete.php
└── database/database.sql
```

## Catatan teknis

- Warna, jenis huruf, dan spacing semua didefinisikan sebagai CSS variable
  di bagian atas `assets/css/style.css` — gampang diubah tanpa menyentuh
  markup manapun. Palet: navy tua (primary), biru langit (secondary), oranye
  (accent) — dipakai konsisten sebagai "label kode jurusan" (TKJ/RPL/TKR/AKL)
  yang jadi elemen ciri khas halaman, bukan sekadar dekorasi.
- Modul CRUD single-row (Pengaturan Website, Sambutan, Sejarah, Visi &
  Misi, Struktur Organisasi) memakai pola "ambil baris pertama, UPDATE kalau
  ada / INSERT kalau belum ada" — tidak butuh halaman list.
- Modul CRUD multi-row (Guru & Tendik, Jurusan, Berita, Galeri, Alumni,
  Partner, Statistik, Social Media, Banner, Manajemen Admin) semuanya
  memakai pola tiga file yang sama: `index.php` (list + tombol aksi),
  `form.php` (tambah & edit dalam satu file, dibedakan dari `?id=`), dan
  `delete.php` (POST + CSRF only, tidak menerima GET demi keamanan).
- Kategori Berita dan Kategori Galeri memakai pola gabungan (tambah/edit/hapus
  dalam satu halaman) karena datanya sederhana — cukup nama saja.
- Slug jurusan dan berita dibuat otomatis dari judul lewat `slugify()`, dan
  diperiksa keunikannya (menambah `-2`, `-3`, dst. kalau sudah dipakai).
