<?php
/**
 * admin/extracurriculars/delete.php
 * Hapus Ekstrakurikuler (POST + CSRF)
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/admin/extracurriculars/index.php');
}

if (!csrfVerify($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Sesi form tidak valid.');
    redirect(BASE_URL . '/admin/extracurriculars/index.php');
}

$id = (int)($_POST['id'] ?? 0);
$db = getDB();

$stmt = $db->prepare("SELECT image FROM extracurriculars WHERE id = ?");
$stmt->execute([$id]);
$img = $stmt->fetchColumn();

if ($img && file_exists(BASE_PATH . '/' . $img)) {
    @unlink(BASE_PATH . '/' . $img);
}

$db->prepare("DELETE FROM extracurriculars WHERE id = ?")->execute([$id]);
setFlash('success', 'Data ekstrakurikuler berhasil dihapus.');
redirect(BASE_URL . '/admin/extracurriculars/index.php');
