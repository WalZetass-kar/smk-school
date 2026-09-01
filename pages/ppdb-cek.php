<?php
/**
 * pages/ppdb-cek.php — /ppdb/cek-status
 * Cek Status Pendaftaran PPDB Online (Desain Modern, Interaktif & Keren)
 */
$db       = getDB();
$profile  = getSchoolProfile();
$settings = getPpdbSettings();

$keyword  = trim($_GET['keyword'] ?? $_GET['no'] ?? $_GET['reg'] ?? '');
$result   = null;
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

$waPPDB = !empty($profile['whatsapp_ppdb']) ? preg_replace('/[^0-9]/', '', $profile['whatsapp_ppdb']) : '6281234567890';
?>

<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/" class="text-decoration-none">Beranda</a></li>
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/ppdb" class="text-decoration-none">PPDB</a></li>
    <li class="breadcrumb-item active" aria-current="page">Cek Status Pendaftaran</li>
  </ol>
</nav>

<section class="section pt-2 pb-5">
  <div class="container" style="max-width: 900px;">
    <!-- Page Intro Heading -->
    <div class="text-center mb-4">
      <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1.5 rounded-pill fw-semibold mb-2" style="font-size: 12px; letter-spacing: 0.05em;">
        <i class="bi bi-shield-check me-1"></i> PORTAL VERIFIKASI RESMI
      </span>
      <h1 class="section-title mb-2">Cek Status Pendaftaran PPDB</h1>
      <p class="text-muted mx-auto" style="max-width: 600px; font-size: 14.5px;">
        Masukkan <strong>Nomor Registrasi</strong> (contoh: <code>PPDB-202609-1001</code>) atau <strong>10 Digit NISN</strong> calon siswa untuk melacak progres verifikasi secara langsung.
      </p>
    </div>

    <!-- Search Card Box -->
    <div class="card border-0 shadow-lg rounded-4 p-3 p-md-4 mb-4 bg-white position-relative overflow-hidden">
      <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #2563eb, #38bdf8, #f59e0b);"></div>
      <form method="get" action="<?= e(BASE_URL) ?>/ppdb/cek-status" class="m-0">
        <div class="input-group input-group-lg rounded-3 overflow-hidden border">
          <span class="input-group-text bg-light border-0 px-3 text-muted">
            <i class="bi bi-search fs-5"></i>
          </span>
          <input type="text" name="keyword" class="form-control border-0 shadow-none fs-6 py-3" placeholder="Ketik No. Registrasi atau NISN..." value="<?= e($keyword) ?>" required autofocus>
          <button class="btn btn-accent px-4 px-md-5 fw-bold d-flex align-items-center gap-1.5" type="submit">
            <span>Cari Data</span> <i class="bi bi-arrow-right"></i>
          </button>
        </div>
      </form>
    </div>

    <?php if ($searched): ?>
      <?php if ($result): ?>
        <?php
          $st = $result['status'] ?? 'pending';

          // Status Theme Configuration
          $theme = match($st) {
            'accepted' => [
              'grad'      => 'linear-gradient(135deg, #065f46 0%, #059669 50%, #10b981 100%)',
              'icon'      => 'bi-trophy-fill',
              'title'     => 'SELAMAT! ANDA DINYATAKAN DITERIMA',
              'subtitle'  => 'Selamat bergabung menjadi bagian dari civitas akademika ' . ($profile['school_name'] ?? 'Sekolah') . '. Silakan segera lakukan daftar ulang sesuai jadwal.',
              'badgeBg'   => 'bg-success',
              'badgeText' => 'DITERIMA',
              'step'      => 4
            ],
            'verified' => [
              'grad'      => 'linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #3b82f6 100%)',
              'icon'      => 'bi-patch-check-fill',
              'title'     => 'BERKAS LOLOS VERIFIKASI',
              'subtitle'  => 'Seluruh berkas pendaftaran Anda telah lengkap dan valid. Saat ini sedang dalam tahap pemeringkatan hasil seleksi.',
              'badgeBg'   => 'bg-primary',
              'badgeText' => 'TERVERIFIKASI',
              'step'      => 2
            ],
            'reserve' => [
              'grad'      => 'linear-gradient(135deg, #78350f 0%, #b45309 50%, #f59e0b 100%)',
              'icon'      => 'bi-hourglass-split',
              'title'     => 'STATUS: CADANGAN',
              'subtitle'  => 'Anda berada pada kuota cadangan pendaftaran. Harap menunggu pembaruan informasi dari panitia PPDB jika ada kuota terbuka.',
              'badgeBg'   => 'bg-warning text-dark',
              'badgeText' => 'CADANGAN',
              'step'      => 3
            ],
            'rejected' => [
              'grad'      => 'linear-gradient(135deg, #7f1d1d 0%, #dc2626 50%, #ef4444 100%)',
              'icon'      => 'bi-x-circle-fill',
              'title'     => 'MOHON MAAF, BELUM MEMENUHI KUOTA',
              'subtitle'  => 'Berdasarkan hasil seleksi panitia PPDB, Anda belum dapat diterima pada periode pendaftaran ini. Tetap semangat menggapai masa depan!',
              'badgeBg'   => 'bg-danger',
              'badgeText' => 'TIDAK DITERIMA',
              'step'      => 4
            ],
            default => [
              'grad'      => 'linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%)',
              'icon'      => 'bi-clock-history',
              'title'     => 'BERKAS DALAM PROSES VERIFIKASI',
              'subtitle'  => 'Formulir Anda telah terkirim. Panitia PPDB sedang memeriksa kelengkapan berkas fisik & administrasi Anda.',
              'badgeBg'   => 'bg-secondary',
              'badgeText' => 'MENUNGGU VERIFIKASI',
              'step'      => 1
            ],
          };

          $verifyUrl = BASE_URL . '/ppdb/cek-status?reg=' . urlencode($result['registration_no']);
          $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($verifyUrl);
        ?>

        <!-- RESULT MASTER CONTAINER -->
        <div class="card border-0 shadow-xl rounded-4 overflow-hidden mb-4 bg-white">
          <!-- 1. Hero Status Banner -->
          <div class="p-4 p-md-5 text-white position-relative" style="background: <?= $theme['grad'] ?>;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
              <span class="badge bg-white bg-opacity-20 text-white px-3 py-1.5 rounded-pill fw-semibold border border-white border-opacity-25" style="font-size: 11.5px; letter-spacing: 0.05em;">
                <i class="bi bi-person-vcard me-1"></i> NO. REGISTRASI: <?= e($result['registration_no']) ?>
              </span>
              <span class="badge <?= $theme['badgeBg'] ?> px-3 py-1.5 rounded-pill fw-bold shadow-sm" style="font-size: 12px; letter-spacing: 0.05em;">
                <?= $theme['badgeText'] ?>
              </span>
            </div>

            <div class="d-flex align-items-start gap-3">
              <div class="p-3 bg-white bg-opacity-15 rounded-circle fs-1 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 64px; height: 64px;">
                <i class="bi <?= $theme['icon'] ?>"></i>
              </div>
              <div>
                <h2 class="h4 fw-bold text-white mb-1.5" style="letter-spacing: 0.5px;"><?= $theme['title'] ?></h2>
                <p class="text-white text-opacity-90 small mb-0" style="line-height: 1.6; max-width: 680px;"><?= $theme['subtitle'] ?></p>
              </div>
            </div>
          </div>

          <!-- 2. Interactive Stepper / Progress Timeline -->
          <div class="p-4 bg-light border-bottom">
            <div class="row g-2 text-center position-relative">
              <!-- Step 1 -->
              <div class="col-3">
                <div class="d-flex flex-column align-items-center">
                  <div class="rounded-circle d-flex align-items-center justify-content-center mb-1.5 fw-bold <?= $theme['step'] >= 1 ? 'bg-primary text-white shadow-sm' : 'bg-secondary bg-opacity-20 text-muted' ?>" style="width: 32px; height: 32px; font-size: 13px;">
                    <?= $theme['step'] > 1 ? '<i class="bi bi-check-lg"></i>' : '1' ?>
                  </div>
                  <span class="small fw-semibold <?= $theme['step'] >= 1 ? 'text-primary' : 'text-muted' ?>" style="font-size: 11.5px;">Pendaftaran</span>
                </div>
              </div>
              <!-- Step 2 -->
              <div class="col-3">
                <div class="d-flex flex-column align-items-center">
                  <div class="rounded-circle d-flex align-items-center justify-content-center mb-1.5 fw-bold <?= $theme['step'] >= 2 ? 'bg-primary text-white shadow-sm' : 'bg-secondary bg-opacity-20 text-muted' ?>" style="width: 32px; height: 32px; font-size: 13px;">
                    <?= $theme['step'] > 2 ? '<i class="bi bi-check-lg"></i>' : '2' ?>
                  </div>
                  <span class="small fw-semibold <?= $theme['step'] >= 2 ? 'text-primary' : 'text-muted' ?>" style="font-size: 11.5px;">Verifikasi</span>
                </div>
              </div>
              <!-- Step 3 -->
              <div class="col-3">
                <div class="d-flex flex-column align-items-center">
                  <div class="rounded-circle d-flex align-items-center justify-content-center mb-1.5 fw-bold <?= $theme['step'] >= 3 ? 'bg-primary text-white shadow-sm' : 'bg-secondary bg-opacity-20 text-muted' ?>" style="width: 32px; height: 32px; font-size: 13px;">
                    <?= $theme['step'] > 3 ? '<i class="bi bi-check-lg"></i>' : '3' ?>
                  </div>
                  <span class="small fw-semibold <?= $theme['step'] >= 3 ? 'text-primary' : 'text-muted' ?>" style="font-size: 11.5px;">Seleksi</span>
                </div>
              </div>
              <!-- Step 4 -->
              <div class="col-3">
                <div class="d-flex flex-column align-items-center">
                  <div class="rounded-circle d-flex align-items-center justify-content-center mb-1.5 fw-bold <?= $theme['step'] >= 4 ? ($st === 'accepted' ? 'bg-success text-white shadow-sm' : 'bg-danger text-white shadow-sm') : 'bg-secondary bg-opacity-20 text-muted' ?>" style="width: 32px; height: 32px; font-size: 13px;">
                    <?= $theme['step'] >= 4 ? ($st === 'accepted' ? '<i class="bi bi-check-lg"></i>' : '<i class="bi bi-x-lg"></i>') : '4' ?>
                  </div>
                  <span class="small fw-semibold <?= $theme['step'] >= 4 ? ($st === 'accepted' ? 'text-success' : 'text-danger') : 'text-muted' ?>" style="font-size: 11.5px;">Pengumuman</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 3. Main Body Content & Candidate Identity -->
          <div class="card-body p-4 p-md-5">
            <!-- Candidate Quick Bar -->
            <div class="row g-4 align-items-center pb-4 mb-4 border-bottom">
              <div class="col-md-auto text-center">
                <?php if (!empty($result['document_photo'])): ?>
                  <img src="<?= e(BASE_URL . '/' . $result['document_photo']) ?>" alt="Pas Foto" class="rounded-4 shadow-sm border" style="width: 100px; height: 130px; object-fit: cover;">
                <?php else: ?>
                  <div class="rounded-4 bg-light border d-flex flex-column align-items-center justify-content-center text-muted" style="width: 100px; height: 130px;">
                    <i class="bi bi-person-fill fs-1 text-secondary"></i>
                    <small style="font-size: 10px;">FOTO 3x4</small>
                  </div>
                <?php endif; ?>
              </div>

              <div class="col-md">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                  <h3 class="h4 fw-bold text-dark mb-0"><?= e($result['full_name']) ?></h3>
                  <span class="badge bg-light text-secondary border"><?= $result['gender'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></span>
                </div>
                <div class="text-muted small mb-3">
                  <i class="bi bi-mortarboard me-1"></i> Asal SMP/MTs: <strong><?= e($result['school_origin']) ?></strong> (Lulus <?= e($result['graduation_year']) ?>)
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <div class="badge bg-primary bg-opacity-10 text-primary p-2 px-3 rounded-pill fw-semibold" style="font-size: 12px;">
                    <i class="bi bi-star-fill me-1 text-warning"></i> Pilihan 1: <strong>[<?= e($result['major1_code'] ?: 'JUR-1') ?>]</strong> <?= e($result['major1_name'] ?: 'Reguler') ?>
                  </div>
                  <?php if (!empty($result['major2_name'])): ?>
                    <div class="badge bg-secondary bg-opacity-10 text-secondary p-2 px-3 rounded-pill fw-semibold" style="font-size: 12px;">
                      Pilihan 2: <strong>[<?= e($result['major2_code'] ?: 'JUR-2') ?>]</strong> <?= e($result['major2_name']) ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

              <div class="col-md-auto text-center border-start-md ps-md-4">
                <div class="p-2 border rounded-3 bg-light d-inline-block">
                  <img src="<?= e($qrCodeUrl) ?>" alt="QR Code" style="width: 80px; height: 80px;" class="img-fluid">
                  <div class="text-muted" style="font-size: 9.5px; font-weight: 600;">Scan Verifikasi</div>
                </div>
              </div>
            </div>

            <!-- Committee Notes / Special Instructions -->
            <?php if (!empty($result['notes'])): ?>
              <div class="p-3.5 p-3 rounded-4 bg-light border mb-4">
                <div class="d-flex align-items-start gap-2">
                  <i class="bi bi-info-circle-fill text-primary fs-5 flex-shrink-0 mt-0.5"></i>
                  <div>
                    <h4 class="h6 fw-bold text-dark mb-1">Catatan Khusus dari Panitia PPDB:</h4>
                    <p class="small text-muted mb-0" style="line-height: 1.6; font-size: 13.5px;"><?= nl2br(e($result['notes'])) ?></p>
                  </div>
                </div>
              </div>
            <?php endif; ?>

            <!-- Detailed Table Breakdown -->
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <div class="p-3 rounded-3 bg-light h-100 border">
                  <h4 class="small fw-bold text-uppercase text-muted mb-3"><i class="bi bi-person-badge me-1"></i> Rincian Identitas</h4>
                  <table class="table table-sm table-borderless small mb-0">
                    <tr><td class="text-muted" style="width:40%;">No. Registrasi</td><td class="fw-bold font-monospace text-primary"><?= e($result['registration_no']) ?></td></tr>
                    <tr><td class="text-muted">NISN / NIK</td><td><?= e($result['nisn']) ?> / <?= e($result['nik']) ?></td></tr>
                    <tr><td class="text-muted">Tempat, Tgl Lahir</td><td><?= e($result['birth_place']) ?>, <?= formatTanggalIndo($result['birth_date']) ?></td></tr>
                    <tr><td class="text-muted">Agama</td><td><?= e($result['religion']) ?></td></tr>
                    <tr><td class="text-muted">Nilai Rata-rata</td><td><span class="badge bg-light text-dark border fw-bold"><?= $result['average_score'] !== null ? number_format((float)$result['average_score'], 2) : '-' ?></span></td></tr>
                  </table>
                </div>
              </div>

              <div class="col-md-6">
                <div class="p-3 rounded-3 bg-light h-100 border">
                  <h4 class="small fw-bold text-uppercase text-muted mb-3"><i class="bi bi-geo-alt me-1"></i> Kontak &amp; Orang Tua</h4>
                  <table class="table table-sm table-borderless small mb-0">
                    <tr><td class="text-muted" style="width:40%;">Nama Orang Tua</td><td class="fw-semibold"><?= e($result['parent_name']) ?></td></tr>
                    <tr><td class="text-muted">No. HP Orang Tua</td><td><?= e($result['parent_phone'] ?: '-') ?></td></tr>
                    <tr><td class="text-muted">No. WhatsApp Siswa</td><td><span class="text-success fw-semibold"><?= e($result['phone']) ?></span></td></tr>
                    <tr><td class="text-muted">Waktu Mendaftar</td><td><?= date('d M Y, H:i', strtotime($result['created_at'])) ?> WIB</td></tr>
                    <tr><td class="text-muted">Alamat</td><td class="text-truncate" style="max-width: 160px;" title="<?= e($result['address']) ?>"><?= e($result['address']) ?></td></tr>
                  </table>
                </div>
              </div>
            </div>

            <!-- Action Buttons Footer -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 pt-3 border-top">
              <a href="<?= e(BASE_URL) ?>/ppdb/cetak?reg=<?= urlencode($result['registration_no']) ?>" target="_blank" class="btn btn-accent btn-lg px-4 py-2.5 fw-bold rounded-3 shadow-sm w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-printer-fill"></i> Cetak Kartu Bukti Pendaftaran (PDF)
              </a>

              <div class="d-flex gap-2 w-100 w-sm-auto">
                <button type="button" class="btn btn-outline-secondary py-2.5 px-3 rounded-3 w-50 w-sm-auto fw-semibold" id="copyRegLinkBtn" data-url="<?= e($verifyUrl) ?>">
                  <i class="bi bi-link-45deg me-1"></i> <span id="copyBtnText">Salin Link</span>
                </button>
                <a href="https://wa.me/<?= e($waPPDB) ?>?text=Halo%20Panitia%20PPDB%2C%20saya%20atas%20nama%20<?= urlencode($result['full_name']) ?>%20(No.%20Reg%3A%20<?= urlencode($result['registration_no']) ?>)%20ingin%20bertanya%20mengenai%20status%20pendaftaran." target="_blank" rel="noopener" class="btn btn-outline-success py-2.5 px-3 rounded-3 w-50 w-sm-auto fw-semibold">
                  <i class="bi bi-whatsapp me-1"></i> Hotline Panitia
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php else: ?>
        <!-- Not Found Card -->
        <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 text-center bg-white">
          <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
            <i class="bi bi-search fs-1"></i>
          </div>
          <h2 class="h4 fw-bold text-dark mb-2">Data Pendaftaran Tidak Ditemukan</h2>
          <p class="text-muted small mb-4 mx-auto" style="max-width: 500px; line-height: 1.6;">
            Kami tidak dapat menemukan data calon siswa dengan kata kunci <strong>"<?= e($keyword) ?>"</strong>. Pastikan Nomor Registrasi atau NISN yang Anda masukkan sudah sesuai.
          </p>
          <div class="d-flex justify-content-center gap-2">
            <a href="<?= e(BASE_URL) ?>/ppdb/cek-status" class="btn btn-outline-primary px-4 py-2 fw-semibold rounded-3">
              <i class="bi bi-arrow-counterclockwise me-1"></i> Coba Lagi
            </a>
            <a href="<?= e(BASE_URL) ?>/ppdb/daftar" class="btn btn-accent px-4 py-2 fw-semibold rounded-3">
              <i class="bi bi-pencil-square me-1"></i> Daftar PPDB Baru
            </a>
          </div>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

<script>
document.getElementById('copyRegLinkBtn')?.addEventListener('click', function() {
  const url = this.dataset.url;
  if (navigator.clipboard) {
    navigator.clipboard.writeText(url).then(() => {
      const btnText = document.getElementById('copyBtnText');
      if (btnText) btnText.textContent = 'Link Disalin!';
      this.classList.replace('btn-outline-secondary', 'btn-success');
      setTimeout(() => {
        if (btnText) btnText.textContent = 'Salin Link';
        this.classList.replace('btn-success', 'btn-outline-secondary');
      }, 2000);
    });
  }
});
</script>

<?php require BASE_PATH . '/includes/footer.php'; ?>
