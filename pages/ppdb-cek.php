<?php
/**
 * pages/ppdb-cek.php — /ppdb/cek-status
 * Cek Status Pendaftaran PPDB Online
 */
$db       = getDB();
$profile  = getSchoolProfile();
$settings = getPpdbSettings();

$keyword = trim($_GET['keyword'] ?? '');
$result  = null;
$searched = false;

if ($keyword !== '') {
    $searched = true;
    $stmt = $db->prepare("
        SELECT r.*, m1.name AS major1_name, m1.code AS major1_code, m2.name AS major2_name, m2.code AS major2_code
        FROM ppdb_registrations r
        LEFT JOIN majors m1 ON m1.id = r.major_choice_1
        LEFT JOIN majors m2 ON m2.id = r.major_choice_2
        WHERE r.registration_no = ? OR r.nisn = ?
        LIMIT 1
    ");
    $stmt->execute([$keyword, $keyword]);
    $result = $stmt->fetch();
}

$pageTitle       = 'Cek Status Pendaftaran PPDB';
$pageDescription = 'Cek status pendaftaran peserta didik baru ' . e($profile['school_name'] ?? '');
$canonicalPath   = '/ppdb/cek-status';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/">Beranda</a></li>
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/ppdb">PPDB</a></li>
    <li class="breadcrumb-item active" aria-current="page">Cek Status Pendaftaran</li>
  </ol>
</nav>

<section class="section pt-3 pb-5">
  <div class="container" style="max-width: 820px;">
    <div class="text-center mb-4">
      <h1 class="section-title mb-2">Cek Status Pendaftaran PPDB</h1>
      <p class="text-muted">Masukkan Nomor Registrasi (contoh: <code>PPDB-202708-1001</code>) atau 10 Digit NISN Anda</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
      <form method="get" action="<?= e(BASE_URL) ?>/ppdb/cek-status">
        <div class="input-group input-group-lg">
          <input type="text" name="keyword" class="form-control" placeholder="Ketik No. Registrasi atau NISN..." value="<?= e($keyword) ?>" required autofocus>
          <button class="btn btn-accent px-4 fw-semibold" type="submit">
            <i class="bi bi-search me-1"></i> Cari Data
          </button>
        </div>
      </form>
    </div>

    <?php if ($searched): ?>
      <?php if ($result): ?>
        <?php
          $statusBg = match($result['status']) {
            'accepted' => 'alert-success border-success',
            'rejected' => 'alert-danger border-danger',
            'verified' => 'alert-info border-info',
            'reserve'  => 'alert-warning border-warning',
            default    => 'alert-secondary border-secondary'
          };
          $statusTitle = match($result['status']) {
            'accepted' => 'SELAMAT! ANDA DINYATAKAN DITERIMA',
            'rejected' => 'MOHON MAAF, ANDA BELUM DITERIMA',
            'verified' => 'BERKAS TERVERIFIKASI (Menunggu Pengumuman)',
            'reserve'  => 'STATUS: CADANGAN',
            default    => 'MENUNGGU VERIFIKASI BERKAS'
          };
        ?>
        <div class="card border-0 shadow rounded-4 overflow-hidden mb-4">
          <div class="card-header bg-primary text-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h6 mb-0 text-white"><i class="bi bi-person-check me-2"></i>Hasil Pencarian Pendaftaran</h2>
            <span class="badge bg-light text-primary fs-7"><?= e($result['registration_no']) ?></span>
          </div>

          <div class="card-body p-4 p-md-5">
            <div class="alert <?= $statusBg ?> border-2 p-4 mb-4 rounded-3 text-center">
              <div class="h5 fw-bold mb-1"><?= $statusTitle ?></div>
              <?php if (!empty($result['notes'])): ?>
                <div class="small mt-2 pt-2 border-top border-dark border-opacity-10">
                  <strong>Catatan Panitia:</strong> <?= nl2br(e($result['notes'])) ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="table-responsive mb-4">
              <table class="table table-bordered mb-0">
                <tbody>
                  <tr>
                    <th class="bg-light" style="width: 35%;">Nama Lengkap</th>
                    <td><strong><?= e($result['full_name']) ?></strong></td>
                  </tr>
                  <tr>
                    <th class="bg-light">NISN</th>
                    <td><?= e($result['nisn']) ?></td>
                  </tr>
                  <tr>
                    <th class="bg-light">Asal Sekolah</th>
                    <td><?= e($result['school_origin']) ?></td>
                  </tr>
                  <tr>
                    <th class="bg-light">Pilihan Jurusan 1</th>
                    <td><span class="badge bg-primary"><?= e($result['major1_code']) ?></span> <?= e($result['major1_name']) ?></td>
                  </tr>
                  <?php if ($result['major2_name']): ?>
                  <tr>
                    <th class="bg-light">Pilihan Jurusan 2</th>
                    <td><span class="badge bg-secondary"><?= e($result['major2_code']) ?></span> <?= e($result['major2_name']) ?></td>
                  </tr>
                  <?php endif; ?>
                  <tr>
                    <th class="bg-light">Tanggal Mendaftar</th>
                    <td><?= formatTanggalIndo($result['created_at']) ?></td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="text-center">
              <a href="<?= e(BASE_URL) ?>/ppdb/cetak?no=<?= urlencode($result['registration_no']) ?>" target="_blank" class="btn btn-accent btn-lg px-4 shadow-sm">
                <i class="bi bi-printer me-2"></i>Cetak Kartu Bukti Pendaftaran
              </a>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="alert alert-warning border-0 shadow-sm rounded-4 p-4 text-center">
          <i class="bi bi-exclamation-triangle-fill fs-2 text-warning d-block mb-2"></i>
          <h2 class="h5 fw-bold mb-1">Data Pendaftaran Tidak Ditemukan</h2>
          <p class="text-muted small mb-0">Pastikan Nomor Registrasi atau NISN yang Anda masukkan sudah sesuai saat mendaftar.</p>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
