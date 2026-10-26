<?php
/**
 * pages/kelulusan-cetak.php
 * Halaman Cetak Surat Keterangan Lulus (SKL) Resmi
 */
require_once __DIR__ . '/../config/config.php';
$db = getDB();

$nisn = trim($_GET['nisn'] ?? '');
if ($nisn === '') {
    setFlash('error', 'Parameter NISN tidak valid.');
    redirect(BASE_URL . '/kelulusan');
}

$ip = getClientIP();
$rateKey = 'skl_print_' . md5($ip);
$remainingLockout = 0;
if (!checkRateLimit($rateKey, 20, 60, $remainingLockout)) {
    setFlash('error', "Terlalu banyak permintaan cetak SKL. Silakan tunggu {$remainingLockout} detik.");
    redirect(BASE_URL . '/kelulusan');
}
recordRateLimitAttempt($rateKey, 60);

$stmt = $db->prepare("SELECT * FROM graduations WHERE nisn = ? LIMIT 1");
$stmt->execute([$nisn]);
$student = $stmt->fetch();

if (!$student) {
    setFlash('error', 'Data siswa tidak ditemukan.');
    redirect(BASE_URL . '/kelulusan');
}

if ($student['status'] !== 'lulus') {
    setFlash('error', 'Dokumen Surat Keterangan Lulus (SKL) hanya dapat dicetak untuk siswa yang telah berstatus LULUS.');
    redirect(BASE_URL . '/kelulusan');
}

$settings = $db->query("SELECT * FROM graduation_settings WHERE id = 1")->fetch() ?: [
    'academic_year'     => '2026/2027',
    'signature_city'    => 'Pekanbaru',
    'signature_date'    => date('Y-m-d'),
    'signature_name'    => null,
    'signature_nip'     => null,
];

// Fallback dynamically to Sambutan Kepala Sekolah if signature_name is not overridden
$principal = $db->query("SELECT principal_name FROM principal_message ORDER BY id LIMIT 1")->fetch();
$principalName = !empty($settings['signature_name']) 
    ? $settings['signature_name'] 
    : (!empty($principal['principal_name']) ? $principal['principal_name'] : 'Drs. H. Rahmat Hidayat, M.Pd.');
$principalNip = !empty($settings['signature_nip']) ? $settings['signature_nip'] : '19750815 200003 1 002';

$profile = getSchoolProfile();

