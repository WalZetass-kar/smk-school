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
  <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="6000">
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
              <div class="row align-items-center">
                <div class="col-lg-8">
                  <h1><?= e($b['title']) ?></h1>
                  <p><?= e($b['description']) ?></p>
                  <div class="d-flex flex-wrap gap-3 mt-4">
                    <?php if (!empty($b['button_text'])): ?>
                      <a href="<?= e(BASE_URL . $b['button_url']) ?>" class="btn btn-accent btn-lg px-4 fw-bold">
                        <?= e($b['button_text']) ?> <i class="bi bi-arrow-right ms-1"></i>
                      </a>
                    <?php endif; ?>
                    <a href="<?= e(BASE_URL) ?>/ppdb/daftar" class="btn btn-outline-light btn-lg px-4 fw-semibold">
                      Daftar PPDB <i class="bi bi-mortarboard ms-1"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" aria-label="Sebelumnya">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" aria-label="Berikutnya">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
    </button>
  </div>
</section>

<!-- ================= SAMBUTAN KEPALA SEKOLAH ================= -->
<?php if ($principal): ?>
<section class="section">
  <div class="container">
    <div class="sambutan-card">
      <div class="row align-items-center g-4 g-lg-5">
        <div class="col-lg-4 col-md-5 reveal-left">
          <div class="sambutan-photo-frame">
            <img src="<?= e(BASE_URL . '/' . $principal['photo']) ?>" alt="<?= e($principal['principal_name']) ?>">
          </div>
        </div>
        <div class="col-lg-8 col-md-7 reveal-right">
          <div class="sambutan-quote-icon">
            <i class="bi bi-quote"></i>
          </div>
          <h2 class="section-title">Sambutan Kepala Sekolah</h2>
          <p class="text-muted fs-6 mb-4" style="line-height: 1.75;"><?= nl2br(e(mb_strimwidth($principal['content'], 0, 340, '...'))) ?></p>
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 pt-2 border-top">
            <div>
              <h5 class="mb-0 text-primary fw-bold"><?= e($principal['principal_name']) ?></h5>
              <span class="text-muted small fw-medium"><?= e($principal['position']) ?></span>
            </div>
            <a href="<?= e(BASE_URL) ?>/profil/sambutan" class="btn btn-outline-primary-custom btn-sm">
              Baca Sambutan Lengkap <i class="bi bi-arrow-right ms-1"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ================= PROFIL SEKOLAH RINGKAS ================= -->
<section class="section section-alt">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5 reveal-left">
        <h2 class="section-title">Mengenal <?= e($profile['school_name'] ?? '') ?></h2>
        <p class="section-subtitle mb-4"><?= e(mb_strimwidth($profile['description'] ?? '', 0, 240, '...')) ?></p>
        <a href="<?= e(BASE_URL) ?>/profil" class="btn btn-accent">
          Profil Lengkap Sekolah <i class="bi bi-arrow-right ms-1"></i>
        </a>
      </div>
      <div class="col-lg-7 reveal-right">
        <div class="row g-3 reveal-group">
          <div class="col-sm-6">
            <div class="profil-fact-card">
              <div class="profil-fact-icon"><i class="bi bi-bank"></i></div>
              <div>
                <div class="profil-fact-label">NPSN</div>
                <div class="profil-fact-value"><?= e($profile['npsn'] ?? '-') ?></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="profil-fact-card">
              <div class="profil-fact-icon"><i class="bi bi-calendar3"></i></div>
              <div>
                <div class="profil-fact-label">Tahun Berdiri</div>
                <div class="profil-fact-value"><?= e($profile['founded_year'] ?? '-') ?></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="profil-fact-card">
              <div class="profil-fact-icon"><i class="bi bi-award"></i></div>
              <div>
                <div class="profil-fact-label">Akreditasi</div>
                <div class="profil-fact-value">Predikat <?= e($profile['accreditation'] ?? '-') ?></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="profil-fact-card">
              <div class="profil-fact-icon"><i class="bi bi-geo-alt"></i></div>
              <div>
                <div class="profil-fact-label">Lokasi</div>
                <div class="profil-fact-value" style="font-size: 0.92rem;"><?= e(mb_strimwidth($profile['address'] ?? '-', 0, 30, '...')) ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ================= JURUSAN ================= -->
