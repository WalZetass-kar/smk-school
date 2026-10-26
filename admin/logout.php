<?php
require_once __DIR__ . '/../config/config.php';

// Protect against Logout CSRF: require POST with CSRF or valid token parameter
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Sesi tidak valid.');
        redirect(BASE_URL . '/admin/dashboard.php');
    }
} elseif (isset($_GET['token'])) {
    if (!csrfVerify($_GET['token'])) {
        setFlash('error', 'Token logout tidak valid.');
        redirect(BASE_URL . '/admin/dashboard.php');
    }
} else {
    // Prevent accidental/image-tag GET logout; redirect back to dashboard
    redirect(BASE_URL . '/admin/dashboard.php');
}

if (!empty($_SESSION['admin_id'])) {
    logActivity('LOGOUT', 'auth', 'Admin ' . ($_SESSION['admin_name'] ?? 'Admin') . ' berhasil keluar dari sesi.');
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

redirect(BASE_URL . '/admin/login.php');
