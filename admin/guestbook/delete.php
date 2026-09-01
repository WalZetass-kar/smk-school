<?php
/**
 * admin/guestbook/delete.php
 * Hapus Catatan Buku Tamu
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $db = getDB();
            $stmt = $db->prepare("DELETE FROM guestbooks WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'Catatan buku tamu berhasil dihapus.');
        }
    }
}
redirect(BASE_URL . '/admin/guestbook/index.php');
