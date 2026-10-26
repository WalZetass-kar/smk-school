<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireSuperAdmin();

$db = getDB();

$moduleFilter = trim($_GET['module'] ?? '');
$search       = trim($_GET['q'] ?? '');

$where = [];
$params = [];

if ($moduleFilter !== '') {
    $where[] = 'module = ?';
    $params[] = $moduleFilter;
}

if ($search !== '') {
    $where[] = '(user_name LIKE ? OR action LIKE ? OR detail LIKE ? OR ip_address LIKE ?)';
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$stmt = $db->prepare("SELECT * FROM activity_logs $whereSql ORDER BY id DESC");
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

logActivity('EXPORT_ACTIVITY_LOGS', 'audit', 'Super Admin mengekspor ' . count($logs) . ' riwayat log aktivitas ke CSV.');

$filename = 'audit_logs_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
// Add UTF-8 BOM for Excel
fputs($output, "\xEF\xBB\xBF");

fputcsv($output, ['ID', 'Waktu (WIB)', 'User ID', 'Nama Pengguna', 'Hak Akses (Role)', 'Modul', 'Aksi', 'Detail Aktivitas', 'IP Address', 'User Agent']);

foreach ($logs as $l) {
    fputcsv($output, [
        $l['id'],
        $l['created_at'],
        sanitizeCsvCell($l['user_id'] ?: '-'),
        sanitizeCsvCell($l['user_name'] ?: 'Guest/Public'),
        sanitizeCsvCell($l['user_role'] ?: '-'),
        sanitizeCsvCell($l['module']),
        sanitizeCsvCell($l['action']),
        sanitizeCsvCell($l['detail']),
        sanitizeCsvCell($l['ip_address']),
        sanitizeCsvCell($l['user_agent']),
    ]);
}

fclose($output);
exit;