// URL Verifikasi Dokumen Asli untuk QR Code
$verificationUrl = rtrim(BASE_URL, '/') . '/kelulusan?nisn=' . urlencode($student['nisn']);
$qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=0&data=' . urlencode($verificationUrl);
$docRef = strtoupper(substr(hash('sha256', $student['nisn'] . $student['exam_number'] . 'SMK_SKL_SECRET'), 0, 10));
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
      align-items: flex-end;
      margin-top: 25px;
      page-break-inside: avoid;
    }
    .auth-boxes {
      display: flex;
      align-items: flex-end;
      gap: 16px;
    }
    .pasfoto-box {
      width: 30mm;
      height: 40mm;
      border: 1.5px dashed #555;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      font-family: Arial, sans-serif;
      font-size: 8pt;
      color: #555;
      text-align: center;
      background: #fafafa;
      border-radius: 4px;
      padding: 6px;
      box-sizing: border-box;
    }
    .pasfoto-box .pasfoto-title {
      font-weight: bold;
      font-size: 9pt;
      color: #222;
      margin-bottom: 3px;
    }
    .pasfoto-box .pasfoto-sub {
      font-size: 7pt;
      color: #666;
      line-height: 1.35;
    }
    .qr-verification-box {
      width: 36mm;
      min-height: 40mm;
      border: 1px solid #ccc;
      border-radius: 5px;
      background: #ffffff;
      padding: 6px 4px 4px 4px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      font-family: Arial, sans-serif;
      box-sizing: border-box;
    }
    .qr-verification-box img {
      width: 76px;
      height: 76px;
      display: block;
      margin: 0 auto;
    }
    .qr-label {
      font-size: 6.8pt;
      font-weight: bold;
      color: #091C3A;
      margin-top: 4px;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }
    .qr-desc {
      font-size: 5.6pt;
      color: #555;
      line-height: 1.25;
      margin-top: 1px;
    }
    .qr-ref {
      font-size: 5.5pt;
      color: #777;
      font-family: monospace;
      margin-top: 2px;
    }
    .qr-fallback-offline {
      display: none;
      font-size: 6.5pt;
      color: #555;
      padding: 10px 4px;
    }
    .ttd-box {
      text-align: center;
      width: 250px;
      font-size: 11pt;
    }
    .no-print-bar {
      width: 210mm;
      max-width: 100%;
      margin: 0 auto 15px auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      font-family: 'Inter', sans-serif;
      background: #ffffff;
      padding: 12px 18px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .no-print-left {
      display: flex;
      align-items: center;
      gap: 15px;
      flex-wrap: wrap;
    }
    .paper-selector-wrap {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      font-weight: 500;
      color: #333;
    }
    .paper-select {
      padding: 6px 12px;
      border: 1px solid #ced4da;
      border-radius: 6px;
      font-size: 13px;
      font-family: inherit;
      background-color: #f8f9fa;
      color: #212529;
      cursor: pointer;
      outline: none;
      transition: border-color 0.15s ease-in-out;
    }
    .paper-select:focus {
      border-color: #091C3A;
      background-color: #fff;
    }
    .btn-print {
      background: #091C3A;
      color: #fff;
      border: none;
      padding: 9px 18px;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      font-size: 13.5px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: background-color 0.2s ease;
    }
    .btn-print:hover {
      background: #183E7A;
    }
    .btn-back {
      background: #6c757d;
      color: #fff;
      border: none;
      padding: 9px 16px;
      border-radius: 6px;
      text-decoration: none;
      font-size: 13.5px;
      font-weight: 500;
      transition: background-color 0.2s ease;
    }
    .btn-back:hover {
      background: #5a6268;
    }
    .paper-hint {
      font-size: 11px;
      color: #6c757d;
      display: inline-block;
    }
    @media screen and (max-width: 820px) {
      body { padding: 10px; }
      .no-print-bar {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
      }
      .no-print-left {
        flex-direction: column;
        align-items: stretch;
      }
      .print-sheet {
        width: 100% !important;
        min-height: auto !important;
        padding: 15mm 10mm !important;
      }
    }
    @media print {
      html, body {
        background: #fff !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        height: auto !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
      .no-print-bar { display: none !important; }
      .print-sheet {
        width: 100% !important;
        max-width: 100% !important;
        min-height: auto !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
        page-break-after: avoid !important;
        page-break-inside: avoid !important;
      }
    }
  </style>
  <style id="dynamicPageSizeStyle">
    @page {
      size: A4 portrait;
      margin: 10mm 15mm 10mm 15mm;
    }
  </style>
</head>
<body>

<div class="no-print-bar">
  <div class="no-print-left">
    <a href="<?= e(BASE_URL) ?>/kelulusan" class="btn-back">&larr; Kembali</a>
    <div class="paper-selector-wrap">
      <label for="paperSizeSelect">Ukuran Kertas:</label>
      <select id="paperSizeSelect" class="paper-select" onchange="changePaperSize(this.value)">
        <option value="A4" selected>A4 (210 × 297 mm) — Standar</option>
        <option value="F4">F4 / Folio (215 × 330 mm) — Standar Sekolah</option>
        <option value="Letter">Letter / Kuarto (216 × 279 mm)</option>
        <option value="Legal">Legal (216 × 356 mm)</option>
        <option value="auto">Auto / Ukuran Lainnya (Pas 1 Halaman)</option>
      </select>
      <span class="paper-hint">Otomatis pas 1 halaman</span>
    </div>
  </div>
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

  <!-- TANDA TANGAN & PENGESAHAN -->
  <div class="ttd-wrapper">
    <div class="auth-boxes">
      <!-- 1. KOTAK PASFOTO 3x4 CM -->
      <div class="pasfoto-box">
        <div style="font-size: 16px; margin-bottom: 2px; opacity: 0.7;">👤</div>
        <div class="pasfoto-title">Pasfoto</div>
        <div class="pasfoto-title" style="margin-top:-3px;">3 × 4 cm</div>
        <div class="pasfoto-sub">Cap 3 Jari Kiri<br>Menimpa Foto</div>
      </div>

      <!-- 2. KOTAK QR CODE VERIFIKASI ASLI -->
      <div class="qr-verification-box">
        <img src="<?= e($qrCodeUrl) ?>" alt="QR Code Verifikasi" loading="eager" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
        <div class="qr-fallback-offline">
          <div style="font-size: 16px;">🛡️</div>
          <strong>VERIFIKASI SAH</strong>
        </div>
        <div class="qr-label">Verifikasi Digital</div>
        <div class="qr-desc">Pindai QR untuk validasi keaslian dokumen</div>
        <div class="qr-ref">REF: <?= e($docRef) ?></div>
      </div>
    </div>

    <!-- 3. TANDA TANGAN KEPALA SEKOLAH -->
    <div class="ttd-box">
      <p><?= e($settings['signature_city'] ?? 'Pekanbaru') ?>, <?= date('d F Y', strtotime($settings['signature_date'] ?? date('Y-m-d'))) ?></p>
      <p style="margin-bottom: 55px;">Kepala Sekolah,</p>
      <p><strong><u><?= e($principalName) ?></u></strong></p>
      <p>NIP. <?= e($principalNip) ?></p>
    </div>
  </div>
</div>

<script>
function changePaperSize(size) {
  const sheet = document.querySelector('.print-sheet');
  const styleEl = document.getElementById('dynamicPageSizeStyle');
  if (!sheet || !styleEl) return;

  let pageCss = '';
  switch(size) {
    case 'F4':
      sheet.style.width = '215mm';
      sheet.style.minHeight = '330mm';
      pageCss = '@page { size: 215mm 330mm; margin: 12mm 15mm 12mm 15mm; }';
      break;
    case 'Letter':
      sheet.style.width = '215.9mm';
      sheet.style.minHeight = '279.4mm';
      pageCss = '@page { size: letter portrait; margin: 8mm 12mm 8mm 12mm; }';
      break;
    case 'Legal':
      sheet.style.width = '215.9mm';
      sheet.style.minHeight = '355.6mm';
      pageCss = '@page { size: legal portrait; margin: 12mm 15mm 12mm 15mm; }';
      break;
    case 'auto':
      sheet.style.width = '100%';
      sheet.style.maxWidth = '215mm';
      sheet.style.minHeight = 'auto';
      pageCss = '@page { size: auto; margin: 10mm 15mm 10mm 15mm; }';
      break;
    case 'A4':
    default:
      sheet.style.width = '210mm';
      sheet.style.minHeight = '297mm';
      pageCss = '@page { size: A4 portrait; margin: 10mm 15mm 10mm 15mm; }';
      break;
  }
  styleEl.innerHTML = pageCss;
}
</script>
</body>
</html>
