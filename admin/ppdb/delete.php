<?php
/**
 * admin/ppdb/delete.php
 * Hapus data pendaftar PPDB (POST only + CSRF)
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/admin/ppdb/index.php');
}

if (!csrfVerify($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Sesi form tidak valid.');
    redirect(BASE_URL . '/admin/ppdb/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
$db = getDB();

$stmt = $db->prepare("SELECT document_photo, document_kk, document_rapor FROM ppdb_registrations WHERE id = ?");
$stmt->execute([$id]);
$files = $stmt->fetch();

if ($files) {
    $candName = $db->query("SELECT full_name FROM ppdb_registrations WHERE id = $id")->fetchColumn() ?: "ID #$id";
    foreach (['document_photo', 'document_kk', 'document_rapor'] as $col) {
        if (!empty($files[$col])) {
            $path = BASE_PATH . '/' . $files[$col];
            if (file_exists($path)) {
                @unlink($path);
            }
        }
    }
    $db->prepare("DELETE FROM ppdb_registrations WHERE id = ?")->execute([$id]);
    logActivity('DELETE_PPDB_REGISTRATION', 'ppdb', "Menghapus berkas & data pendaftar: $candName (ID: $id)");
    setFlash('success', 'Data pendaftar berhasil dihapus.');
}

redirect(BASE_URL . '/admin/ppdb/index.php');
