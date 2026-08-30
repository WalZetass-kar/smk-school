<?php
/**
 * pages/berita-detail.php — /berita/{slug}
 * Halaman Detail Artikel / Berita Sekolah
 */
$db   = getDB();
$slug = $_GET['slug'] ?? '';

$stmt = $db->prepare("SELECT n.*, c.name AS category_name, c.slug AS category_slug, u.name AS author_name, u.photo AS author_photo
                       FROM news n
                       LEFT JOIN news_categories c ON c.id = n.category_id
                       LEFT JOIN users u ON u.id = n.author_id
                       WHERE n.slug = ? AND n.status = 'publish' LIMIT 1");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    $pageTitle = 'Berita Tidak Ditemukan';
    require BASE_PATH . '/includes/header.php';
    require BASE_PATH . '/includes/navbar.php';
    ?>
    <section class="section text-center py-5">
      <div class="container my-5">
        <div class="p-3 bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;">
          <i class="bi bi-journal-x fs-1 text-muted"></i>
        </div>
        <h1 class="h3 fw-bold text-primary mb-2">Berita Tidak Ditemukan</h1>
        <p class="text-muted small mx-auto mb-4" style="max-width: 460px;">Maaf, artikel atau berita yang Anda cari mungkin telah dihapus, diubah tautannya, atau belum dipublikasikan.</p>
        <a href="<?= e(BASE_URL) ?>/berita" class="btn btn-accent px-4 fw-semibold">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Berita
        </a>
      </div>
    </section>
    <?php
    require BASE_PATH . '/includes/footer.php';
    exit;
}

// Count view per browser session
$viewedKey = 'viewed_news_' . $article['id'];
if (empty($_SESSION[$viewedKey])) {
    $db->prepare("UPDATE news SET views = views + 1 WHERE id = ?")->execute([$article['id']]);
    $_SESSION[$viewedKey] = true;
    $article['views']++;
}

// Related News (from same category or latest other news)
$relatedStmt = $db->prepare("
    SELECT n.*, c.name AS category_name 
    FROM news n 
    LEFT JOIN news_categories c ON c.id = n.category_id 
    WHERE (n.category_id = ? OR 1=1) AND n.id != ? AND n.status = 'publish' 
    ORDER BY (n.category_id = ?) DESC, n.published_at DESC 
    LIMIT 3
");
$relatedStmt->execute([$article['category_id'] ?: 0, $article['id'], $article['category_id'] ?: 0]);
$related = $relatedStmt->fetchAll();

// Estimate Reading Time (approx 200 words/min)
$wordCount = str_word_count(strip_tags($article['content']));
$readMinutes = max(1, (int) ceil($wordCount / 200));

$pageTitle       = $article['title'];
$pageDescription = $article['excerpt'] ?: excerpt($article['content'], 155);
$pageImage       = BASE_URL . '/' . $article['featured_image'];
$canonicalPath   = '/berita/' . $article['slug'];
$shareUrl        = BASE_URL . '/berita/' . $article['slug'];

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small mb-0">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/berita">Berita</a></li>
    <?php if (!empty($article['category_name'])): ?>
      <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/berita?category=<?= urlencode($article['category_slug']) ?>"><?= e($article['category_name']) ?></a></li>
    <?php endif; ?>
    <li class="breadcrumb-item active text-truncate" style="max-width: 250px;" aria-current="page"><?= e($article['title']) ?></li>
  </ol>
</nav>

<article class="section pt-3 pb-5">
  <div class="container" style="max-width: 880px;">
    <!-- Category Badge -->
    <?php if (!empty($article['category_name'])): ?>
      <a href="<?= e(BASE_URL) ?>/berita?category=<?= urlencode($article['category_slug']) ?>" class="badge bg-primary text-white text-decoration-none px-3 py-2 rounded-pill fw-semibold mb-3 d-inline-block">
        <i class="bi bi-tag-fill me-1"></i><?= e($article['category_name']) ?>
      </a>
    <?php endif; ?>

    <!-- Title -->
    <h1 class="h2 fw-bold text-primary mb-3" style="line-height: 1.35;"><?= e($article['title']) ?></h1>

    <!-- Meta Info Bar -->
    <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 border">
      <div class="d-flex align-items-center gap-2">
        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; overflow:hidden;">
          <?php if (!empty($article['author_photo']) && file_exists(BASE_PATH . '/' . $article['author_photo'])): ?>
            <img src="<?= e(BASE_URL . '/' . $article['author_photo']) ?>" class="w-100 h-100" style="object-fit:cover;" alt="">
          <?php else: ?>
            <i class="bi bi-person-fill fs-5"></i>
          <?php endif; ?>
        </div>
        <div>
          <div class="small fw-bold text-dark"><?= e($article['author_name'] ?? 'Administrator') ?></div>
          <div class="small text-muted" style="font-size: 11.5px;">Penulis / Redaksi Sekolah</div>
        </div>
      </div>

      <div class="d-flex align-items-center gap-3 small text-muted">
        <span><i class="bi bi-calendar3 me-1 text-primary"></i><?= formatTanggalIndo($article['published_at']) ?></span>
        <span><i class="bi bi-clock me-1 text-primary"></i><?= $readMinutes ?> mnt baca</span>
        <span><i class="bi bi-eye me-1 text-primary"></i><?= number_format($article['views']) ?> views</span>
      </div>
    </div>

    <!-- Featured Image -->
    <?php if (!empty($article['featured_image']) && file_exists(BASE_PATH . '/' . $article['featured_image'])): ?>
      <div class="position-relative rounded-4 overflow-hidden shadow-sm mb-4 border bg-light">
        <img src="<?= e(BASE_URL . '/' . $article['featured_image']) ?>" alt="<?= e($article['title']) ?>" class="w-100" style="max-height: 480px; object-fit: cover;">
      </div>
    <?php endif; ?>

    <!-- Article Content -->
    <div class="article-body text-dark py-2" style="font-size: 1.05rem; line-height: 1.9;">
      <?= cleanHtml($article['content']) ?>
    </div>

    <!-- Share Buttons Bar -->
    <div class="p-3 p-md-4 rounded-4 bg-light border mt-5 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-share-fill text-primary"></i>
        <span class="small fw-bold text-dark">Bagikan Artikel:</span>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-sm btn-success d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill fw-semibold" target="_blank" rel="noopener"
           href="https://wa.me/?text=<?= urlencode($article['title'] . ' - ' . $shareUrl) ?>">
          <i class="bi bi-whatsapp"></i> WhatsApp
        </a>
        <a class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill fw-semibold" target="_blank" rel="noopener"
           href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl) ?>">
          <i class="bi bi-facebook"></i> Facebook
        </a>
        <a class="btn btn-sm btn-dark d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill fw-semibold" target="_blank" rel="noopener"
           href="https://twitter.com/intent/tweet?url=<?= urlencode($shareUrl) ?>&text=<?= urlencode($article['title']) ?>">
          <i class="bi bi-twitter-x"></i> X
        </a>
        <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill fw-semibold" id="copyLinkBtn">
          <i class="bi bi-link-45deg"></i> <span id="copyText">Salin Tautan</span>
        </button>
      </div>
    </div>

    <!-- Back to News Button -->
    <div class="mt-4 text-start">
      <a href="<?= e(BASE_URL) ?>/berita" class="btn btn-outline-primary-custom btn-sm px-3">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Semua Berita
      </a>
    </div>
  </div>
