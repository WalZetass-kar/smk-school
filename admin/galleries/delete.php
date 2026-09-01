<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfVerify($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Permintaan tidak valid.');
    redirect(BASE_URL . '/admin/galleries/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id) {
    $galTitle = getDB()->query("SELECT title FROM galleries WHERE id = " . (int)$id)->fetchColumn() ?: "ID #$id";
    getDB()->prepare("DELETE FROM galleries WHERE id = ?")->execute([$id]);
    logActivity('DELETE_GALLERY', 'galleries', "Menghapus foto galeri: $galTitle (ID: $id)");
    setFlash('success', 'Foto berhasil dihapus.');
}
redirect(BASE_URL . '/admin/galleries/index.php');
