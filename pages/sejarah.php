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
    <div class="text-center mx-auto mb-5 reveal-up" style="max-width:740px;">
      <h1 class="section-title mb-2"><?= e($history['title'] ?? 'Sejarah & Perkembangan Sekolah') ?></h1>
      <p class="section-subtitle mx-auto">Menengok kembali perjalanan dedikasi <strong><?= e($profile['school_name'] ?? 'SMK') ?></strong> dalam mencetak generasi vokasi yang unggul, terampil, dan berakhlak mulia.</p>
    </div>

    <!-- 4 Overview Fact Badges -->
    <div class="row g-3 mb-5 justify-content-center reveal-group">
      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center bg-white h-100 hover-lift">
          <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:46px;height:46px;">
            <i class="bi bi-calendar-check-fill fs-4"></i>
          </div>
          <div class="fw-bold text-dark fs-5"><?= e($profile['founded_year'] ?: '1998') ?></div>
          <div class="small text-muted" style="font-size:12px;">Tahun Berdiri</div>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center bg-white h-100 hover-lift">
          <div class="p-2 bg-warning bg-opacity-10 text-warning-emphasis rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:46px;height:46px;">
            <i class="bi bi-award-fill fs-4"></i>
          </div>
          <div class="fw-bold text-dark fs-5">Akreditasi <?= e($profile['accreditation'] ?: 'A') ?></div>
          <div class="small text-muted" style="font-size:12px;">Predikat Sekolah</div>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center bg-white h-100 hover-lift">
          <div class="p-2 bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:46px;height:46px;">
            <i class="bi bi-mortarboard-fill fs-4"></i>
          </div>
          <div class="fw-bold text-dark fs-5">Ribuan Lulusan</div>
          <div class="small text-muted" style="font-size:12px;">Alumni Berkarier</div>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center bg-white h-100 hover-lift">
          <div class="p-2 bg-info bg-opacity-10 text-info-emphasis rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:46px;height:46px;">
            <i class="bi bi-buildings fs-4"></i>
          </div>
          <div class="fw-bold text-dark fs-5">Mitra DU/DI</div>
          <div class="small text-muted" style="font-size:12px;">Jaringan Industri</div>
        </div>
      </div>
    </div>

    <!-- Two-Column Layout -->
    <div class="row g-4 g-lg-5">
      <!-- Left Column: Main Narrative & Timeline -->
      <div class="col-lg-8 reveal-left">
        <!-- Main Narrative Box -->
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-5">
          <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">
            <i class="bi bi-book-half text-accent fs-4"></i>
            <h2 class="h5 fw-bold text-primary mb-0">Kilas Balik Pendirian</h2>
          </div>

          <?php if (!empty($history['content'])): ?>
            <div class="article-body" style="font-size: 1.05rem; line-height: 1.9; color: #334155;">
              <?= cleanHtml($history['content']) ?>
            </div>
          <?php else: ?>
            <p class="text-muted">Konten narasi sejarah sekolah sedang dalam proses pembaruan.</p>
          <?php endif; ?>
        </div>

        <!-- Historical Milestones (Linimasa Sejarah) -->
        <div class="mb-4">
          <h2 class="h4 fw-bold text-primary mb-3">Linimasa Perkembangan Sekolah</h2>
          <p class="text-muted small mb-4">Tahapan fase penting dalam perjalanan transformasi mutu pendidikan kejuruan kami.</p>

          <div class="history-timeline">
            <!-- Timeline 1: Inisiasi -->
            <div class="history-timeline-item">
              <div class="history-timeline-dot"><i class="bi bi-flag-fill"></i></div>
              <div class="history-timeline-card">
                <span class="history-timeline-year">1998</span>
                <h3 class="h6 fw-bold text-primary mb-2">Fase Perintisan &amp; Pendirian Resmi</h3>
                <p class="small text-muted mb-0" style="line-height:1.7;">
                  Sekolah resmi didirikan dengan komitmen kuat menghadirkan akses pendidikan vokasi berkualitas. Pada fase awal, kegiatan belajar mengajar dimulai dengan 2 program keahlian perintis serta fasilitas workshop dasar.
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
                  Pembangunan gedung ruang kelas baru, gedung perpustakaan modern, dan laboratorium komputer mandiri. Sekolah secara resmi meraih akreditasi dengan predikat yang membanggakan.
                </p>
              </div>
            </div>

            <!-- Timeline 3: Kemitraan Industri -->
            <div class="history-timeline-item">
              <div class="history-timeline-dot"><i class="bi bi-briefcase-fill"></i></div>
              <div class="history-timeline-card">
                <span class="history-timeline-year">2014</span>
                <h3 class="h6 fw-bold text-primary mb-2">Transformasi Kurikulum &amp; Peresmian Unit BKK</h3>
                <p class="small text-muted mb-0" style="line-height:1.7;">
                  Mengukuhkan kerja sama strategis dengan asosiasi industri (DU/DI) serta membentuk unit resmi <strong>Bursa Kerja Khusus (BKK)</strong> guna menjamin kelancaran penyaluran magang dan rekrutmen kerja alumni.
                </p>
              </div>
            </div>

            <!-- Timeline 4: Era Unggulan & Digital -->
            <div class="history-timeline-item">
              <div class="history-timeline-dot"><i class="bi bi-trophy-fill"></i></div>
              <div class="history-timeline-card">
                <span class="history-timeline-year">2020 – Sekarang</span>
                <h3 class="h6 fw-bold text-primary mb-2">Sekolah Rujukan Vokasi &amp; Era Digitalisasi</h3>
                <p class="small text-muted mb-0" style="line-height:1.7;">
                  Meraih <strong>Akreditasi A</strong>, menerapkan sistem informasi manajemen terpadu, digitalisasi PPDB Online, dan menjadi sekolah rujukan kejuruan dengan fasilitas praktik terlengkap di wilayahnya.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- 3 Pillars of Legacy -->
        <div class="row g-3 mt-4">
          <div class="col-md-4">
            <div class="p-3 bg-white rounded-4 border shadow-sm h-100">
              <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3 d-inline-flex mb-2">
                <i class="bi bi-shield-check fs-4"></i>
              </div>
              <h3 class="h6 fw-bold text-primary mb-1">Integritas Karakter</h3>
              <p class="small text-muted mb-0">Menanamkan kedisiplinan kerja, etika profesional, dan akhlak mulia dalam setiap aktivitas siswa.</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 bg-white rounded-4 border shadow-sm h-100">
              <div class="p-2 bg-warning bg-opacity-10 text-warning-emphasis rounded-3 d-inline-flex mb-2">
                <i class="bi bi-gear-wide-connected fs-4"></i>
              </div>
              <h3 class="h6 fw-bold text-primary mb-1">Keahlian Terapan</h3>
              <p class="small text-muted mb-0">Pembelajaran berbasis praktik 70% dan teori 30% dengan kurikulum sinkron standar industri.</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 bg-white rounded-4 border shadow-sm h-100">
              <div class="p-2 bg-success bg-opacity-10 text-success rounded-3 d-inline-flex mb-2">
                <i class="bi bi-hand-thumbs-up-fill fs-4"></i>
              </div>
              <h3 class="h6 fw-bold text-primary mb-1">Kemitraan Luas</h3>
              <p class="small text-muted mb-0">Kerja sama erat dengan perusahaan nasional untuk magang siswa, sertifikasi, dan rekrutmen kerja.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Sidebar Navigation & Widgets -->
      <div class="col-lg-4">
        <!-- Quick Profile Navigation -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white reveal-right">
          <h2 class="h6 fw-bold text-primary mb-3"><i class="bi bi-list-nested me-2"></i>Navigasi Profil</h2>
          <div class="list-group list-group-flush">
            <a href="<?= e(BASE_URL) ?>/profil/sambutan" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small text-dark">
              <span><i class="bi bi-person-badge me-2 text-primary"></i>Sambutan Kepala Sekolah</span>
              <i class="bi bi-chevron-right text-muted"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/sejarah" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small fw-bold text-primary" style="background:#f8fafc; padding-left:10px !important; border-radius:6px;">
              <span><i class="bi bi-clock-history me-2 text-primary"></i>Sejarah Sekolah</span>
              <i class="bi bi-chevron-right text-primary"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/visi-misi" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small text-dark">
              <span><i class="bi bi-bullseye me-2 text-primary"></i>Visi &amp; Misi</span>
              <i class="bi bi-chevron-right text-muted"></i>
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

        <!-- School Identity Card -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white reveal-right">
          <h2 class="h6 fw-bold text-primary mb-3"><i class="bi bi-building-check me-2"></i>Identitas Sekolah</h2>
          <div class="d-flex flex-column gap-2 small">
            <div class="d-flex justify-content-between py-1 border-bottom">
              <span class="text-muted">Nama Sekolah</span>
              <span class="fw-bold text-dark text-end"><?= e($profile['school_name'] ?? 'SMK') ?></span>
            </div>
            <div class="d-flex justify-content-between py-1 border-bottom">
              <span class="text-muted">NPSN</span>
              <span class="fw-bold text-dark"><?= e($profile['npsn'] ?? '-') ?></span>
            </div>
            <div class="d-flex justify-content-between py-1 border-bottom">
              <span class="text-muted">Akreditasi</span>
              <span class="badge bg-success text-white">Akreditasi <?= e($profile['accreditation'] ?: 'A') ?></span>
            </div>
            <div class="d-flex justify-content-between py-1 border-bottom">
              <span class="text-muted">Tahun Berdiri</span>
              <span class="fw-bold text-dark"><?= e($profile['founded_year'] ?: '1998') ?></span>
            </div>
            <div class="d-flex justify-content-between py-1">
              <span class="text-muted">Status</span>
              <span class="fw-bold text-primary">Sekolah Menengah Kejuruan</span>
            </div>
          </div>
        </div>

        <!-- PPDB Callout Widget -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-primary text-white text-center reveal-right" style="background: linear-gradient(135deg, #0F2C59 0%, #183E7A 100%);">
          <div class="p-3 bg-white bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:55px;height:55px;">
            <i class="bi bi-mortarboard fs-2 text-warning"></i>
          </div>
          <h2 class="h5 fw-bold text-white mb-2">Jadilah Bagian dari Sejarah Kami</h2>
          <p class="small text-white-50 mb-3">Bergabunglah bersama ribuan alumni sukses kami. Pendaftaran Peserta Didik Baru (PPDB Online) telah dibuka.</p>
          <a href="<?= e(BASE_URL) ?>/ppdb/daftar" class="btn btn-accent btn-sm w-100 fw-bold py-2">
            <i class="bi bi-arrow-right-circle me-1"></i> Daftar Sekarang
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
