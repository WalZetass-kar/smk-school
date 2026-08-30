<?php
/**
 * pages/ppdb-sukses.php — /ppdb/sukses
 * Halaman Konfirmasi Pendaftaran Berhasil
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
    redirect(BASE_URL . '/ppdb');
}

$pageTitle       = 'Pendaftaran Berhasil — ' . e($data['registration_no']);
$pageDescription = 'Bukti Pendaftaran PPDB ' . e($profile['school_name'] ?? '');
$canonicalPath   = '/ppdb/sukses';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<section class="section py-5">
  <div class="container" style="max-width: 780px;">
    <div class="card border-0 shadow rounded-4 overflow-hidden text-center p-4 p-md-5">
      <div class="mb-3">
        <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2.5rem;">
          <i class="bi bi-check-lg"></i>
        </div>
      </div>

      <h1 class="h3 fw-bold text-primary mb-2">Pendaftaran Berhasil Dikirim!</h1>
      <p class="text-muted mb-4">Terima kasih telah mendaftar di <?= e($profile['school_name'] ?? '') ?>. Simpan dan catat Nomor Registrasi Anda di bawah ini:</p>

      <div class="card bg-light border-2 border-primary border-opacity-25 rounded-3 p-4 mb-4">
        <div class="text-muted small fw-semibold text-uppercase tracking-wider">Nomor Registrasi PPDB</div>
        <div class="display-6 fw-bold text-primary my-1" style="font-family: var(--font-display);"><?= e($data['registration_no']) ?></div>
        <div class="small text-muted">Tanggal Daftar: <?= formatTanggalIndo($data['created_at']) ?></div>
      </div>

      <div class="table-responsive text-start mb-4">
        <table class="table table-bordered mb-0">
          <tbody>
            <tr>
              <th class="bg-light" style="width: 35%;">Nama Lengkap</th>
              <td><strong><?= e($data['full_name']) ?></strong></td>
            </tr>
            <tr>
              <th class="bg-light">NISN / NIK</th>
              <td><?= e($data['nisn']) ?> / <?= e($data['nik']) ?></td>
            </tr>
            <tr>
              <th class="bg-light">Asal Sekolah</th>
              <td><?= e($data['school_origin']) ?></td>
            </tr>
            <tr>
              <th class="bg-light">Pilihan Jurusan 1</th>
              <td><span class="badge bg-primary fs-7"><?= e($data['major1_code']) ?></span> <?= e($data['major1_name']) ?></td>
            </tr>
            <?php if ($data['major2_name']): ?>
            <tr>
              <th class="bg-light">Pilihan Jurusan 2</th>
              <td><span class="badge bg-secondary fs-7"><?= e($data['major2_code']) ?></span> <?= e($data['major2_name']) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
              <th class="bg-light">Status Saat Ini</th>
              <td>
                <?php
                  $badgeClass = match($data['status']) {
                    'verified' => 'bg-info text-dark',
                    'accepted' => 'bg-success',
                    'rejected' => 'bg-danger',
                    'reserve'  => 'bg-warning text-dark',
                    default    => 'bg-secondary'
                  };
                  $statusLabel = match($data['status']) {
                    'verified' => 'Terverifikasi (Menunggu Pengumuman)',
                    'accepted' => 'Selamat! Anda Diterima',
                    'rejected' => 'Tidak Diterima',
                    'reserve'  => 'Cadangan',
                    default    => 'Menunggu Verifikasi Berkas'
                  };
                ?>
                <span class="badge <?= $badgeClass ?> p-2 fs-7"><?= $statusLabel ?></span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="alert alert-info text-start small mb-4">
        <i class="bi bi-info-circle-fill me-2"></i>
        <strong>Petunjuk Selanjutnya:</strong>
        <ol class="mb-0 ps-3 mt-1">
          <li>Klik tombol <strong>Cetak Bukti Pendaftaran</strong> di bawah ini untuk mencetak atau mengunduh Kartu Bukti Registrasi (PDF).</li>
          <li>Pantau status verifikasi dan pengumuman hasil seleksi secara berkala di halaman <a href="<?= e(BASE_URL) ?>/ppdb/cek-status" class="fw-bold">Cek Status</a>.</li>
        </ol>
      </div>

      <div class="d-flex justify-content-center gap-3 flex-wrap">
        <a href="<?= e(BASE_URL) ?>/ppdb/cetak?no=<?= urlencode($data['registration_no']) ?>" target="_blank" class="btn btn-accent btn-lg px-4 shadow-sm">
          <i class="bi bi-printer me-2"></i>Cetak Bukti Pendaftaran
        </a>
        <a href="<?= e(BASE_URL) ?>/ppdb/cek-status?keyword=<?= urlencode($data['registration_no']) ?>" class="btn btn-outline-primary-custom btn-lg px-4">
          <i class="bi bi-search me-2"></i>Cek Status Berkas
        </a>
        <a href="<?= e(BASE_URL) ?>/" class="btn btn-outline-secondary btn-lg px-4">
          <i class="bi bi-house me-2"></i>Beranda
        </a>
      </div>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
