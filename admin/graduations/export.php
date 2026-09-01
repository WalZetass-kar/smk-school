<?php
/**
 * admin/graduations/export.php
 * Ekspor Data Siswa Kelulusan ke CSV
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();

$db = getDB();
$rows = $db->query("SELECT nisn, exam_number, student_name, birth_place, birth_date, major_name, average_score, status, remarks FROM graduations ORDER BY student_name ASC")->fetchAll(PDO::FETCH_ASSOC);

$filename = 'rekap_kelulusan_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fputcsv($output, ['nisn', 'exam_number', 'student_name', 'birth_place', 'birth_date', 'major_name', 'average_score', 'status', 'remarks']);

foreach ($rows as $row) {
    fputcsv($output, $row);
}
fclose($output);
exit;
