<?php
/**
 * admin/bkk/delete.php
 * Hapus Lowongan Kerja BKK (POST + CSRF)
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/admin/bkk/index.php');
}

if (!csrfVerify($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Sesi form tidak valid.');
    redirect(BASE_URL . '/admin/bkk/index.php');
}

$id = (int)($_POST['id'] ?? 0);
$db = getDB();

$stmt = $db->prepare("SELECT company_logo FROM bkk_jobs WHERE id = ?");
$stmt->execute([$id]);
$logo = $stmt->fetchColumn();

if ($logo && file_exists(BASE_PATH . '/' . $logo)) {
    @unlink(BASE_PATH . '/' . $logo);
}

$db->prepare("DELETE FROM bkk_jobs WHERE id = ?")->execute([$id]);
setFlash('success', 'Data lowongan kerja berhasil dihapus.');
redirect(BASE_URL . '/admin/bkk/index.php');
