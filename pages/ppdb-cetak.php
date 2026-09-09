<?php
/**
 * pages/ppdb-cetak.php — /ppdb/cetak?no=...
 * Cetak Kartu Bukti Pendaftaran PPDB Resmi
 */
$db       = getDB();
$profile  = getSchoolProfile();
$settings = getPpdbSettings();
$regNo    = trim($_GET['no'] ?? $_GET['reg'] ?? '');

$stmt = $db->prepare("
    SELECT r.*, m1.name AS major1_name, m1.code AS major1_code, m2.name AS major2_name, m2.code AS major2_code
    FROM ppdb_registrations r
    LEFT JOIN majors m1 ON m1.id = r.major_choice_1
    LEFT JOIN majors m2 ON m2.id = r.major_choice_2
    WHERE r.registration_no = ?
");
$stmt->execute([$regNo]);
$data = $stmt->fetch();

if (!$data) {
    die('Data pendaftaran tidak ditemukan. Silakan periksa kembali nomor registrasi Anda.');
}

$verifyUrl = BASE_URL . '/ppdb/cek-status?reg=' . urlencode($data['registration_no']);
$qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=' . urlencode($verifyUrl);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kartu Bukti Pendaftaran PPDB — <?= e($data['registration_no']) ?> — <?= e($data['full_name']) ?></title>
  <link href="<?= e(ASSETS_URL . '/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
  <link href="<?= e(ASSETS_URL . '/vendor/bootstrap-icons/bootstrap-icons.css') ?>" rel="stylesheet">
  <style>
    body {
      background-color: #f1f5f9;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #0f172a;
      margin: 0;
      padding: 0;
    }
    .print-card {
      max-width: 820px;
      margin: 25px auto;
      background: #ffffff;
      padding: 35px 40px;
      border: 1px solid #cbd5e1;
      box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08);
      position: relative;
    }
    .kop-surat {
      border-bottom: 3px double #0f2744;
      padding-bottom: 12px;
      margin-bottom: 20px;
    }
    .kop-logo {
      width: 72px;
      height: 72px;
      object-fit: contain;
    }
    .table-detail th {
      width: 32%;
      background-color: #f8fafc;
      color: #334155;
      font-weight: 600;
      padding: 5.5px 10px;
      font-size: 13px;
      vertical-align: middle;
    }
    .table-detail td {
      padding: 5.5px 10px;
      font-size: 13px;
      vertical-align: middle;
    }
    .pas-foto-box {
      width: 110px;
      height: 145px;
      border: 2px dashed #94a3b8;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      background: #f8fafc;
      overflow: hidden;
    }
    .pas-foto-box img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .tear-slip {
      border-top: 2px dashed #94a3b8;
      margin-top: 25px;
      padding-top: 15px;
      position: relative;
    }
    .tear-slip-badge {
      position: absolute;
      top: -12px;
      left: 50%;
      transform: translateX(-50%);
      background: #ffffff;
      padding: 0 12px;
      font-size: 11px;
      color: #64748b;
      font-weight: 600;
    }
    @media (max-width: 575.98px) {
      .print-card {
        margin: 10px;
        padding: 18px 14px;
        border-radius: 8px;
      }
      .kop-logo {
        width: 48px;
        height: 48px;
      }
      .table-detail th {
        width: 40%;
        font-size: 12px;
        padding: 4px 8px;
      }
      .table-detail td {
        font-size: 12px;
        padding: 4px 8px;
      }
    }
    @media print {
      body {
        background: #fff;
        color: #000;
      }
      .no-print {
        display: none !important;
      }
      .print-card {
        border: none;
        box-shadow: none;
        margin: 0;
        padding: 0;
        max-width: 100%;
        width: 100%;
      }
      @page {
        size: A4 portrait;
        margin: 1.5cm 1.2cm;
      }
    }
  </style>
</head>
<body>

