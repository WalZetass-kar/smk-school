<?php
/** pages/guru-tendik.php — /profil/guru-tendik?filter=guru|tendik */
$db     = getDB();
$filter = $_GET['filter'] ?? 'semua';
$filter = in_array($filter, ['guru', 'tendik'], true) ? $filter : 'semua';

if ($filter === 'semua') {
    $stmt = $db->prepare("SELECT * FROM teachers WHERE status = 'aktif' ORDER BY category ASC, name ASC");
    $stmt->execute();
} else {
    $stmt = $db->prepare("SELECT * FROM teachers WHERE status = 'aktif' AND category = ? ORDER BY name ASC");
    $stmt->execute([$filter]);
}
$teachers = $stmt->fetchAll();

$pageTitle       = 'Guru & Tendik';
$pageDescription = 'Daftar guru dan tenaga kependidikan sekolah.';
$canonicalPath   = '/profil/guru-tendik';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/">Beranda</a></li>
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/profil">Profil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Guru &amp; Tendik</li>
  </ol>
</nav>

<section class="section pt-4">
  <div class="container">
    <div class="text-center mx-auto mb-4 reveal-up" style="max-width:640px;">
      <h1 class="section-title">Guru &amp; Tenaga Kependidikan</h1>
    </div>

    <div class="d-flex justify-content-center gap-2 mb-5 reveal-up">
      <a href="?filter=semua" class="btn btn-sm <?= $filter === 'semua' ? 'btn-accent' : 'btn-outline-primary-custom' ?>">Semua</a>
      <a href="?filter=guru" class="btn btn-sm <?= $filter === 'guru' ? 'btn-accent' : 'btn-outline-primary-custom' ?>">Guru</a>
      <a href="?filter=tendik" class="btn btn-sm <?= $filter === 'tendik' ? 'btn-accent' : 'btn-outline-primary-custom' ?>">Tendik</a>
    </div>

    <div class="row g-4 reveal-group">
      <?php foreach ($teachers as $t): ?>
        <div class="col-lg-3 col-md-4 col-6">
          <div class="jurusan-card text-center hover-lift h-100 d-flex flex-column">
            <div class="img-skeleton-wrapper">
              <img src="<?= e(BASE_URL . '/' . $t['photo']) ?>" alt="<?= e($t['name']) ?>" class="jurusan-card-img" style="height:220px; object-fit:cover;">
            </div>
            <div class="jurusan-card-body d-flex flex-column flex-grow-1">
              <h3 class="jurusan-card-title" style="font-size:1rem;"><?= e($t['name']) ?></h3>
              <p class="jurusan-card-desc mb-1"><?= e($t['position'] ?? ucfirst($t['category'])) ?></p>
              <?php if ($t['subject']): ?><p class="text-muted small mb-0 mt-auto"><?= e($t['subject']) ?></p><?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($teachers)): ?>
        <p class="text-muted text-center">Belum ada data untuk kategori ini.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
