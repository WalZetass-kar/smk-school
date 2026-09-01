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
            $guestName = $db->query("SELECT name FROM guestbooks WHERE id = $id")->fetchColumn() ?: "ID #$id";
            $stmt = $db->prepare("DELETE FROM guestbooks WHERE id = ?");
            $stmt->execute([$id]);
            logActivity('DELETE_GUESTBOOK', 'guestbook', "Menghapus catatan buku tamu: $guestName (ID: $id)");
            setFlash('success', 'Catatan buku tamu berhasil dihapus.');
        }
    }
}
redirect(BASE_URL . '/admin/guestbook/index.php');
