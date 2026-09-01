<?php
/**
 * pages/kelulusan-cetak.php
 * Halaman Cetak Surat Keterangan Lulus (SKL) Resmi
 */
require_once __DIR__ . '/../config/config.php';
$db = getDB();

$nisn = trim($_GET['nisn'] ?? '');
if ($nisn === '') {
    die('Parameter NISN tidak valid.');
}

$stmt = $db->prepare("SELECT * FROM graduations WHERE nisn = ? LIMIT 1");
$stmt->execute([$nisn]);
$student = $stmt->fetch();

if (!$student || $student['status'] !== 'lulus') {
    die('Data kelulusan siswa tidak ditemukan atau belum berstatus Lulus.');
}

$settings = $db->query("SELECT * FROM graduation_settings WHERE id = 1")->fetch() ?: [
    'academic_year'     => '2026/2027',
    'signature_city'    => 'Pekanbaru',
    'signature_date'    => date('Y-m-d'),
];

$profile = getSchoolProfile();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>SKL_<?= e($student['nisn']) ?>_<?= e(str_replace(' ', '_', $student['student_name'])) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Times+New+Roman&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Times New Roman', Times, serif;
      background-color: #f4f6f9;
      color: #000000;
      padding: 20px;
    }
    .print-sheet {
      width: 210mm;
      min-height: 297mm;
      margin: 0 auto;
      background: #ffffff;
      padding: 20mm 20mm 20mm 20mm;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      position: relative;
    }
    .kop-surat {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 3px double #000;
      padding-bottom: 12px;
      margin-bottom: 20px;
    }
    .kop-logo {
      width: 80px;
      height: 80px;
      object-fit: contain;
    }
    .kop-text {
      text-align: center;
      flex-grow: 1;
      padding: 0 15px;
    }
    .kop-text h3 {
      font-size: 13pt;
      font-weight: normal;
      letter-spacing: 0.05em;
    }
    .kop-text h2 {
      font-size: 15pt;
      font-weight: bold;
      text-transform: uppercase;
      margin: 2px 0;
    }
    .kop-text p {
      font-size: 9.5pt;
      font-family: Arial, sans-serif;
      margin: 1px 0;
    }
    .judul-surat {
      text-align: center;
      margin-bottom: 20px;
    }
    .judul-surat h4 {
      font-size: 13pt;
      font-weight: bold;
      text-decoration: underline;
      text-transform: uppercase;
    }
    .judul-surat p {
      font-size: 10pt;
      margin-top: 2px;
    }
    .isi-surat {
      font-size: 11pt;
      line-height: 1.6;
      text-align: justify;
    }
    .tabel-data {
      width: 100%;
      margin: 15px 0;
      border-collapse: collapse;
    }
    .tabel-data td {
      padding: 4px 6px;
      vertical-align: top;
      font-size: 11pt;
    }
    .box-status {
      text-align: center;
      margin: 20px 0;
      padding: 12px;
      border: 2px solid #000;
      font-size: 14pt;
      font-weight: bold;
      letter-spacing: 0.1em;
    }
    .ttd-wrapper {
      display: flex;
      justify-content: space-between;
      margin-top: 30px;
      page-break-inside: avoid;
    }
    .qr-box {
      width: 120px;
      height: 120px;
      border: 1px dashed #666;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      font-family: Arial, sans-serif;
      font-size: 7.5pt;
      text-align: center;
      padding: 5px;
      background: #fafafa;
    }
    .ttd-box {
      text-align: center;
      width: 250px;
      font-size: 11pt;
    }
    .no-print-bar {
      width: 210mm;
      margin: 0 auto 15px auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-family: 'Inter', sans-serif;
    }
    .btn-print {
      background: #091C3A;
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      font-size: 14px;
    }
    .btn-back {
      background: #6c757d;
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      text-decoration: none;
      font-size: 14px;
      font-weight: 600;
    }
    @media print {
      body { background: #fff; padding: 0; }
      .no-print-bar { display: none !important; }
      .print-sheet { width: 100%; min-height: auto; box-shadow: none; padding: 0; margin: 0; }
    }
  </style>
</head>
<body>

<div class="no-print-bar">
  <a href="<?= e(BASE_URL) ?>/kelulusan" class="btn-back">&larr; Kembali ke Pencarian</a>
  <button onclick="window.print()" class="btn-print">&#128438; Cetak Dokumen SKL (PDF / Printer)</button>
</div>

<div class="print-sheet">
  <!-- KOP SURAT -->
  <div class="kop-surat">
    <img src="<?= e(BASE_URL . '/' . ($profile['logo'] ?? 'assets/images/logo.png')) ?>" class="kop-logo" alt="Logo">
    <div class="kop-text">
      <h3>PEMERINTAH PROVINSI</h3>
      <h3>DINAS PENDIDIKAN DAN KEBUDAYAAN</h3>
      <h2><?= e($profile['school_name'] ?? 'SMK NEGERI CONTOH INDONESIA') ?></h2>
      <p><?= e($profile['address'] ?? 'Jl. Pendidikan No. 45') ?> | Telp: <?= e($profile['phone'] ?? '-') ?></p>
      <p>NPSN: <?= e($profile['npsn'] ?? '-') ?> | Website: <?= e($profile['website'] ?? '-') ?> | Email: <?= e($profile['email'] ?? '-') ?></p>
    </div>
  </div>

  <!-- JUDUL SURAT -->
  <div class="judul-surat">
    <h4>SURAT KETERANGAN LULUS</h4>
    <p>Nomor: 421.5 / SMK-SKL / <?= date('Y') ?> / <?= e($student['nisn']) ?></p>
  </div>

  <!-- ISI SURAT -->
  <div class="isi-surat">
    <p>
      Kepala <?= e($profile['school_name'] ?? 'SMK Negeri Contoh Indonesia') ?>, dengan ini menerangkan bahwa:
    </p>

    <table class="tabel-data">
      <tr>
        <td style="width: 32%;">Nama Lengkap</td>
        <td style="width: 3%;">:</td>
        <td style="width: 65%;"><strong><?= e($student['student_name']) ?></strong></td>
      </tr>
      <tr>
        <td>Nomor Induk Siswa Nasional (NISN)</td>
        <td>:</td>
        <td><?= e($student['nisn']) ?></td>
      </tr>
      <tr>
        <td>Nomor Peserta Ujian</td>
        <td>:</td>
        <td><?= e($student['exam_number']) ?></td>
      </tr>
      <tr>
        <td>Tempat, Tanggal Lahir</td>
        <td>:</td>
        <td><?= e($student['birth_place'] ?? '-') ?>, <?= !empty($student['birth_date']) ? date('d F Y', strtotime($student['birth_date'])) : '-' ?></td>
      </tr>
      <tr>
        <td>Program Keahlian / Jurusan</td>
        <td>:</td>
        <td><strong><?= e($student['major_name']) ?></strong></td>
      </tr>
      <tr>
        <td>Nilai Rata-rata Ujian Sekolah</td>
        <td>:</td>
        <td><strong><?= e(number_format((float)$student['average_score'], 2)) ?></strong></td>
      </tr>
    </table>

    <p>
      Berdasarkan kriteria kelulusan satuan pendidikan yang telah ditetapkan dan hasil rapat pleno dewan guru, maka yang bersangkutan dinyatakan:
    </p>

    <div class="box-status">
      L U L U S
    </div>

    <p>
      Surat Keterangan Lulus ini bersifat resmi dan berlaku sebagai dokumen pengganti Ijazah sementara sampai dengan diterbitkannya Ijazah Asli.
    </p>
  </div>

  <!-- TANDA TANGAN -->
  <div class="ttd-wrapper">
    <div class="qr-box">
      <div style="font-size:18px; margin-bottom:4px;">&#10003;</div>
      <strong>DOKUMEN SAH</strong>
      <span>Terverifikasi Sistem Digital Sekolah</span>
      <span style="font-size:6pt; margin-top:4px;">Ref: <?= md5($student['nisn'] . $student['exam_number']) ?></span>
    </div>

    <div class="ttd-box">
      <p><?= e($settings['signature_city'] ?? 'Pekanbaru') ?>, <?= date('d F Y', strtotime($settings['signature_date'] ?? date('Y-m-d'))) ?></p>
      <p style="margin-bottom: 60px;">Kepala Sekolah,</p>
      <p><strong><u>Drs. H. Rahmat Hidayat, M.Pd.</u></strong></p>
      <p>NIP. 19750815 200003 1 002</p>
    </div>
  </div>
</div>

</body>
</html>
