<?php
/** pages/organisasi.php — /profil/struktur-organisasi */
$db  = getDB();
$org = $db->query("SELECT * FROM organization ORDER BY id LIMIT 1")->fetch();

$pageTitle       = 'Struktur Organisasi';
$pageDescription = $org['description'] ?? '';
$canonicalPath   = '/profil/struktur-organisasi';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/">Beranda</a></li>
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/profil">Profil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Struktur Organisasi</li>
  </ol>
</nav>

<section class="section pt-4">
  <div class="container text-center" style="max-width: 960px;">
    <h1 class="section-title mb-3"><?= e($org['title'] ?? 'Struktur Organisasi') ?></h1>
    <?php if ($org && !empty($org['description'])): ?>
      <p class="section-subtitle mx-auto mb-4"><?= e($org['description']) ?></p>
    <?php endif; ?>
    <?php if ($org && !empty($org['image'])): ?>
      <img src="<?= e(BASE_URL . '/' . $org['image']) ?>" alt="Struktur Organisasi" class="img-fluid rounded shadow-sm">
    <?php else: ?>
      <p class="text-muted">Bagan struktur organisasi belum diunggah.</p>
    <?php endif; ?>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
