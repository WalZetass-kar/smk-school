<?php
/**
 * pages/bkk-detail.php — /bkk/{slug}
 * Detail Lowongan Kerja / Magang
 */
$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    redirect(BASE_URL . '/bkk');
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM bkk_jobs WHERE slug = ? AND status = 'open' LIMIT 1");
$stmt->execute([$slug]);
$job = $stmt->fetch();

if (!$job) {
    http_response_code(404);
    $pageTitle = 'Lowongan Tidak Ditemukan';
    require BASE_PATH . '/includes/header.php';
    require BASE_PATH . '/includes/navbar.php';
    echo '<div class="container py-5 text-center my-5"><h2>Lowongan Tidak Ditemukan atau Sudah Ditutup</h2><a href="' . BASE_URL . '/bkk" class="btn btn-accent mt-3">Kembali ke BKK</a></div>';
    require BASE_PATH . '/includes/footer.php';
    exit;
}

// Increment views
$db->prepare("UPDATE bkk_jobs SET views = views + 1 WHERE id = ?")->execute([$job['id']]);

$pageTitle       = $job['title'] . ' — ' . $job['company_name'] . ' (BKK)';
$pageDescription = 'Lowongan ' . $job['title'] . ' di ' . $job['company_name'] . '. ' . mb_strimwidth(strip_tags($job['description']), 0, 150, '...');
$canonicalPath   = '/bkk/' . $job['slug'];

$typeBadge = match($job['job_type']) {
    'internship' => 'bg-info text-dark',
    'contract'   => 'bg-warning text-dark',
    'parttime'   => 'bg-secondary',
    default      => 'bg-primary'
};
$typeLabel = match($job['job_type']) {
    'internship' => 'Magang / Internship',
    'contract'   => 'Kontrak Kerja',
    'parttime'   => 'Paruh Waktu (Part Time)',
    default      => 'Full Time (Penuh Waktu)'
};

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/">Beranda</a></li>
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/bkk">BKK</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= e($job['title']) ?></li>
  </ol>
</nav>

<section class="section pt-3 pb-5">
  <div class="container">
    <div class="row g-5">
      <!-- Main Content -->
      <div class="col-lg-8 reveal-left">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4">
          <div class="d-flex flex-wrap align-items-center gap-4 border-bottom pb-4 mb-4">
            <div class="img-skeleton-wrapper rounded-3 p-3 bg-light d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
              <?php if (!empty($job['company_logo'])): ?>
                <img src="<?= e(BASE_URL . '/' . $job['company_logo']) ?>" alt="<?= e($job['company_name']) ?>" style="max-height: 60px; max-width: 60px; object-fit: contain;">
              <?php else: ?>
                <i class="bi bi-building fs-1 text-primary"></i>
              <?php endif; ?>
            </div>
            <div>
              <span class="badge <?= $typeBadge ?> mb-2"><?= $typeLabel ?></span>
              <h1 class="h3 fw-bold text-primary mb-1"><?= e($job['title']) ?></h1>
              <div class="fs-6 fw-semibold text-muted"><?= e($job['company_name']) ?></div>
            </div>
          </div>

          <h2 class="h5 fw-bold text-primary mb-3">Deskripsi Pekerjaan</h2>
          <div class="article-body mb-5">
            <?= cleanHtml(nl2br($job['description'])) ?>
          </div>

          <h2 class="h5 fw-bold text-primary mb-3">Kualifikasi &amp; Persyaratan</h2>
          <div class="article-body mb-5">
            <?= cleanHtml(nl2br($job['requirements'])) ?>
          </div>

          <?php if (!empty($job['apply_url'])): ?>
            <div class="p-4 rounded-3 bg-light border text-center">
              <h3 class="h6 fw-bold mb-2">Tertarik dengan posisi ini?</h3>
              <p class="text-muted small mb-3">Pastikan CV dan portofolio Anda sudah disiapkan dengan baik.</p>
              <?php if (str_starts_with($job['apply_url'], 'http') || str_starts_with($job['apply_url'], 'https')): ?>
                <a href="<?= e($job['apply_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-accent btn-lg px-5">
                  <i class="bi bi-send me-2"></i>Lamar Sekarang (Tautan Resmi)
                </a>
              <?php elseif (str_contains($job['apply_url'], '@')): ?>
                <a href="mailto:<?= e($job['apply_url']) ?>?subject=Lamaran%20Pekerjaan%20<?= urlencode($job['title']) ?>" class="btn btn-accent btn-lg px-5">
                  <i class="bi bi-envelope me-2"></i>Kirim Email Lamaran
                </a>
              <?php else: ?>
                <div class="alert alert-info mb-0">Kirim lamaran melalui: <strong><?= e($job['apply_url']) ?></strong></div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="col-lg-4 reveal-right">
        <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 100px;">
          <h3 class="h6 fw-bold text-primary mb-3 pb-2 border-bottom">Ringkasan Lowongan</h3>
          
          <ul class="list-unstyled d-flex flex-column gap-3 small mb-4">
            <li class="d-flex gap-3">
              <i class="bi bi-geo-alt text-danger fs-5"></i>
              <div>
                <div class="text-muted">Lokasi Penempatan:</div>
                <div class="fw-semibold text-dark"><?= e($job['location']) ?></div>
              </div>
            </li>
            <li class="d-flex gap-3">
              <i class="bi bi-briefcase text-primary fs-5"></i>
              <div>
                <div class="text-muted">Tipe Pekerjaan:</div>
                <div class="fw-semibold text-dark"><?= $typeLabel ?></div>
              </div>
            </li>
            <?php if ($job['salary']): ?>
            <li class="d-flex gap-3">
              <i class="bi bi-cash-coin text-success fs-5"></i>
              <div>
                <div class="text-muted">Perkiraan Gaji / Uang Saku:</div>
                <div class="fw-semibold text-success"><?= e($job['salary']) ?></div>
              </div>
            </li>
            <?php endif; ?>
            <li class="d-flex gap-3">
              <i class="bi bi-calendar-check text-warning fs-5"></i>
              <div>
                <div class="text-muted">Batas Akhir Pendaftaran:</div>
                <div class="fw-semibold text-dark"><?= $job['deadline'] ? formatTanggalIndo($job['deadline']) : 'Terbuka hingga terpenuhi' ?></div>
              </div>
            </li>
            <li class="d-flex gap-3">
              <i class="bi bi-clock-history text-secondary fs-5"></i>
              <div>
                <div class="text-muted">Dipublikasikan:</div>
                <div class="fw-semibold text-dark"><?= formatTanggalIndo($job['created_at']) ?></div>
              </div>
            </li>
          </ul>

          <a href="<?= e(BASE_URL) ?>/bkk" class="btn btn-outline-secondary w-100 btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Lihat Lowongan Lainnya
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
