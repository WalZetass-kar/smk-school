Lakukan **security hardening menyeluruh** pada CMS sekolah yang sudah ada tanpa merusak fitur, UI, database, atau arsitektur existing.

Audit dan perbaiki semua bagian berikut:

- Authentication & secure session/cookie
- RBAC dan server-side authorization
- IDOR/BOLA & privilege escalation
- Input validation dengan schema validation
- SQL Injection
- XSS & HTML sanitization
- CSRF
- Rate limiting & brute-force protection
- Secure file upload dan download
- Path traversal
- Storage/database security
- Supabase RLS jika digunakan
- API security & mass assignment
- CORS
- Security headers (CSP, HSTS, X-Content-Type-Options, Referrer-Policy)
- HTTPS
- Secret/environment variable protection
- Password reset security
- Error handling agar tidak membocorkan informasi
- Audit log untuk aktivitas admin
- Dependency vulnerability
- Backup & recovery

Semua security-critical validation harus dilakukan **server-side**, bukan hanya frontend.

Jangan expose password, token, API key, service role key, stack trace, atau data sensitif ke client.

Setelah selesai:
1. Jalankan lint/typecheck/build/test yang tersedia.
2. Perbaiki error yang muncul.
3. Berikan ringkasan vulnerability yang ditemukan dan diperbaiki berdasarkan **Critical / High / Medium / Low**.
4. Tampilkan file yang diubah dan alasan perubahannya.
5. Jangan klaim aplikasi 100% aman; tampilkan remaining risks jika masih ada.