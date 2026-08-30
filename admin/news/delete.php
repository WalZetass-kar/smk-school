<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfVerify($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Permintaan tidak valid.');
    redirect(BASE_URL . '/admin/news/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id) {
    getDB()->prepare("DELETE FROM news WHERE id = ?")->execute([$id]);
    setFlash('success', 'Berita berhasil dihapus.');
}
redirect(BASE_URL . '/admin/news/index.php');
