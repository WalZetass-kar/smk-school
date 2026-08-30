<?php
/** pages/sambutan.php — /profil/sambutan */
$db        = getDB();
$profile   = getSchoolProfile();
$principal = $db->query("SELECT * FROM principal_message ORDER BY id LIMIT 1")->fetch();

$pageTitle       = 'Sambutan Kepala Sekolah';
$pageDescription = $principal ? excerpt($principal['content'], 155) : '';
$canonicalPath   = '/profil/sambutan';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/">Beranda</a></li>
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/profil">Profil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Sambutan Kepala Sekolah</li>
  </ol>
</nav>

<section class="section pt-4">
  <div class="container">
    <?php if ($principal): ?>
    <div class="row align-items-start g-5">
      <div class="col-lg-4">
        <div class="sambutan-photo">
          <img src="<?= e(BASE_URL . '/' . $principal['photo']) ?>" alt="<?= e($principal['principal_name']) ?>" class="img-fluid w-100">
        </div>
        <p class="fw-bold text-primary mt-3 mb-0"><?= e($principal['principal_name']) ?></p>
        <p class="text-muted small"><?= e($principal['position']) ?></p>
      </div>
      <div class="col-lg-8">
        <h1 class="section-title">Sambutan Kepala Sekolah</h1>
        <div class="mt-3 article-body" style="line-height: 1.9;"><?= cleanHtml($principal['content']) ?></div>
      </div>
    </div>
    <?php else: ?>
      <p class="text-muted">Konten sambutan belum tersedia.</p>
    <?php endif; ?>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
