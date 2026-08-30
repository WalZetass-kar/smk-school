<?php
/**
 * functions.php
 * Small, reusable helpers shared across the whole site.
 */

/** Escape output for safe HTML display. Always wrap dynamic content with this. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Redirect and stop execution. */
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

/** Turn a title into a URL-friendly slug, e.g. "Berita Terbaru!" -> "berita-terbaru". */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/** Format a MySQL datetime/date as "24 Agustus 2026". */
function formatTanggalIndo(?string $date): string
{
    if (!$date) {
        return '-';
    }
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];
    $ts = strtotime($date);
    return date('d', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

/** Fetch the single-row school_profile settings, cached for the request. */
function getSchoolProfile(): array
{
    static $profile = null;
    if ($profile === null) {
        $stmt = getDB()->query('SELECT * FROM school_profile ORDER BY id LIMIT 1');
        $profile = $stmt->fetch() ?: [];
    }
    return $profile;
}

/** Active social media links, ordered by platform. */
function getSocialMedia(): array
{
    $stmt = getDB()->query('SELECT * FROM social_media WHERE status = 1 ORDER BY id ASC');
    return $stmt->fetchAll();
}

/**
 * Validate + move an uploaded image into /assets/uploads with a random filename.
 * Supports both:
 *   handleImageUpload($file, $error, $subDir)
 *   handleImageUpload($file, $subDir, $error)
 *   handleImageUpload($file, $error)
 */
function handleImageUpload(array $file, mixed &$arg2 = null, mixed &$arg3 = null): ?string
{
    $subDir = '';
    $errorRef = null;

    if (is_string($arg2)) {
        $subDir = trim($arg2, '/');
        $errorRef = &$arg3;
    } else {
        $errorRef = &$arg2;
        if (is_string($arg3)) {
            $subDir = trim($arg3, '/');
        }
    }

    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        $errorRef = 'Upload gagal atau tidak ada file yang dipilih.';
        return null;
    }
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        $errorRef = 'Ukuran file melebihi batas maksimal 2MB.';
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_IMAGE_EXT, true)) {
        $errorRef = 'Format file tidak diizinkan. Gunakan JPG, JPEG, PNG, atau WEBP.';
        return null;
    }

    // Re-check with finfo — don't trust the client-supplied extension/MIME alone.
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $allowedMime, true)) {
        $errorRef = 'Isi file bukan gambar yang valid.';
        return null;
    }

    $targetDir = UPLOADS_PATH . ($subDir !== '' ? '/' . $subDir : '');
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $destination = $targetDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $errorRef = 'Gagal menyimpan file ke server.';
        return null;
    }

    return 'assets/uploads' . ($subDir !== '' ? '/' . $subDir : '') . '/' . $filename;
}

/** Generate (and remember) a CSRF token for the current session. */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Verify a submitted CSRF token against the session's token. */
function csrfVerify(?string $token): bool
{
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Build the Bootstrap pagination markup for a list page.
 * $baseUrl should already include any other query params, e.g. "/berita?search=abc".
 */
function renderPagination(int $currentPage, int $totalPages, string $baseUrl): string
{
    if ($totalPages <= 1) {
        return '';
    }
    $sep = str_contains($baseUrl, '?') ? '&' : '?';
    $html = '<nav aria-label="Navigasi halaman"><ul class="pagination justify-content-center">';

    $prevDisabled = $currentPage <= 1 ? ' disabled' : '';
    $html .= '<li class="page-item' . $prevDisabled . '"><a class="page-link" href="' . e($baseUrl . $sep . 'page=' . max(1, $currentPage - 1)) . '">&laquo;</a></li>';

    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i === $currentPage ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . e($baseUrl . $sep . 'page=' . $i) . '">' . $i . '</a></li>';
    }

    $nextDisabled = $currentPage >= $totalPages ? ' disabled' : '';
    $html .= '<li class="page-item' . $nextDisabled . '"><a class="page-link" href="' . e($baseUrl . $sep . 'page=' . min($totalPages, $currentPage + 1)) . '">&raquo;</a></li>';

    $html .= '</ul></nav>';
    return $html;
}

/** Cut a plain-text excerpt to $length chars without breaking mid-word too awkwardly. */
function excerpt(string $text, int $length = 150): string
{
    $text = trim(strip_tags($text));
    return mb_strimwidth($text, 0, $length, '...');
}

/** Set a one-time flash message (e.g. after a CRUD action), read by flashMessage(). */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Render (and clear) the pending flash message, if any. Call this inside a layout. */
function flashMessage(): string
{
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $type = $flash['type'] === 'error' ? 'danger' : 'success';
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">'
        . e($flash['message'])
        . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
}

