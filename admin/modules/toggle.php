<?php
/**
 * admin/modules/toggle.php
 * Endpoint toggle 1-klik untuk status aktif/nonaktif modul fitur
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireSuperAdmin();

if (!canManageModules()) {
    setFlash('danger', 'Akses ke manajemen fitur & modul dinonaktifkan oleh lisensi sistem.');
    redirect(BASE_URL . '/admin/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        setFlash('danger', 'Sesi tidak valid, silakan coba lagi.');
        redirect(BASE_URL . '/admin/modules/index.php');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $db = getDB();

    $stmt = $db->prepare("SELECT id, module_key, name, is_active FROM site_modules WHERE id = ?");
    $stmt->execute([$id]);
    $mod = $stmt->fetch();

    if ($mod) {
        $newStatus = $mod['is_active'] ? 0 : 1;
        $db->prepare("UPDATE site_modules SET is_active = ? WHERE id = ?")->execute([$newStatus, $id]);

        $statusText = $newStatus ? 'diaktifkan (ON)' : 'dinonaktifkan (OFF)';
        $modDisplayName = $mod['name'] ?? ucfirst($mod['module_key']);
        logActivity('TOGGLE_MODULE', 'site_modules', "Modul {$modDisplayName} ({$mod['module_key']}) $statusText.");
        setFlash('success', "Modul \"{$modDisplayName}\" berhasil $statusText.");
    } else {
        setFlash('danger', 'Modul tidak ditemukan.');
    }
}

redirect(BASE_URL . '/admin/modules/index.php');
