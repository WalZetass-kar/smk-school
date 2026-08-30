<?php
/**
 * admin/achievements/delete.php
 * Hapus Prestasi Siswa (POST + CSRF)
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/admin/achievements/index.php');
}

if (!csrfVerify($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Sesi form tidak valid.');
    redirect(BASE_URL . '/admin/achievements/index.php');
}

$id = (int)($_POST['id'] ?? 0);
$db = getDB();

$stmt = $db->prepare("SELECT photo FROM achievements WHERE id = ?");
$stmt->execute([$id]);
$photo = $stmt->fetchColumn();

if ($photo && file_exists(BASE_PATH . '/' . $photo)) {
    @unlink(BASE_PATH . '/' . $photo);
}

$db->prepare("DELETE FROM achievements WHERE id = ?")->execute([$id]);
setFlash('success', 'Data prestasi siswa berhasil dihapus.');
redirect(BASE_URL . '/admin/achievements/index.php');
