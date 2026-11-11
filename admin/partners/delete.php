<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if (!isModuleActive('partners')) {
    setFlash('danger', 'Fitur Mitra Kerja Sama DU/DI tidak tersedia pada paket lisensi sekolah ini.');
    redirect(BASE_URL . '/admin/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfVerify($_POST['csrf_token'] ?? null)) {
    setFlash('error', 'Permintaan tidak valid.');
    redirect(BASE_URL . '/admin/partners/index.php');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT name, logo FROM partners WHERE id = ?");
    $stmt->execute([$id]);
    $partner = $stmt->fetch();
    if ($partner) {
        $partnerName = $partner['name'] ?: "ID #$id";
        $logo = $partner['logo'];
        if (!empty($logo) && !str_starts_with($logo, 'assets/images/')) {
            $logoPath = BASE_PATH . '/' . ltrim($logo, '/\\');
            if (is_file($logoPath) && str_starts_with(realpath($logoPath) ?: '', realpath(UPLOADS_PATH) ?: '')) {
                @unlink($logoPath);
            }
        }
        $db->prepare("DELETE FROM partners WHERE id = ?")->execute([$id]);
        logActivity('DELETE_PARTNER', 'partners', "Menghapus mitra kerja sama: $partnerName (ID: $id)");
        setFlash('success', 'Partner berhasil dihapus.');
    }
}
redirect(BASE_URL . '/admin/partners/index.php');
