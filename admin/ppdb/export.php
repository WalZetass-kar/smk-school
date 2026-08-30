<?php
/**
 * admin/ppdb/export.php
 * Export Data Pendaftar PPDB ke CSV
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();

$sql = "
    SELECT 
        r.registration_no,
        r.full_name,
        r.nisn,
        r.nik,
        CASE WHEN r.gender = 'L' THEN 'Laki-laki' ELSE 'Perempuan' END AS jenis_kelamin,
        r.birth_place,
        r.birth_date,
        r.religion,
        r.address,
        r.phone,
        r.email,
        r.school_origin,
        r.graduation_year,
        r.average_score,
        r.parent_name,
        r.parent_phone,
        r.parent_job,
        m1.name AS pilihan_jurusan_1,
        m2.name AS pilihan_jurusan_2,
        r.status,
        r.notes,
        r.created_at
    FROM ppdb_registrations r
    LEFT JOIN majors m1 ON m1.id = r.major_choice_1
    LEFT JOIN majors m2 ON m2.id = r.major_choice_2
    ORDER BY r.id DESC
";

$stmt = $db->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = 'Data_Pendaftar_PPDB_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// Add UTF-8 BOM for Microsoft Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header columns
fputcsv($output, [
    'No. Registrasi',
    'Nama Lengkap',
    'NISN',
    'NIK',
    'Jenis Kelamin',
    'Tempat Lahir',
    'Tanggal Lahir',
    'Agama',
    'Alamat',
    'No. HP/WA',
    'Email',
    'Asal Sekolah',
    'Tahun Lulus',
    'Nilai Rata-rata',
    'Nama Orang Tua',
    'No. HP Orang Tua',
    'Pekerjaan Orang Tua',
    'Pilihan Jurusan 1',
    'Pilihan Jurusan 2',
    'Status',
    'Catatan',
    'Waktu Daftar'
]);

foreach ($rows as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit;
