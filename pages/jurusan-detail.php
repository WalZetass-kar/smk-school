<?php
/**
 * pages/jurusan-detail.php — /jurusan/detail/{slug}
 * Halaman Detail Program Keahlian / Jurusan Sekolah
 */
$db   = getDB();
$profile = getSchoolProfile();
$slug = $_GET['slug'] ?? '';

$stmt = $db->prepare("SELECT * FROM majors WHERE slug = ? AND status = 1 LIMIT 1");
$stmt->execute([$slug]);
$major = $stmt->fetch();

if (!$major) {
    http_response_code(404);
    $pageTitle = 'Jurusan Tidak Ditemukan';
    require BASE_PATH . '/includes/header.php';
    require BASE_PATH . '/includes/navbar.php';
    ?>
    <section class="section text-center py-5">
      <div class="container my-5">
        <div class="p-3 bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;">
          <i class="bi bi-mortarboard fs-1 text-muted"></i>
        </div>
        <h1 class="h3 fw-bold text-primary mb-2">Program Keahlian Tidak Ditemukan</h1>
        <p class="text-muted small mx-auto mb-4" style="max-width: 460px;">Maaf, jurusan yang Anda cari tidak tersedia atau sedang dinonaktifkan.</p>
        <a href="<?= e(BASE_URL) ?>/jurusan" class="btn btn-accent px-4 fw-semibold">
          <i class="bi bi-arrow-left me-1"></i> Lihat Semua Jurusan
        </a>
      </div>
    </section>
    <?php
    require BASE_PATH . '/includes/footer.php';
    exit;
}

// Other Majors for Sidebar
$otherMajors = $db->prepare("SELECT * FROM majors WHERE status = 1 AND id != ? ORDER BY id ASC");
$otherMajors->execute([$major['id']]);
$otherMajors = $otherMajors->fetchAll();

// Parse competencies into array
$competenciesRaw = $major['competency'] ?? '';
$competenciesList = array_values(array_filter(array_map('trim', explode(',', $competenciesRaw))));
if (count($competenciesList) <= 1) {
    $competenciesList = array_values(array_filter(array_map('trim', explode("\n", strip_tags($competenciesRaw)))));
}

// Parse careers into array
$careersRaw = $major['career'] ?? '';
$careersList = array_values(array_filter(array_map('trim', explode(',', $careersRaw))));
if (count($careersList) <= 1) {
    $careersList = array_values(array_filter(array_map('trim', explode("\n", strip_tags($careersRaw)))));
}

// Parse facilities into array
$facilitiesRaw = $major['facilities'] ?? '';
$facilitiesList = array_values(array_filter(array_map('trim', explode(',', $facilitiesRaw))));
if (count($facilitiesList) <= 1) {
    $facilitiesList = array_values(array_filter(array_map('trim', explode("\n", strip_tags($facilitiesRaw)))));
}

$pageTitle       = 'Jurusan ' . $major['name'] . ' (' . ($major['code'] ?? '') . ')';
$pageDescription = excerpt($major['description'] ?: $major['short_description'], 155);
$pageImage       = BASE_URL . '/' . $major['image'];
$canonicalPath   = '/jurusan/detail/' . $major['slug'];

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small mb-0">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/jurusan">Program Keahlian</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?= e($major['name']) ?></li>
  </ol>
</nav>

