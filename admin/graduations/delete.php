<?php
/**
 * admin/graduations/delete.php
 * Hapus Data Siswa Kelulusan
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $db = getDB();
            $stmt = $db->prepare("DELETE FROM graduations WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'Data siswa berhasil dihapus.');
        }
    }
}
redirect(BASE_URL . '/admin/graduations/index.php');
