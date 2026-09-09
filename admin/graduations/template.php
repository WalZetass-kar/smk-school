<?php
/**
 * admin/graduations/template.php
 * Download file template import data kelulusan siswa (CSV & Excel)
 * Dilengkapi UTF-8 BOM, format Bahasa Indonesia rapi, dan anti-nyatu di Microsoft Excel.
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();

$format = strtolower(trim($_GET['format'] ?? 'csv'));
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

// 1. FORMAT EXCEL (.XLS)
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
        body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; color: #1e293b; }
        .title { font-size: 14pt; font-weight: bold; color: #0f2c59; }
        .sub { font-size: 10pt; color: #64748b; margin-bottom: 10px; }
        .notice { font-size: 9.5pt; color: #b45309; background-color: #fef3c7; padding: 6px 10px; border: 1px solid #fde68a; }
        .table-data { border-collapse: collapse; width: 100%; margin-top: 10px; }
        .table-data th { background-color: #0f2c59; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #cbd5e1; padding: 8px 12px; }
        .table-data td { border: 1px solid #cbd5e1; padding: 6px 10px; }
        .text-center { text-align: center; }
        .text-format { mso-number-format:"\@"; }
        .num-format { mso-number-format:"0\.00"; text-align: right; }
        .badge-lulus { color: #166534; font-weight: bold; }
        .badge-ditunda { color: #854d0e; font-weight: bold; }
      </style>
    </head>
    <body>
      <div class="title">TEMPLATE IMPORT DATA KELULUSAN SISWA (SKL ONLINE)</div>
      <div class="sub"><?= htmlspecialchars($schoolName) ?> &mdash; Silakan isi data siswa mulai dari baris tabel di bawah ini</div>
      <div class="notice">
        <strong>PANDUAN PENGISIAN:</strong><br>
        1. Jangan mengubah susunan kolom di baris judul.<br>
        2. Kolom <strong>NISN</strong> dan <strong>Nomor Peserta Ujian</strong> wajib unik untuk setiap siswa.<br>
        3. Format <strong>Tanggal Lahir</strong>: <code>YYYY-MM-DD</code> (contoh: 2008-04-15).<br>
        4. Pilihan <strong>Status Kelulusan</strong>: <code>LULUS</code>, <code>TIDAK LULUS</code>, atau <code>DITUNDA</code>.<br>
        5. File ini dapat langsung diisi dan disimpan sebagai berkas CSV atau XLS untuk diunggah.
      </div>
      <br>
      <table class="table-data">
        <thead>
          <tr>
            <th>NISN</th>
            <th>Nomor Peserta Ujian</th>
            <th>Nama Lengkap Siswa</th>
            <th>Tempat Lahir</th>
            <th>Tanggal Lahir</th>
            <th><?= $level === 'SMA' ? 'Peminatan' : 'Jurusan' ?></th>
            <th>Nilai Rata-rata</th>
            <th>Status Kelulusan</th>
            <th>Keterangan Tambahan</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sampleData as $row): ?>
            <tr>
              <td class="text-format text-center"><?= htmlspecialchars($row['nisn']) ?></td>
              <td class="text-format text-center"><?= htmlspecialchars($row['exam_number']) ?></td>
              <td><?= htmlspecialchars($row['student_name']) ?></td>
              <td><?= htmlspecialchars($row['birth_place']) ?></td>
              <td class="text-format text-center"><?= htmlspecialchars($row['birth_date']) ?></td>
              <td><?= htmlspecialchars($row['major_name']) ?></td>
              <td class="num-format"><?= htmlspecialchars($row['average_score']) ?></td>
              <td class="text-center <?= $row['status'] === 'LULUS' ? 'badge-lulus' : 'badge-ditunda' ?>"><?= htmlspecialchars($row['status']) ?></td>
              <td><?= htmlspecialchars($row['remarks']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </body>
    </html>
    <?php
    exit;
}

// 2. FORMAT CSV STANDAR (.CSV) ANTI-NYATU DI EXCEL WINDOWS
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

$majorColName = ($level === 'SMA') ? 'Peminatan' : 'Jurusan';
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