</article>

<!-- Related Articles Section -->
<?php if (!empty($related)): ?>
<section class="section section-alt pt-5 pb-5">
  <div class="container" style="max-width: 960px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h2 class="h5 fw-bold text-primary mb-0"><i class="bi bi-newspaper me-2"></i>Berita Terkait Lainnya</h2>
      <a href="<?= e(BASE_URL) ?>/berita" class="small fw-semibold text-primary text-decoration-none">
        Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>

    <div class="row g-4 reveal-group">
      <?php foreach ($related as $r): ?>
        <div class="col-md-4">
          <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column hover-lift overflow-hidden bg-white">
            <div class="position-relative" style="height: 160px; overflow:hidden;">
              <a href="<?= e(BASE_URL) ?>/berita/<?= e($r['slug']) ?>">
                <img src="<?= e(BASE_URL . '/' . $r['featured_image']) ?>" alt="<?= e($r['title']) ?>" class="w-100 h-100" style="object-fit: cover;">
              </a>
              <?php if (!empty($r['category_name'])): ?>
                <span class="position-absolute top-0 start-0 m-2 badge bg-primary small" style="font-size: 11px;">
                  <?= e($r['category_name']) ?>
                </span>
              <?php endif; ?>
            </div>
            <div class="card-body p-3 d-flex flex-column flex-grow-1">
              <div class="small text-muted mb-1" style="font-size: 11px;">
                <i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y', strtotime($r['published_at'])) ?>
              </div>
              <h3 class="h6 fw-bold mb-2 flex-grow-1">
                <a href="<?= e(BASE_URL) ?>/berita/<?= e($r['slug']) ?>" class="text-primary text-decoration-none hover-primary d-block" style="line-height:1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                  <?= e($r['title']) ?>
                </a>
              </h3>
              <a href="<?= e(BASE_URL) ?>/berita/<?= e($r['slug']) ?>" class="small fw-semibold text-primary text-decoration-none mt-2">
                Baca Selengkapnya <i class="bi bi-arrow-right"></i>
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<script>
// Copy Link to Clipboard
const copyBtn = document.getElementById('copyLinkBtn');
const copyText = document.getElementById('copyText');
if (copyBtn) {
  copyBtn.addEventListener('click', function() {
    navigator.clipboard.writeText('<?= e($shareUrl) ?>').then(function() {
      copyText.textContent = 'Tersalin!';
      copyBtn.classList.replace('btn-outline-secondary', 'btn-success');
      setTimeout(function() {
        copyText.textContent = 'Salin Tautan';
        copyBtn.classList.replace('btn-success', 'btn-outline-secondary');
      }, 2000);
    });
  });
}
</script>

<?php require BASE_PATH . '/includes/footer.php'; ?>
