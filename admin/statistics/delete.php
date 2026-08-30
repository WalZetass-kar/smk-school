<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfVerify($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Permintaan tidak valid.');
    redirect(BASE_URL . '/admin/statistics/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id) {
    getDB()->prepare("DELETE FROM statistics WHERE id = ?")->execute([$id]);
    setFlash('success', 'Statistik berhasil dihapus.');
}
redirect(BASE_URL . '/admin/statistics/index.php');
