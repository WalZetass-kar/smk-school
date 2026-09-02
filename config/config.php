<?php
/**
 * config.php
 * App bootstrap. Every entry point (public pages, admin pages) requires this first.
 */

// Show errors while developing; set both to 0 / false before going live.
error_reporting(E_ALL);
ini_set('display_errors', '1');

date_default_timezone_set('Asia/Jakarta');

// Secure session configuration
if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    } else {
        @session_start();
    }
}

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/database.php';
require_once BASE_PATH . '/includes/functions.php';
