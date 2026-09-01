<?php
/**
 * home.php — the "/" homepage. Included by index.php after config.php is loaded.
 */
$db      = getDB();
$profile = getSchoolProfile();

$pageTitle       = null; // use the site's own name as <title>
$pageDescription = $profile['description'] ?? '';
$canonicalPath   = '/';

$banners   = $db->query("SELECT * FROM banners WHERE status = 1 ORDER BY sort_order ASC")->fetchAll();
$principal = $db->query("SELECT * FROM principal_message ORDER BY id LIMIT 1")->fetch();
$majors    = $db->query("SELECT * FROM majors WHERE status = 1 ORDER BY id ASC LIMIT 4")->fetchAll();
$news      = $db->query("SELECT n.*, c.name AS category_name FROM news n
                          LEFT JOIN news_categories c ON c.id = n.category_id
                          WHERE n.status = 'publish'
                          ORDER BY n.published_at DESC LIMIT 6")->fetchAll();
$testimonials = $db->query("SELECT * FROM alumni_testimonials WHERE status = 1 ORDER BY id DESC LIMIT 6")->fetchAll();
$partners  = $db->query("SELECT * FROM partners WHERE status = 1 ORDER BY sort_order ASC")->fetchAll();
$galleries = $db->query("SELECT * FROM galleries ORDER BY id DESC LIMIT 8")->fetchAll();
$stats     = $db->query("SELECT * FROM statistics WHERE status = 1 ORDER BY sort_order ASC")->fetchAll();
$upcomingAgendas = $db->query("SELECT * FROM agendas WHERE status = 1 ORDER BY start_date ASC LIMIT 3")->fetchAll();

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<?= flashMessage() ?>

<!-- ================= HERO / BANNER SLIDER ================= -->
<section class="hero-slider">
  <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5500">
    <div class="carousel-indicators">
      <?php foreach ($banners as $i => $b): ?>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Slide <?= $i + 1 ?>"></button>
      <?php endforeach; ?>
    </div>
    <div class="carousel-inner">
      <?php foreach ($banners as $i => $b): ?>
        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
          <div class="hero-slide" style="background-image: url('<?= e(BASE_URL . '/' . $b['image']) ?>');">
            <div class="container">
              <h1><?= e($b['title']) ?></h1>
              <p><?= e($b['description']) ?></p>
              <?php if (!empty($b['button_text'])): ?>
                <a href="<?= e(BASE_URL . $b['button_url']) ?>" class="btn btn-accent btn-lg mt-2"><?= e($b['button_text']) ?></a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
    </button>
  </div>
</section>

<!-- ================= SAMBUTAN KEPALA SEKOLAH ================= -->
<?php if ($principal): ?>
<section class="section">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-4 reveal-left">
        <div class="sambutan-photo img-skeleton-wrapper shadow-soft">
          <img src="<?= e(BASE_URL . '/' . $principal['photo']) ?>" alt="<?= e($principal['principal_name']) ?>" class="img-fluid w-100">
        </div>
      </div>
      <div class="col-lg-8 reveal-right">
        <div class="sambutan-quote-mark">&ldquo;</div>
        <h2 class="section-title mt-n3">Sambutan Kepala Sekolah</h2>
        <p class="text-muted"><?= nl2br(e(mb_strimwidth($principal['content'], 0, 320, '...'))) ?></p>
        <p class="fw-bold text-primary mb-0"><?= e($principal['principal_name']) ?></p>
        <p class="text-muted small mb-3"><?= e($principal['position']) ?></p>
        <a href="<?= e(BASE_URL) ?>/profil/sambutan" class="btn btn-outline-primary-custom">Selengkapnya</a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ================= PROFIL SEKOLAH RINGKAS ================= -->
<section class="section section-alt">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6 reveal-left">
        <h2 class="section-title">Mengenal <?= e($profile['school_name'] ?? '') ?></h2>
        <p class="section-subtitle mb-4"><?= e(mb_strimwidth($profile['description'] ?? '', 0, 220, '...')) ?></p>
        <a href="<?= e(BASE_URL) ?>/profil" class="btn btn-accent">Lihat Profil Lengkap</a>
      </div>
      <div class="col-lg-6 reveal-right">
        <div class="row row-cols-1 row-cols-sm-2 g-0 reveal-group">
          <div class="col"><div class="profil-fact"><i class="bi bi-bank"></i><div><div class="profil-fact-label">NPSN</div><div class="profil-fact-value"><?= e($profile['npsn'] ?? '-') ?></div></div></div></div>
          <div class="col"><div class="profil-fact"><i class="bi bi-calendar3"></i><div><div class="profil-fact-label">Tahun Berdiri</div><div class="profil-fact-value"><?= e($profile['founded_year'] ?? '-') ?></div></div></div></div>
          <div class="col"><div class="profil-fact"><i class="bi bi-award"></i><div><div class="profil-fact-label">Akreditasi</div><div class="profil-fact-value"><?= e($profile['accreditation'] ?? '-') ?></div></div></div></div>
          <div class="col"><div class="profil-fact"><i class="bi bi-geo-alt"></i><div><div class="profil-fact-label">Alamat</div><div class="profil-fact-value" style="font-weight:500; font-size:0.9rem;"><?= e($profile['address'] ?? '-') ?></div></div></div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ================= JURUSAN ================= -->
<section class="section">
  <div class="container">
    <div class="text-center mx-auto mb-5 reveal-up" style="max-width:640px;">
      <h2 class="section-title">Jurusan Pilihan</h2>
      <p class="section-subtitle mx-auto">Empat program keahlian yang dirancang sesuai kebutuhan dunia usaha dan dunia industri saat ini.</p>
    </div>
    <div class="row g-4 reveal-group">
      <?php foreach ($majors as $m): ?>
        <div class="col-lg-3 col-md-6">
          <div class="jurusan-card hover-lift h-100 d-flex flex-column">
            <div class="position-relative img-skeleton-wrapper">
              <div class="jurusan-code-tag"><?= e($m['code']) ?></div>
              <img src="<?= e(BASE_URL . '/' . $m['image']) ?>" alt="<?= e($m['name']) ?>" class="jurusan-card-img">
            </div>
            <div class="jurusan-card-body d-flex flex-column flex-grow-1">
              <h3 class="jurusan-card-title"><?= e($m['name']) ?></h3>
              <p class="jurusan-card-desc flex-grow-1"><?= e($m['short_description']) ?></p>
              <a href="<?= e(BASE_URL) ?>/jurusan/detail/<?= e($m['slug']) ?>" class="fw-semibold small">Selengkapnya <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ================= VIDEO PROFIL SEKOLAH ================= -->
<?php if (!empty($profile['video_profile_url'])): ?>
<?php 
  $embedUrl = getYouTubeEmbedUrl($profile['video_profile_url']); 
  $videoTitle = !empty($profile['video_title']) ? $profile['video_title'] : 'Video Profil & Fasilitas Sekolah';
  $videoDesc  = !empty($profile['video_description']) ? $profile['video_description'] : 'Saksikan suasana pembelajaran modern, bengkel kejuruan berstandar industri, serta berbagai fasilitas penunjang prestasi di ' . ($profile['school_name'] ?? '');
  $rawFeatures = !empty($profile['video_features']) ? explode("\n", str_replace("\r", "", $profile['video_features'])) : [
      'Kurikulum Berbasis Industri 4.0',
      'Laboratorium Komputer & Bengkel Praktik Lengkap',
      'Sertifikasi Kompetensi BNSP & Penyaluran Kerja'
  ];
?>
<section class="section section-video-tour">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5 reveal-left">
        <h2 class="section-title text-white"><?= e($videoTitle) ?></h2>
        <p class="text-white-50 mb-4"><?= nl2br(e($videoDesc)) ?></p>
        <div class="d-flex flex-column gap-3">
          <?php foreach ($rawFeatures as $feat): ?>
            <?php $feat = trim($feat); if ($feat === '') continue; ?>
            <div class="d-flex align-items-center gap-3 text-white">
              <i class="bi bi-patch-check-fill text-warning fs-4"></i>
              <div><?= e($feat) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-lg-7 reveal-right">
        <div class="video-container-card shadow-lg rounded-4 overflow-hidden position-relative">
          <div class="ratio ratio-16x9">
            <iframe src="<?= e($embedUrl) ?>" title="<?= e($videoTitle) ?>" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($upcomingAgendas)): ?>
