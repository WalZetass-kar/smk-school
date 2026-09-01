<?php
/**
 * pages/profil.php — /profil
 * Halaman Ringkasan Profil Sekolah & Navigasi Sub-Modul Profil
 */
$db      = getDB();
$profile = getSchoolProfile();

$pageTitle       = 'Profil Sekolah';
$pageDescription = $profile['description'] ?? ('Profil lengkap ' . e($profile['school_name'] ?? 'sekolah'));
$canonicalPath   = '/profil';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';

$links = [
    ['title' => 'Sambutan Kepala Sekolah', 'desc' => 'Pesan, visi kepemimpinan, dan komitmen mutu pendidikan dari kepala sekolah.', 'url' => '/profil/sambutan', 'icon' => 'bi-person-badge'],
    ['title' => 'Sejarah Sekolah', 'desc' => 'Rekam jejak berdirinya sekolah dan fase transformasi perkembangan hingga kini.', 'url' => '/profil/sejarah', 'icon' => 'bi-clock-history'],
    ['title' => 'Visi, Misi & Tujuan', 'desc' => 'Landasan filosofis, arah strategis, dan komitmen mutu pembinaan insan vokasi.', 'url' => '/profil/visi-misi', 'icon' => 'bi-bullseye'],
    ['title' => 'Struktur Organisasi', 'desc' => 'Susunan manajemen kepemimpinan, wewenang, dan tata kelola unit kerja sekolah.', 'url' => '/profil/struktur-organisasi', 'icon' => 'bi-diagram-3'],
    ['title' => 'Guru & Tenaga Kependidikan', 'desc' => 'Daftar pendidik profesional dan tenaga kependidikan berdedikasi tinggi.', 'url' => '/profil/guru-tendik', 'icon' => 'bi-people'],
];
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small mb-0">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
    <li class="breadcrumb-item active" aria-current="page">Profil</li>
  </ol>
</nav>

<section class="section pt-3 pb-5">
  <div class="container">
    <div class="text-center mx-auto mb-5 reveal-up" style="max-width:720px;">
      <h1 class="section-title mb-2">Profil <?= e($profile['school_name'] ?? 'Sekolah') ?></h1>
      <p class="section-subtitle mx-auto"><?= e($profile['description'] ?? 'Mengenal lebih dekat visi, sejarah, pimpinan, dan tata kelola sekolah kami.') ?></p>
    </div>

    <!-- 4 Key Facts Grid -->
    <div class="row g-3 mb-5 reveal-group">
      <div class="col-md-3 col-6">
        <div class="profil-fact-card">
          <div class="profil-fact-icon"><i class="bi bi-bank"></i></div>
          <div>
            <div class="profil-fact-label">NPSN</div>
            <div class="profil-fact-value"><?= e($profile['npsn'] ?? '-') ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="profil-fact-card">
          <div class="profil-fact-icon"><i class="bi bi-calendar3"></i></div>
          <div>
            <div class="profil-fact-label">Tahun Berdiri</div>
            <div class="profil-fact-value"><?= e($profile['founded_year'] ?? '-') ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="profil-fact-card">
          <div class="profil-fact-icon"><i class="bi bi-award"></i></div>
          <div>
            <div class="profil-fact-label">Akreditasi</div>
            <div class="profil-fact-value">Predikat <?= e($profile['accreditation'] ?? '-') ?></div>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="profil-fact-card">
          <div class="profil-fact-icon"><i class="bi bi-telephone"></i></div>
          <div>
            <div class="profil-fact-label">Kontak Resmi</div>
            <div class="profil-fact-value" style="font-size: 0.95rem;"><?= e($profile['phone'] ?? '-') ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- 5 Module Cards -->
    <div class="row g-4 reveal-group">
      <?php foreach ($links as $l): ?>
        <div class="col-lg-4 col-md-6">
          <a href="<?= e(BASE_URL . $l['url']) ?>" class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white hover-lift text-decoration-none d-flex flex-column border">
            <div class="p-2.5 rounded-3 bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width:48px;height:48px;">
              <i class="bi <?= e($l['icon']) ?> fs-4"></i>
            </div>
            <h2 class="h6 fw-bold text-primary mb-2"><?= e($l['title']) ?></h2>
            <p class="small text-muted mb-3 flex-grow-1" style="line-height: 1.6;"><?= e($l['desc']) ?></p>
            <div class="fw-semibold small text-primary d-flex align-items-center gap-1">
              <span>Buka Halaman</span>
              <i class="bi bi-arrow-right"></i>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>

