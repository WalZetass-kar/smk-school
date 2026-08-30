<?php
/**
 * auth.php
 * Guards admin pages. require this (after config.php) at the top of any page
 * under /admin that should only be visible to a logged-in administrator.
 */

if (empty($_SESSION['admin_id'])) {
    redirect(BASE_URL . '/admin/login.php');
}

/** True when the logged-in admin is a superadmin (can manage other admin accounts). */
function isSuperAdmin(): bool
{
    return ($_SESSION['admin_role'] ?? '') === 'superadmin';
}

/** Call at the top of superadmin-only pages (e.g. admin/users). */
function requireSuperAdmin(): void
{
    if (!isSuperAdmin()) {
        setFlash('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        redirect(BASE_URL . '/admin/dashboard.php');
    }
}
