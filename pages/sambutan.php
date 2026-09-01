<?php
/**
 * pages/sambutan.php — /profil/sambutan
 * Halaman Sambutan Resmi Kepala Sekolah
 */
$db        = getDB();
$profile   = getSchoolProfile();
$principal = $db->query("SELECT * FROM principal_message ORDER BY id LIMIT 1")->fetch();

$pageTitle       = 'Sambutan Kepala Sekolah';
$pageDescription = $principal ? excerpt($principal['content'], 155) : 'Sambutan resmi Kepala Sekolah ' . e($profile['school_name'] ?? 'kami');
$canonicalPath   = '/profil/sambutan';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small mb-0">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/profil">Profil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Sambutan Kepala Sekolah</li>
  </ol>
</nav>

<section class="section pt-3 pb-5">
  <div class="container">
    <!-- Header -->
    <div class="text-center mx-auto mb-5 reveal-up" style="max-width:720px;">
      <h1 class="section-title mb-2">Sambutan Kepala Sekolah</h1>
      <p class="section-subtitle mx-auto">Pesan resmi dan komitmen kepemimpinan <strong><?= e($profile['school_name'] ?? 'Sekolah') ?></strong> dalam memajukan pendidikan vokasi yang bermutu dan berkarakter.</p>
    </div>

    <!-- Main Content Layout -->
    <div class="row g-4 g-lg-5">
      <!-- Left Column: Unified Sambutan Article -->
      <div class="col-lg-8 reveal-left">
        <?php if ($principal): ?>
          <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
            
            <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom flex-wrap gap-2">
              <div>
                <h2 class="h5 fw-bold text-primary mb-0">Penyampaian Sambutan Resmi</h2>
                <div class="text-muted small"><?= e($principal['principal_name']) ?> &mdash; <?= e($principal['position']) ?></div>
              </div>
              <span class="badge bg-light text-dark border px-3 py-1.5 small">
                <i class="bi bi-shield-check text-primary me-1"></i>Dokumen Resmi
              </span>
            </div>

            <!-- Article Body -->
            <div class="article-body mb-5" style="font-size: 1rem; line-height: 1.9; color: #334155;">
              <?= cleanHtml($principal['content']) ?>
            </div>

            <!-- Formal Signature & Seal Block -->
            <div class="pt-4 border-top">
              <div class="row align-items-center">
                <div class="col-sm-6 mb-3 mb-sm-0">
                  <div class="text-muted small">
                    <strong><?= e($profile['school_name'] ?? 'SMK') ?></strong><br>
                    <?= e($profile['address'] ?? 'Alamat Sekolah') ?>
                  </div>
                </div>
                <div class="col-sm-6 text-sm-end">
                  <p class="small text-muted mb-1"><?= e($profile['city'] ?? 'Pekanbaru') ?>, <?= date('d F Y') ?></p>
                  <p class="small text-muted fw-semibold mb-3"><?= e($principal['position']) ?></p>
                  <div class="d-inline-block text-start border-bottom border-2 border-dark pb-1" style="min-width: 170px;">
                    <span class="fw-bold text-primary fs-6 d-block"><?= e($principal['principal_name']) ?></span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Share & Print Toolbar -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4 pt-3 border-top">
              <div class="d-flex align-items-center gap-2">
                <span class="small text-muted me-1">Bagikan:</span>
                <a href="https://api.whatsapp.com/send?text=<?= urlencode('Sambutan Kepala ' . ($profile['school_name'] ?? 'Sekolah') . ': ' . BASE_URL . '/profil/sambutan') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-light border" title="Bagikan ke WhatsApp">
                  <i class="bi bi-whatsapp text-success"></i>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . '/profil/sambutan') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-light border" title="Bagikan ke Facebook">
                  <i class="bi bi-facebook text-primary"></i>
                </a>
                <button type="button" class="btn btn-sm btn-light border" onclick="navigator.clipboard.writeText(window.location.href); alert('Tautan berhasil disalin!');" title="Salin Tautan">
                  <i class="bi bi-link-45deg"></i>
                </button>
              </div>

              <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print();">
                <i class="bi bi-printer me-1"></i>Cetak
              </button>
            </div>

          </div>
        <?php else: ?>
          <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <h2 class="h5 fw-bold text-dark">Sambutan Belum Diterbitkan</h2>
            <p class="text-muted small mb-0">Konten sambutan kepala sekolah saat ini sedang dipersiapkan.</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Right Column: Principal Profile & Navigation -->
      <div class="col-lg-4">
        <?php if ($principal): ?>
          <!-- Principal Photo & Profile Card -->
          <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white text-center reveal-right">
            <div class="sambutan-photo-frame mx-auto mb-3" style="max-width: 220px;">
              <img src="<?= e(BASE_URL . '/' . $principal['photo']) ?>" alt="<?= e($principal['principal_name']) ?>" class="img-fluid w-100">
            </div>

            <h2 class="h6 fw-bold text-primary mb-1"><?= e($principal['principal_name']) ?></h2>
            <div class="text-muted small mb-3"><?= e($principal['position']) ?></div>

            <div class="border-top pt-3 text-start small text-muted">
              <div class="d-flex justify-content-between py-1 border-bottom">
                <span>Instansi:</span>
                <span class="fw-semibold text-dark text-end"><?= e($profile['school_name'] ?? 'SMK') ?></span>
              </div>
              <div class="d-flex justify-content-between py-1">
                <span>Status:</span>
                <span class="text-success fw-semibold">Pimpinan Aktif</span>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <!-- Quick Profile Navigation -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white reveal-right">
          <h2 class="h6 fw-bold text-primary mb-3 pb-2 border-bottom"><i class="bi bi-list-nested me-2"></i>Navigasi Profil</h2>
          <div class="list-group list-group-flush">
            <a href="<?= e(BASE_URL) ?>/profil/sambutan" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small fw-bold text-primary" style="background:#f8fafc; padding-left:10px !important; border-radius:6px;">
              <span><i class="bi bi-person-badge me-2 text-primary"></i>Sambutan Kepala Sekolah</span>
              <i class="bi bi-chevron-right text-primary"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/sejarah" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small text-dark">
              <span><i class="bi bi-clock-history me-2 text-muted"></i>Sejarah Sekolah</span>
              <i class="bi bi-chevron-right text-muted"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/visi-misi" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small text-dark">
              <span><i class="bi bi-bullseye me-2 text-muted"></i>Visi &amp; Misi</span>
              <i class="bi bi-chevron-right text-muted"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/struktur-organisasi" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small text-dark">
              <span><i class="bi bi-diagram-3 me-2 text-muted"></i>Struktur Organisasi</span>
              <i class="bi bi-chevron-right text-muted"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/guru-tendik" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-0 small text-dark">
              <span><i class="bi bi-people me-2 text-muted"></i>Guru &amp; Tendik</span>
              <i class="bi bi-chevron-right text-muted"></i>
            </a>
          </div>
        </div>

        <!-- PPDB Callout -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-light text-center reveal-right border">
          <h2 class="h6 fw-bold text-primary mb-2">Pendaftaran Siswa Baru</h2>
          <p class="small text-muted mb-3">Informasi persyaratan dan alur pendaftaran PPDB Online.</p>
          <a href="<?= e(BASE_URL) ?>/ppdb/daftar" class="btn btn-primary btn-sm w-100 fw-semibold">
            Daftar PPDB Online
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