<!-- ================= AGENDA KEGIATAN TERDEKAT ================= -->
<section class="section">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3 reveal-up">
      <div>
        <h2 class="section-title mb-0">Agenda &amp; Kegiatan Sekolah</h2>
      </div>
      <a href="<?= e(BASE_URL) ?>/agenda" class="btn btn-outline-primary-custom">Semua Agenda</a>
    </div>

    <div class="row g-4 reveal-group">
      <?php foreach ($upcomingAgendas as $ag): ?>
        <?php
          $day = date('d', strtotime($ag['start_date']));
          $month = date('M', strtotime($ag['start_date']));
        ?>
        <div class="col-lg-4 col-md-6">
          <div class="card h-100 border shadow-sm rounded-4 p-3 bg-white hover-lift">
            <div class="d-flex gap-3 align-items-start">
              <div class="p-2 rounded-3 text-center text-white bg-primary flex-shrink-0" style="width:65px;">
                <span class="d-block fs-4 fw-bold lh-1"><?= e($day) ?></span>
                <span class="small text-uppercase fw-semibold" style="font-size:0.75rem;"><?= e($month) ?></span>
              </div>
              <div class="flex-grow-1 min-w-0">
                <div class="small text-muted mb-1"><i class="bi bi-clock me-1"></i><?= e($ag['time_info']) ?></div>
                <h3 class="h6 fw-bold text-dark mb-1 line-clamp-1">
                  <a href="<?= e(BASE_URL) ?>/agenda/<?= e($ag['slug']) ?>" class="text-dark text-decoration-none hover-primary">
                    <?= e($ag['title']) ?>
                  </a>
                </h3>
                <div class="small text-muted text-truncate"><i class="bi bi-geo-alt me-1 text-danger"></i><?= e($ag['location']) ?></div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ================= BERITA TERBARU ================= -->
