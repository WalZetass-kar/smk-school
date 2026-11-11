# 07 — SEO & Web Accessibility (WCAG 2.2 AA) Audit

**Audit Date:** 2026-11-11  
**Project:** Website Profil & CMS Sekolah (SMK Negeri Contoh Indonesia)  
**Standard Reference:** Web Content Accessibility Guidelines (WCAG) 2.2 Level AA, Google Search Central Technical SEO  
**Auditor Role:** Accessibility Specialist & Technical SEO Engineer  
**Status:** Pre-Production Assessment  

---

## 1. Executive Summary

Audit mesin pencari (SEO) dan aksesibilitas ramah disabilitas (A11y) menguji kepatuhan terhadap standar WCAG 2.2 AA, validitas metadata Open Graph/Twitter Card, struktur heading, keberadaan Peta Situs (`sitemap.xml`), konfigurasi crawling bot (`robots.txt`), navigasi keyboard, asosiasi label formulir, serta markup semantik Schema.org.

Sisi SEO teknis diimplementasikan secara sangat baik:
- Halaman dinamis memiliki `<title>`, `<meta description>`, dan `<link rel="canonical">` yang terkalkulasi otomatis.
- Open Graph mendukung visual share WhatsApp, Facebook, dan Twitter Card dengan resolusi 1200x630.
- Peta situs `sitemap.xml` dinamis (`sitemap.php`) mematuhi Sitemaps Protocol 0.9 dan menyertakan Google Image Sitemap extension secara otomatis.
- Berkas `robots.txt` (`robots.php`) mengisolasi folder admin dan konfigurasi dari perayapan bot publik.
- Tag bahasa dokumen telah diatur ke Bahasa Indonesia (`<html lang="id">`).

Namun, dari sisi aksesibilitas bagi penyandang disabilitas motorik dan tuna netra (*screen reader*), ditemukan beberapa catatan kepatuhan WCAG 2.2 AA yang perlu ditingkatkan.

---

## 2. SEO & Accessibility Findings Catalog

### Finding SEO-01: Ketiadaan Fitur "Skip to Content" (Bypass Blocks)
- **Category:** WCAG 2.2 — SC 2.4.1 Bypass Blocks (Level A)
- **Severity:** Medium
- **File & Lokasi:** `includes/header.php:114` & `includes/navbar.php:15-40`
- **Bukti Temuan:**
  Elemen `<body>` langsung diikuti oleh top announcement bar dan navbar menu yang memiliki belasan link navigasi:
  ```html
  <body class="public-body">
    <div class="top-announcement-bar ...">...</div>
    <nav class="navbar navbar-expand-xl ...">...</div>
  ```
  Tidak ditemukan tautan navigasi pintas (*skip link*) di awal dokumen.
- **Dampak:** Pengguna tunanetra yang mengandalkan pembaca layar (*screen reader*) atau disabilitas motorik yang hanya menggunakan keyboard (tombol `Tab`) terpaksa menekan tombol Tab belasan kali melewati pengumuman dan seluruh menu dropdown navbar di setiap kali berpindah halaman sebelum mencapai isi konten utama.
- **Rekomendasi Solusi:** Tempatkan tautan tersembunyi yang hanya muncul saat menerima fokus keyboard di baris pertama setelah tag `<body>`:
  ```html
  <a href="#main-content" class="visually-hidden-focusable btn btn-primary position-absolute top-0 start-0 m-2 z-3">
    Langsung ke konten utama
  </a>
  ```
  Dan tambahkan atribut `id="main-content"` pada elemen `<main>` konten setiap halaman.
- **Verification Method:** Uji navigasi keyboard (tekan tombol `Tab` pertama kali setelah halaman dimuat).
- **Status:** Confirmed

---

### Finding SEO-02: Kurangnya Asosiasi Eksplisit Atribut `for` dan `id` pada Formulir Publik
- **Category:** WCAG 2.2 — SC 1.3.1 Info & Relationships / SC 4.1.2 Name, Role, Value (Level A)
- **Severity:** Medium
- **File & Lokasi:** `pages/kontak.php:258-290`, `pages/buku-tamu.php:75-120`
- **Bukti Temuan:**
  ```html
  <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
  <div class="input-group">
    <input type="text" name="name" class="form-control" value="<?= e($old['name']) ?>" required placeholder="Nama Anda">
  </div>
  ```
