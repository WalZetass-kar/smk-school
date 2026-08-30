<?php
/**
 * config.php
 * App bootstrap. Every entry point (public pages, admin pages) requires this first.
 */

// Show errors while developing; set both to 0 / false before going live.
error_reporting(E_ALL);
ini_set('display_errors', '1');

date_default_timezone_set('Asia/Jakarta');

// Harden the session cookie before starting it (section 26 — keamanan)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/database.php';
require_once BASE_PATH . '/includes/functions.php';
