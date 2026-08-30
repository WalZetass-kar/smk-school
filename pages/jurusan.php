<?php
/** pages/jurusan.php — /jurusan */
$db     = getDB();
$majors = $db->query("SELECT * FROM majors WHERE status = 1 ORDER BY name ASC")->fetchAll();

$pageTitle       = 'Jurusan';
$pageDescription = 'Program keahlian yang tersedia di sekolah kami.';
$canonicalPath   = '/jurusan';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<section class="section pt-5">
  <div class="container">
    <div class="text-center mx-auto mb-5 reveal-up" style="max-width:640px;">
      <h1 class="section-title">Jurusan</h1>
      <p class="section-subtitle mx-auto">Pilih program keahlian yang sesuai dengan minat dan bakatmu.</p>
    </div>
    <div class="row g-4 reveal-group">
      <?php foreach ($majors as $m): ?>
        <div class="col-lg-3 col-md-6">
          <div class="jurusan-card hover-lift h-100 d-flex flex-column">
            <div class="position-relative img-skeleton-wrapper">
              <div class="jurusan-code-tag"><?= e($m['code']) ?></div>
              <img src="<?= e(BASE_URL . '/' . $m['image']) ?>" alt="<?= e($m['name']) ?>" class="jurusan-card-img">
            </div>
            <div class="jurusan-card-body d-flex flex-column flex-grow-1">
              <h3 class="jurusan-card-title"><?= e($m['name']) ?></h3>
              <p class="jurusan-card-desc flex-grow-1"><?= e($m['short_description']) ?></p>
              <a href="<?= e(BASE_URL) ?>/jurusan/detail/<?= e($m['slug']) ?>" class="fw-semibold small">Selengkapnya <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($majors)): ?>
        <p class="text-muted text-center">Belum ada jurusan yang ditampilkan.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