- **Dampak:** Elemen `<label>` tidak memiliki atribut `for="contact_name"` dan elemen `<input>` tidak memiliki atribut `id="contact_name"`. Pengguna pembaca layar akan mendengar input teks sebagai kotak sunting tanpa label kontekstual yang jelas. Pengguna juga tidak dapat memfokuskan input dengan mengklik teks label.
- **Rekomendasi Solusi:** Tambahkan pasangan atribut `id` pada input dan `for` pada label yang bersangkutan di seluruh formulir:
  ```html
  <label for="contact_name" class="form-label small fw-semibold">Nama Lengkap *</label>
  <input type="text" id="contact_name" name="name" class="form-control" ...>
  ```
- **Verification Method:** Periksa dengan ekstensi browser WAVE (Web Accessibility Evaluation Tool) atau Lighthouse Accessibility.
- **Status:** Confirmed

---

### Finding SEO-03: Kelengkapan Schema.org JSON-LD Masih Terbatas pada Informasi Dasar
- **Category:** Structured Data & Rich Snippets (Technical SEO)
- **Severity:** Low
- **File & Lokasi:** `includes/header.php:88-97`
- **Bukti Temuan:**
  ```json
  {
    "@context": "https://schema.org",
    "@type": "EducationalOrganization",
    "name": "SMK Negeri Contoh Indonesia",
    "url": "http://localhost/smk-school",
    "address": "..."
  }
  ```
- **Dampak:** Struktur Schema.org belum menyertakan properti kaya (*rich properties*) seperti nomor telepon resmi, email, logo, koordinat geolokasi (`geo`), tautan media sosial (`sameAs`), dan NPSN (`identifier`), sehingga Google Rich Results tidak dapat menampilkan Knowledge Graph profil sekolah secara optimal.
- **Rekomendasi Solusi:** Perkaya skema JSON-LD di `includes/header.php` dengan data telepon, logo URL, koordinat geo, dan tautan sosial media dari fungsi `getSocialMedia()`.
- **Verification Method:** Uji validitas JSON-LD menggunakan Google Rich Results Test Tool.
- **Status:** Confirmed

---

### Finding SEO-04: Deklarasi Bahasa Halaman HTML Mematuhi Standar Aksesibilitas
- **Category:** WCAG 2.2 — SC 3.1.1 Language of Page (Level A)
- **Severity:** Informational
- **File & Lokasi:** `includes/header.php:44` & `admin/includes/header.php:20`
- **Bukti Temuan:** Seluruh halaman mengawali tag HTML dengan `<html lang="id">`.
- **Dampak:** Perangkat lunak sintesis suara (*speech synthesizer*) dan pembaca layar dapat secara otomatis memilih aksen pengucapan Bahasa Indonesia yang tepat tanpa salah membaca fonetik.
- **Rekomendasi Solusi:** Pertahankan implementasi ini.
- **Verification Method:** Inspeksi baris pembuka tag HTML di browser.
- **Status:** Confirmed

---

## 3. Matriks Kepatuhan Aksesibilitas WCAG 2.2 AA

| Kriteria Keberhasilan WCAG | Status | Catatan Audit |
| :--- | :---: | :--- |
| **1.1.1 Non-text Content (Alt Text)** | ✅ Lulus | Seluruh gambar utama memiliki atribut alt yang deskriptif. |
| **1.3.1 Info and Relationships** | ⚠️ Butuh Perbaikan | Label formulir perlu dipasangkan dengan `for` dan `id`; heading beranda perlu diperbaiki. |
| **1.4.3 Contrast (Minimum)** | ✅ Lulus | Teks utama di atas background memiliki rasio kontras > 7:1 (melebihi standar 4.5:1). |
| **2.1.1 Keyboard Navigation** | ✅ Lulus | Semua tautan, form, modal, dan dropdown dapat diakses tombol Tab dan Enter. |
| **2.4.1 Bypass Blocks (Skip Link)** | ❌ Gagal | Belum tersedia tombol pintas "Skip to Content". |
| **2.4.2 Page Titled** | ✅ Lulus | Setiap halaman memiliki `<title>` dinamis yang unik dan relevan. |
| **3.1.1 Language of Page** | ✅ Lulus | Atribut `lang="id"` terpasang konsisten. |
| **4.1.2 Name, Role, Value** | ⚠️ Butuh Perbaikan | Elemen form publik membutuhkan asosiasi label terprogram. |
