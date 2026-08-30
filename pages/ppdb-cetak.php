<?php
/**
 * pages/ppdb-cetak.php — /ppdb/cetak?no=...
 * Cetak Kartu Bukti Pendaftaran PPDB Resmi
 */
$db       = getDB();
$profile  = getSchoolProfile();
$settings = getPpdbSettings();
$regNo    = trim($_GET['no'] ?? '');

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
    die('Data pendaftaran tidak ditemukan.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bukti Pendaftaran PPDB — <?= e($data['registration_no']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8fafc;
      font-family: 'Segoe UI', Arial, sans-serif;
      color: #1e293b;
    }
    .print-card {
      max-width: 800px;
      margin: 30px auto;
      background: #fff;
      padding: 40px;
      border: 1px solid #cbd5e1;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
      position: relative;
    }
    .kop-surat {
      border-bottom: 3px double #0f2744;
      padding-bottom: 15px;
      margin-bottom: 25px;
    }
    .kop-logo {
      width: 75px;
      height: 75px;
      object-fit: contain;
    }
    .table-detail th {
      width: 32%;
      background-color: #f1f5f9;
      color: #334155;
      font-weight: 600;
      padding: 6px 12px;
      font-size: 13.5px;
    }
    .table-detail td {
      padding: 6px 12px;
      font-size: 13.5px;
    }
    .pas-foto-box {
      width: 120px;
      height: 155px;
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
    @media print {
      body {
        background: #fff;
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
      }
    }
  </style>
</head>
<body>

<div class="no-print text-center py-3 bg-dark text-white sticky-top shadow-sm">
  <div class="container d-flex justify-content-between align-items-center">
    <a href="<?= e(BASE_URL) ?>/ppdb/sukses?no=<?= urlencode($data['registration_no']) ?>" class="btn btn-sm btn-outline-light">
      <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
    <span class="fw-semibold">Pratinjau Cetak Bukti Pendaftaran</span>
    <button onclick="window.print()" class="btn btn-sm btn-warning fw-bold px-3">
      <i class="bi bi-printer me-1"></i>Cetak / Simpan PDF
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
      <div class="text-uppercase small fw-bold text-muted">Pemerintah Provinsi &bull; Dinas Pendidikan</div>
      <h1 class="h4 fw-bold text-uppercase mb-0" style="color: #0f2744;"><?= e($profile['school_name'] ?? 'SMK Negeri Contoh Indonesia') ?></h1>
      <div class="small text-muted"><?= e($profile['address'] ?? '') ?></div>
      <div class="small text-muted">Telp: <?= e($profile['phone'] ?? '-') ?> | Email: <?= e($profile['email'] ?? '-') ?> | NPSN: <?= e($profile['npsn'] ?? '-') ?></div>
    </div>
  </div>

  <div class="text-center mb-4">
    <h2 class="h5 fw-bold text-uppercase mb-1" style="text-decoration: underline;">KARTU BUKTI PENDAFTARAN PPDB</h2>
    <div class="small fw-semibold text-muted">Tahun Ajaran <?= e($settings['academic_year']) ?></div>
  </div>

  <div class="d-flex justify-content-between align-items-center bg-light border p-3 rounded mb-3">
    <div>
      <div class="text-muted small text-uppercase">Nomor Registrasi</div>
      <div class="h4 fw-bold mb-0 text-primary" style="letter-spacing: 1px;"><?= e($data['registration_no']) ?></div>
    </div>
    <div class="text-end">
      <div class="text-muted small">Waktu Registrasi</div>
      <div class="fw-bold small"><?= formatTanggalIndo($data['created_at']) ?></div>
    </div>
  </div>

  <div class="row g-3 mb-4">
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
            <th>Asal SMP / MTs</th>
            <td><?= e($data['school_origin']) ?> (Lulus <?= e($data['graduation_year']) ?>)</td>
          </tr>
          <tr>
            <th>Nilai Rata-rata</th>
            <td><?= $data['average_score'] !== null ? number_format($data['average_score'], 2) : '-' ?></td>
          </tr>
          <tr>
            <th>Pilihan Jurusan 1</th>
            <td><strong>[<?= e($data['major1_code']) ?>]</strong> <?= e($data['major1_name']) ?></td>
          </tr>
          <?php if ($data['major2_name']): ?>
          <tr>
            <th>Pilihan Jurusan 2</th>
            <td><strong>[<?= e($data['major2_code']) ?>]</strong> <?= e($data['major2_name']) ?></td>
          </tr>
          <?php endif; ?>
          <tr>
            <th>Orang Tua / Wali</th>
            <td><?= e($data['parent_name']) ?> (<?= e($data['parent_phone']) ?>)</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="col-4 d-flex flex-column align-items-center justify-content-center">
      <div class="pas-foto-box rounded mb-2">
        <?php if (!empty($data['document_photo'])): ?>
          <img src="<?= e(BASE_URL . '/' . $data['document_photo']) ?>" alt="Pas Foto">
        <?php else: ?>
          <div class="small text-muted p-2">PAS FOTO 3x4</div>
        <?php endif; ?>
      </div>
      <div class="small text-muted text-center" style="font-size: 11px;">Tempel Pas Foto 3x4 jika belum diunggah</div>
    </div>
  </div>

  <div class="p-3 bg-light border rounded small mb-4">
    <strong>Petunjuk Peserta:</strong>
    <ol class="mb-0 ps-3 mt-1" style="font-size: 12px; line-height: 1.6;">
      <li>Bawa kartu bukti pendaftaran ini bersama berkas asli (SKL/Ijazah, KK, Akta Kelahiran) pada saat verifikasi fisik.</li>
      <li>Pastikan nomor WhatsApp dan kontak yang didaftarkan selalu aktif untuk menerima informasi terbaru dari panitia PPDB.</li>
      <li>Pengumuman kelulusan dapat dipantau di website resmi sekolah: <code><?= e(BASE_URL) ?>/ppdb/cek-status</code>.</li>
    </ol>
  </div>

  <div class="row pt-2 text-center" style="font-size: 13px;">
    <div class="col-6">
      <p class="mb-5">Calon Peserta Didik,</p>
      <p class="fw-bold mb-0" style="text-decoration: underline;"><?= e($data['full_name']) ?></p>
      <span class="text-muted small">NISN. <?= e($data['nisn']) ?></span>
    </div>
    <div class="col-6">
      <p class="mb-5">Panitia PPDB <?= e($settings['academic_year']) ?>,</p>
      <p class="fw-bold mb-0" style="text-decoration: underline;">( Panitia Verifikator )</p>
      <span class="text-muted small">NIP. ........................................</span>
    </div>
  </div>
</div>

</body>
</html>
