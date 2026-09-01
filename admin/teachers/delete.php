<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfVerify($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Permintaan tidak valid.');
    redirect(BASE_URL . '/admin/teachers/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id) {
    $teacherName = getDB()->query("SELECT name FROM teachers WHERE id = " . (int)$id)->fetchColumn() ?: "ID #$id";
    getDB()->prepare("DELETE FROM teachers WHERE id = ?")->execute([$id]);
    logActivity('DELETE_TEACHER', 'teachers', "Menghapus data guru/tendik: $teacherName (ID: $id)");
    setFlash('success', 'Data berhasil dihapus.');
}
redirect(BASE_URL . '/admin/teachers/index.php');
