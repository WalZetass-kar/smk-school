<?php
/**
 * pages/visi-misi.php — /profil/visi-misi
 * Halaman Visi, Misi, dan Tujuan Pendidikan Sekolah
 */
$db      = getDB();
$profile = getSchoolProfile();
$vm      = $db->query("SELECT * FROM vision_mission ORDER BY id LIMIT 1")->fetch();

$pageTitle       = 'Visi, Misi & Tujuan Pendidikan';
$pageDescription = $vm ? excerpt($vm['vision'], 155) : 'Visi, Misi dan Tujuan ' . e($profile['school_name'] ?? 'sekolah');
$canonicalPath   = '/profil/visi-misi';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';

// Parse mission text into array of clean items
$missionRaw = $vm['mission'] ?? '';
$missionLines = array_values(array_filter(array_map('trim', explode("\n", strip_tags($missionRaw)))));
$missionItems = [];
foreach ($missionLines as $line) {
    // Strip leading numbers like "1. ", "2. ", "- "
    $cleaned = preg_replace('/^(\d+[\.\)]|\-|\*)\s*/', '', $line);
    if (!empty($cleaned)) {
        $missionItems[] = $cleaned;
    }
}
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small mb-0">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/profil">Profil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Visi &amp; Misi</li>
  </ol>
</nav>

