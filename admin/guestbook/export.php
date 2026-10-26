<?php
/**
 * admin/guestbook/export.php
 * Ekspor Data Buku Tamu Digital ke Format CSV & Microsoft Excel
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();

$db = getDB();
$profile = getSchoolProfile();
$schoolName = $profile['school_name'] ?? 'Sekolah';

$q = trim($_GET['q'] ?? '');
$purposeFilter = trim($_GET['purpose'] ?? 'all');

$where = ["1=1"];
$params = [];

if ($q !== '') {
    $where[] = "(name LIKE ? OR institution LIKE ? OR phone LIKE ? OR person_to_meet LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

if ($purposeFilter !== 'all') {
    $where[] = "purpose = ?";
    $params[] = $purposeFilter;
}

$whereSql = implode(' AND ', $where);
$stmt = $db->prepare("SELECT * FROM guestbooks WHERE $whereSql ORDER BY created_at DESC");
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

logActivity('EXPORT_GUESTBOOK', 'guestbook', 'Mengekspor ' . count($rows) . ' catatan buku tamu ke file Excel/CSV.');

$filename = 'Rekap_Buku_Tamu_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

$output = fopen('php://output', 'w');

// UTF-8 BOM untuk Excel
fwrite($output, "\xEF\xBB\xBF");
fwrite($output, "sep=;\r\n");

// Header Kolom Resmi
fputcsv($output, [
    'No',
    'Tanggal Kunjungan',
    'Jam (WIB)',
    'Nama Lengkap Tamu',
    'Instansi / Lembaga',
    'Nomor WhatsApp / HP',
    'Email',
    'Kategori Keperluan',
    'Pihak yang Dituju',
    'Catatan / Pesan Kunjungan'
], ';');

$no = 1;
foreach ($rows as $row) {
    fputcsv($output, [
        $no++,
        date('d/m/Y', strtotime($row['created_at'])),
        date('H:i', strtotime($row['created_at'])),
        sanitizeCsvCell($row['name']),
        sanitizeCsvCell($row['institution'] ?: '-'),
        sanitizeCsvCell($row['phone']),
        sanitizeCsvCell($row['email'] ?: '-'),
        sanitizeCsvCell($row['purpose']),
        sanitizeCsvCell($row['person_to_meet']),
        sanitizeCsvCell($row['notes'] ?: '-')
    ], ';');
}

fclose($output);
exit;
