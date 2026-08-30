<?php
/**
 * router.php
 * Router script for PHP's built-in web server (php -S localhost:8000 router.php).
 * Allows running the site locally without Apache mod_rewrite.
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Block direct access to sensitive folders (matching .htaccess rules)
if (str_starts_with($uri, '/config/') || str_starts_with($uri, '/includes/')) {
    http_response_code(403);
    echo 'Access forbidden';
    exit;
}

// Dynamic sitemap rewrite
if ($uri === '/sitemap.xml') {
    require __DIR__ . '/sitemap.php';
    exit;
}

// Let static files and existing admin PHP files be handled directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Route all other requests to the front controller
require __DIR__ . '/index.php';
