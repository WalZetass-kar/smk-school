<?php
/**
 * functions.php
 * Small, reusable helpers shared across the whole site.
 */

/** Escape output for safe HTML display. Always wrap dynamic content with this. */
function e(?string $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
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

/** Get sanitized client IP address. */
function getClientIP(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '127.0.0.1';
}

/** Get client user agent string (truncated). */
function getUserAgent(): string
{
    return substr(strip_tags($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'), 0, 250);
}

/**
 * Record an activity in the audit trail (activity_logs).
 */
function logActivity(string $action, string $module, ?string $detail = null): void
{
    try {
        $db = getDB();
        $userId   = $_SESSION['admin_id'] ?? null;
        $userName = $_SESSION['admin_name'] ?? ($_SESSION['admin_username'] ?? 'Guest/Public');
        $userRole = $_SESSION['admin_role'] ?? 'guest';
        $ip       = getClientIP();
        $agent    = getUserAgent();

        $stmt = $db->prepare("
            INSERT INTO activity_logs (user_id, user_name, user_role, action, module, detail, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $userName, $userRole, $action, $module, $detail, $ip, $agent]);
    } catch (Throwable $e) {
        error_log('Failed to log activity: ' . $e->getMessage());
    }
}

/**
 * Check if an action key has exceeded rate limits.
 * Returns true if action is ALLOWED, false if BLOCKED.
 */
function checkRateLimit(string $key, int $maxAttempts = 5, int $decaySeconds = 600, int &$remainingLockout = 0): bool
{
    try {
        $db = getDB();
        $now = time();
        $stmt = $db->prepare("SELECT attempts, last_attempt, lockout_until FROM rate_limits WHERE rate_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch();

        if (!$row) {
            return true;
        }

        // Check if currently locked out
        if (!empty($row['lockout_until']) && $row['lockout_until'] > $now) {
            $remainingLockout = $row['lockout_until'] - $now;
            return false;
        }

        // Check if decayed
        if (($now - (int)$row['last_attempt']) > $decaySeconds) {
            $del = $db->prepare("DELETE FROM rate_limits WHERE rate_key = ?");
            $del->execute([$key]);
            return true;
        }

        if ((int)$row['attempts'] >= $maxAttempts) {
            $lockoutUntil = $now + $decaySeconds;
            $upd = $db->prepare("UPDATE rate_limits SET lockout_until = ? WHERE rate_key = ?");
            $upd->execute([$lockoutUntil, $key]);
            $remainingLockout = $decaySeconds;
            return false;
        }

        return true;
    } catch (Throwable $e) {
        error_log('Rate limiter check error: ' . $e->getMessage());
        return true; // fail open safely on DB error
    }
}

/**
 * Record a failed attempt for rate limiting.
 */
function recordRateLimitAttempt(string $key, int $decaySeconds = 600): void
{
    try {
        $db = getDB();
        $now = time();
        $stmt = $db->prepare("SELECT id, attempts, last_attempt FROM rate_limits WHERE rate_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $row = $stmt->fetch();

        if ($row) {
            // Check if decayed
            if (($now - (int)$row['last_attempt']) > $decaySeconds) {
                $upd = $db->prepare("UPDATE rate_limits SET attempts = 1, last_attempt = ?, lockout_until = NULL WHERE id = ?");
                $upd->execute([$now, $row['id']]);
            } else {
                $upd = $db->prepare("UPDATE rate_limits SET attempts = attempts + 1, last_attempt = ? WHERE id = ?");
                $upd->execute([$now, $row['id']]);
            }
        } else {
            $ins = $db->prepare("INSERT INTO rate_limits (rate_key, attempts, last_attempt) VALUES (?, 1, ?)");
            $ins->execute([$key, $now]);
        }
    } catch (Throwable $e) {
        error_log('Rate limiter record error: ' . $e->getMessage());
    }
}

/**
 * Reset rate limit counter upon successful action (e.g. valid login).
 */
function resetRateLimit(string $key): void
{
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM rate_limits WHERE rate_key = ?");
        $stmt->execute([$key]);
    } catch (Throwable $e) {
        error_log('Rate limiter reset error: ' . $e->getMessage());
    }
}

/**
 * Check if a file extension is potentially dangerous/executable.
 */
function isForbiddenExtension(string $filename): bool
{
    $forbidden = [
        'php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phar', 'inc',
        'exe', 'bat', 'sh', 'cgi', 'pl', 'py', 'js', 'jsp', 'asp', 'aspx',
        'htaccess', 'htpasswd', 'env', 'config'
    ];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $forbidden, true);
}

/**
 * Verify Magic Bytes (file header) to ensure uploaded file actually matches its expected format.
 */
function validateMagicBytes(string $tmpFilePath, string $extension): bool
{
    if (!file_exists($tmpFilePath) || !is_readable($tmpFilePath)) {
        return false;
    }

    $handle = fopen($tmpFilePath, 'rb');
    if (!$handle) {
        return false;
    }
    $bytes = fread($handle, 16);
    fclose($handle);

    if (empty($bytes)) {
        return false;
    }

    $ext = strtolower($extension);

    // Check Magic Bytes
    return match ($ext) {
        'jpg', 'jpeg' => str_starts_with($bytes, "\xFF\xD8\xFF"),
        'png'         => str_starts_with($bytes, "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"),
        'gif'         => str_starts_with($bytes, "GIF87a") || str_starts_with($bytes, "GIF89a"),
        'webp'        => str_starts_with($bytes, "RIFF") && substr($bytes, 8, 4) === "WEBP",
        'pdf'         => str_starts_with($bytes, "%PDF-"),
        'zip', 'docx', 'xlsx', 'pptx' => str_starts_with($bytes, "PK\x03\x04") || str_starts_with($bytes, "PK\x05\x06"),
        'csv', 'txt'  => true, // Plain text files have no fixed binary signature
        default       => true,
    };
}

/**
 * Check if a target file path resides safely inside the allowed base directory (Path Traversal Protection).
 */
function isSafePath(string $baseDir, string $targetPath): bool
{
    $realBase = realpath($baseDir);
    $realTarget = realpath($targetPath);

    if ($realBase === false || $realTarget === false) {
        return false;
    }

    return str_starts_with($realTarget, $realBase);
}

/** Strict validator for 10-digit NISN. */
function validateNISN(?string $nisn): bool
{
    return (bool) preg_match('/^\d{10}$/', (string)$nisn);
}

/** Strict validator for phone numbers (Indonesian standard 9-15 digits). */
function validatePhone(?string $phone): bool
{
    return (bool) preg_match('/^(\+62|62|08)[0-9]{8,13}$/', preg_replace('/[\s\-\.\(\)]/', '', (string)$phone));
}

/** Strict validator for email. */
function validateEmail(?string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
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

/** Alias for csrfVerify */
function verifyCsrfToken(?string $token): bool
{
    return csrfVerify($token);
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
 * Validate + move an uploaded image into /assets/uploads with a random filename.
 * Signature: handleImageUpload(array $file, ?string &$error = null, string $subDir = '')
 */
function handleImageUpload(array $file, ?string &$error = null, string $subDir = ''): ?string
{
    $subDir = trim($subDir, '/');

    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload gagal atau tidak ada file yang dipilih.';
        return null;
    }
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        $error = 'Ukuran file melebihi batas maksimal 2MB.';
        return null;
    }

    if (isForbiddenExtension($file['name'])) {
        $error = 'Format ekstensi file berbahaya dan dilarang diunggah.';
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_IMAGE_EXT, true)) {
        $error = 'Format file tidak diizinkan. Gunakan JPG, JPEG, PNG, atau WEBP.';
        return null;
    }

    // Magic Bytes Check
    if (!validateMagicBytes($file['tmp_name'], $ext)) {
        $error = 'Struktur binary file tidak sesuai dengan gambar asli.';
        return null;
    }

    // Re-check with finfo — don't trust the client-supplied extension/MIME alone.
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $allowedMime, true)) {
        $error = 'Isi file bukan gambar yang valid.';
        return null;
    }

    $targetDir = UPLOADS_PATH . ($subDir !== '' ? '/' . $subDir : '');
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $destination = $targetDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $error = 'Gagal menyimpan file ke server.';
        return null;
    }

    return 'assets/uploads' . ($subDir !== '' ? '/' . $subDir : '') . '/' . $filename;
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

    if (isForbiddenExtension($file['name'])) {
        $error = 'Format file berbahaya dan dilarang diunggah.';
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
    if (!in_array($ext, $allowedExt, true)) {
        $error = 'Format file tidak diizinkan. Gunakan JPG, PNG, WEBP, atau PDF.';
        return null;
    }

    if (!validateMagicBytes($file['tmp_name'], $ext)) {
        $error = 'Struktur binary file tidak valid.';
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

    if (isForbiddenExtension($file['name'])) {
        $error = 'Format file berbahaya dan dilarang diunggah.';
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $detectedExt = $ext;
    $sizeFormatted = formatBytes($file['size']);

    if (!in_array($ext, $allowedExt, true)) {
        $error = 'Format file tidak diizinkan. Gunakan format: ' . implode(', ', array_map('strtoupper', $allowedExt));
        return null;
    }

    if (!validateMagicBytes($file['tmp_name'], $ext)) {
        $error = 'Struktur binary file tidak valid.';
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