<section class="section">
  <div class="container">
    <div class="text-center mx-auto mb-5 reveal-up" style="max-width:640px;">
      <h2 class="section-title">Jurusan Unggulan</h2>
      <p class="section-subtitle mx-auto">Program keahlian terakreditasi yang siap mencetak lulusan terampil dan kompeten.</p>
    </div>
    <div class="row g-4 reveal-group">
      <?php foreach ($majors as $m): ?>
        <div class="col-lg-3 col-md-6">
          <div class="jurusan-card hover-lift d-flex flex-column">
            <div class="jurusan-card-img-wrap">
              <div class="jurusan-code-tag"><?= e($m['code']) ?></div>
              <img src="<?= e(BASE_URL . '/' . $m['image']) ?>" alt="<?= e($m['name']) ?>" class="jurusan-card-img">
            </div>
            <div class="jurusan-card-body d-flex flex-column flex-grow-1">
              <h3 class="jurusan-card-title"><?= e($m['name']) ?></h3>
              <p class="jurusan-card-desc flex-grow-1"><?= e($m['short_description']) ?></p>
              <a href="<?= e(BASE_URL) ?>/jurusan/detail/<?= e($m['slug']) ?>" class="fw-semibold small text-secondary mt-2">
                Selengkapnya <i class="bi bi-arrow-right"></i>
              </a>
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
  $videoTitle = !empty($profile['video_title']) ? $profile['video_title'] : 'Video Profil & Lingkungan Sekolah';
  $videoDesc  = !empty($profile['video_description']) ? $profile['video_description'] : 'Saksikan suasana pembelajaran modern, bengkel kejuruan berstandar industri, serta berbagai sarana penunjang prestasi di sekolah kami.';
  $rawFeatures = !empty($profile['video_features']) ? explode("\n", str_replace("\r", "", $profile['video_features'])) : [
      'Kurikulum Berbasis Industri 4.0 & BNSP',
      'Laboratorium Komputer & Bengkel Praktik Lengkap',
      'Penyaluran Kerja Langsung via Bursa Kerja Khusus (BKK)'
  ];
?>
<section class="section section-video-tour">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5 reveal-left">
        <h2 class="section-title text-white"><?= e($videoTitle) ?></h2>
        <p class="text-white-50 mb-4" style="line-height: 1.7;"><?= nl2br(e($videoDesc)) ?></p>
        <div class="d-flex flex-column gap-3">
          <?php foreach ($rawFeatures as $feat): ?>
            <?php $feat = trim($feat); if ($feat === '') continue; ?>
            <div class="d-flex align-items-center gap-3 text-white">
              <i class="bi bi-check-circle-fill text-warning fs-5"></i>
              <span class="fw-medium"><?= e($feat) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-lg-7 reveal-right">
        <div class="video-container-card">
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
      <a href="<?= e(BASE_URL) ?>/berita" class="btn btn-outline-primary-custom">
        Semua Berita <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>
    <div class="row g-4 reveal-group">
      <?php foreach ($news as $n): ?>
        <div class="col-lg-4 col-md-6">
          <div class="berita-card hover-lift d-flex flex-column">
            <div class="berita-card-img-wrap">
              <img src="<?= e(BASE_URL . '/' . $n['featured_image']) ?>" alt="<?= e($n['title']) ?>" class="berita-card-img">
            </div>
            <div class="berita-card-body d-flex flex-column flex-grow-1">
              <?php if ($n['category_name']): ?>
                <span class="berita-category-badge align-self-start"><?= e($n['category_name']) ?></span>
              <?php endif; ?>
              <div class="berita-meta"><i class="bi bi-calendar3"></i> <?= formatTanggalIndo($n['published_at']) ?></div>
              <h3 class="berita-card-title">
                <a href="<?= e(BASE_URL) ?>/berita/<?= e($n['slug']) ?>"><?= e($n['title']) ?></a>
              </h3>
              <p class="berita-card-excerpt flex-grow-1"><?= e($n['excerpt']) ?></p>
              <a href="<?= e(BASE_URL) ?>/berita/<?= e($n['slug']) ?>" class="fw-semibold small text-secondary mt-2">
                Baca Selengkapnya <i class="bi bi-arrow-right"></i>
              </a>
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
      <p class="section-subtitle mx-auto">Kisah sukses dan pengalaman nyata lulusan kami yang telah berkiprah di dunia industri.</p>
    </div>
    <div class="row g-4 reveal-group">
      <?php foreach ($testimonials as $t): ?>
        <div class="col-lg-4 col-md-6">
          <div class="testimoni-card hover-lift">
            <div class="d-flex align-items-center gap-3">
              <img src="<?= e(BASE_URL . '/' . $t['photo']) ?>" alt="<?= e($t['name']) ?>" class="testimoni-photo">
              <div>
                <h6 class="testimoni-name"><?= e($t['name']) ?></h6>
                <div class="testimoni-role"><?= e($t['job']) ?> &middot; Angkatan <?= e($t['graduation_year']) ?></div>
              </div>
            </div>
            <p class="testimoni-text">&ldquo;<?= e($t['testimonial']) ?>&rdquo;</p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ================= PARTNER & MITRA INDUSTRI ================= -->
