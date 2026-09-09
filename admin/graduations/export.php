<?php
/**
 * admin/graduations/export.php
 * Ekspor Data Siswa Kelulusan ke Format CSV & Microsoft Excel
 * Dilengkapi UTF-8 BOM, pemisah titik-koma (;), dan header resmi Bahasa Indonesia (anti-nyatu di Excel).
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();

$db = getDB();
$format = strtolower(trim($_GET['format'] ?? 'csv'));
$level  = getSchoolLevel();
$profile = getSchoolProfile();
$schoolName = $profile['school_name'] ?? 'Sekolah';

$rows = $db->query("
    SELECT nisn, exam_number, student_name, birth_place, birth_date, major_name, average_score, status, remarks 
    FROM graduations 
    ORDER BY student_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$majorLabel = ($level === 'SMA') ? 'Peminatan' : 'Jurusan';

// ==========================================
// 1. FORMAT EXCEL (.XLS)
// ==========================================
if ($format === 'excel' || $format === 'xls') {
    $filename = 'Rekap_Kelulusan_Siswa_' . date('Ymd_His') . '.xls';
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    ?>
    <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
      <style>
        body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; color: #1e293b; }
        .title { font-size: 14pt; font-weight: bold; color: #0f2c59; }
        .sub { font-size: 10pt; color: #64748b; margin-bottom: 8px; }
        .meta { font-size: 9.5pt; color: #475569; margin-bottom: 12px; }
        .table-data { border-collapse: collapse; width: 100%; border: 1.5pt solid #000000; }
        .table-data th { background-color: #0f2c59; color: #ffffff; font-weight: bold; text-align: center; border: 1.5pt solid #000000; padding: 8px 12px; }
        .table-data td { border: 1pt solid #000000; padding: 6px 10px; }
        .text-center { text-align: center; }
        .text-format { mso-number-format:"\@"; }
        .num-format { mso-number-format:"0\.00"; text-align: right; }
        .status-lulus { color: #166534; font-weight: bold; }
        .status-tidak { color: #991b1b; font-weight: bold; }
        .status-ditunda { color: #854d0e; font-weight: bold; }
      </style>
    </head>
    <body>
      <div class="title">REKAPITULASI DATA KELULUSAN SISWA (SKL ONLINE)</div>
      <div class="sub"><?= htmlspecialchars($schoolName) ?></div>
      <div class="meta">Waktu Ekspor: <?= date('d/m/Y H:i:s') ?> WIB | Total Data: <?= count($rows) ?> Siswa</div>
      <br>
      <table border="1" cellpadding="6" cellspacing="0" class="table-data" style="border-collapse: collapse; border: 1.5pt solid #000000; width: 100%;">
        <thead>
          <tr>
            <th style="width: 45px;">No</th>
            <th>NISN</th>
            <th>Nomor Peserta Ujian</th>
            <th>Nama Lengkap Siswa</th>
            <th>Tempat Lahir</th>
            <th>Tanggal Lahir</th>
            <th><?= $majorLabel ?></th>
            <th>Nilai Rata-rata</th>
            <th>Status Kelulusan</th>
            <th>Keterangan</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($rows): ?>
            <?php $no = 1; foreach ($rows as $r): 
              $statusUpper = match($r['status']) {
                'lulus' => 'LULUS',
                'tidak_lulus' => 'TIDAK LULUS',
                'ditunda' => 'DITUNDA',
                default => strtoupper($r['status'])
              };
              $statusClass = match($r['status']) {
                'lulus' => 'status-lulus',
                'tidak_lulus' => 'status-tidak',
                'ditunda' => 'status-ditunda',
                default => ''
              };
            ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td class="text-format text-center"><?= htmlspecialchars($r['nisn']) ?></td>
                <td class="text-format text-center"><?= htmlspecialchars($r['exam_number']) ?></td>
                <td><?= htmlspecialchars($r['student_name']) ?></td>
                <td><?= htmlspecialchars($r['birth_place']) ?></td>
                <td class="text-format text-center"><?= htmlspecialchars($r['birth_date']) ?></td>
                <td><?= htmlspecialchars($r['major_name']) ?></td>
                <td class="num-format"><?= number_format((float)$r['average_score'], 2, '.', '') ?></td>
                <td class="text-center <?= $statusClass ?>"><?= $statusUpper ?></td>
                <td><?= htmlspecialchars($r['remarks']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="10" class="text-center" style="padding: 20px; color: #64748b;">Belum ada data siswa kelulusan.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </body>
    </html>
    <?php
    exit;
}

// ==========================================
// 2. FORMAT CSV STANDAR (.CSV) ANTI-NYATU
// ==========================================
$filename = 'rekap_kelulusan_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

$output = fopen('php://output', 'w');

// UTF-8 BOM untuk Excel
fwrite($output, "\xEF\xBB\xBF");

// sep=; agar Excel otomatis memisahkan kolom tanpa nyatu
fwrite($output, "sep=;\r\n");

// Header Kolom Resmi Bahasa Indonesia
fputcsv($output, [
    'NISN',
    'Nomor Peserta Ujian',
    'Nama Lengkap Siswa',
    'Tempat Lahir',
    'Tanggal Lahir',
    $majorLabel,
    'Nilai Rata-rata',
    'Status Kelulusan',
    'Keterangan'
], ';');

foreach ($rows as $row) {
    $statusText = match($row['status']) {
        'lulus' => 'LULUS',
        'tidak_lulus' => 'TIDAK LULUS',
        'ditunda' => 'DITUNDA',
        default => strtoupper($row['status'])
    };

    fputcsv($output, [
        $row['nisn'],
        $row['exam_number'],
        $row['student_name'],
        $row['birth_place'],
        $row['birth_date'],
        $row['major_name'],
        number_format((float)$row['average_score'], 2, '.', ''),
        $statusText,
        $row['remarks']
    ], ';');
}

fclose($output);
exit;
