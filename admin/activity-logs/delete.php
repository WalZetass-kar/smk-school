<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireSuperAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Token CSRF tidak valid.');
        redirect(BASE_URL . '/admin/activity-logs/index.php');
    }

    $db = getDB();
    $count = (int)$db->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
    $db->exec("TRUNCATE TABLE activity_logs");

    logActivity('CLEAR_LOGS', 'audit', "Super Admin membersihkan $count riwayat catatan aktivitas.");
    setFlash('success', "Berhasil membersihkan $count catatan riwayat log aktivitas.");
}

redirect(BASE_URL . '/admin/activity-logs/index.php');
