<?php
/** pages/galeri.php — /galeri?category=... */
$db       = getDB();
$category = trim($_GET['category'] ?? '');
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = GALLERY_PER_PAGE;

$where  = ['1=1'];
$params = [];
if ($category !== '') {
    $where[] = 'gc.slug = ?';
    $params[] = $category;
}
$whereSql = implode(' AND ', $where);

$countStmt = $db->prepare("SELECT COUNT(*) FROM galleries g LEFT JOIN gallery_categories gc ON gc.id = g.category_id WHERE $whereSql");
$countStmt->execute($params);
$totalRows  = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

$stmt = $db->prepare("SELECT g.*, gc.name AS category_name FROM galleries g
                       LEFT JOIN gallery_categories gc ON gc.id = g.category_id
                       WHERE $whereSql ORDER BY g.id DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$galleries = $stmt->fetchAll();

$categories = $db->query("SELECT * FROM gallery_categories ORDER BY name ASC")->fetchAll();

$pageTitle       = 'Galeri';
$pageDescription = 'Dokumentasi kegiatan, praktik, dan prestasi siswa.';
$canonicalPath   = '/galeri';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';

$baseUrl = '/galeri' . ($category ? '?category=' . urlencode($category) : '');
?>

<section class="section pt-5">
  <div class="container">
    <div class="text-center mx-auto mb-4" style="max-width:640px;">
      <h1 class="section-title">Galeri</h1>
    </div>

    <div class="d-flex flex-wrap justify-content-center gap-2 mb-5">
      <a href="<?= e(BASE_URL) ?>/galeri" class="btn btn-sm <?= $category === '' ? 'btn-accent' : 'btn-outline-primary-custom' ?>">Semua</a>
      <?php foreach ($categories as $c): ?>
        <a href="<?= e(BASE_URL) ?>/galeri?category=<?= e($c['slug']) ?>" class="btn btn-sm <?= $category === $c['slug'] ? 'btn-accent' : 'btn-outline-primary-custom' ?>"><?= e($c['name']) ?></a>
      <?php endforeach; ?>
    </div>

    <?php if (empty($galleries)): ?>
      <p class="text-muted text-center">Belum ada foto pada kategori ini.</p>
    <?php endif; ?>

    <div class="row g-3 reveal-group">
      <?php foreach ($galleries as $i => $g): ?>
        <div class="col-lg-3 col-md-4 col-6">
          <a href="#" class="galeri-item img-skeleton-wrapper hover-lift" data-bs-toggle="modal" data-bs-target="#galeriModal<?= $i ?>">
            <img src="<?= e(BASE_URL . '/' . $g['image']) ?>" alt="<?= e($g['title']) ?>" loading="lazy">
            <span class="galeri-item-caption"><?= e($g['title']) ?></span>
          </a>
        </div>
        <div class="modal fade" id="galeriModal<?= $i ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
              <div class="modal-header border-0 bg-light">
                <h5 class="modal-title h6 fw-bold mb-0 text-primary"><?= e($g['title']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
              </div>
              <div class="img-skeleton-wrapper">
                <img src="<?= e(BASE_URL . '/' . $g['image']) ?>" alt="<?= e($g['title']) ?>" class="w-100">
              </div>
              <?php if ($g['description']): ?><div class="modal-body bg-white"><p class="text-muted mb-0 small"><?= e($g['description']) ?></p></div><?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mt-5"><?= renderPagination($page, $totalPages, $baseUrl) ?></div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
