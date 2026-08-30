<?php
/**
 * pages/ekstrakurikuler.php — /ekstrakurikuler
 * Daftar Kegiatan Ekstrakurikuler Siswa
 */
$db = getDB();
$profile = getSchoolProfile();

$catFilter = $_GET['category'] ?? '';
$where = ["status = 1"];
$params = [];

if ($catFilter !== '') {
    $where[] = "category = ?";
    $params[] = $catFilter;
}
$whereSql = implode(' AND ', $where);

$stmt = $db->prepare("SELECT * FROM extracurriculars WHERE $whereSql ORDER BY id ASC");
$stmt->execute($params);
$ekskuls = $stmt->fetchAll();

$categories = $db->query("SELECT DISTINCT category FROM extracurriculars WHERE status = 1 ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle       = 'Ekstrakurikuler Siswa';
$pageDescription = 'Daftar kegiatan pengembangan bakat dan minat siswa di ' . e($profile['school_name'] ?? '');
$canonicalPath   = '/ekstrakurikuler';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/">Beranda</a></li>
    <li class="breadcrumb-item text-muted">Kesiswaan</li>
    <li class="breadcrumb-item active" aria-current="page">Ekstrakurikuler</li>
  </ol>
</nav>

<section class="section pt-3 pb-5">
  <div class="container">
    <div class="text-center mx-auto mb-4 reveal-up" style="max-width:680px;">
      <h1 class="section-title">Ekstrakurikuler Siswa</h1>
      <p class="section-subtitle mx-auto">Wadah bagi siswa untuk mengasah bakat, kepemimpinan, kreativitas, dan sportivitas di luar jam pelajaran akademik.</p>
    </div>

    <!-- Category Badges -->
    <div class="d-flex flex-wrap justify-content-center gap-2 mb-5 reveal-up">
      <a href="<?= e(BASE_URL) ?>/ekstrakurikuler" class="btn btn-sm <?= $catFilter === '' ? 'btn-accent' : 'btn-outline-primary-custom' ?>">Semua Kategori</a>
      <?php foreach ($categories as $c): ?>
        <a href="<?= e(BASE_URL) ?>/ekstrakurikuler?category=<?= urlencode($c) ?>" class="btn btn-sm <?= $catFilter === $c ? 'btn-accent' : 'btn-outline-primary-custom' ?>"><?= e($c) ?></a>
      <?php endforeach; ?>
    </div>

    <!-- Grid Cards -->
    <?php if ($ekskuls): ?>
      <div class="row g-4 reveal-group">
        <?php foreach ($ekskuls as $i => $e): ?>
          <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column hover-lift overflow-hidden">
              <div class="position-relative img-skeleton-wrapper" style="height: 200px;">
                <img src="<?= e(BASE_URL . '/' . ($e['image'] ?: 'assets/images/galeri-1.jpg')) ?>" alt="<?= e($e['name']) ?>" class="w-100 h-100" style="object-fit: cover;">
                <span class="badge bg-primary position-absolute top-0 start-0 m-3 px-3 py-2 fs-8 shadow-sm">
                  <i class="bi <?= e($e['icon']) ?> me-1 text-warning"></i><?= e($e['category']) ?>
                </span>
              </div>
              <div class="card-body p-4 d-flex flex-column flex-grow-1">
                <h2 class="h5 fw-bold text-primary mb-2"><?= e($e['name']) ?></h2>
                <p class="text-muted small flex-grow-1 mb-3"><?= nl2br(e($e['description'])) ?></p>

                <div class="pt-3 border-top small text-muted">
                  <?php if ($e['schedule']): ?>
                    <div class="mb-1 d-flex align-items-center gap-2">
                      <i class="bi bi-clock text-accent"></i>
                      <span><strong>Jadwal:</strong> <?= e($e['schedule']) ?></span>
                    </div>
                  <?php endif; ?>
                  <?php if ($e['coach_name']): ?>
                    <div class="d-flex align-items-center gap-2">
                      <i class="bi bi-person-check text-secondary"></i>
                      <span><strong>Pembina:</strong> <?= e($e['coach_name']) ?></span>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-5">
        <i class="bi bi-activity fs-1 text-muted d-block mb-3"></i>
        <h2 class="h5 fw-bold">Belum Ada Ekstrakurikuler pada Kategori Ini</h2>
        <a href="<?= e(BASE_URL) ?>/ekstrakurikuler" class="btn btn-accent btn-sm mt-2">Lihat Semua</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
