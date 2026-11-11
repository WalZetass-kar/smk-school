<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfVerify($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Permintaan tidak valid.');
    redirect(BASE_URL . '/admin/banners/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT title, image FROM banners WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if ($item) {
        $bannerTitle = $item['title'] ?: "ID #$id";
        $img = $item['image'];
        if (!empty($img) && !str_starts_with($img, 'assets/images/')) {
            $imgPath = BASE_PATH . '/' . ltrim($img, '/\\');
            if (is_file($imgPath) && str_starts_with(realpath($imgPath) ?: '', realpath(UPLOADS_PATH) ?: '')) {
                @unlink($imgPath);
            }
        }
        $db->prepare("DELETE FROM banners WHERE id = ?")->execute([$id]);
        logActivity('DELETE_BANNER', 'banners', "Menghapus banner: $bannerTitle (ID: $id)");
        setFlash('success', 'Banner berhasil dihapus.');
    }
}
redirect(BASE_URL . '/admin/banners/index.php');
