<?php
/**
 * admin/banners/toggle.php
 * Endpoint untuk toggle status Aktif/Nonaktif banner (ON/OFF)
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        setFlash('danger', 'Sesi tidak valid, silakan coba lagi.');
        redirect(BASE_URL . '/admin/banners/index.php');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $db = getDB();

    $stmt = $db->prepare("SELECT id, title, status FROM banners WHERE id = ?");
    $stmt->execute([$id]);
    $banner = $stmt->fetch();

    if ($banner) {
        $newStatus = $banner['status'] ? 0 : 1;
        $db->prepare("UPDATE banners SET status = ? WHERE id = ?")->execute([$newStatus, $id]);

        $statusText = $newStatus ? 'diaktifkan (ON)' : 'dinonaktifkan (OFF)';
        logActivity('TOGGLE_BANNER', 'banners', "Banner '{$banner['title']}' berhasil $statusText.");
        setFlash('success', "Status banner \"{$banner['title']}\" berhasil $statusText.");
    } else {
        setFlash('danger', 'Banner tidak ditemukan.');
    }
}

redirect(BASE_URL . '/admin/banners/index.php');