<section class="section pt-3 pb-5">
  <div class="container">
    <!-- Header -->
    <div class="text-center mx-auto mb-5 reveal-up" style="max-width:740px;">
      <h1 class="section-title mb-2">Visi, Misi &amp; Tujuan</h1>
      <p class="section-subtitle mx-auto">Landasan filosofis, arah strategis, dan komitmen mutu <strong><?= e($profile['school_name'] ?? 'SMK') ?></strong> dalam membina insan vokasi yang mandiri dan berkarakter unggul.</p>
    </div>

    <!-- Two-Column Layout -->
    <div class="row g-4 g-lg-5">
      <!-- Left Column: Vision, Mission Cards & Goals -->
      <div class="col-lg-8 reveal-left">
        <!-- 1. VISI SEKOLAH (Hero Gradient Card) -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5 text-white" style="background: linear-gradient(135deg, #0F2C59 0%, #183E7A 100%);">
          <div class="card-body p-4 p-md-5 position-relative">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold" style="font-size:12px;">
                <i class="bi bi-stars me-1"></i> VISI UTAMA SEKOLAH
              </span>
              <i class="bi bi-quote fs-1 text-white-50 opacity-25"></i>
            </div>
            
            <div class="fs-4 fw-bold mb-4 text-white" style="line-height: 1.6; letter-spacing: -0.01em;">
              "<?= cleanHtml($vm['vision'] ?? 'Menjadi lembaga pendidikan kejuruan unggulan yang menghasilkan lulusan kompeten, berkarakter, dan berdaya saing global.') ?>"
            </div>

            <div class="d-flex flex-wrap gap-2 pt-3 border-top border-white border-opacity-25">
              <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 small fw-normal"><i class="bi bi-check2-circle text-warning me-1"></i>Kompetensi Teruji</span>
              <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 small fw-normal"><i class="bi bi-check2-circle text-warning me-1"></i>Karakter &amp; Akhlak</span>
              <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 small fw-normal"><i class="bi bi-check2-circle text-warning me-1"></i>Daya Saing Global</span>
            </div>
          </div>
        </div>

        <!-- 2. MISI SEKOLAH (Interactive Numbered Cards Grid) -->
        <div class="mb-5">
          <h2 class="h4 fw-bold text-primary mb-3">Misi Sekolah</h2>
          <p class="text-muted small mb-4">Upaya konkret dan terstruktur yang dilaksanakan secara berkelanjutan guna mewujudkan visi besar sekolah.</p>

          <div class="row g-3">
            <?php 
            $missionIcons = ['bi-mortarboard-fill', 'bi-buildings', 'bi-person-check-fill', 'bi-award-fill', 'bi-laptop-fill', 'bi-globe'];
            if (!empty($missionItems)): 
              foreach ($missionItems as $idx => $mText): 
                $icon = $missionIcons[$idx % count($missionIcons)];
            ?>
              <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-3.5 p-md-4 h-100 bg-white hover-lift border-top border-3 border-primary">
                  <div class="d-flex align-items-start gap-3">
                    <div class="p-2.5 rounded-3 bg-primary bg-opacity-10 text-primary fw-bold fs-5 d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px; height:44px;">
                      <?= $idx + 1 ?>
                    </div>
                    <div class="flex-grow-1">
                      <div class="text-muted small mb-1"><i class="bi <?= $icon ?> text-accent me-1"></i>Pilar <?= $idx + 1 ?></div>
                      <p class="small text-dark fw-medium mb-0" style="line-height: 1.6;"><?= e($mText) ?></p>
                    </div>
                  </div>
                </div>
              </div>
            <?php 
              endforeach; 
            else: 
            ?>
              <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                  <div class="article-body" style="line-height:2;"><?= cleanHtml($vm['mission'] ?? '') ?></div>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- 3. TUJUAN PENDIDIKAN & TARGET KELULUSAN (BMW) -->
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
          <div class="d-flex align-items-center gap-2 mb-3 pb-3 border-bottom">
            <i class="bi bi-flag-fill text-accent fs-4"></i>
            <h2 class="h5 fw-bold text-primary mb-0">Tujuan &amp; Profil Lulusan (BMW)</h2>
          </div>

          <?php if (!empty($vm['goals'])): ?>
            <div class="article-body mb-4 text-muted small" style="line-height: 1.8;">
              <?= cleanHtml($vm['goals']) ?>
            </div>
          <?php endif; ?>

          <div class="row g-3 mt-1">
            <!-- B - Bekerja -->
            <div class="col-md-4">
              <div class="p-3.5 rounded-3 bg-light border text-center h-100">
                <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                  <i class="bi bi-briefcase-fill fs-4"></i>
                </div>
                <h3 class="h6 fw-bold text-primary mb-1">1. Bekerja (B)</h3>
                <p class="small text-muted mb-0">Lulusan langsung terserap di industri mitra skala nasional dan multinasional dengan standar kompetensi kerja.</p>
              </div>
            </div>

            <!-- M - Melanjutkan -->
            <div class="col-md-4">
              <div class="p-3.5 rounded-3 bg-light border text-center h-100">
                <div class="p-2 bg-warning bg-opacity-10 text-warning-emphasis rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                  <i class="bi bi-mortarboard-fill fs-4"></i>
                </div>
                <h3 class="h6 fw-bold text-primary mb-1">2. Melanjutkan (M)</h3>
                <p class="small text-muted mb-0">Mampu bersaing melanjutkan jenjang pendidikan ke perguruan tinggi negeri maupun politeknik vokasi unggulan.</p>
              </div>
            </div>

            <!-- W - Wirausaha -->
            <div class="col-md-4">
              <div class="p-3.5 rounded-3 bg-light border text-center h-100">
                <div class="p-2 bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                  <i class="bi bi-shop-window fs-4"></i>
                </div>
                <h3 class="h6 fw-bold text-primary mb-1">3. Wirausaha (W)</h3>
                <p class="small text-muted mb-0">Memiliki jiwa *technopreneurship* mandiri dalam membuka usaha dan menciptakan lapangan kerja baru.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- 4. BUDAYA KERJA INDUSTRI 5R / 5S -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white border-start border-4 border-warning">
          <h3 class="h6 fw-bold text-primary mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-gear-wide-connected text-warning"></i> Budaya Kerja &amp; Karakter Industri 5R (5S)
          </h3>
          <p class="small text-muted mb-3">Seluruh peserta didik dibiasakan menerapkan 5 prinsip kedisiplinan kerja industri:</p>
          <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">1. Ringkas <em>(Seiri)</em></span>
            <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">2. Rapi <em>(Seiton)</em></span>
            <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">3. Resik <em>(Seiso)</em></span>
            <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">4. Rawat <em>(Seiketsu)</em></span>
            <span class="badge bg-light text-dark border px-3 py-2 fw-semibold">5. Rajin <em>(Shitsuke)</em></span>
          </div>
        </div>
      </div>

      <!-- Right Column: Profile Navigation & Callout Widgets -->
      <div class="col-lg-4">
        <!-- Quick Profile Navigation -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white reveal-right">
          <h2 class="h6 fw-bold text-primary mb-3"><i class="bi bi-list-nested me-2"></i>Navigasi Profil</h2>
          <div class="list-group list-group-flush">
            <a href="<?= e(BASE_URL) ?>/profil/sambutan" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small text-dark">
              <span><i class="bi bi-person-badge me-2 text-primary"></i>Sambutan Kepala Sekolah</span>
              <i class="bi bi-chevron-right text-muted"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/sejarah" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small text-dark">
              <span><i class="bi bi-clock-history me-2 text-primary"></i>Sejarah Sekolah</span>
              <i class="bi bi-chevron-right text-muted"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/visi-misi" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small fw-bold text-primary" style="background:#f8fafc; padding-left:10px !important; border-radius:6px;">
              <span><i class="bi bi-bullseye me-2 text-primary"></i>Visi &amp; Misi</span>
              <i class="bi bi-chevron-right text-primary"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/struktur-organisasi" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small text-dark">
              <span><i class="bi bi-diagram-3 me-2 text-primary"></i>Struktur Organisasi</span>
              <i class="bi bi-chevron-right text-muted"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/guru-tendik" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-0 small text-dark">
              <span><i class="bi bi-people me-2 text-primary"></i>Guru &amp; Tendik</span>
              <i class="bi bi-chevron-right text-muted"></i>
            </a>
          </div>
        </div>

        <!-- School Stats Summary Card -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white reveal-right">
          <h2 class="h6 fw-bold text-primary mb-3"><i class="bi bi-award me-2"></i>Komitmen Mutu</h2>
          <div class="d-flex flex-column gap-2.5 small">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-success"></i>
              <span>Kurikulum Merdeka Berbasis Industri</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-success"></i>
              <span>Sertifikasi Kompetensi BNSP &amp; LSP-P1</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-success"></i>
              <span>Program Magang Kerja Industri 6 Bulan</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-success"></i>
              <span>Penyaluran Kerja melalui BKK Resmi</span>
            </div>
          </div>
        </div>

        <!-- PPDB Callout -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-primary text-white text-center reveal-right" style="background: linear-gradient(135deg, #0F2C59 0%, #183E7A 100%);">
          <div class="p-3 bg-white bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:55px;height:55px;">
            <i class="bi bi-mortarboard fs-2 text-warning"></i>
          </div>
          <h2 class="h5 fw-bold text-white mb-2">Raih Masa Depan Vokasi Bersama Kami</h2>
          <p class="small text-white-50 mb-3">Pendaftaran Peserta Didik Baru (PPDB Online) telah dibuka. Pilih jurusan impian Anda sekarang.</p>
          <a href="<?= e(BASE_URL) ?>/ppdb/daftar" class="btn btn-accent btn-sm w-100 fw-bold py-2">
            <i class="bi bi-arrow-right-circle me-1"></i> Daftar PPDB Online
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
