<?php
/**
 * admin/modules/preset.php
 * Endpoint untuk menerapkan preset template jenjang sekolah (SMK, SMA, SMP, SD)
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if (!canManageModules()) {
    setFlash('danger', 'Akses ke manajemen fitur & modul dinonaktifkan oleh lisensi sistem.');
    redirect(BASE_URL . '/admin/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        setFlash('danger', 'Sesi tidak valid, silakan coba lagi.');
        redirect(BASE_URL . '/admin/modules/index.php');
    }

    $preset = trim($_POST['preset'] ?? '');
    $db = getDB();

    $presets = [
        'SMK' => [
            'name' => 'SMK (Kejuruan / Vokasi Lengkap)',
            'active' => ['bkk', 'partners', 'majors', 'kelulusan', 'downloads', 'agendas', 'ppdb', 'extracurriculars', 'prestasi', 'testimonials', 'news', 'galleries', 'video_profile', 'guestbook', 'statistics']
        ],
        'SMA' => [
            'name' => 'SMA / MA (Akademik & Peminatan)',
            'active' => ['majors', 'kelulusan', 'downloads', 'agendas', 'ppdb', 'extracurriculars', 'prestasi', 'testimonials', 'news', 'galleries', 'video_profile', 'guestbook', 'statistics']
        ],
        'SMP' => [
            'name' => 'SMP / MTs (Pendidikan Menengah Pertama)',
            'active' => ['downloads', 'agendas', 'ppdb', 'extracurriculars', 'prestasi', 'news', 'galleries', 'video_profile', 'guestbook', 'statistics']
        ],
        'SD' => [
            'name' => 'SD / MI (Pendidikan Dasar)',
            'active' => ['downloads', 'agendas', 'ppdb', 'extracurriculars', 'prestasi', 'news', 'galleries', 'video_profile', 'guestbook', 'statistics']
        ],
        'ALL_ON' => [
            'name' => 'Semua Modul Aktif',
            'active' => ['bkk', 'partners', 'majors', 'kelulusan', 'downloads', 'agendas', 'ppdb', 'extracurriculars', 'prestasi', 'testimonials', 'news', 'galleries', 'video_profile', 'guestbook', 'statistics']
        ]
    ];

    if (isset($presets[$preset])) {
        $activeKeys = $presets[$preset]['active'];
        
        // Turn all to 0 first
        $db->exec("UPDATE site_modules SET is_active = 0");
        
        // Activate selected keys
        if (!empty($activeKeys)) {
            $placeholders = implode(',', array_fill(0, count($activeKeys), '?'));
            $stmt = $db->prepare("UPDATE site_modules SET is_active = 1 WHERE module_key IN ($placeholders)");
            $stmt->execute($activeKeys);
        }

        $presetName = $presets[$preset]['name'];
        logActivity('APPLY_MODULE_PRESET', 'site_modules', "Menerapkan preset template modul: $presetName");
        setFlash('success', "Preset template \"$presetName\" berhasil diterapkan.");
    } else {
        setFlash('danger', 'Preset tidak valid.');
    }
}

redirect(BASE_URL . '/admin/modules/index.php');
