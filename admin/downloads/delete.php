<?php
/**
 * admin/downloads/delete.php
 * Hapus Dokumen Unduhan (POST + CSRF)
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/admin/downloads/index.php');
}

if (!csrfVerify($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Sesi form tidak valid.');
    redirect(BASE_URL . '/admin/downloads/index.php');
}

$id = (int)($_POST['id'] ?? 0);
$db = getDB();

$stmt = $db->prepare("SELECT file_path FROM downloads WHERE id = ?");
$stmt->execute([$id]);
$path = $stmt->fetchColumn();

if ($path && file_exists(BASE_PATH . '/' . $path)) {
    @unlink(BASE_PATH . '/' . $path);
}

$db->prepare("DELETE FROM downloads WHERE id = ?")->execute([$id]);
setFlash('success', 'Dokumen unduhan berhasil dihapus.');
redirect(BASE_URL . '/admin/downloads/index.php');
