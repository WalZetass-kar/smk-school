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
    <div class="text-center mx-auto mb-5 reveal-up" style="max-width:720px;">
      <h1 class="section-title mb-2">Visi, Misi &amp; Tujuan</h1>
      <p class="section-subtitle mx-auto">Arah strategis dan komitmen mutu <strong><?= e($profile['school_name'] ?? 'Sekolah') ?></strong> dalam membina insan vokasi yang kompeten dan berkarakter.</p>
    </div>

    <!-- Main Content Layout -->
    <div class="row g-4 g-lg-5">
      <!-- Left Column: Unified Profile Document -->
      <div class="col-lg-8 reveal-left">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
          
          <!-- 1. VISI SEKOLAH -->
          <div class="mb-5">
            <div class="d-flex align-items-center gap-2 mb-3">
              <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1.5 rounded-pill fw-bold" style="font-size: 11.5px; letter-spacing: 0.05em;">
                VISI PENDIDIKAN
              </span>
            </div>
            <div class="p-4 rounded-3 bg-light border-start border-4 border-primary">
              <p class="fs-5 fw-bold text-dark mb-0" style="line-height: 1.65; color: #1e293b;">
                &ldquo;<?= cleanHtml($vm['vision'] ?? 'Menjadi lembaga pendidikan kejuruan unggulan yang menghasilkan lulusan kompeten, berkarakter, dan berdaya saing global.') ?>&rdquo;
              </p>
            </div>
          </div>

          <!-- 2. MISI SEKOLAH -->
          <div class="mb-5">
            <h2 class="h5 fw-bold text-primary mb-3 pb-2 border-bottom">Misi Sekolah</h2>
            
            <?php if (!empty($missionItems)): ?>
              <div class="d-flex flex-column gap-3">
                <?php foreach ($missionItems as $idx => $mText): ?>
                  <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-light bg-opacity-50 border">
                    <span class="badge bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; font-size: 13px;">
                      <?= $idx + 1 ?>
                    </span>
                    <p class="mb-0 text-dark" style="line-height: 1.65; font-size: 0.96rem;">
                      <?= e($mText) ?>
                    </p>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="article-body text-muted" style="line-height: 1.9;">
                <?= cleanHtml($vm['mission'] ?? '') ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- 3. TUJUAN PENDIDIKAN & PROFIL LULUSAN (BMW) -->
          <div class="mb-5">
            <h2 class="h5 fw-bold text-primary mb-3 pb-2 border-bottom">Tujuan Pendidikan &amp; Profil Lulusan (BMW)</h2>
            
            <?php if (!empty($vm['goals'])): ?>
              <div class="text-muted mb-4" style="line-height: 1.8; font-size: 0.95rem;">
                <?= cleanHtml($vm['goals']) ?>
              </div>
            <?php endif; ?>

            <div class="row g-3">
              <div class="col-md-4">
                <div class="p-3 p-md-4 rounded-3 bg-light border h-100">
                  <div class="fw-bold text-primary mb-1">1. Bekerja (B)</div>
                  <p class="small text-muted mb-0" style="line-height: 1.55;">Terserap di dunia industri dan usaha terkemuka dengan keahlian bersertifikat.</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="p-3 p-md-4 rounded-3 bg-light border h-100">
                  <div class="fw-bold text-primary mb-1">2. Melanjutkan (M)</div>
                  <p class="small text-muted mb-0" style="line-height: 1.55;">Mampu melanjutkan pendidikan ke perguruan tinggi dan politeknik vokasi negeri.</p>
                </div>
              </div>
              <div class="col-md-4">
                <div class="p-3 p-md-4 rounded-3 bg-light border h-100">
                  <div class="fw-bold text-primary mb-1">3. Wirausaha (W)</div>
                  <p class="small text-muted mb-0" style="line-height: 1.55;">Memiliki mental mandiri dan kecakapan membuka usaha secara profesional.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- 4. BUDAYA KERJA 5R (5S) -->
          <div class="pt-3 border-top">
            <h2 class="h6 fw-bold text-primary mb-2">Budaya Kerja &amp; Karakter Industri 5R</h2>
            <p class="small text-muted mb-3">Seluruh peserta didik dibiasakan menerapkan 5 prinsip kedisiplinan kerja industri:</p>
            <div class="d-flex flex-wrap gap-2">
              <span class="badge bg-light text-dark border px-3 py-2 fw-medium">1. Ringkas <em>(Seiri)</em></span>
              <span class="badge bg-light text-dark border px-3 py-2 fw-medium">2. Rapi <em>(Seiton)</em></span>
              <span class="badge bg-light text-dark border px-3 py-2 fw-medium">3. Resik <em>(Seiso)</em></span>
              <span class="badge bg-light text-dark border px-3 py-2 fw-medium">4. Rawat <em>(Seiketsu)</em></span>
              <span class="badge bg-light text-dark border px-3 py-2 fw-medium">5. Rajin <em>(Shitsuke)</em></span>
            </div>
          </div>

        </div>
      </div>

      <!-- Right Column: Sidebar Navigation & Information -->
      <div class="col-lg-4">
        <!-- Quick Profile Navigation -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white reveal-right">
          <h2 class="h6 fw-bold text-primary mb-3 pb-2 border-bottom"><i class="bi bi-list-nested me-2"></i>Navigasi Profil</h2>
          <div class="list-group list-group-flush">
            <a href="<?= e(BASE_URL) ?>/profil/sambutan" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small text-dark">
              <span><i class="bi bi-person-badge me-2 text-muted"></i>Sambutan Kepala Sekolah</span>
              <i class="bi bi-chevron-right text-muted"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/sejarah" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small text-dark">
              <span><i class="bi bi-clock-history me-2 text-muted"></i>Sejarah Sekolah</span>
              <i class="bi bi-chevron-right text-muted"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/visi-misi" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small fw-bold text-primary" style="background:#f8fafc; padding-left:10px !important; border-radius:6px;">
              <span><i class="bi bi-bullseye me-2 text-primary"></i>Visi &amp; Misi</span>
              <i class="bi bi-chevron-right text-primary"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/struktur-organisasi" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small text-dark">
              <span><i class="bi bi-diagram-3 me-2 text-muted"></i>Struktur Organisasi</span>
              <i class="bi bi-chevron-right text-muted"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/guru-tendik" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-0 small text-dark">
              <span><i class="bi bi-people me-2 text-muted"></i>Guru &amp; Tendik</span>
              <i class="bi bi-chevron-right text-muted"></i>
            </a>
          </div>
        </div>

        <!-- School Stats Summary Card -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white reveal-right">
          <h2 class="h6 fw-bold text-primary mb-3 pb-2 border-bottom"><i class="bi bi-award me-2"></i>Komitmen Mutu</h2>
          <div class="d-flex flex-column gap-2.5 small text-muted">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-primary"></i>
              <span>Kurikulum Berbasis Industri</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-primary"></i>
              <span>Sertifikasi Kompetensi BNSP &amp; LSP</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-primary"></i>
              <span>Program Magang Kerja Industri 6 Bulan</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-primary"></i>
              <span>Penyaluran Kerja via BKK Resmi</span>
            </div>
          </div>
        </div>

        <!-- PPDB Callout -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-light text-center reveal-right border">
          <h2 class="h6 fw-bold text-primary mb-2">Pendaftaran Siswa Baru</h2>
          <p class="small text-muted mb-3">Informasi persyaratan dan alur pendaftaran PPDB Online.</p>
          <a href="<?= e(BASE_URL) ?>/ppdb/daftar" class="btn btn-primary btn-sm w-100 fw-semibold">
            Daftar PPDB Online
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