/**
 * Sanitize and clean HTML output from WYSIWYG editors (Summernote/TinyMCE).
 * Allows common formatting tags while stripping scripts and dangerous event handlers.
 */
function cleanHtml(?string $html): string
{
    if (!$html) {
        return '';
    }
    // Allow basic safe HTML tags
    $allowedTags = '<p><br><strong><b><em><i><u><s><strike><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><table><thead><tbody><tr><th><td><a><img><iframe><span><div><hr><code><pre>';
    $cleaned = strip_tags($html, $allowedTags);

    // Remove dangerous inline attributes (like onload, onclick, javascript: links)
    $cleaned = preg_replace('/(\s+on\w+\s*=\s*(["\']).*?\2)/is', '', $cleaned);
    $cleaned = preg_replace('/href\s*=\s*(["\'])\s*javascript:[^"\']*\1/is', 'href="#"', $cleaned);
    $cleaned = preg_replace('/src\s*=\s*(["\'])\s*javascript:[^"\']*\1/is', '', $cleaned);

    return $cleaned;
}

/** Fetch PPDB settings from database. */
function getPpdbSettings(): array
{
    static $settings = null;
    if ($settings === null) {
        $stmt = getDB()->query('SELECT * FROM ppdb_settings ORDER BY id LIMIT 1');
        $settings = $stmt->fetch() ?: [
            'academic_year' => '2027/2028',
            'is_open' => 1,
            'start_date' => '2027-05-01',
            'end_date' => '2027-07-10',
            'announcement_date' => '2027-07-15',
            'information' => '',
            'contact_person' => '(0761) 123456',
        ];
    }
    return $settings;
}

/**
 * Validate + move an uploaded document (PDF/Image) for PPDB registrations.
 */
function handleDocumentUpload(array $file, ?string &$error = null): ?string
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload gagal atau tidak ada file yang dipilih.';
        return null;
    }
    if ($file['size'] > 4 * 1024 * 1024) { // 4MB
        $error = 'Ukuran file melebihi batas maksimal 4MB.';
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
    if (!in_array($ext, $allowedExt, true)) {
        $error = 'Format file tidak diizinkan. Gunakan JPG, PNG, WEBP, atau PDF.';
        return null;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
    if (!in_array($mime, $allowedMime, true)) {
        $error = 'Isi file tidak valid (harus berupa gambar atau PDF asli).';
        return null;
    }

    $uploadDir = UPLOADS_PATH . '/ppdb';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = 'ppdb_' . bin2hex(random_bytes(12)) . '.' . $ext;
    $destination = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $error = 'Gagal menyimpan file ke server.';
        return null;
    }

    return 'assets/uploads/ppdb/' . $filename;
}

/**
 * Handle generic document / asset upload (PDF, DOCX, XLSX, ZIP, Images).
 */
function handleGenericFileUpload(array $file, array $allowedExt, int $maxSize, string $subDir, ?string &$error = null, ?string &$sizeFormatted = null, ?string &$detectedExt = null): ?string
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload gagal atau tidak ada file yang dipilih.';
        return null;
    }
    if ($file['size'] > $maxSize) {
        $error = 'Ukuran file melebihi batas maksimal (' . formatBytes($maxSize) . ').';
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $detectedExt = $ext;
    $sizeFormatted = formatBytes($file['size']);

    if (!in_array($ext, $allowedExt, true)) {
        $error = 'Format file tidak diizinkan. Gunakan format: ' . implode(', ', array_map('strtoupper', $allowedExt));
        return null;
    }

    $targetDir = UPLOADS_PATH . '/' . trim($subDir, '/');
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $filename = $subDir . '_' . bin2hex(random_bytes(10)) . '.' . $ext;
    $destination = $targetDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $error = 'Gagal menyimpan file ke server.';
        return null;
    }

    return 'assets/uploads/' . trim($subDir, '/') . '/' . $filename;
}

/**
 * Format bytes to readable string (e.g. 1.2 MB).
 */
function formatBytes(int $bytes, int $precision = 1): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Convert standard YouTube URL to clean embed URL.
 */
function getYouTubeEmbedUrl(?string $url): ?string
{
    if (empty($url)) {
        return null;
    }
    if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return 'https://www.youtube-nocookie.com/embed/' . $matches[1];
    }
    if (preg_match('/[?&]v=([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return 'https://www.youtube-nocookie.com/embed/' . $matches[1];
    }
    if (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return 'https://www.youtube-nocookie.com/embed/' . $matches[1];
    }
    return $url;
}


