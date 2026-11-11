# MASTER PROMPT — SCHOOL WEBSITE PRE-PRODUCTION AUDIT

Bertindak sebagai Senior Software Engineer, Application Security Engineer, QA Engineer, UI/UX Designer, dan Performance Engineer.

Lakukan audit menyeluruh terhadap project website sekolah yang sedang dikembangkan dan BELUM DEPLOY ke production.

## ATURAN UTAMA

1. Audit seluruh project terlebih dahulu sebelum melakukan perubahan kode.
2. Jangan langsung memperbaiki atau mengubah source code.
3. Jangan menghapus fitur, mengubah arsitektur, atau mengganti framework.
4. Jangan mengasumsikan fitur sudah berjalan hanya karena source code tersedia.
5. Verifikasi temuan berdasarkan source code dan pengujian aktual.
6. Jangan menggunakan data dummy untuk menyatakan fitur berhasil.
7. Jangan menampilkan atau membocorkan secret, password, API key, maupun credential dalam laporan.
8. Jangan melakukan destructive testing terhadap database.
9. Jangan mengubah konfigurasi production atau melakukan deployment.
10. Identifikasi keterbatasan pengujian dan tandai hal yang belum dapat diverifikasi.

## AUDIT MODULE

### 1. PROJECT DISCOVERY
- Identifikasi framework dan teknologi.
- Analisis struktur direktori.
- Periksa dependency dan versi.
- Identifikasi seluruh halaman, routes, API, database, dan middleware.
- Petakan fitur berdasarkan role pengguna.

### 2. SECURITY AUDIT
- Authentication dan session management.
- Authorization dan role-based access control.
- SQL Injection dan XSS.
- CSRF dan SSRF jika relevan.
- Input validation dan output encoding.
- Password hashing.
- Secure cookie configuration.
- API security dan rate limiting.
- File upload validation.
- Sensitive information exposure.
- Environment variables dan secret management.
- Security headers dan CORS.
- Dependency vulnerability scanning.
- Error handling dan information disclosure.

Gunakan OWASP Top 10 sebagai referensi.

### 3. UI/UX AUDIT
Periksa seluruh halaman:
- Visual hierarchy.
- Layout dan spacing.
- Alignment dan consistency.
- Color contrast.
- Navigation.
- Button dan interaction states.
- Form usability.
- Empty, loading, success, dan error states.
- Desktop, tablet, dan mobile responsiveness.
- Konsistensi design system.

Hindari redesign tanpa alasan yang terukur.

### 4. TYPOGRAPHY AUDIT
- Font family.
- Font size.
- Font weight.
- Line height.
- Letter spacing.
- Heading hierarchy.
- Text readability.
- Font loading dan fallback.
- Konsistensi typography seluruh halaman.

### 5. FUNCTIONAL QA
- Semua routes dan navigation.
- Seluruh tombol dan link.
- Form validation.
- CRUD operations.
- Authentication dan logout.
- Admin dashboard.
- Upload dan download.
- Database integration.
- Error handling.
- Role permissions.

Jalankan pengujian dengan lingkungan lokal dan database testing jika tersedia.

### 6. PERFORMANCE AUDIT
- Build verification.
- Bundle size.
- Image optimization.
- Lazy loading.
- Rendering performance.
- API response.
- Database query efficiency.
- Caching.
- Core Web Vitals jika dapat diukur melalui browser.

### 7. SEO & ACCESSIBILITY
- Metadata.
- Sitemap.
- Robots.txt.
- Canonical URL.
- Heading structure.
- Semantic HTML.
- Alt text.
- Keyboard navigation.
- Accessible forms.
- WCAG 2.2 AA checks.

## OUTPUT WAJIB

Buat direktori:

audit/
  01-project-discovery.md
  02-security-audit.md
  03-ui-ux-audit.md
  04-typography-audit.md
  05-functional-audit.md
  06-performance-audit.md
  07-seo-accessibility-audit.md
  08-production-readiness.md
  09-priority-fix-list.md

Setiap temuan harus berisi:
- Finding ID.
- Category.
- Severity: Critical / High / Medium / Low / Informational.
- File dan lokasi kode terkait.
- Bukti temuan.
- Dampak.
- Rekomendasi solusi.
- Verification method.
- Status: Confirmed / Potential / Not Tested.

## FINAL REQUIREMENT

Berikan ringkasan:
1. Total temuan berdasarkan severity.
2. Risiko keamanan paling serius.
3. Masalah UI/UX paling terlihat.
4. Masalah typography.
5. Fitur yang tidak berfungsi.
6. Performance bottlenecks.
7. Daftar perbaikan berdasarkan prioritas.
8. Production readiness: READY / NOT READY / NEEDS VERIFICATION.

Jangan melakukan perbaikan otomatis pada tahap audit pertama.

Mulai dari project discovery, kemudian lakukan audit secara sistematis. Jika aplikasi belum dapat dijalankan, identifikasi kebutuhan untuk menjalankannya secara lokal tanpa mengubah konfigurasi inti.