<section class="section section-alt">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-5 gap-3 reveal-up">
      <div>
        <h2 class="section-title mb-0">Berita Terbaru</h2>
      </div>
      <a href="<?= e(BASE_URL) ?>/berita" class="btn btn-outline-primary-custom">Semua Berita</a>
    </div>
    <div class="row g-4 reveal-group">
      <?php foreach ($news as $n): ?>
        <div class="col-lg-4 col-md-6">
          <div class="berita-card hover-lift h-100 d-flex flex-column">
            <div class="img-skeleton-wrapper">
              <img src="<?= e(BASE_URL . '/' . $n['featured_image']) ?>" alt="<?= e($n['title']) ?>" class="berita-card-img">
            </div>
            <div class="berita-card-body d-flex flex-column flex-grow-1">
              <?php if ($n['category_name']): ?><span class="berita-category-badge"><?= e($n['category_name']) ?></span><?php endif; ?>
              <div class="berita-meta"><i class="bi bi-calendar3"></i> <?= formatTanggalIndo($n['published_at']) ?></div>
              <h3 class="berita-card-title"><?= e($n['title']) ?></h3>
              <p class="berita-card-excerpt flex-grow-1"><?= e($n['excerpt']) ?></p>
              <a href="<?= e(BASE_URL) ?>/berita/<?= e($n['slug']) ?>" class="fw-semibold small">Baca Selengkapnya <i class="bi bi-arrow-right"></i></a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ================= TESTIMONI ALUMNI ================= -->
<section class="section">
  <div class="container">
    <div class="text-center mx-auto mb-5 reveal-up" style="max-width:640px;">
      <h2 class="section-title">Apa Kata Alumni?</h2>
    </div>
    <div class="row g-4 reveal-group">
      <?php foreach ($testimonials as $t): ?>
        <div class="col-lg-4 col-md-6">
          <div class="testimoni-card hover-lift h-100">
            <div class="d-flex align-items-center gap-3">
              <div class="img-skeleton-wrapper rounded-circle">
                <img src="<?= e(BASE_URL . '/' . $t['photo']) ?>" alt="<?= e($t['name']) ?>" class="testimoni-photo">
              </div>
              <div>
                <p class="testimoni-name"><?= e($t['name']) ?></p>
                <p class="testimoni-role mb-0"><?= e($t['job']) ?> &middot; Alumni <?= e($t['graduation_year']) ?></p>
              </div>
            </div>
            <p class="testimoni-text">&ldquo;<?= e($t['testimonial']) ?>&rdquo;</p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ================= PARTNER & DUNIA INDUSTRI ================= -->
<section class="section section-alt">
  <div class="container">
    <div class="text-center mx-auto mb-5 reveal-up" style="max-width:640px;">
      <h2 class="section-title">Partner &amp; Dunia Industri</h2>
    </div>
    <div class="row g-4 justify-content-center reveal-group">
      <?php foreach ($partners as $p): ?>
        <div class="col-lg-2 col-md-3 col-4">
          <a href="<?= e($p['website'] ?? '#') ?>" target="_blank" rel="noopener" class="partner-logo-box hover-lift" title="<?= e($p['name']) ?>">
            <img src="<?= e(BASE_URL . '/' . $p['logo']) ?>" alt="<?= e($p['name']) ?>">
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ================= GALERI HOMEPAGE ================= -->
<section class="section">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-5 gap-3 reveal-up">
      <div>
        <h2 class="section-title mb-0">Galeri</h2>
      </div>
      <a href="<?= e(BASE_URL) ?>/galeri" class="btn btn-outline-primary-custom">Lihat Semua Galeri</a>
    </div>
    <div class="row g-3 reveal-group">
      <?php foreach ($galleries as $g): ?>
        <div class="col-lg-3 col-md-4 col-6">
          <a class="galeri-item img-skeleton-wrapper hover-lift" href="<?= e(BASE_URL . '/' . $g['image']) ?>" data-lightbox-caption="<?= e($g['title']) ?>">
            <img src="<?= e(BASE_URL . '/' . $g['image']) ?>" alt="<?= e($g['title']) ?>" loading="lazy">
            <span class="galeri-item-caption"><?= e($g['title']) ?></span>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ================= STATISTIK SEKOLAH ================= -->
<section class="section statistik-section">
  <div class="container">
    <div class="row g-4 text-center reveal-group">
      <?php foreach ($stats as $s): ?>
        <div class="col-lg col-6">
          <i class="bi <?= e($s['icon']) ?> statistik-icon"></i>
          <div class="statistik-value"><span data-counter-target="<?= (int) $s['value'] ?>">0</span>+</div>
          <div class="statistik-label"><?= e($s['title']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
