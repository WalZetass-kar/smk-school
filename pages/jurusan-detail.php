<?php
/** pages/jurusan-detail.php — /jurusan/detail/{slug} (router sets $_GET['slug']) */
$db   = getDB();
$slug = $_GET['slug'] ?? '';

$stmt = $db->prepare("SELECT * FROM majors WHERE slug = ? AND status = 1 LIMIT 1");
$stmt->execute([$slug]);
$major = $stmt->fetch();

if (!$major) {
    http_response_code(404);
    $pageTitle = 'Jurusan Tidak Ditemukan';
    require BASE_PATH . '/includes/header.php';
    require BASE_PATH . '/includes/navbar.php';
    echo '<section class="section text-center"><div class="container"><h1 class="text-primary">404</h1><p class="section-subtitle mx-auto mb-4">Jurusan yang Anda cari tidak ditemukan.</p><a href="' . e(BASE_URL) . '/jurusan" class="btn btn-accent">Kembali ke Jurusan</a></div></section>';
    require BASE_PATH . '/includes/footer.php';
    exit;
}

$otherMajors = $db->prepare("SELECT * FROM majors WHERE status = 1 AND id != ? ORDER BY RAND() LIMIT 3");
$otherMajors->execute([$major['id']]);
$otherMajors = $otherMajors->fetchAll();

$pageTitle       = $major['name'];
$pageDescription = excerpt($major['description'] ?? $major['short_description'], 155);
$pageImage       = BASE_URL . '/' . $major['image'];
$canonicalPath   = '/jurusan/detail/' . $major['slug'];

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/">Beranda</a></li>
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/jurusan">Jurusan</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= e($major['name']) ?></li>
  </ol>
</nav>

<section class="pt-3">
  <div class="container">
    <div class="ratio ratio-21x9 rounded overflow-hidden mb-4">
      <img src="<?= e(BASE_URL . '/' . $major['image']) ?>" alt="<?= e($major['name']) ?>" style="object-fit:cover; width:100%; height:100%;">
    </div>
  </div>
</section>

<section class="section pt-2">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-8">
        <div class="jurusan-code-tag position-relative d-inline-block mb-3" style="position:static !important;"><?= e($major['code']) ?></div>
        <h1 class="section-title"><?= e($major['name']) ?></h1>
        <p class="section-subtitle mb-4"><?= e($major['short_description']) ?></p>

        <h2 class="h5 text-primary mt-4">Deskripsi Program</h2>
        <div class="article-body mb-4" style="line-height:1.8;"><?= cleanHtml($major['description']) ?></div>

        <?php if (!empty($major['competency'])): ?>
          <h2 class="h5 text-primary mt-4">Kompetensi Keahlian</h2>
          <div class="article-body mb-4" style="line-height:1.8;"><?= cleanHtml($major['competency']) ?></div>
        <?php endif; ?>

        <?php if (!empty($major['career'])): ?>
          <h2 class="h5 text-primary mt-4">Prospek Kerja</h2>
          <div class="article-body mb-4" style="line-height:1.8;"><?= cleanHtml($major['career']) ?></div>
        <?php endif; ?>

        <?php if (!empty($major['facilities'])): ?>
          <h2 class="h5 text-primary mt-4">Fasilitas</h2>
          <div class="article-body mb-4" style="line-height:1.8;"><?= cleanHtml($major['facilities']) ?></div>
        <?php endif; ?>
      </div>

      <div class="col-lg-4">
        <div class="jurusan-card p-4">
          <h3 class="h6 text-primary mb-3">Jurusan Lainnya</h3>
          <?php foreach ($otherMajors as $om): ?>
            <a href="<?= e(BASE_URL) ?>/jurusan/detail/<?= e($om['slug']) ?>" class="d-flex align-items-center gap-2 text-decoration-none mb-3">
              <img src="<?= e(BASE_URL . '/' . $om['image']) ?>" alt="<?= e($om['name']) ?>" style="width:56px; height:56px; object-fit:cover; border-radius:8px;">
              <span class="small fw-semibold text-dark"><?= e($om['name']) ?></span>
            </a>
          <?php endforeach; ?>
          <a href="<?= e(BASE_URL) ?>/ppdb/daftar?jurusan=<?= (int)$major['id'] ?>" class="btn btn-accent w-100 mt-2">Daftar Jurusan Ini</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