<?php if (!empty($partners)): ?>
<?php
  // Build continuous seamless loop for marquee
  $marqueeList = $partners;
  if (count($marqueeList) < 10) {
      while (count($marqueeList) < 12) {
          $marqueeList = array_merge($marqueeList, $partners);
      }
  }
  $marqueeLoop = array_merge($marqueeList, $marqueeList);
?>
<section class="section section-alt overflow-hidden py-5">
  <div class="container">
    <div class="text-center mx-auto mb-4 reveal-up" style="max-width:680px;">
      <h2 class="section-title mb-2">Mitra Kerja Sama Industri (DU/DI)</h2>
      <p class="section-subtitle mx-auto">Didukung oleh perusahaan terkemuka untuk magang siswa (PKL), sertifikasi keahlian, kelas industri, dan penyaluran kerja alumni.</p>
    </div>
  </div>

  <div class="partner-marquee-container reveal-fade">
    <div class="partner-marquee-track">
      <?php foreach ($marqueeLoop as $p): ?>
        <a href="<?= !empty($p['website']) ? e($p['website']) : '#' ?>" <?= !empty($p['website']) ? 'target="_blank" rel="noopener"' : '' ?> class="partner-logo-box" title="<?= e($p['name']) ?><?= !empty($p['description']) ? ' — ' . e($p['description']) : '' ?>">
          <?php if (!empty($p['logo']) && file_exists(BASE_PATH . '/' . $p['logo'])): ?>
            <img src="<?= e(BASE_URL . '/' . $p['logo']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
          <?php else: ?>
            <span class="small fw-bold text-dark text-center px-2 text-truncate"><?= e($p['name']) ?></span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ================= GALERI ================= -->
<section class="section">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-5 gap-3 reveal-up">
      <div>
        <h2 class="section-title mb-0">Galeri Kegiatan</h2>
      </div>
      <a href="<?= e(BASE_URL) ?>/galeri" class="btn btn-outline-primary-custom">
        Lihat Semua Foto <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>
    <div class="row g-3 reveal-group">
      <?php foreach ($galleries as $g): ?>
        <div class="col-lg-3 col-md-4 col-6">
          <a class="galeri-item" href="<?= e(BASE_URL . '/' . $g['image']) ?>" target="_blank">
            <img src="<?= e(BASE_URL . '/' . $g['image']) ?>" alt="<?= e($g['title']) ?>" loading="lazy">
            <span class="galeri-item-caption"><?= e($g['title']) ?></span>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ================= STATISTIK ================= -->
<section class="section statistik-section">
  <div class="container">
    <div class="row g-4 text-center reveal-group">
      <?php foreach ($stats as $s): ?>
        <div class="col-lg col-6">
          <div class="statistik-card">
            <i class="bi <?= e($s['icon']) ?> statistik-icon"></i>
            <div class="statistik-value"><span data-counter-target="<?= (int) $s['value'] ?>"><?= (int) $s['value'] ?></span>+</div>
            <div class="statistik-label"><?= e($s['title']) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
