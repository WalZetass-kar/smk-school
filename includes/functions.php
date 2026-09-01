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
        $stmt = $db->prepare("
            INSERT INTO rate_limits (rate_key, attempts, last_attempt, lockout_until)
            VALUES (?, 1, ?, NULL)
            ON DUPLICATE KEY UPDATE 
              attempts = IF(attempts >= 100, 100, attempts + 1),
              last_attempt = VALUES(last_attempt)
        ");
        $stmt->execute([$key, $now]);
    } catch (Throwable $e) {
        error_log('Rate limit attempt error: ' . $e->getMessage());
    }
}

/**
 * Reset rate limit counter on success.
 */
function resetRateLimit(string $key): void
{
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM rate_limits WHERE rate_key = ?");
        $stmt->execute([$key]);
    } catch (Throwable $e) {
        // ignore
    }
}

/**
 * Validate + move an uploaded image into /assets/uploads with a random filename and deep security inspection.
 * Signature: handleImageUpload(array $file, ?string &$error = null, string $subDir = '')
 */
function handleImageUpload(array $file, ?string &$error = null, string $subDir = ''): ?string
{
    $subDir = trim($subDir, '/\\');

    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload gagal atau tidak ada file yang dipilih.';
        return null;
    }
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        $error = 'Ukuran file melebihi batas maksimal 2MB.';
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (isForbiddenExtension($ext) || !in_array($ext, ALLOWED_IMAGE_EXT, true)) {
        $error = 'Format file tidak diizinkan. Gunakan JPG, JPEG, PNG, atau WEBP.';
        return null;
    }

    // Re-check with finfo — don't trust the client-supplied extension/MIME alone.
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $allowedMime, true)) {
        $error = 'Isi file bukan gambar yang valid (MIME tidak cocok).';
        return null;
    }

    // Deep Magic Bytes validation
    if (!validateMagicBytes($file['tmp_name'], $ext)) {
        $error = 'Struktur header file tidak valid (magic bytes mismatch).';
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
    if (isForbiddenExtension($ext) || !in_array($ext, $allowedExt, true)) {
        $error = 'Format file tidak diizinkan. Gunakan JPG, PNG, WEBP, atau PDF.';
        return null;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
    if (!in_array($mime, $allowedMime, true)) {
        $error = 'Isi file tidak valid (MIME mismatch).';
        return null;
    }

    // Magic Bytes Check
    if (!validateMagicBytes($file['tmp_name'], $ext)) {
        $error = 'Header file tidak sesuai dengan tipe ekstensi.';
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

    if (isForbiddenExtension($ext) || !in_array($ext, $allowedExt, true)) {
        $error = 'Format file tidak diizinkan. Gunakan format: ' . implode(', ', array_map('strtoupper', $allowedExt));
        return null;
    }

    // Magic bytes check
    if (!validateMagicBytes($file['tmp_name'], $ext)) {
        $error = 'Header berkas tidak valid atau rusak.';
        return null;
    }

    $targetDir = UPLOADS_PATH . '/' . trim($subDir, '/\\');
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $filename = trim($subDir, '/\\') . '_' . bin2hex(random_bytes(12)) . '.' . $ext;
    $destination = $targetDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $error = 'Gagal menyimpan file ke server.';
        return null;
    }

    return 'assets/uploads/' . trim($subDir, '/\\') . '/' . $filename;
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
 * Check if a file extension is dangerous / forbidden to prevent RCE.
 */
function isForbiddenExtension(string $ext): bool
{
    $blacklisted = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phps', 'pht', 'phar',
        'inc', 'cgi', 'pl', 'py', 'sh', 'bash', 'zsh', 'exe', 'bat', 'cmd', 'ps1',
        'vbs', 'js', 'jsp', 'asp', 'aspx', 'cer', 'htaccess', 'htpasswd', 'env', 'ini',
        'conf', 'config', 'sql', 'dll', 'so', 'bin'
    ];
    return in_array(strtolower(trim($ext, '.')), $blacklisted, true);
}

/**
 * Validate real file header / magic bytes against declared extension.
 */
function validateMagicBytes(string $filePath, string $ext): bool
{
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return false;
    }

    $ext = strtolower(trim($ext, '.'));
    if (isForbiddenExtension($ext)) {
        return false;
    }

    $handle = @fopen($filePath, 'rb');
    if (!$handle) {
        return false;
    }
    $bytes = fread($handle, 16);
    fclose($handle);

    if (empty($bytes)) {
        return false;
    }

    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            return str_starts_with($bytes, "\xFF\xD8\xFF");
        case 'png':
            return str_starts_with($bytes, "\x89PNG\r\n\x1a\n");
        case 'webp':
            return str_starts_with($bytes, "RIFF") && substr($bytes, 8, 4) === "WEBP";
        case 'pdf':
            return str_starts_with($bytes, "%PDF");
        case 'zip':
        case 'docx':
        case 'xlsx':
            return str_starts_with($bytes, "PK\x03\x04") || str_starts_with($bytes, "PK\x05\x06") || str_starts_with($bytes, "PK\x07\x08");
        default:
            return true;
    }
}

/**
 * Check if a file path is strictly within an allowed base directory (Anti Path Traversal).
 */
function isSafePath(string $filePath, string $baseDirectory = UPLOADS_PATH): bool
{
    $realBase = realpath($baseDirectory);
    $realPath = realpath($filePath);

    if ($realBase === false || $realPath === false) {
        return false;
    }

    return str_starts_with($realPath, $realBase);
}

/**
 * Strict Validators
 */
function validateNISN(string $nisn): bool
{
    return (bool) preg_match('/^[0-9]{10}$/', trim($nisn));
}

function validatePhone(string $phone): bool
{
    $clean = preg_replace('/[\s\-\(\)\+]/', '', $phone);
    return (bool) preg_match('/^[0-9]{8,18}$/', $clean);
}

function validateEmail(string $email): bool
{
    return (bool) filter_var(trim($email), FILTER_VALIDATE_EMAIL);
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


