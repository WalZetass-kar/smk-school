<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireSuperAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfVerify($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Permintaan tidak valid.');
    redirect(BASE_URL . '/admin/users/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id && $id !== (int) $_SESSION['admin_id']) {
    getDB()->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    setFlash('success', 'Akun admin berhasil dihapus.');
} else {
    setFlash('error', 'Anda tidak dapat menghapus akun sendiri.');
}
redirect(BASE_URL . '/admin/users/index.php');
