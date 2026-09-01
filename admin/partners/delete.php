<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfVerify($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Permintaan tidak valid.');
    redirect(BASE_URL . '/admin/partners/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id) {
    $partnerName = getDB()->query("SELECT name FROM partners WHERE id = " . (int)$id)->fetchColumn() ?: "ID #$id";
    getDB()->prepare("DELETE FROM partners WHERE id = ?")->execute([$id]);
    logActivity('DELETE_PARTNER', 'partners', "Menghapus mitra kerja sama: $partnerName (ID: $id)");
    setFlash('success', 'Partner berhasil dihapus.');
}
redirect(BASE_URL . '/admin/partners/index.php');
