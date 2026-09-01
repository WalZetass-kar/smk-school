<?php
/**
 * pages/sejarah.php — /profil/sejarah
 * Halaman Sejarah & Perkembangan Sekolah
 */
$db      = getDB();
$profile = getSchoolProfile();
$history = $db->query("SELECT * FROM history ORDER BY id LIMIT 1")->fetch();

$pageTitle       = 'Sejarah & Perkembangan Sekolah';
$pageDescription = $history ? excerpt($history['content'], 155) : 'Sejarah dan rekam jejak perjalanan ' . e($profile['school_name'] ?? 'sekolah');
$canonicalPath   = '/profil/sejarah';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small mb-0">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/profil">Profil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Sejarah</li>
  </ol>
</nav>

<section class="section pt-3 pb-5">
  <div class="container">
    <!-- Header -->
    <div class="text-center mx-auto mb-5 reveal-up" style="max-width:720px;">
      <h1 class="section-title mb-2"><?= e($history['title'] ?? 'Sejarah & Perkembangan Sekolah') ?></h1>
      <p class="section-subtitle mx-auto">Perjalanan dedikasi <strong><?= e($profile['school_name'] ?? 'Sekolah') ?></strong> dalam mencetak generasi vokasi yang unggul, terampil, dan berkarakter.</p>
    </div>

    <!-- Two-Column Layout -->
    <div class="row g-4 g-lg-5">
      <!-- Left Column: Main Narrative & Timeline -->
      <div class="col-lg-8 reveal-left">
        <!-- Main Narrative Box -->
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-5">
          <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">
            <h2 class="h5 fw-bold text-primary mb-0">Kilas Balik Pendirian</h2>
          </div>

          <?php if (!empty($history['content'])): ?>
            <div class="article-body" style="font-size: 1rem; line-height: 1.9; color: #334155;">
              <?= cleanHtml($history['content']) ?>
            </div>
          <?php else: ?>
            <p class="text-muted">Konten narasi sejarah sekolah sedang dalam proses pembaruan.</p>
          <?php endif; ?>
        </div>

        <!-- Historical Milestones (Linimasa Sejarah) -->
        <div class="mb-4">
          <h2 class="h5 fw-bold text-primary mb-2 pb-2 border-bottom">Linimasa Perkembangan Sekolah</h2>
          <p class="text-muted small mb-4">Tahapan fase penting dalam perjalanan transformasi mutu pendidikan kejuruan kami.</p>

          <div class="history-timeline">
            <!-- Timeline 1: Inisiasi -->
            <div class="history-timeline-item">
              <div class="history-timeline-dot"><i class="bi bi-flag-fill"></i></div>
              <div class="history-timeline-card">
                <span class="history-timeline-year">1998</span>
                <h3 class="h6 fw-bold text-primary mb-2">Fase Perintisan &amp; Pendirian Resmi</h3>
                <p class="small text-muted mb-0" style="line-height:1.7;">
                  Sekolah resmi didirikan dengan komitmen menghadirkan akses pendidikan kejuruan berkualitas dengan program keahlian perintis dan fasilitas workshop dasar.
                </p>
              </div>
            </div>

            <!-- Timeline 2: Pengembangan -->
            <div class="history-timeline-item">
              <div class="history-timeline-dot"><i class="bi bi-building"></i></div>
              <div class="history-timeline-card">
                <span class="history-timeline-year">2005</span>
                <h3 class="h6 fw-bold text-primary mb-2">Ekspansi Gedung &amp; Akreditasi Sekolah</h3>
                <p class="small text-muted mb-0" style="line-height:1.7;">
                  Pembangunan gedung ruang kelas baru, perpustakaan, dan laboratorium komputer mandiri, serta pencapaian akreditasi sekolah secara resmi.
                </p>
              </div>
            </div>

            <!-- Timeline 3: Kemitraan Industri -->
            <div class="history-timeline-item">
              <div class="history-timeline-dot"><i class="bi bi-briefcase-fill"></i></div>
              <div class="history-timeline-card">
                <span class="history-timeline-year">2014</span>
                <h3 class="h6 fw-bold text-primary mb-2">Transformasi Kurikulum &amp; Peresmian BKK</h3>
                <p class="small text-muted mb-0" style="line-height:1.7;">
                  Kerja sama strategis bersama asosiasi industri serta peresmian unit Bursa Kerja Khusus (BKK) guna menjamin kelancaran magang dan penyerapan alumni.
                </p>
              </div>
            </div>

            <!-- Timeline 4: Era Unggulan & Digital -->
            <div class="history-timeline-item">
              <div class="history-timeline-dot"><i class="bi bi-trophy-fill"></i></div>
              <div class="history-timeline-card">
                <span class="history-timeline-year">2020 – Sekarang</span>
                <h3 class="h6 fw-bold text-primary mb-2">Sekolah Rujukan Vokasi &amp; Era Digital</h3>
                <p class="small text-muted mb-0" style="line-height:1.7;">
                  Pencapaian <strong>Akreditasi A</strong>, digitalisasi layanan PPDB Online, dan standarisasi fasilitas bengkel praktik sesuai kebutuhan industri modern.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Sidebar Navigation & Identity -->
      <div class="col-lg-4">
        <!-- Quick Profile Navigation -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white reveal-right">
          <h2 class="h6 fw-bold text-primary mb-3 pb-2 border-bottom"><i class="bi bi-list-nested me-2"></i>Navigasi Profil</h2>
          <div class="list-group list-group-flush">
            <a href="<?= e(BASE_URL) ?>/profil/sambutan" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small text-dark">
              <span><i class="bi bi-person-badge me-2 text-muted"></i>Sambutan Kepala Sekolah</span>
              <i class="bi bi-chevron-right text-muted"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/sejarah" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small fw-bold text-primary" style="background:#f8fafc; padding-left:10px !important; border-radius:6px;">
              <span><i class="bi bi-clock-history me-2 text-primary"></i>Sejarah Sekolah</span>
              <i class="bi bi-chevron-right text-primary"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/visi-misi" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small text-dark">
              <span><i class="bi bi-bullseye me-2 text-muted"></i>Visi &amp; Misi</span>
              <i class="bi bi-chevron-right text-muted"></i>
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

        <!-- School Identity Card -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white reveal-right">
          <h2 class="h6 fw-bold text-primary mb-3 pb-2 border-bottom"><i class="bi bi-building-check me-2"></i>Identitas Sekolah</h2>
          <div class="d-flex flex-column gap-2 small text-muted">
            <div class="d-flex justify-content-between py-1.5 border-bottom">
              <span>Nama Sekolah:</span>
              <span class="fw-semibold text-dark text-end"><?= e($profile['school_name'] ?? 'SMK') ?></span>
            </div>
            <div class="d-flex justify-content-between py-1.5 border-bottom">
              <span>NPSN:</span>
              <span class="fw-semibold text-dark"><?= e($profile['npsn'] ?? '-') ?></span>
            </div>
            <div class="d-flex justify-content-between py-1.5 border-bottom">
              <span>Akreditasi:</span>
              <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">Predikat <?= e($profile['accreditation'] ?: 'A') ?></span>
            </div>
            <div class="d-flex justify-content-between py-1.5 border-bottom">
              <span>Tahun Berdiri:</span>
              <span class="fw-semibold text-dark"><?= e($profile['founded_year'] ?: '1998') ?></span>
            </div>
            <div class="d-flex justify-content-between py-1.5">
              <span>Status Lembaga:</span>
              <span class="fw-semibold text-dark">Sekolah Kejuruan</span>
            </div>
          </div>
        </div>

        <!-- PPDB Callout Widget -->
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
