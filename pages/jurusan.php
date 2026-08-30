<?php
/**
 * pages/jurusan.php — /jurusan
 * Halaman Daftar Seluruh Program Keahlian / Jurusan Sekolah
 */
$db      = getDB();
$profile = getSchoolProfile();
$majors  = $db->query("SELECT * FROM majors WHERE status = 1 ORDER BY id ASC")->fetchAll();

$pageTitle       = 'Program Keahlian (Jurusan)';
$pageDescription = 'Daftar pilihan jurusan dan program keahlian vokasi unggulan ' . e($profile['school_name'] ?? 'sekolah') . '.';
$canonicalPath   = '/jurusan';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small mb-0">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
    <li class="breadcrumb-item active" aria-current="page">Program Keahlian</li>
  </ol>
</nav>

<section class="section pt-3 pb-5">
  <div class="container">
    <!-- Header -->
    <div class="text-center mx-auto mb-5 reveal-up" style="max-width:740px;">
      <span class="section-badge"><i class="bi bi-mortarboard me-1"></i>Pilihan Masa Depan</span>
      <h1 class="section-title mb-2">Program Keahlian (Jurusan)</h1>
      <p class="section-subtitle mx-auto">Kurikulum berbasis industri yang dirancang mencetak tenaga ahli terampil, siap kerja, siap kuliah, dan siap berwirausaha mandiri.</p>
    </div>

    <!-- 4 Keunggulan Vokasi -->
    <div class="row g-3 mb-5 justify-content-center reveal-group">
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center bg-white h-100 hover-lift">
          <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:44px;height:44px;">
            <i class="bi bi-gear-wide-connected fs-4"></i>
          </div>
          <div class="fw-bold text-dark fs-6">70% Praktik</div>
          <div class="small text-muted" style="font-size:12px;">Metode Pembelajaran</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center bg-white h-100 hover-lift">
          <div class="p-2 bg-warning bg-opacity-10 text-warning-emphasis rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:44px;height:44px;">
            <i class="bi bi-award-fill fs-4"></i>
          </div>
          <div class="fw-bold text-dark fs-6">Sertifikasi BNSP</div>
          <div class="small text-muted" style="font-size:12px;">Uji Kompetensi Keahlian</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center bg-white h-100 hover-lift">
          <div class="p-2 bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:44px;height:44px;">
            <i class="bi bi-buildings fs-4"></i>
          </div>
          <div class="fw-bold text-dark fs-6">Magang Industri</div>
          <div class="small text-muted" style="font-size:12px;">Praktik Kerja Lapangan</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center bg-white h-100 hover-lift">
          <div class="p-2 bg-info bg-opacity-10 text-info-emphasis rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:44px;height:44px;">
            <i class="bi bi-briefcase-fill fs-4"></i>
          </div>
          <div class="fw-bold text-dark fs-6">Penyaluran BKK</div>
          <div class="small text-muted" style="font-size:12px;">Karier &amp; Rekrutmen Kerja</div>
        </div>
      </div>
    </div>

    <!-- Major Cards Grid -->
    <div class="row g-4 reveal-group">
      <?php foreach ($majors as $m): ?>
        <div class="col-lg-3 col-md-6">
          <div class="card border-0 shadow-sm rounded-4 hover-lift h-100 d-flex flex-column overflow-hidden bg-white">
            <div class="position-relative" style="height: 190px; overflow:hidden;">
              <span class="position-absolute top-0 start-0 m-3 badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold shadow-sm z-1">
                <?= e($m['code']) ?>
              </span>
              <a href="<?= e(BASE_URL) ?>/jurusan/detail/<?= e($m['slug']) ?>">
                <img src="<?= e(BASE_URL . '/' . $m['image']) ?>" alt="<?= e($m['name']) ?>" class="w-100 h-100" style="object-fit: cover; transition: transform 0.4s ease;" loading="lazy">
              </a>
            </div>
            <div class="card-body p-4 d-flex flex-column flex-grow-1">
              <h2 class="h6 fw-bold mb-2">
                <a href="<?= e(BASE_URL) ?>/jurusan/detail/<?= e($m['slug']) ?>" class="text-primary text-decoration-none hover-primary">
                  <?= e($m['name']) ?>
                </a>
              </h2>
              <p class="small text-muted mb-4 flex-grow-1" style="line-height:1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                <?= e($m['short_description']) ?>
              </p>
              <div class="pt-3 border-top mt-auto d-flex align-items-center justify-content-between">
                <a href="<?= e(BASE_URL) ?>/jurusan/detail/<?= e($m['slug']) ?>" class="btn btn-sm btn-outline-primary-custom fw-semibold">
                  Rincian Jurusan <i class="bi bi-arrow-right ms-1"></i>
                </a>
                <a href="<?= e(BASE_URL) ?>/ppdb/daftar" class="btn btn-sm btn-accent fw-bold px-2.5" title="Daftar PPDB">
                  Daftar
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($majors)): ?>
        <div class="col-12 text-center text-muted py-5">
          <i class="bi bi-mortarboard fs-1 text-muted d-block mb-2"></i>
          Belum ada program keahlian yang ditampilkan.
        </div>
      <?php endif; ?>
    </div>

    <!-- Bottom PPDB Banner -->
    <div class="mt-5 card border-0 shadow-sm rounded-4 p-4 p-md-5 text-white text-center reveal-up position-relative overflow-hidden" style="background: linear-gradient(135deg, #0F2C59 0%, #183E7A 100%);">
      <div class="p-3 bg-white bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px;">
        <i class="bi bi-mortarboard fs-2 text-warning"></i>
      </div>
      <h2 class="h4 fw-bold text-white mb-2">Tentukan Pilihan Jurusanmu Sekarang</h2>
      <p class="small text-white-50 mx-auto mb-4" style="max-width: 600px;">Pendaftaran Peserta Didik Baru (PPDB Online) dibuka dengan kuota terbatas untuk setiap program keahlian. Amankan kursi belajarmu sekarang!</p>
      <div>
        <a href="<?= e(BASE_URL) ?>/ppdb/daftar" class="btn btn-accent btn-lg px-4 py-2.5 fw-bold shadow-sm me-2 mb-2">
          <i class="bi bi-pencil-square me-1"></i> Daftar PPDB Online
        </a>
        <a href="<?= e(BASE_URL) ?>/kontak" class="btn btn-outline-light btn-lg px-4 py-2.5 fw-semibold mb-2">
          <i class="bi bi-headset me-1"></i> Konsultasi Jurusan
        </a>
      </div>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
