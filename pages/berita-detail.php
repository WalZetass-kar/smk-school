<?php
/** pages/berita-detail.php — /berita/{slug} (router sets $_GET['slug']) */
$db   = getDB();
$slug = $_GET['slug'] ?? '';

$stmt = $db->prepare("SELECT n.*, c.name AS category_name, u.name AS author_name
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
    echo '<section class="section text-center"><div class="container"><h1 class="text-primary">404</h1><p class="section-subtitle mx-auto mb-4">Berita yang Anda cari tidak ditemukan.</p><a href="' . e(BASE_URL) . '/berita" class="btn btn-accent">Kembali ke Berita</a></div></section>';
    require BASE_PATH . '/includes/footer.php';
    exit;
}

// Count a view once per browser session per article (simple, no extra table needed).
$viewedKey = 'viewed_news_' . $article['id'];
if (empty($_SESSION[$viewedKey])) {
    $db->prepare("UPDATE news SET views = views + 1 WHERE id = ?")->execute([$article['id']]);
    $_SESSION[$viewedKey] = true;
    $article['views']++;
}

$relatedStmt = $db->prepare("SELECT * FROM news WHERE category_id = ? AND id != ? AND status = 'publish' ORDER BY published_at DESC LIMIT 3");
$relatedStmt->execute([$article['category_id'], $article['id']]);
$related = $relatedStmt->fetchAll();

$pageTitle       = $article['title'];
$pageDescription = $article['excerpt'] ?: excerpt($article['content'], 155);
$pageImage       = BASE_URL . '/' . $article['featured_image'];
$canonicalPath   = '/berita/' . $article['slug'];
$shareUrl        = BASE_URL . '/berita/' . $article['slug'];

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/">Beranda</a></li>
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/berita">Berita</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= e($article['title']) ?></li>
  </ol>
</nav>

<article class="section pt-3">
  <div class="container" style="max-width: 860px;">
    <?php if ($article['category_name']): ?><span class="berita-category-badge mb-3"><?= e($article['category_name']) ?></span><?php endif; ?>
    <h1 class="section-title mb-3"><?= e($article['title']) ?></h1>
    <div class="berita-meta mb-4">
      <span><i class="bi bi-person"></i> <?= e($article['author_name'] ?? 'Admin') ?></span>
      <span><i class="bi bi-calendar3"></i> <?= formatTanggalIndo($article['published_at']) ?></span>
      <span><i class="bi bi-eye"></i> <?= (int) $article['views'] ?> dilihat</span>
    </div>

    <img src="<?= e(BASE_URL . '/' . $article['featured_image']) ?>" alt="<?= e($article['title']) ?>" class="img-fluid rounded mb-4 w-100" style="max-height:420px; object-fit:cover;">

    <div class="article-body" style="line-height:1.9;"><?= cleanHtml($article['content']) ?></div>

    <div class="d-flex align-items-center gap-2 mt-5 pt-4 border-top">
      <span class="fw-semibold small text-muted me-2">Bagikan:</span>
      <a class="btn btn-sm btn-outline-primary-custom" target="_blank" rel="noopener"
         href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl) ?>"><i class="bi bi-facebook"></i></a>
      <a class="btn btn-sm btn-outline-primary-custom" target="_blank" rel="noopener"
         href="https://twitter.com/intent/tweet?url=<?= urlencode($shareUrl) ?>&text=<?= urlencode($article['title']) ?>"><i class="bi bi-twitter-x"></i></a>
      <a class="btn btn-sm btn-outline-primary-custom" target="_blank" rel="noopener"
         href="https://wa.me/?text=<?= urlencode($article['title'] . ' - ' . $shareUrl) ?>"><i class="bi bi-whatsapp"></i></a>
    </div>
  </div>
</article>

<?php if (!empty($related)): ?>
<section class="section section-alt pt-4">
  <div class="container">
    <h2 class="h5 text-primary mb-4">Berita Terkait</h2>
    <div class="row g-4">
      <?php foreach ($related as $r): ?>
        <div class="col-md-4">
          <div class="berita-card">
            <img src="<?= e(BASE_URL . '/' . $r['featured_image']) ?>" alt="<?= e($r['title']) ?>" class="berita-card-img">
            <div class="berita-card-body">
              <div class="berita-meta"><i class="bi bi-calendar3"></i> <?= formatTanggalIndo($r['published_at']) ?></div>
              <h3 class="berita-card-title"><?= e($r['title']) ?></h3>
              <a href="<?= e(BASE_URL) ?>/berita/<?= e($r['slug']) ?>" class="fw-semibold small">Baca Selengkapnya <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require BASE_PATH . '/includes/footer.php'; ?>