<div class="no-print text-center py-3 bg-dark text-white sticky-top shadow-sm">
  <div class="container d-flex justify-content-between align-items-center">
    <a href="<?= e(BASE_URL) ?>/ppdb/sukses?no=<?= urlencode($data['registration_no']) ?>" class="btn btn-sm btn-outline-light">
      <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <span class="fw-semibold small d-none d-sm-inline">Pratinjau Resmi Kartu Bukti Pendaftaran PPDB</span>
    <button onclick="window.print()" class="btn btn-sm btn-warning fw-bold px-3">
      <i class="bi bi-printer me-1"></i> Cetak / Simpan PDF
    </button>
  </div>
</div>

<div class="print-card">
  <!-- KOP SURAT -->
  <div class="kop-surat d-flex align-items-center gap-3">
    <?php if (!empty($profile['logo'])): ?>
      <img src="<?= e(BASE_URL . '/' . $profile['logo']) ?>" alt="Logo" class="kop-logo">
    <?php endif; ?>
    <div class="flex-grow-1 text-center">
      <div class="text-uppercase small fw-bold text-muted" style="letter-spacing: 0.05em;">Pemerintah Provinsi &bull; Dinas Pendidikan</div>
      <h1 class="h4 fw-bold text-uppercase mb-0" style="color: #0f2744;"><?= e($profile['school_name'] ?? 'SMK Negeri Contoh Indonesia') ?></h1>
      <div class="small text-muted"><?= e($profile['address'] ?? '') ?></div>
      <div class="small text-muted">Telp: <?= e($profile['phone'] ?? '-') ?> | Email: <?= e($profile['email'] ?? '-') ?> | NPSN: <?= e($profile['npsn'] ?? '-') ?></div>
    </div>
  </div>

  <div class="text-center mb-3">
    <h2 class="h5 fw-bold text-uppercase mb-1" style="text-decoration: underline; letter-spacing: 0.5px;">KARTU BUKTI PENDAFTARAN PPDB ONLINE</h2>
    <div class="small fw-semibold text-muted">Tahun Ajaran <?= e($settings['academic_year'] ?? '2026/2027') ?></div>
  </div>

  <!-- Registration Header Ribbon -->
  <div class="d-flex justify-content-between align-items-center bg-light border p-2.5 px-3 rounded mb-3">
    <div>
      <div class="text-muted small text-uppercase" style="font-size: 11px;">Nomor Pendaftaran Resmi</div>
      <div class="h5 fw-bold mb-0 text-primary font-monospace" style="letter-spacing: 1px;"><?= e($data['registration_no']) ?></div>
    </div>
    <div class="text-end">
      <div class="text-muted small" style="font-size: 11px;">Waktu Submit Form</div>
      <div class="fw-bold small"><?= formatTanggalIndo($data['created_at']) ?></div>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-8">
      <table class="table table-bordered table-detail mb-0">
        <tbody>
          <tr>
            <th>Nama Calon Siswa</th>
            <td><strong><?= e($data['full_name']) ?></strong></td>
          </tr>
          <tr>
            <th>NISN / NIK</th>
            <td><?= e($data['nisn']) ?> / <?= e($data['nik']) ?></td>
          </tr>
          <tr>
            <th>Tempat, Tgl Lahir</th>
            <td><?= e($data['birth_place']) ?>, <?= formatTanggalIndo($data['birth_date']) ?></td>
          </tr>
          <tr>
            <th>Jenis Kelamin</th>
            <td><?= $data['gender'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
          </tr>
          <tr>
            <th>Asal Sekolah (SMP/MTs)</th>
            <td><?= e($data['school_origin']) ?> (Lulus <?= e($data['graduation_year']) ?>)</td>
          </tr>
          <tr>
            <th>Nilai Rata-rata Rapor</th>
            <td><?= $data['average_score'] !== null ? number_format($data['average_score'], 2) : '-' ?></td>
          </tr>
          <?php if (!empty($data['major1_name'])): ?>
          <tr>
            <th><?= getSchoolLevel() === 'SMA' ? 'Peminatan Utama' : 'Pilihan Jurusan 1' ?></th>
            <td><strong>[<?= e($data['major1_code']) ?>]</strong> <?= e($data['major1_name']) ?></td>
          </tr>
          <?php if (!empty($data['major2_name'])): ?>
          <tr>
            <th><?= getSchoolLevel() === 'SMA' ? 'Peminatan Alternatif' : 'Pilihan Jurusan 2' ?></th>
            <td><strong>[<?= e($data['major2_code']) ?>]</strong> <?= e($data['major2_name']) ?></td>
          </tr>
          <?php endif; ?>
          <?php else: ?>
          <tr>
            <th>Program / Kelas</th>
            <td><strong>Siswa Baru Kelas X (Reguler / Kurikulum Merdeka)</strong></td>
          </tr>
          <?php endif; ?>
          <tr>
            <th>Orang Tua / Wali</th>
            <td><?= e($data['parent_name']) ?> (<?= e($data['parent_phone']) ?>)</td>
          </tr>
          <tr>
            <th>No. WhatsApp Siswa</th>
            <td><?= e($data['phone']) ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="col-4 d-flex flex-column align-items-center justify-content-between text-center">
      <div class="pas-foto-box rounded mb-2">
        <?php if (!empty($data['document_photo'])): ?>
          <img src="<?= e(BASE_URL . '/' . $data['document_photo']) ?>" alt="Pas Foto Siswa">
        <?php else: ?>
          <div class="small text-muted p-2" style="font-size: 11px;">PAS FOTO<br>3 x 4</div>
        <?php endif; ?>
      </div>

      <!-- QR Code Validasi Digital -->
      <div class="p-2 border rounded bg-light text-center w-100">
        <img src="<?= e($qrCodeUrl) ?>" alt="QR Code Verifikasi" class="img-fluid mb-1" style="width: 80px; height: 80px;">
        <div class="text-muted" style="font-size: 10px; font-weight: 600;">Scan Cek Status</div>
      </div>
    </div>
  </div>

  <div class="p-2.5 px-3 bg-light border rounded small mb-3">
    <strong class="d-block mb-1 text-dark" style="font-size: 12px;"><i class="bi bi-info-circle me-1"></i> Catatan &amp; Ketentuan Panitia PPDB:</strong>
    <ol class="mb-0 ps-3" style="font-size: 11.5px; line-height: 1.55;">
      <li>Bawa kartu bukti pendaftaran ini bersama berkas fisik asli (SKL/Ijazah, KK, Akta Kelahiran, Pas Foto) ke sekretariat PPDB sekolah.</li>
      <li>Nomor WhatsApp yang Anda daftarkan akan menerima notifikasi status verifikasi berkas secara otomatis.</li>
      <li>Pantau pengumuman kelulusan resmi secara online melalui: <strong><?= e(BASE_URL) ?>/ppdb/cek-status</strong>.</li>
    </ol>
  </div>

  <div class="row pt-1 text-center" style="font-size: 12.5px;">
    <div class="col-6">
      <p class="mb-4 pb-2">Calon Peserta Didik,</p>
      <p class="fw-bold mb-0" style="text-decoration: underline;"><?= e($data['full_name']) ?></p>
      <span class="text-muted small">NISN. <?= e($data['nisn']) ?></span>
    </div>
    <div class="col-6">
      <p class="mb-4 pb-2">Panitia PPDB <?= e($settings['academic_year'] ?? '') ?>,</p>
      <p class="fw-bold mb-0" style="text-decoration: underline;">( Panitia Verifikator )</p>
      <span class="text-muted small">NIP. ........................................</span>
    </div>
  </div>

  <!-- TANDA TERIMA BERKAS / SLIP GUNTING UNTUK PANITIA -->
  <div class="tear-slip">
    <div class="tear-slip-badge"><i class="bi bi-scissors me-1"></i> Gunting di sini untuk Tanda Terima Verifikator</div>
    <div class="d-flex justify-content-between align-items-center mt-2" style="font-size: 11.5px;">
      <div>
        <strong>BUKTI PENYERAHAN BERKAS PPDB — <?= e($profile['school_name'] ?? '') ?></strong><br>
        No. Reg: <strong><?= e($data['registration_no']) ?></strong> | Nama: <strong><?= e($data['full_name']) ?></strong> | Pilihan 1: <?= e($data['major1_name'] ?? '-') ?>
      </div>
      <div class="text-end text-muted">
        Paraf Petugas: [ &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ]
      </div>
    </div>
  </div>
</div>

</body>
</html>
