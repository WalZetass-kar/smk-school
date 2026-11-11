<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfVerify($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Permintaan tidak valid.');
    redirect(BASE_URL . '/admin/teachers/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT name, photo FROM teachers WHERE id = ?");
    $stmt->execute([$id]);
    $teacher = $stmt->fetch();
    if ($teacher) {
        $teacherName = $teacher['name'] ?: "ID #$id";
        $img = $teacher['photo'];
        if (!empty($img) && !str_starts_with($img, 'assets/images/')) {
            $imgPath = BASE_PATH . '/' . ltrim($img, '/\\');
            if (is_file($imgPath) && str_starts_with(realpath($imgPath) ?: '', realpath(UPLOADS_PATH) ?: '')) {
                @unlink($imgPath);
            }
        }
        $db->prepare("DELETE FROM teachers WHERE id = ?")->execute([$id]);
        logActivity('DELETE_TEACHER', 'teachers', "Menghapus data guru/tendik: $teacherName (ID: $id)");
        setFlash('success', 'Data berhasil dihapus.');
    }
}
redirect(BASE_URL . '/admin/teachers/index.php');
