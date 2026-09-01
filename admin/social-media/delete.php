<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfVerify($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Permintaan tidak valid.');
    redirect(BASE_URL . '/admin/social-media/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id) {
    $platform = getDB()->query("SELECT platform FROM social_media WHERE id = " . (int)$id)->fetchColumn() ?: "ID #$id";
    getDB()->prepare("DELETE FROM social_media WHERE id = ?")->execute([$id]);
    logActivity('DELETE_SOCIAL_MEDIA', 'social_media', "Menghapus tautan media sosial: $platform (ID: $id)");
    setFlash('success', 'Tautan berhasil dihapus.');
}
redirect(BASE_URL . '/admin/social-media/index.php');
