<?php
/** pages/sejarah.php — /profil/sejarah */
$db      = getDB();
$profile = getSchoolProfile();
$history = $db->query("SELECT * FROM history ORDER BY id LIMIT 1")->fetch();

$pageTitle       = 'Sejarah';
$pageDescription = $history ? excerpt($history['content'], 155) : '';
$canonicalPath   = '/profil/sejarah';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/">Beranda</a></li>
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/profil">Profil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Sejarah</li>
  </ol>
</nav>

<section class="section pt-4">
  <div class="container" style="max-width:860px;">
    <h1 class="section-title mb-4"><?= e($history['title'] ?? 'Sejarah') ?></h1>
    <?php if ($history): ?>
      <div class="article-body" style="line-height: 1.9;"><?= cleanHtml($history['content']) ?></div>
    <?php else: ?>
      <p class="text-muted">Konten sejarah belum tersedia.</p>
    <?php endif; ?>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
