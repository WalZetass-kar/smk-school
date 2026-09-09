<?php
/**
 * admin/graduations/template.php
 * Download file template import data kelulusan siswa (CSV & Excel)
 * Dilengkapi garis border tabel nyata (solid gridline), UTF-8 BOM, dan format Bahasa Indonesia resmi.
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();

// Default ke excel jika format tidak ditentukan agar langsung ada garis border rapi di Excel
$format = strtolower(trim($_GET['format'] ?? 'excel'));
$profile = getSchoolProfile();
$schoolName = $profile['school_name'] ?? 'Sekolah';
$level = getSchoolLevel();

$sampleData = [
    [
        'nisn'          => '0061234567',
        'exam_number'   => 'UJ-2026-001',
        'student_name'  => 'Ahmad Fauzi Pratama',
        'birth_place'   => 'Jakarta',
        'birth_date'    => '2008-04-15',
        'major_name'    => $level === 'SMA' ? 'MIPA (Matematika & IPA)' : ($level === 'SMK' ? 'Teknik Komputer & Jaringan' : 'Reguler / Umum'),
        'average_score' => '88.50',
        'status'        => 'LULUS',
        'remarks'       => 'Lulus dengan predikat Sangat Baik'
    ],
    [
        'nisn'          => '0061234568',
        'exam_number'   => 'UJ-2026-002',
        'student_name'  => 'Siti Nurhaliza',
        'birth_place'   => 'Bandung',
        'birth_date'    => '2008-06-20',
        'major_name'    => $level === 'SMA' ? 'IPS (Ilmu Pengetahuan Sosial)' : ($level === 'SMK' ? 'Akuntansi & Keuangan' : 'Reguler / Umum'),
        'average_score' => '91.20',
        'status'        => 'LULUS',
        'remarks'       => 'Lulus dengan predikat Pujian (Cumlaude)'
    ],
    [
        'nisn'          => '0061234569',
        'exam_number'   => 'UJ-2026-003',
        'student_name'  => 'Budi Santoso',
        'birth_place'   => 'Surabaya',
        'birth_date'    => '2007-11-05',
        'major_name'    => $level === 'SMA' ? 'Bahasa & Budaya' : ($level === 'SMK' ? 'Desain Komunikasi Visual' : 'Reguler / Umum'),
        'average_score' => '82.00',
        'status'        => 'LULUS',
        'remarks'       => 'Memenuhi seluruh kriteria kelulusan'
    ],
    [
        'nisn'          => '0061234570',
        'exam_number'   => 'UJ-2026-004',
        'student_name'  => 'Rizky Ramadhan',
        'birth_place'   => 'Semarang',
        'birth_date'    => '2008-01-18',
        'major_name'    => $level === 'SMA' ? 'MIPA (Matematika & IPA)' : ($level === 'SMK' ? 'Teknik Kendaraan Ringan' : 'Reguler / Umum'),
        'average_score' => '78.40',
        'status'        => 'DITUNDA',
        'remarks'       => 'Menunggu verifikasi kelengkapan administrasi'
    ]
];

$majorColName = ($level === 'SMA') ? 'Peminatan' : 'Jurusan';

// ==========================================
// 1. FORMAT EXCEL (.XLS) � LANGSUNG BERGARIS / BORDER LENGKAP
// ==========================================
if ($format === 'excel' || $format === 'xls') {
    $filename = 'Template_Import_Kelulusan_' . date('Ymd') . '.xls';
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    ?>
    <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
      <style>
        body { font-family: Calibri, 'Segoe UI', Arial, sans-serif; font-size: 11pt; color: #0f172a; margin: 0; padding: 0; }
        table { border-collapse: collapse; width: 100%; border: 1.5pt solid #000000; }
        th { border: 1.5pt solid #000000; background-color: #0F2C59; color: #ffffff; font-weight: bold; text-align: center; vertical-align: middle; padding: 8px 10px; font-size: 11pt; }
        td { border: 1pt solid #000000; padding: 6px 8px; vertical-align: middle; font-size: 10.5pt; }
        .text-center { text-align: center; }
        .text-format { mso-number-format:"\@"; }
        .num-format { mso-number-format:"0\.00"; text-align: right; }
        .status-lulus { color: #15803d; font-weight: bold; text-align: center; }
        .status-ditunda { color: #b45309; font-weight: bold; text-align: center; }
        .status-tidak { color: #b91c1c; font-weight: bold; text-align: center; }
        .empty-row td { height: 26px; border: 1pt solid #000000; }
      </style>
    </head>
    <body>
      <table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; border: 1.5pt solid #000000; width: 100%;">
        <thead>
          <tr style="background-color: #0F2C59; color: #ffffff;">
            <th style="border: 1.5pt solid #000000; background-color: #0F2C59; color: #ffffff; text-align: center;">NISN</th>
            <th style="border: 1.5pt solid #000000; background-color: #0F2C59; color: #ffffff; text-align: center;">Nomor Peserta Ujian</th>
            <th style="border: 1.5pt solid #000000; background-color: #0F2C59; color: #ffffff; text-align: center;">Nama Lengkap Siswa</th>
            <th style="border: 1.5pt solid #000000; background-color: #0F2C59; color: #ffffff; text-align: center;">Tempat Lahir</th>
            <th style="border: 1.5pt solid #000000; background-color: #0F2C59; color: #ffffff; text-align: center;">Tanggal Lahir</th>
            <th style="border: 1.5pt solid #000000; background-color: #0F2C59; color: #ffffff; text-align: center;"><?= $majorColName ?></th>
            <th style="border: 1.5pt solid #000000; background-color: #0F2C59; color: #ffffff; text-align: center;">Nilai Rata-rata</th>
            <th style="border: 1.5pt solid #000000; background-color: #0F2C59; color: #ffffff; text-align: center;">Status Kelulusan</th>
            <th style="border: 1.5pt solid #000000; background-color: #0F2C59; color: #ffffff; text-align: center;">Keterangan Tambahan</th>
          </tr>
        </thead>
        <tbody>
          <!-- 4 Baris Contoh Data -->
          <?php foreach ($sampleData as $row): ?>
            <tr>
              <td class="text-format text-center" style="border: 1pt solid #000000; text-align: center;"><?= htmlspecialchars($row['nisn']) ?></td>
              <td class="text-format text-center" style="border: 1pt solid #000000; text-align: center;"><?= htmlspecialchars($row['exam_number']) ?></td>
              <td style="border: 1pt solid #000000;"><?= htmlspecialchars($row['student_name']) ?></td>
              <td style="border: 1pt solid #000000;"><?= htmlspecialchars($row['birth_place']) ?></td>
              <td class="text-format text-center" style="border: 1pt solid #000000; text-align: center;"><?= htmlspecialchars($row['birth_date']) ?></td>
              <td style="border: 1pt solid #000000;"><?= htmlspecialchars($row['major_name']) ?></td>
              <td class="num-format" style="border: 1pt solid #000000; text-align: right;"><?= htmlspecialchars($row['average_score']) ?></td>
              <td class="text-center <?= $row['status'] === 'LULUS' ? 'status-lulus' : 'status-ditunda' ?>" style="border: 1pt solid #000000; text-align: center; font-weight: bold;"><?= htmlspecialchars($row['status']) ?></td>
              <td style="border: 1pt solid #000000;"><?= htmlspecialchars($row['remarks']) ?></td>
            </tr>
          <?php endforeach; ?>

          <!-- 12 Baris Kosong yang Sudah Bergaris Border Siap Diisi -->
          <?php for ($i = 1; $i <= 12; $i++): ?>
            <tr class="empty-row">
              <td class="text-format text-center" style="border: 1pt solid #000000; height: 26px;"></td>
              <td class="text-format text-center" style="border: 1pt solid #000000;"></td>
              <td style="border: 1pt solid #000000;"></td>
              <td style="border: 1pt solid #000000;"></td>
              <td class="text-format text-center" style="border: 1pt solid #000000;"></td>
              <td style="border: 1pt solid #000000;"></td>
              <td class="num-format" style="border: 1pt solid #000000;"></td>
              <td class="text-center" style="border: 1pt solid #000000;"></td>
              <td style="border: 1pt solid #000000;"></td>
            </tr>
          <?php endfor; ?>
        </tbody>
      </table>
    </body>
    </html>
    <?php
    exit;
}

// ==========================================
// 2. FORMAT CSV STANDAR (.CSV) ANTI-NYATU DI EXCEL WINDOWS
// ==========================================
$filename = 'Template_Import_Kelulusan_' . date('Ymd') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');

$output = fopen('php://output', 'w');

// UTF-8 BOM agar Excel membaca huruf dan karakter khusus secara tepat
fwrite($output, "\xEF\xBB\xBF");

// sep=; memberi tahu Microsoft Excel agar otomatis memisahkan kolom ke kolom A, B, C, dst (tidak nyatu)
fwrite($output, "sep=;\r\n");

$headers = [
    'NISN',
    'Nomor Peserta Ujian',
    'Nama Lengkap Siswa',
    'Tempat Lahir',
    'Tanggal Lahir',
    $majorColName,
    'Nilai Rata-rata',
    'Status Kelulusan',
    'Keterangan'
];
fputcsv($output, $headers, ';');

foreach ($sampleData as $item) {
    fputcsv($output, [
        $item['nisn'],
        $item['exam_number'],
        $item['student_name'],
        $item['birth_place'],
        $item['birth_date'],
        $item['major_name'],
        $item['average_score'],
        $item['status'],
        $item['remarks']
    ], ';');
}

fclose($output);
exit;
