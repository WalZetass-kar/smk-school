<?php
/** pages/profil.php — /profil */
$db      = getDB();
$profile = getSchoolProfile();

$pageTitle       = 'Profil Sekolah';
$pageDescription = $profile['description'] ?? '';
$canonicalPath   = '/profil';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';

$links = [
    ['title' => 'Sambutan Kepala Sekolah', 'desc' => 'Pesan dan harapan dari kepala sekolah untuk seluruh warga sekolah.', 'url' => '/profil/sambutan', 'icon' => 'bi-person-badge'],
    ['title' => 'Sejarah', 'desc' => 'Perjalanan berdirinya sekolah hingga saat ini.', 'url' => '/profil/sejarah', 'icon' => 'bi-clock-history'],
    ['title' => 'Visi & Misi', 'desc' => 'Arah dan tujuan pendidikan yang ingin dicapai.', 'url' => '/profil/visi-misi', 'icon' => 'bi-bullseye'],
    ['title' => 'Struktur Organisasi', 'desc' => 'Susunan kepemimpinan dan tata kelola sekolah.', 'url' => '/profil/struktur-organisasi', 'icon' => 'bi-diagram-3'],
    ['title' => 'Guru & Tendik', 'desc' => 'Daftar tenaga pendidik dan kependidikan sekolah.', 'url' => '/profil/guru-tendik', 'icon' => 'bi-people'],
];
?>

<section class="section">
  <div class="container">
    <div class="text-center mx-auto mb-5" style="max-width:720px;">
      <h1 class="section-title"><?= e($profile['school_name'] ?? '') ?></h1>
      <p class="section-subtitle mx-auto"><?= e($profile['description'] ?? '') ?></p>
    </div>

    <div class="row g-4 mb-5">
      <div class="col-md-3 col-6"><div class="profil-fact"><i class="bi bi-bank"></i><div><div class="profil-fact-label">NPSN</div><div class="profil-fact-value"><?= e($profile['npsn'] ?? '-') ?></div></div></div></div>
      <div class="col-md-3 col-6"><div class="profil-fact"><i class="bi bi-calendar3"></i><div><div class="profil-fact-label">Berdiri</div><div class="profil-fact-value"><?= e($profile['founded_year'] ?? '-') ?></div></div></div></div>
      <div class="col-md-3 col-6"><div class="profil-fact"><i class="bi bi-award"></i><div><div class="profil-fact-label">Akreditasi</div><div class="profil-fact-value"><?= e($profile['accreditation'] ?? '-') ?></div></div></div></div>
      <div class="col-md-3 col-6"><div class="profil-fact"><i class="bi bi-telephone"></i><div><div class="profil-fact-label">Telepon</div><div class="profil-fact-value"><?= e($profile['phone'] ?? '-') ?></div></div></div></div>
    </div>

    <div class="row g-4">
      <?php foreach ($links as $l): ?>
        <div class="col-lg-4 col-md-6">
          <a href="<?= e(BASE_URL . $l['url']) ?>" class="jurusan-card d-block text-decoration-none h-100">
            <div class="jurusan-card-body">
              <i class="bi <?= e($l['icon']) ?> mb-3" style="font-size:1.8rem; color: var(--color-accent);"></i>
              <h3 class="jurusan-card-title"><?= e($l['title']) ?></h3>
              <p class="jurusan-card-desc"><?= e($l['desc']) ?></p>
              <span class="fw-semibold small">Lihat detail <i class="bi bi-arrow-right"></i></span>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
