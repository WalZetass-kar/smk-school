<?php
/**
 * auth.php
 * Guards admin pages. require this (after config.php) at the top of any page
 * under /admin that should only be visible to a logged-in administrator.
 */

// 1. Authentication check
if (empty($_SESSION['admin_id'])) {
    redirect(BASE_URL . '/admin/login.php');
}

// 2. Session Inactivity Timeout (60 minutes / 3600 seconds)
$sessionTimeout = 3600;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $sessionTimeout)) {
    $adminName = $_SESSION['admin_name'] ?? 'Admin';
    logActivity('SESSION_TIMEOUT', 'auth', 'Sesi admin ' . $adminName . ' kedaluwarsa karena tidak aktif.');
    
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    
    session_start();
    setFlash('error', 'Sesi Anda telah berakhir karena tidak ada aktivitas selama 60 menit. Silakan login kembali.');
    redirect(BASE_URL . '/admin/login.php');
}
$_SESSION['last_activity'] = time();

/** True when the logged-in admin is a superadmin (can manage other admin accounts). */
function isSuperAdmin(): bool
{
    return ($_SESSION['admin_role'] ?? '') === 'superadmin';
}

/** Call to enforce authentication. */
function requireAuth(): void
{
    if (empty($_SESSION['admin_id'])) {
        redirect(BASE_URL . '/admin/login.php');
    }
}

/** Call at the top of superadmin-only pages (e.g. admin/users, admin/activity-logs). */
function requireSuperAdmin(): void
{
    if (!isSuperAdmin()) {
        logActivity('UNAUTHORIZED_ACCESS_ATTEMPT', 'auth', 'Akses ditolak ke modul superadmin.');
        setFlash('error', 'Anda tidak memiliki izin (Super Admin) untuk mengakses halaman ini.');
        redirect(BASE_URL . '/admin/dashboard.php');
    }
}
