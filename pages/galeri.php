<?php
/**
 * pages/galeri.php — /galeri
 * Halaman Galeri Foto, Video, & Dokumentasi Aktivitas Sekolah
 */
$db       = getDB();
$profile  = getSchoolProfile();
$category = trim($_GET['category'] ?? '');
$page     = max(1, (int) ($_GET['page'] ?? 1));
$perPage  = 12;

$where  = ['1=1'];
$params = [];
if ($category !== '') {
    $where[] = 'gc.slug = ?';
    $params[] = $category;
}
$whereSql = implode(' AND ', $where);

// Count total photos
$countStmt = $db->prepare("SELECT COUNT(*) FROM galleries g LEFT JOIN gallery_categories gc ON gc.id = g.category_id WHERE $whereSql");
$countStmt->execute($params);
$totalRows  = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

// Fetch Galleries
$stmt = $db->prepare("SELECT g.*, gc.name AS category_name, gc.slug AS category_slug FROM galleries g
                       LEFT JOIN gallery_categories gc ON gc.id = g.category_id
                       WHERE $whereSql ORDER BY g.id DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$galleries = $stmt->fetchAll();

// Categories with Counts
$categories = $db->query("
    SELECT gc.*, COUNT(g.id) AS count 
    FROM gallery_categories gc 
    LEFT JOIN galleries g ON g.category_id = gc.id 
    GROUP BY gc.id 
    ORDER BY gc.name ASC
")->fetchAll();

$pageTitle       = 'Galeri Foto & Dokumentasi';
$pageDescription = 'Dokumentasi kegiatan, praktikum kejuruan, upacara, perlombaan, dan prestasi siswa ' . e($profile['school_name'] ?? 'sekolah') . '.';
$canonicalPath   = '/galeri';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';

$baseUrl = BASE_URL . '/galeri' . ($category ? '?category=' . urlencode($category) : '');
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small mb-0">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
    <li class="breadcrumb-item active" aria-current="page">Galeri Foto</li>
  </ol>
</nav>

<section class="section pt-3 pb-5">
  <div class="container">
    <!-- Header -->
    <div class="text-center mx-auto mb-5 reveal-up" style="max-width:740px;">
      <h1 class="section-title mb-2">Galeri Foto</h1>
      <p class="section-subtitle mx-auto">Kumpulan momen pembelajaran, praktik kejuruan, perlombaan prestasi, dan kegiatan siswa <strong><?= e($profile['school_name'] ?? 'SMK') ?></strong>.</p>
    </div>

    <!-- Category Filter Bar (Pills) -->
    <div class="filter-pills-nav reveal-up">
      <a href="<?= e(BASE_URL) ?>/galeri" class="filter-pill-btn <?= $category === '' ? 'active' : '' ?>">
        Semua Foto
      </a>
      <?php foreach ($categories as $c): ?>
        <a href="<?= e(BASE_URL) ?>/galeri?category=<?= urlencode($c['slug']) ?>" class="filter-pill-btn <?= $category === $c['slug'] ? 'active' : '' ?>">
          <?= e($c['name']) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Photo Gallery Grid -->
    <?php if (!empty($galleries)): ?>
      <div class="row g-3 g-md-4 reveal-group">
        <?php foreach ($galleries as $i => $g): ?>
          <div class="col-lg-3 col-md-4 col-6">
            <div class="galeri-item" role="button" data-bs-toggle="modal" data-bs-target="#galeriModal<?= $i ?>" title="<?= e($g['title']) ?>">
              <?php if (!empty($g['category_name'])): ?>
                <span class="galeri-item-badge">
                  <i class="bi bi-tag me-1"></i><?= e($g['category_name']) ?>
                </span>
              <?php endif; ?>

              <img src="<?= e(BASE_URL . '/' . $g['image']) ?>" alt="<?= e($g['title']) ?>" loading="lazy">

              <div class="galeri-item-caption">
                <span class="text-truncate me-2"><?= e($g['title']) ?></span>
                <span class="galeri-item-zoom-icon" title="Perbesar">
                  <i class="bi bi-arrows-fullscreen"></i>
                </span>
              </div>
            </div>
          </div>

          <!-- Lightbox Modal for Each Photo -->
          <div class="modal fade" id="galeriModal<?= $i ?>" tabindex="-1" aria-labelledby="galeriModalLabel<?= $i ?>" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
              <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-white">
                <div class="modal-header border-bottom py-3 px-4 bg-light d-flex align-items-center justify-content-between">
                  <div class="d-flex align-items-center gap-2">
                    <?php if (!empty($g['category_name'])): ?>
                      <span class="badge bg-primary px-2.5 py-1 rounded-pill small"><?= e($g['category_name']) ?></span>
                    <?php endif; ?>
                    <h5 class="modal-title h6 fw-bold mb-0 text-dark" id="galeriModalLabel<?= $i ?>"><?= e($g['title']) ?></h5>
                  </div>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body p-0 bg-dark text-center position-relative">
                  <img src="<?= e(BASE_URL . '/' . $g['image']) ?>" alt="<?= e($g['title']) ?>" class="img-fluid" style="max-height: 540px; width: 100%; object-fit: contain;">
                </div>

                <div class="modal-footer border-top bg-white p-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                  <div class="text-muted small text-start">
                    <?php if (!empty($g['description'])): ?>
                      <i class="bi bi-card-text me-1 text-primary"></i> <?= e($g['description']) ?>
                    <?php else: ?>
                      <i class="bi bi-calendar3 me-1 text-primary"></i> Dokumentasi Resmi <?= e($profile['school_name'] ?? 'SMK') ?>
                    <?php endif; ?>
                  </div>
                  <div class="d-flex gap-2">
                    <a href="<?= e(BASE_URL . '/' . $g['image']) ?>" target="_blank" download class="btn btn-sm btn-outline-primary-custom fw-semibold">
                      <i class="bi bi-download me-1"></i> Buka Gambar Penuh
                    </a>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <div class="mt-5 pt-2">
          <?= renderPagination($page, $totalPages, $baseUrl) ?>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <!-- Empty State -->
      <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-4 mx-auto" style="max-width: 540px;">
        <div class="p-3 bg-light rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width:70px; height:70px;">
          <i class="bi bi-images fs-2 text-muted"></i>
        </div>
        <h2 class="h5 fw-bold text-dark mb-1">Belum Ada Foto pada Kategori Ini</h2>
        <p class="text-muted small mb-3">Silakan pilih kategori lainnya atau lihat seluruh dokumentasi sekolah.</p>
        <div>
          <a href="<?= e(BASE_URL) ?>/galeri" class="btn btn-accent btn-sm px-3.5 fw-semibold">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Lihat Semua Foto
          </a>
        </div>
      </div>
    <?php endif; ?>

    <!-- Featured Video Section (if video exists in school profile) -->
    <?php if (!empty($profile['video_profile_url'])): ?>
      <?php
        // Extract YouTube ID
        $videoUrl = $profile['video_profile_url'];
        $videoId = null;
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $videoUrl, $matches)) {
            $videoId = $matches[1];
        }
      ?>
      <div class="mt-5 pt-4 reveal-up">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white p-4 p-md-5">
          <div class="row align-items-center g-4">
            <div class="col-lg-6">
              <h2 class="h4 fw-bold text-primary mb-2"><?= e($profile['video_title'] ?: 'Video Profil & Fasilitas Sekolah') ?></h2>
              <p class="small text-muted mb-4" style="line-height:1.7;">
                <?= e($profile['video_description'] ?: 'Saksikan tur visual mengenai sarana pembelajaran, laboratorium kejuruan, dan keseharian aktivitas siswa di sekolah kami.') ?>
              </p>
              <?php if (!empty($profile['video_features'])): ?>
                <div class="small text-muted mb-4">
                  <div class="fw-bold text-dark mb-1"><i class="bi bi-check2-all text-success me-1"></i>Sorotan Fasilitas:</div>
                  <p class="mb-0"><?= e($profile['video_features']) ?></p>
                </div>
              <?php endif; ?>
              <a href="<?= e($profile['video_profile_url']) ?>" target="_blank" rel="noopener" class="btn btn-accent fw-bold px-4">
                <i class="bi bi-youtube me-1"></i> Tonton di YouTube
              </a>
            </div>

            <div class="col-lg-6">
              <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-sm border bg-dark">
                <?php if ($videoId): ?>
                  <iframe src="https://www.youtube.com/embed/<?= e($videoId) ?>" title="<?= e($profile['video_title'] ?: 'Video Profil') ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                <?php else: ?>
                  <iframe src="<?= e($profile['video_profile_url']) ?>" title="Video Profil" allowfullscreen></iframe>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Social Media Follow Callout Banner -->
    <div class="mt-5 card border-0 shadow-sm rounded-4 p-4 p-md-5 text-white text-center reveal-up position-relative overflow-hidden" style="background-color: #0F2C59;">
      <div class="p-3 bg-white bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px;">
        <i class="bi bi-instagram fs-2 text-warning"></i>
      </div>
      <h2 class="h4 fw-bold text-white mb-2">Ikuti Update Dokumentasi Harian Kami</h2>
      <p class="small text-white-50 mx-auto mb-4" style="max-width: 600px;">Dapatkan siaran langsung, story harian, cuplikan karya siswa, dan liputan kegiatan ekstrakurikuler terbaru melalui saluran media sosial resmi kami.</p>
      <div class="d-flex justify-content-center flex-wrap gap-2">
        <a href="<?= e(BASE_URL) ?>/kontak" class="btn btn-accent fw-bold px-4 py-2.5 shadow-sm">
          <i class="bi bi-share me-1"></i> Kunjungi Akun Media Sosial
        </a>
        <a href="<?= e(BASE_URL) ?>/ppdb/daftar" class="btn btn-outline-light fw-semibold px-4 py-2.5">
          <i class="bi bi-mortarboard me-1"></i> Daftar PPDB Online
        </a>
      </div>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
