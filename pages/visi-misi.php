<?php
/** pages/visi-misi.php — /profil/visi-misi */
$db   = getDB();
$vm   = getDB()->query("SELECT * FROM vision_mission ORDER BY id LIMIT 1")->fetch();

$pageTitle       = 'Visi & Misi';
$pageDescription = $vm ? excerpt($vm['vision'], 155) : '';
$canonicalPath   = '/profil/visi-misi';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';

/** The mission column stores one item per line ("1. ...\n2. ..."). */
$missionItems = $vm ? array_values(array_filter(array_map('trim', explode("\n", $vm['mission'])))) : [];
?>

<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/">Beranda</a></li>
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/profil">Profil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Visi &amp; Misi</li>
  </ol>
</nav>

<section class="section pt-4">
  <div class="container" style="max-width:860px;">
    <?php if ($vm): ?>
      <h1 class="section-title mb-3">Visi Sekolah</h1>
      <div class="fs-5 mb-5 article-body" style="line-height:1.8;"><?= cleanHtml($vm['vision']) ?></div>

      <h2 class="section-title mb-3">Misi Sekolah</h2>
      <div class="fs-6 mb-5 article-body" style="line-height:2;"><?= cleanHtml($vm['mission']) ?></div>

      <?php if (!empty($vm['goals'])): ?>
        <h2 class="section-title mb-3">Tujuan Sekolah</h2>
        <div class="article-body" style="line-height:1.8;"><?= cleanHtml($vm['goals']) ?></div>
      <?php endif; ?>
    <?php else: ?>
      <p class="text-muted">Konten visi &amp; misi belum tersedia.</p>
    <?php endif; ?>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
