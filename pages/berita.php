<?php
/** pages/berita.php — /berita?search=...&category=...&page=N */
$db = getDB();

$search   = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = NEWS_PER_PAGE;

$where  = ["n.status = 'publish'"];
$params = [];

if ($search !== '') {
    $where[] = '(n.title LIKE ? OR n.excerpt LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
if ($category !== '') {
    $where[] = 'c.slug = ?';
    $params[] = $category;
}
$whereSql = implode(' AND ', $where);

$countStmt = $db->prepare("SELECT COUNT(*) FROM news n LEFT JOIN news_categories c ON c.id = n.category_id WHERE $whereSql");
$countStmt->execute($params);
$totalRows  = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

$sql = "SELECT n.*, c.name AS category_name, c.slug AS category_slug FROM news n
        LEFT JOIN news_categories c ON c.id = n.category_id
        WHERE $whereSql
        ORDER BY n.published_at DESC
        LIMIT $perPage OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$news = $stmt->fetchAll();

$categories = $db->query("SELECT * FROM news_categories ORDER BY name ASC")->fetchAll();
$popular    = $db->query("SELECT id, title, slug, published_at FROM news WHERE status = 'publish' ORDER BY views DESC LIMIT 5")->fetchAll();

$pageTitle       = 'Berita';
$pageDescription = 'Kumpulan berita dan kegiatan terbaru sekolah.';
$canonicalPath   = '/berita';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';

// Base URL for pagination links — keeps the current search/category filters.
$baseParams = array_filter(['search' => $search, 'category' => $category]);
$baseUrl = '/berita' . ($baseParams ? '?' . http_build_query($baseParams) : '');
?>

<section class="section pt-5">
  <div class="container">
    <div class="text-center mx-auto mb-5" style="max-width:640px;">
      <h1 class="section-title">Berita</h1>
    </div>

    <div class="row g-5">
      <div class="col-lg-8">
        <form method="get" class="row g-2 mb-4">
          <div class="col-8 col-md-9">
            <input type="text" name="search" class="form-control" placeholder="Cari berita..." value="<?= e($search) ?>">
          </div>
          <div class="col-4 col-md-3">
            <button type="submit" class="btn btn-accent w-100">Cari</button>
          </div>
        </form>

        <div class="d-flex flex-wrap gap-2 mb-4">
          <a href="<?= e(BASE_URL) ?>/berita<?= $search ? '?search=' . urlencode($search) : '' ?>" class="btn btn-sm <?= $category === '' ? 'btn-accent' : 'btn-outline-primary-custom' ?>">Semua Kategori</a>
          <?php foreach ($categories as $c): ?>
            <?php $qs = array_filter(['search' => $search, 'category' => $c['slug']]); ?>
            <a href="<?= e(BASE_URL) ?>/berita?<?= e(http_build_query($qs)) ?>" class="btn btn-sm <?= $category === $c['slug'] ? 'btn-accent' : 'btn-outline-primary-custom' ?>"><?= e($c['name']) ?></a>
          <?php endforeach; ?>
        </div>

        <?php if (empty($news)): ?>
          <p class="text-muted">Tidak ada berita yang ditemukan.</p>
        <?php endif; ?>

        <div class="row g-4 reveal-group">
          <?php foreach ($news as $n): ?>
            <div class="col-md-6">
              <div class="berita-card hover-lift h-100 d-flex flex-column">
                <div class="img-skeleton-wrapper">
                  <img src="<?= e(BASE_URL . '/' . $n['featured_image']) ?>" alt="<?= e($n['title']) ?>" class="berita-card-img">
                </div>
                <div class="berita-card-body d-flex flex-column flex-grow-1">
                  <?php if ($n['category_name']): ?><span class="berita-category-badge"><?= e($n['category_name']) ?></span><?php endif; ?>
                  <div class="berita-meta"><i class="bi bi-calendar3"></i> <?= formatTanggalIndo($n['published_at']) ?> <i class="bi bi-eye ms-2"></i> <?= (int) $n['views'] ?></div>
                  <h3 class="berita-card-title"><?= e($n['title']) ?></h3>
                  <p class="berita-card-excerpt flex-grow-1"><?= e($n['excerpt']) ?></p>
                  <a href="<?= e(BASE_URL) ?>/berita/<?= e($n['slug']) ?>" class="fw-semibold small">Baca Selengkapnya <i class="bi bi-arrow-right"></i></a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="mt-5"><?= renderPagination($page, $totalPages, $baseUrl) ?></div>
      </div>

      <div class="col-lg-4 reveal-right">
        <div class="jurusan-card p-4 sticky-top" style="top: 100px;">
          <h3 class="h6 text-primary mb-3"><i class="bi bi-fire text-danger me-1"></i>Berita Populer</h3>
          <?php foreach ($popular as $p): ?>
            <a href="<?= e(BASE_URL) ?>/berita/<?= e($p['slug']) ?>" class="d-block text-decoration-none mb-3 p-2 rounded hover-lift" style="background:#f8fafc;">
              <span class="small fw-semibold text-dark d-block"><?= e($p['title']) ?></span>
              <span class="text-muted" style="font-size:0.78rem;"><i class="bi bi-calendar3 me-1"></i><?= formatTanggalIndo($p['published_at']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
