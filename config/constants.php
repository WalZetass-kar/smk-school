<?php
/**
 * constants.php
 * Path & environment constants. Loaded once by config.php.
 */

// Root filesystem path of the project
define('BASE_PATH', dirname(__DIR__));

// Public base URL — computed from where the project sits under the web
// server's document root, NOT from the current script's own folder. That
// matters because admin/*.php files are requested directly (their SCRIPT_NAME
// dirname would be ".../admin"), while public pages go through index.php at
// the project root — using SCRIPT_NAME's dirname directly would give two
// different (and for admin, wrong) base URLs depending on which was open.
// Example on XAMPP with the project at htdocs/smk-school: http://localhost/smk-school
if (!defined('BASE_URL')) {
    // Intelligent HTTPS Detection (supports direct SSL, Cloudflare, AWS, Nginx reverse proxy)
    $isHttps = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
        || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on');

    $protocol = $isHttps ? 'https://' : 'http://';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';

    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/') : '';
    $basePathNormalized = str_replace('\\', '/', BASE_PATH);

    if ($docRoot !== '' && str_starts_with($basePathNormalized, $docRoot)) {
        // Reliable: derive the URL prefix from the filesystem relationship
        // between the document root and this project's folder.
        $urlPrefix = substr($basePathNormalized, strlen($docRoot));
    } else {
        // Fallback for unusual server configs: best-effort guess from the
        // current script location (correct for requests through index.php).
        $urlPrefix = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    }

    define('BASE_URL', rtrim($protocol . $host . $urlPrefix, '/'));
}

define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_PATH', BASE_PATH . '/assets/uploads');
define('UPLOADS_URL', ASSETS_URL . '/uploads');

// Upload restrictions (section 26 — keamanan)
define('ALLOWED_IMAGE_EXT', ['jpg', 'jpeg', 'png', 'webp']);
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2MB

// Pagination
define('NEWS_PER_PAGE', 9);
define('GALLERY_PER_PAGE', 12);
