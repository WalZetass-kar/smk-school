<?php
/** pages/guru-tendik.php — /profil/guru-tendik?filter=guru|tendik&q=... */
require_once __DIR__ . '/../config/config.php';
$db     = getDB();
$filter = $_GET['filter'] ?? 'semua';
$filter = in_array($filter, ['guru', 'tendik'], true) ? $filter : 'semua';
$q      = trim($_GET['q'] ?? '');

$where = ["status = 'aktif'"];
$params = [];

if ($filter !== 'semua') {
    $where[] = "category = ?";
    $params[] = $filter;
}

if ($q !== '') {
    $where[] = "(name LIKE ? OR subject LIKE ? OR position LIKE ?)";
    $term = "%$q%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$whereSql = implode(' AND ', $where);
$stmt = $db->prepare("SELECT * FROM teachers WHERE $whereSql ORDER BY category ASC, sort_order ASC, name ASC");
$stmt->execute($params);
$teachers = $stmt->fetchAll();

$pageTitle       = 'Guru & Tenaga Kependidikan';
$pageDescription = 'Daftar dewan guru dan tenaga kependidikan sekolah.';
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

<section class="section pt-3 pb-5">
  <div class="container">
    <div class="text-center mx-auto mb-4 reveal-up" style="max-width:680px;">
      <h1 class="section-title mb-2">Guru &amp; Tenaga Kependidikan</h1>
      <p class="section-subtitle mx-auto">Pendidik profesional dan staf berdedikasi tinggi yang membimbing putra-putri di <?= e($profile['school_name'] ?? 'sekolah kami') ?>.</p>
    </div>

    <!-- Search & Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white reveal-up">
      <form method="get" action="<?= e(BASE_URL) ?>/profil/guru-tendik" class="row g-2 align-items-center">
        <input type="hidden" name="filter" value="<?= e($filter) ?>">
        <div class="col-md-8 col-lg-9">
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Cari nama guru, mata pelajaran, atau jabatan..." value="<?= e($q) ?>">
          </div>
        </div>
        <div class="col-md-4 col-lg-3 d-flex gap-2">
          <button type="submit" class="btn btn-primary w-100 fw-semibold btn-sm py-2">
            <i class="bi bi-filter me-1"></i>Cari Guru
          </button>
          <?php if ($q !== '' || $filter !== 'semua'): ?>
            <a href="<?= e(BASE_URL) ?>/profil/guru-tendik" class="btn btn-outline-secondary btn-sm py-2" title="Reset Filter">
              <i class="bi bi-x-lg"></i>
            </a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <!-- Filter Category Pills -->
    <div class="d-flex justify-content-center gap-2 mb-4 reveal-up flex-wrap">
      <a href="?filter=semua<?= $q !== '' ? '&q=' . urlencode($q) : '' ?>" class="btn btn-sm rounded-pill px-3.5 <?= $filter === 'semua' ? 'btn-accent' : 'btn-outline-primary-custom' ?>">Semua Staf</a>
      <a href="?filter=guru<?= $q !== '' ? '&q=' . urlencode($q) : '' ?>" class="btn btn-sm rounded-pill px-3.5 <?= $filter === 'guru' ? 'btn-accent' : 'btn-outline-primary-custom' ?>">Dewan Guru</a>
      <a href="?filter=tendik<?= $q !== '' ? '&q=' . urlencode($q) : '' ?>" class="btn btn-sm rounded-pill px-3.5 <?= $filter === 'tendik' ? 'btn-accent' : 'btn-outline-primary-custom' ?>">Tenaga Kependidikan (TU)</a>
    </div>

    <!-- Teachers Grid -->
    <div class="row g-4 reveal-group">
      <?php foreach ($teachers as $t): ?>
        <?php 
          $hasPhoto = !empty($t['photo']) && file_exists(BASE_PATH . '/' . $t['photo']);
        ?>
        <div class="col-lg-3 col-md-4 col-6">
          <div class="card border-0 shadow-sm rounded-4 text-center hover-lift h-100 d-flex flex-column overflow-hidden bg-white">
            <div class="position-relative bg-light overflow-hidden" style="height:230px;">
              <?php if ($hasPhoto): ?>
                <img src="<?= e(BASE_URL . '/' . $t['photo']) ?>" alt="<?= e($t['name']) ?>" class="w-100 h-100" style="object-fit:cover; object-position: top center;" loading="lazy">
              <?php else: ?>
                <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-primary bg-opacity-10 text-primary">
                  <div class="rounded-circle bg-white shadow-xs p-3 mb-2 d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                    <i class="bi bi-person-fill display-6 text-primary"></i>
                  </div>
                  <span class="badge bg-white text-primary border small fw-bold">Foto Guru</span>
                </div>
              <?php endif; ?>
              <span class="position-absolute top-0 start-0 m-2.5 badge <?= $t['category'] === 'guru' ? 'bg-primary' : 'bg-secondary' ?> bg-opacity-90 rounded-pill px-2.5 py-1 small">
                <?= $t['category'] === 'guru' ? 'Guru' : 'Tendik' ?>
              </span>
            </div>
            <div class="p-3 d-flex flex-column flex-grow-1">
              <h3 class="h6 fw-bold text-dark mb-1" style="font-size:0.95rem; line-height:1.4;"><?= e($t['name']) ?></h3>
              <p class="text-primary small fw-semibold mb-1"><?= e($t['position'] ?: ($t['category'] === 'guru' ? 'Guru Mata Pelajaran' : 'Staf Administrasi')) ?></p>
              <?php if (!empty($t['subject'])): ?>
                <p class="text-muted small mb-0 mt-auto pt-2 border-top" style="font-size: 0.8rem;">
                  <i class="bi bi-book me-1"></i><?= e($t['subject']) ?>
                </p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

      <?php if (empty($teachers)): ?>
        <div class="col-12">
          <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-3 mx-auto" style="max-width: 500px;">
            <i class="bi bi-people display-4 text-muted mb-3"></i>
            <h5 class="fw-bold text-dark">Data Guru Tidak Ditemukan</h5>
            <p class="text-muted small mb-3">Tidak ditemukan data guru atau staf dengan kata kunci pencarian tersebut.</p>
            <div>
              <a href="<?= e(BASE_URL) ?>/profil/guru-tendik" class="btn btn-accent btn-sm px-3">Reset Pencarian</a>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
