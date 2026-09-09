<?php
/**
 * admin/bkk/delete.php
 * Hapus Lowongan Kerja BKK (POST + CSRF)
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if (!isModuleActive('bkk')) {
    setFlash('danger', 'Fitur Bursa Kerja Khusus (BKK) tidak tersedia pada paket lisensi sekolah ini.');
    redirect(BASE_URL . '/admin/dashboard.php');
}

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

$title = $db->query("SELECT title FROM bkk_jobs WHERE id = $id")->fetchColumn() ?: "ID #$id";
if ($logo && file_exists(BASE_PATH . '/' . $logo)) {
    @unlink(BASE_PATH . '/' . $logo);
}

$db->prepare("DELETE FROM bkk_jobs WHERE id = ?")->execute([$id]);
logActivity('DELETE_BKK_JOB', 'bkk', "Menghapus lowongan BKK: $title (ID: $id)");
setFlash('success', 'Data lowongan kerja berhasil dihapus.');
redirect(BASE_URL . '/admin/bkk/index.php');
