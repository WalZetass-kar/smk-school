<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfVerify($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Permintaan tidak valid.');
    redirect(BASE_URL . '/admin/news/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT title, featured_image FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $news = $stmt->fetch();
    if ($news) {
        $newsTitle = $news['title'] ?: "ID #$id";
        $img = $news['featured_image'];
        if (!empty($img) && !str_starts_with($img, 'assets/images/')) {
            $imgPath = BASE_PATH . '/' . ltrim($img, '/\\');
            if (is_file($imgPath) && str_starts_with(realpath($imgPath) ?: '', realpath(UPLOADS_PATH) ?: '')) {
                @unlink($imgPath);
            }
        }
        $db->prepare("DELETE FROM news WHERE id = ?")->execute([$id]);
        logActivity('DELETE_NEWS', 'news', "Menghapus artikel berita: $newsTitle (ID: $id)");
        setFlash('success', 'Berita berhasil dihapus.');
    }
}
redirect(BASE_URL . '/admin/news/index.php');
