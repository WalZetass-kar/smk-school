<?php
/**
 * admin/agendas/delete.php
 * Hapus Agenda
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $db = getDB();
            $stmt = $db->prepare("DELETE FROM agendas WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'Agenda kegiatan berhasil dihapus.');
        }
    }
}
redirect(BASE_URL . '/admin/agendas/index.php');