<!-- Hero Section with Cover Image & Overview -->
<section class="section pt-3 pb-4">
  <div class="container">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 position-relative bg-dark text-white" style="min-height: 380px;">
      <!-- Background Image with Dark Gradient Overlay -->
      <?php if (!empty($major['image']) && file_exists(BASE_PATH . '/' . $major['image'])): ?>
        <img src="<?= e(BASE_URL . '/' . $major['image']) ?>" alt="<?= e($major['name']) ?>" class="position-absolute w-100 h-100 top-0 start-0" style="object-fit: cover; opacity: 0.38;">
      <?php endif; ?>
      <div class="position-absolute w-100 h-100 top-0 start-0" style="background-color: rgba(15, 44, 89, 0.92);"></div>

      <!-- Hero Content -->
      <div class="card-body p-4 p-md-5 position-relative z-1 d-flex flex-column justify-content-center">
        <div class="d-flex align-items-center gap-2 mb-3">
          <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold fs-7">
            <i class="bi bi-mortarboard-fill me-1"></i> <?= e($major['code']) ?>
          </span>
          <span class="badge bg-white bg-opacity-20 text-white px-2.5 py-1.5 rounded-pill small">
            Program Keahlian 3 Tahun
          </span>
        </div>

        <h1 class="h2 fw-bold text-white mb-3" style="max-width: 780px; letter-spacing: -0.01em;">
          <?= e($major['name']) ?>
        </h1>
        <p class="text-white text-opacity-85 small fs-6 mb-4" style="max-width: 680px; line-height: 1.6;">
          <?= e($major['short_description'] ?: 'Program keahlian unggulan dengan kurikulum berbasis industri dan fasilitas praktik lengkap.') ?>
        </p>

        <div class="d-flex flex-wrap gap-3">
          <a href="<?= e(BASE_URL) ?>/ppdb/daftar" class="btn btn-accent fw-bold px-4 py-2.5 shadow-sm d-inline-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-check-fill"></i> Daftar PPDB Jurusan Ini
          </a>
          <?php if (!empty($profile['whatsapp_ppdb'])): ?>
            <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $profile['whatsapp_ppdb'])) ?>?text=<?= urlencode('Halo Panitia PPDB, saya ingin berkonsultasi mengenai Jurusan ' . $major['name']) ?>" target="_blank" rel="noopener" class="btn btn-outline-light px-4 py-2.5 fw-semibold d-inline-flex align-items-center gap-2">
              <i class="bi bi-whatsapp text-success"></i> Konsultasi Jurusan
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- 4 Quick Fact Metrics -->
    <div class="row g-3 mb-5 reveal-group">
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center bg-white h-100 hover-lift">
          <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:44px;height:44px;">
            <i class="bi bi-clock-history fs-4"></i>
          </div>
          <div class="fw-bold text-dark fs-6">3 Tahun</div>
          <div class="small text-muted" style="font-size:12px;">Masa Studi (Kelas X - XII)</div>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center bg-white h-100 hover-lift">
          <div class="p-2 bg-warning bg-opacity-10 text-warning-emphasis rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:44px;height:44px;">
            <i class="bi bi-gear-wide-connected fs-4"></i>
          </div>
          <div class="fw-bold text-dark fs-6">70% Praktik</div>
          <div class="small text-muted" style="font-size:12px;">Metode Pembelajaran</div>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center bg-white h-100 hover-lift">
          <div class="p-2 bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:44px;height:44px;">
            <i class="bi bi-buildings fs-4"></i>
          </div>
          <div class="fw-bold text-dark fs-6">6 Bulan PKL</div>
          <div class="small text-muted" style="font-size:12px;">Magang Kerja Industri</div>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center bg-white h-100 hover-lift">
          <div class="p-2 bg-info bg-opacity-10 text-info-emphasis rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:44px;height:44px;">
            <i class="bi bi-award-fill fs-4"></i>
          </div>
          <div class="fw-bold text-dark fs-6">Sertifikasi BNSP</div>
          <div class="small text-muted" style="font-size:12px;">Uji Kompetensi Keahlian</div>
        </div>
      </div>
    </div>

    <!-- Main Two-Column Layout -->
    <div class="row g-4 g-lg-5">
      <!-- Left Column: Details, Competencies, Careers, Facilities -->
      <div class="col-lg-8 reveal-left">
        <!-- 1. Deskripsi Program Keahlian -->
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
          <h2 class="h5 fw-bold text-primary mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
            <i class="bi bi-info-circle-fill text-accent"></i> Tentang Program Keahlian
          </h2>
          <div class="article-body" style="font-size: 1.02rem; line-height: 1.85; color: #334155;">
            <?= cleanHtml($major['description'] ?: $major['short_description']) ?>
          </div>
        </div>

        <!-- 2. Kompetensi Keahlian & Keterampilan Utama -->
        <?php if (!empty($competenciesList)): ?>
          <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
            <h2 class="h5 fw-bold text-primary mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
              <i class="bi bi-tools text-accent"></i> Kompetensi &amp; Keterampilan yang Dipelajari
            </h2>
            <p class="small text-muted mb-4">Materi kejuruan dan standar kompetensi kerja yang dikuasai siswa selama masa pendidikan:</p>
            
            <div class="row g-3">
              <?php foreach ($competenciesList as $idx => $comp): ?>
                <div class="col-md-6">
                  <div class="p-3 rounded-3 bg-light border h-100 d-flex align-items-start gap-2.5">
                    <i class="bi bi-check-circle-fill text-success fs-5 flex-shrink-0 mt-0.5"></i>
                    <span class="small fw-semibold text-dark lh-sm"><?= e($comp) ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- 3. Peluang & Prospek Kerja Lulusan -->
        <?php if (!empty($careersList)): ?>
          <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
            <h2 class="h5 fw-bold text-primary mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
              <i class="bi bi-briefcase-fill text-accent"></i> Prospek Karier &amp; Peluang Kerja
            </h2>
            <p class="small text-muted mb-4">Lulusan jurusan ini memiliki kesempatan berkarier di berbagai sektor industri dan kewirausahaan:</p>

            <div class="row g-3">
              <?php foreach ($careersList as $career): ?>
                <div class="col-md-6">
                  <div class="p-3 rounded-3 bg-light border h-100 hover-lift d-flex align-items-center gap-3">
                    <div class="p-2 rounded-2 bg-primary bg-opacity-10 text-primary flex-shrink-0">
                      <i class="bi bi-person-check-fill fs-5"></i>
                    </div>
                    <span class="small fw-bold text-dark"><?= e($career) ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- 4. Fasilitas Laboratorium & Bengkel Praktik -->
        <?php if (!empty($facilitiesList)): ?>
          <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
            <h2 class="h5 fw-bold text-primary mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
              <i class="bi bi-building-gear text-accent"></i> Fasilitas Praktik &amp; Laboratorium
            </h2>
            <p class="small text-muted mb-4">Didukung sarana prasarana modern standar industri untuk menunjang kegiatan pembelajaran:</p>

            <div class="row g-3">
              <?php foreach ($facilitiesList as $fac): ?>
                <div class="col-md-6">
                  <div class="p-3 rounded-3 bg-light border h-100 d-flex align-items-center gap-3">
                    <div class="p-2 rounded-2 bg-warning bg-opacity-10 text-warning-emphasis flex-shrink-0">
                      <i class="bi bi-pc-display fs-5"></i>
                    </div>
                    <span class="small fw-semibold text-dark"><?= e($fac) ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- 5. Tiga Jalur Kelulusan (BMW) -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-light border">
          <h3 class="h6 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
            <i class="bi bi-award-fill text-warning"></i> Ke Mana Lulusan <?= e($major['code']) ?> Melangkah? (BMW)
          </h3>
          <div class="row g-3 small">
            <div class="col-md-4">
              <div class="p-3 bg-white rounded-3 border h-100">
                <strong class="text-primary d-block mb-1"><i class="bi bi-briefcase me-1"></i>Bekerja</strong>
                <span class="text-muted">Terserap di perusahaan mitra industri, teknisi ahli, programmer, mekanik, atau staf profesional.</span>
              </div>
            </div>
            <div class="col-md-4">
              <div class="p-3 bg-white rounded-3 border h-100">
                <strong class="text-warning-emphasis d-block mb-1"><i class="bi bi-mortarboard me-1"></i>Melanjutkan</strong>
                <span class="text-muted">Kuliah di Politeknik dan Perguruan Tinggi Negeri/Swasta dengan beasiswa prestasi vokasi.</span>
              </div>
            </div>
            <div class="col-md-4">
              <div class="p-3 bg-white rounded-3 border h-100">
                <strong class="text-success d-block mb-1"><i class="bi bi-shop me-1"></i>Wirausaha</strong>
                <span class="text-muted">Membuka usaha mandiri, startup digital, jasa service, atau konsultan kejuruan.</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Registration CTA & Other Majors -->
      <div class="col-lg-4">
        <!-- Registration Card Widget -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-primary text-white text-center reveal-right" style="background-color: #0F2C59;">
          <div class="p-3 bg-white bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:58px;height:58px;">
            <i class="bi bi-mortarboard fs-2 text-warning"></i>
          </div>
          <span class="badge bg-warning text-dark px-3 py-1 rounded-pill small fw-bold mb-2">PILIH JURUSAN INI</span>
          <h2 class="h5 fw-bold text-white mb-2"><?= e($major['name']) ?></h2>
          <p class="small text-white-50 mb-3">Kuota pendaftaran terbatas untuk setiap gelombang. Daftarkan diri Anda sekarang di PPDB Online.</p>
          
          <a href="<?= e(BASE_URL) ?>/ppdb/daftar" class="btn btn-accent btn-sm w-100 fw-bold py-2.5 mb-2 shadow-sm">
            <i class="bi bi-check-circle-fill me-1"></i> Formulir Pendaftaran PPDB
          </a>
          <a href="<?= e(BASE_URL) ?>/ppdb" class="btn btn-outline-light btn-sm w-100 fw-medium py-2">
            <i class="bi bi-info-circle me-1"></i> Informasi Syarat &amp; Jalur
          </a>
        </div>

        <!-- Other Majors Widget -->
        <?php if (!empty($otherMajors)): ?>
          <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white reveal-right">
            <h2 class="h6 fw-bold text-primary mb-3"><i class="bi bi-grid me-2"></i>Jurusan Lainnya</h2>
            <div class="d-flex flex-column gap-3">
              <?php foreach ($otherMajors as $om): ?>
                <a href="<?= e(BASE_URL) ?>/jurusan/detail/<?= e($om['slug']) ?>" class="d-flex align-items-center gap-3 text-decoration-none p-2 rounded-3 hover-lift bg-light border">
                  <div class="position-relative flex-shrink-0" style="width: 60px; height: 50px; border-radius: 8px; overflow: hidden;">
                    <img src="<?= e(BASE_URL . '/' . $om['image']) ?>" alt="<?= e($om['name']) ?>" class="w-100 h-100" style="object-fit: cover;">
                  </div>
                  <div class="overflow-hidden flex-grow-1">
                    <span class="badge bg-primary text-white small fs-8 mb-0.5"><?= e($om['code']) ?></span>
                    <span class="small fw-semibold text-dark d-block text-truncate" style="font-size: 13px;">
                      <?= e($om['name']) ?>
                    </span>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
            <div class="mt-3 pt-2 border-top text-center">
              <a href="<?= e(BASE_URL) ?>/jurusan" class="small fw-semibold text-primary text-decoration-none">
                Lihat Seluruh Program Keahlian <i class="bi bi-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        <?php endif; ?>

        <!-- Contact Support -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white reveal-right">
          <h2 class="h6 fw-bold text-primary mb-2"><i class="bi bi-headset me-2"></i>Punya Pertanyaan?</h2>
          <p class="small text-muted mb-3">Tim bimbingan konseling dan panitia PPDB siap membantu menjawab pertanyaan Anda seputar kurikulum jurusan.</p>
          <?php if (!empty($profile['whatsapp_ppdb'])): ?>
            <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $profile['whatsapp_ppdb'])) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success w-100 fw-semibold d-flex align-items-center justify-content-center gap-1.5">
              <i class="bi bi-whatsapp"></i> Chat WhatsApp PPDB
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
