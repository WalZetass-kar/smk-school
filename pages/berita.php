<?php
/**
 * pages/berita.php — /berita
 * Halaman Portal Berita & Artikel Sekolah
 */
$db = getDB();
$profile = getSchoolProfile();

$search   = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$sort     = trim($_GET['sort'] ?? 'latest');
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = 6;

$where  = ["n.status = 'publish'"];
$params = [];

if ($search !== '') {
    $where[] = '(n.title LIKE ? OR n.excerpt LIKE ? OR n.content LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
if ($category !== '') {
    $where[] = 'c.slug = ?';
    $params[] = $category;
}
$whereSql = implode(' AND ', $where);

// Count total
$countStmt = $db->prepare("SELECT COUNT(*) FROM news n LEFT JOIN news_categories c ON c.id = n.category_id WHERE $whereSql");
$countStmt->execute($params);
$totalRows  = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

// Order By
$orderBy = match($sort) {
    'popular' => 'n.views DESC, n.published_at DESC',
    'oldest'  => 'n.published_at ASC',
    default   => 'n.published_at DESC'
};

$sql = "SELECT n.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name FROM news n
        LEFT JOIN news_categories c ON c.id = n.category_id
        LEFT JOIN users u ON u.id = n.author_id
        WHERE $whereSql
        ORDER BY $orderBy
        LIMIT $perPage OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$news = $stmt->fetchAll();

// Categories with counts
$categories = $db->query("
    SELECT c.*, COUNT(n.id) as count 
    FROM news_categories c 
    LEFT JOIN news n ON n.category_id = c.id AND n.status = 'publish' 
    GROUP BY c.id 
    ORDER BY c.name ASC
")->fetchAll();

// Top 5 Popular News
$popular = $db->query("
    SELECT n.id, n.title, n.slug, n.featured_image, n.published_at, n.views, c.name as category_name
    FROM news n 
    LEFT JOIN news_categories c ON c.id = n.category_id
    WHERE n.status = 'publish' 
    ORDER BY n.views DESC, n.id DESC 
    LIMIT 5
")->fetchAll();

// Featured latest headline (if page 1 and no search/category filter)
$featured = null;
if ($page === 1 && $search === '' && $category === '' && !empty($news)) {
    $featured = $news[0];
}

$pageTitle       = 'Berita & Artikel';
$pageDescription = 'Kumpulan berita, liputan kegiatan, pengumuman resmi, dan prestasi ' . e($profile['school_name'] ?? 'sekolah') . '.';
$canonicalPath   = '/berita';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';

// Base URL for pagination links
$baseParams = array_filter(['search' => $search, 'category' => $category, 'sort' => $sort !== 'latest' ? $sort : '']);
$baseUrl = '/berita' . ($baseParams ? '?' . http_build_query($baseParams) : '');
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small mb-0">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
    <li class="breadcrumb-item active" aria-current="page">Berita &amp; Artikel</li>
  </ol>
</nav>

<section class="section pt-3 pb-5">
  <div class="container">
    <!-- Header -->
    <div class="text-center mx-auto mb-4 reveal-up" style="max-width:700px;">
      <h1 class="section-title mb-2">Berita &amp; Artikel Sekolah</h1>
      <p class="section-subtitle mx-auto">Ikuti update kegiatan akademik, pengumuman penting, inovasi jurusan, dan catatan prestasi civitas akademika.</p>
    </div>

    <!-- Category Filter Bar (Pills) -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-2 border-bottom reveal-up">
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <a href="<?= e(BASE_URL) ?>/berita<?= $search ? '?search=' . urlencode($search) : '' ?>" class="filter-pill-btn <?= $category === '' ? 'active' : '' ?>">
          Semua Berita
        </a>
        <?php foreach ($categories as $c): ?>
          <?php $qs = array_filter(['search' => $search, 'category' => $c['slug']]); ?>
          <a href="<?= e(BASE_URL) ?>/berita?<?= e(http_build_query($qs)) ?>" class="filter-pill-btn <?= $category === $c['slug'] ? 'active' : '' ?>">
            <?= e($c['name']) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Quick Sort -->
      <div class="d-flex align-items-center gap-2">
        <span class="small text-muted d-none d-sm-inline">Urutkan:</span>
        <form method="get" class="d-inline">
          <?php if ($search): ?><input type="hidden" name="search" value="<?= e($search) ?>"><?php endif; ?>
          <?php if ($category): ?><input type="hidden" name="category" value="<?= e($category) ?>"><?php endif; ?>
          <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Terbaru</option>
            <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Terpopuler</option>
            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Terlama</option>
          </select>
        </form>
      </div>
    </div>

    <!-- Featured Headline Card (Shown on page 1 with no search filter) -->
    <?php if ($featured): ?>
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5 bg-white hover-lift reveal-up">
        <div class="row g-0 align-items-center">
          <div class="col-lg-7">
            <div class="position-relative" style="height: 340px; overflow:hidden;">
              <a href="<?= e(BASE_URL) ?>/berita/<?= e($featured['slug']) ?>">
                <img src="<?= e(BASE_URL . '/' . $featured['featured_image']) ?>" alt="<?= e($featured['title']) ?>" class="w-100 h-100" style="object-fit: cover; transition: transform 0.4s ease;">
              </a>
              <?php if (!empty($featured['category_name'])): ?>
                <span class="position-absolute top-0 start-0 m-3 badge bg-primary px-3 py-2 rounded-pill fw-semibold shadow-sm">
                  <i class="bi bi-tag-fill me-1"></i><?= e($featured['category_name']) ?>
                </span>
              <?php endif; ?>
              <span class="position-absolute bottom-0 start-0 m-3 badge bg-dark bg-opacity-75 text-white px-2.5 py-1.5 rounded-pill small">
                <i class="bi bi-star-fill text-warning me-1"></i>Berita Utama
              </span>
            </div>
          </div>
          <div class="col-lg-5 p-4 p-md-5 d-flex flex-column justify-content-between">
            <div>
              <div class="d-flex align-items-center gap-3 small text-muted mb-2">
                <span><i class="bi bi-calendar3 text-primary me-1"></i><?= formatTanggalIndo($featured['published_at']) ?></span>
                <span><i class="bi bi-eye text-primary me-1"></i><?= number_format($featured['views']) ?> dilihat</span>
              </div>
              <h2 class="h4 fw-bold mb-3">
                <a href="<?= e(BASE_URL) ?>/berita/<?= e($featured['slug']) ?>" class="text-primary text-decoration-none hover-primary">
                  <?= e($featured['title']) ?>
                </a>
              </h2>
              <p class="text-muted small mb-4 line-clamp-3">
                <?= e($featured['excerpt'] ?: mb_strimwidth(strip_tags($featured['content']), 0, 160, '...')) ?>
              </p>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-3 border-top">
              <div class="d-flex align-items-center gap-2">
                <div class="p-1 rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width:34px;height:34px;">
                  <i class="bi bi-person-fill"></i>
                </div>
                <span class="small fw-semibold text-dark"><?= e($featured['author_name'] ?? 'Admin') ?></span>
              </div>
              <a href="<?= e(BASE_URL) ?>/berita/<?= e($featured['slug']) ?>" class="btn btn-accent btn-sm px-3 fw-semibold">
                Baca Lengkap <i class="bi bi-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Main Content Layout -->
    <div class="row g-4 g-lg-5">
      <!-- Left Column: Articles Grid -->
      <div class="col-lg-8">
        <?php if (!empty($news)): ?>
          <div class="row g-4 reveal-group">
            <?php foreach ($news as $index => $n): ?>
              <?php 
                // Skip the first one if already shown in the headline banner on page 1 without filters
                if ($featured && $index === 0 && $page === 1 && $search === '' && $category === '') continue; 
              ?>
              <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column hover-lift overflow-hidden bg-white">
                  <!-- Thumbnail -->
                  <div class="position-relative" style="height: 200px; overflow:hidden;">
                    <a href="<?= e(BASE_URL) ?>/berita/<?= e($n['slug']) ?>">
                      <img src="<?= e(BASE_URL . '/' . $n['featured_image']) ?>" alt="<?= e($n['title']) ?>" class="w-100 h-100" style="object-fit: cover; transition: transform 0.4s ease;" loading="lazy">
                    </a>
                    <?php if (!empty($n['category_name'])): ?>
                      <span class="position-absolute top-0 start-0 m-3 badge bg-primary px-2.5 py-1 rounded-pill small fw-semibold shadow-sm">
                        <?= e($n['category_name']) ?>
                      </span>
                    <?php endif; ?>
                  </div>

                  <!-- Card Body -->
                  <div class="card-body p-4 d-flex flex-column flex-grow-1">
                    <div class="d-flex align-items-center gap-3 small text-muted mb-2" style="font-size: 12px;">
                      <span><i class="bi bi-calendar3 me-1 text-primary"></i><?= formatTanggalIndo($n['published_at']) ?></span>
                      <span><i class="bi bi-eye me-1 text-primary"></i><?= (int) $n['views'] ?></span>
                    </div>

                    <h3 class="h6 fw-bold mb-2 flex-shrink-0">
                      <a href="<?= e(BASE_URL) ?>/berita/<?= e($n['slug']) ?>" class="text-primary text-decoration-none hover-primary d-block" style="line-height:1.4;">
                        <?= e($n['title']) ?>
                      </a>
                    </h3>

                    <p class="text-muted small mb-4 flex-grow-1" style="line-height:1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                      <?= e($n['excerpt'] ?: mb_strimwidth(strip_tags($n['content']), 0, 120, '...')) ?>
                    </p>

                    <div class="pt-3 border-top d-flex align-items-center justify-content-between mt-auto">
                      <span class="small text-muted" style="font-size: 12px;">
                        <i class="bi bi-person me-1"></i><?= e($n['author_name'] ?? 'Admin') ?>
                      </span>
                      <a href="<?= e(BASE_URL) ?>/berita/<?= e($n['slug']) ?>" class="btn btn-sm btn-outline-primary-custom fw-semibold px-2.5">
                        Baca <i class="bi bi-arrow-right ms-1"></i>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Pagination -->
          <?php if ($totalPages > 1): ?>
            <div class="mt-5 pt-3">
              <?= renderPagination($page, $totalPages, $baseUrl) ?>
            </div>
          <?php endif; ?>

        <?php else: ?>
          <!-- Empty State -->
          <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-4">
            <div class="p-3 bg-light rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width:70px; height:70px;">
              <i class="bi bi-search fs-2 text-muted"></i>
            </div>
            <h2 class="h5 fw-bold text-dark mb-1">Tidak Ada Berita yang Ditemukan</h2>
            <p class="text-muted small mb-3">Tidak ada artikel yang cocok dengan kata kunci <strong>"<?= e($search) ?>"</strong> atau kategori yang Anda pilih.</p>
            <div>
              <a href="<?= e(BASE_URL) ?>/berita" class="btn btn-accent btn-sm px-3">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Pencarian
              </a>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Right Column: Sidebar Widgets -->
      <div class="col-lg-4">
        <!-- Search Widget -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white reveal-right">
          <h2 class="h6 fw-bold text-primary mb-3"><i class="bi bi-search me-2"></i>Pencarian Berita</h2>
          <form method="get" action="<?= e(BASE_URL) ?>/berita">
            <?php if ($category): ?><input type="hidden" name="category" value="<?= e($category) ?>"><?php endif; ?>
            <div class="input-group">
              <input type="text" name="search" class="form-control" placeholder="Ketik kata kunci artikel..." value="<?= e($search) ?>">
              <button class="btn btn-accent" type="submit"><i class="bi bi-search"></i></button>
            </div>
          </form>
        </div>

        <!-- Popular Articles Widget -->
        <?php if (!empty($popular)): ?>
          <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white reveal-right">
            <h2 class="h6 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
              <i class="bi bi-fire text-danger fs-5"></i> Berita Terpopuler
            </h2>
            <div class="d-flex flex-column gap-3">
              <?php foreach ($popular as $idx => $p): ?>
                <a href="<?= e(BASE_URL) ?>/berita/<?= e($p['slug']) ?>" class="d-flex align-items-center gap-3 text-decoration-none p-2 rounded-3 hover-lift bg-light border">
                  <div class="position-relative flex-shrink-0" style="width: 70px; height: 55px; border-radius: 8px; overflow: hidden;">
                    <img src="<?= e(BASE_URL . '/' . $p['featured_image']) ?>" alt="<?= e($p['title']) ?>" class="w-100 h-100" style="object-fit: cover;">
                    <span class="position-absolute top-0 start-0 badge bg-danger rounded-0 rounded-bottom-end small" style="font-size:10px;">#<?= $idx + 1 ?></span>
                  </div>
                  <div class="overflow-hidden flex-grow-1">
                    <span class="small fw-semibold text-dark d-block text-truncate mb-1" style="font-size: 13px;">
                      <?= e($p['title']) ?>
                    </span>
                    <div class="small text-muted d-flex align-items-center gap-2" style="font-size: 11px;">
                      <span><i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y', strtotime($p['published_at'])) ?></span>
                      <span><i class="bi bi-eye me-1"></i><?= number_format($p['views']) ?></span>
                    </div>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Categories List Widget -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white reveal-right">
          <h2 class="h6 fw-bold text-primary mb-3"><i class="bi bi-tags me-2"></i>Kategori Berita</h2>
          <div class="list-group list-group-flush">
            <a href="<?= e(BASE_URL) ?>/berita" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0 py-2 border-0 small <?= $category === '' ? 'fw-bold text-primary' : 'text-muted' ?>">
              <span><i class="bi bi-chevron-right me-2 text-primary"></i>Semua Kategori</span>
              <span class="badge bg-light text-dark border rounded-pill"><?= (int) $totalRows ?></span>
            </a>
            <?php foreach ($categories as $c): ?>
              <a href="<?= e(BASE_URL) ?>/berita?category=<?= urlencode($c['slug']) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0 py-2 border-0 small <?= $category === $c['slug'] ? 'fw-bold text-primary' : 'text-muted' ?>">
                <span><i class="bi bi-chevron-right me-2 text-primary"></i><?= e($c['name']) ?></span>
                <span class="badge bg-light text-dark border rounded-pill"><?= (int) $c['count'] ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- PPDB Banner Widget -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-primary text-white text-center reveal-right position-relative overflow-hidden" style="background: linear-gradient(135deg, #0F2C59 0%, #183E7A 100%);">
          <div class="p-3 bg-white bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:55px;height:55px;">
            <i class="bi bi-mortarboard fs-2 text-warning"></i>
          </div>
          <h2 class="h5 fw-bold text-white mb-2">Pendaftaran PPDB Online</h2>
          <p class="small text-white-50 mb-3">Penerimaan Peserta Didik Baru telah dibuka. Daftarkan diri Anda sekarang dan raih masa depan gemilang!</p>
          <a href="<?= e(BASE_URL) ?>/ppdb/daftar" class="btn btn-accent btn-sm w-100 fw-bold py-2">
            <i class="bi bi-arrow-right-circle me-1"></i> Daftar Sekarang
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
