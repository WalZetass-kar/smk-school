<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfVerify($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Permintaan tidak valid.');
    redirect(BASE_URL . '/admin/majors/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id) {
    $majorName = getDB()->query("SELECT name FROM majors WHERE id = " . (int)$id)->fetchColumn() ?: "ID #$id";
    getDB()->prepare("DELETE FROM majors WHERE id = ?")->execute([$id]);
    logActivity('DELETE_MAJOR', 'majors', "Menghapus program keahlian: $majorName (ID: $id)");
    setFlash('success', 'Jurusan berhasil dihapus.');
}
redirect(BASE_URL . '/admin/majors/index.php');
