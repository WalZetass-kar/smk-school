<?php
/**
 * pages/organisasi.php — /profil/struktur-organisasi
 * Halaman Struktur Organisasi & Tata Kelola Sekolah
 */
$db      = getDB();
$profile = getSchoolProfile();
$org     = $db->query("SELECT * FROM organization ORDER BY id LIMIT 1")->fetch();
$principal = $db->query("SELECT * FROM principal_message ORDER BY id LIMIT 1")->fetch();

$pageTitle       = 'Struktur Organisasi Sekolah';
$pageDescription = $org['description'] ?? ('Struktur organisasi dan tata kelola manajerial ' . e($profile['school_name'] ?? 'sekolah'));
$canonicalPath   = '/profil/struktur-organisasi';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small mb-0">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/profil">Profil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Struktur Organisasi</li>
  </ol>
</nav>

<section class="section pt-3 pb-5">
  <div class="container">
    <!-- Header -->
    <div class="text-center mx-auto mb-5 reveal-up" style="max-width:740px;">
      <h1 class="section-title mb-2"><?= e($org['title'] ?? 'Struktur Organisasi Sekolah') ?></h1>
      <p class="section-subtitle mx-auto"><?= e($org['description'] ?: ('Bagan pembagian tugas, wewenang, dan koordinasi struktural kepemimpinan di ' . ($profile['school_name'] ?? 'SMK'))) ?></p>
    </div>

    <!-- Two-Column Layout -->
    <div class="row g-4 g-lg-5">
      <!-- Left Column: Leadership Tree & Diagram Image -->
      <div class="col-lg-8 reveal-left">
        <!-- 1. Bagan Gambar Visual Utama -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-5">
          <div class="card-header bg-primary text-white py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h2 class="h6 mb-0 text-white d-flex align-items-center gap-2">
              <i class="bi bi-image text-warning"></i> Bagan Struktur Organisasi
            </h2>
            <?php if (!empty($org['image']) && file_exists(BASE_PATH . '/' . $org['image'])): ?>
              <a href="<?= e(BASE_URL . '/' . $org['image']) ?>" target="_blank" class="btn btn-sm btn-light text-primary fw-semibold" title="Buka Gambar Penuh">
                <i class="bi bi-arrows-fullscreen me-1"></i> Gambar Resolusi Penuh
              </a>
            <?php endif; ?>
          </div>

          <div class="card-body p-3 p-md-4 text-center bg-light">
            <?php if (!empty($org['image']) && file_exists(BASE_PATH . '/' . $org['image'])): ?>
              <div class="position-relative rounded-3 overflow-hidden border bg-white p-2">
                <a href="<?= e(BASE_URL . '/' . $org['image']) ?>" target="_blank" title="Klik untuk memperbesar">
                  <img src="<?= e(BASE_URL . '/' . $org['image']) ?>" alt="Bagan Struktur Organisasi <?= e($profile['school_name'] ?? 'Sekolah') ?>" class="img-fluid rounded hover-lift" style="max-height: 520px; object-fit: contain; width: 100%;">
                </a>
              </div>
              <div class="form-text small text-muted mt-2">
                <i class="bi bi-info-circle me-1"></i>Klik pada gambar di atas untuk melihat atau mengunduh bagan dalam ukuran penuh.
              </div>
            <?php else: ?>
              <div class="py-5 text-muted">
                <i class="bi bi-diagram-3 fs-1 text-muted d-block mb-2"></i>
                <p class="mb-0">Bagan gambar struktur organisasi belum diunggah.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- 2. Rincian Hierarki Unsur Pimpinan Sekolah -->
        <div class="mb-5">
          <h2 class="h4 fw-bold text-primary mb-3">Unsur Manajemen &amp; Kepemimpinan</h2>
          <p class="text-muted small mb-4">Pembagian tugas dan wewenang operasional penjamin mutu pendidikan di sekolah.</p>

          <!-- Top Leader: Kepala Sekolah -->
          <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border-start border-5 border-primary">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px;">
                <i class="bi bi-person-badge-fill fs-2"></i>
              </div>
              <div>
                <span class="badge bg-primary text-white px-2.5 py-1 rounded-pill small mb-1">Pimpinan Utama</span>
                <h3 class="h5 fw-bold text-primary mb-0"><?= e($principal['principal_name'] ?? 'Kepala Sekolah') ?></h3>
                <span class="text-muted small fw-medium"><?= e($principal['position'] ?? 'Kepala Sekolah SMK') ?></span>
              </div>
            </div>
            <p class="small text-muted mt-3 mb-0 pt-2 border-top" style="line-height:1.6;">
              Bertanggung jawab penuh atas penyelenggaraan pendidikan, pembinaan ketenagaan, pengembangan kurikulum, dan tata kelola manajerial seluruh unit kerja sekolah.
            </p>
          </div>

          <!-- 4 Wakil Kepala Sekolah (Waka) Grid -->
          <!-- 4 Wakil Kepala Sekolah (Waka) Grid -->
          <div class="row g-3">
            <!-- Waka Kurikulum -->
            <div class="col-md-6">
              <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 h-100 bg-white hover-lift border">
                <div class="d-flex align-items-start gap-3">
                  <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px; height:44px;">
                    <i class="bi bi-journal-bookmark-fill fs-5"></i>
                  </div>
                  <div>
                    <h4 class="h6 fw-bold text-primary mb-1">Waka Bidang Kurikulum</h4>
                    <span class="badge bg-light text-dark border small mb-2">Akademik &amp; Pembelajaran</span>
                    <p class="small text-muted mb-0" style="line-height:1.5;">
                      Mengelola kalender akademik, penyusunan jadwal KBM, sinkronisasi kurikulum industri, dan evaluasi hasil belajar siswa.
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Waka Kesiswaan -->
            <div class="col-md-6">
              <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 h-100 bg-white hover-lift border">
                <div class="d-flex align-items-start gap-3">
                  <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px; height:44px;">
                    <i class="bi bi-people-fill fs-5"></i>
                  </div>
                  <div>
                    <h4 class="h6 fw-bold text-primary mb-1">Waka Bidang Kesiswaan</h4>
                    <span class="badge bg-light text-dark border small mb-2">Karakter &amp; Ekstrakurikuler</span>
                    <p class="small text-muted mb-0" style="line-height:1.5;">
                      Membina kedisiplinan siswa, OSIS/MPK, pembinaan prestasi perlombaan (LKS/O2SN), dan kegiatan ekstrakurikuler.
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Waka Sarpras -->
            <div class="col-md-6">
              <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 h-100 bg-white hover-lift border">
                <div class="d-flex align-items-start gap-3">
                  <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px; height:44px;">
                    <i class="bi bi-tools fs-5"></i>
                  </div>
                  <div>
                    <h4 class="h6 fw-bold text-primary mb-1">Waka Sarana &amp; Prasarana</h4>
                    <span class="badge bg-light text-dark border small mb-2">Fasilitas, Bengkel &amp; Lab</span>
                    <p class="small text-muted mb-0" style="line-height:1.5;">
                      Merawat dan mengoptimalkan gedung, laboratorium komputer, bengkel kerja praktik, dan infrastruktur penunjang belajar.
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Waka Humas / Hubin -->
            <div class="col-md-6">
              <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 h-100 bg-white hover-lift border">
                <div class="d-flex align-items-start gap-3">
                  <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px; height:44px;">
                    <i class="bi bi-buildings fs-5"></i>
                  </div>
                  <div>
                    <h4 class="h6 fw-bold text-primary mb-1">Waka Hubungan Industri (Hubin)</h4>
                    <span class="badge bg-light text-dark border small mb-2">Mitra DU/DI &amp; BKK</span>
                    <p class="small text-muted mb-0" style="line-height:1.5;">
                      Membangun kemitraan dunia usaha/industri, koordinasi Prakerin/PKL, dan penyaluran kerja lulusan via Bursa Kerja Khusus.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. Unsur Pelaksana Teknis & Akademik -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
          <h3 class="h6 fw-bold text-primary mb-3"><i class="bi bi-diagram-2 me-2"></i>Unsur Pelaksana Teknis &amp; Layanan</h3>
          <div class="row g-3 small">
            <div class="col-md-6">
              <div class="p-3 bg-light rounded-3 border">
                <strong class="text-dark d-block mb-1"><i class="bi bi-mortarboard text-primary me-1"></i>Ketua Program Keahlian (Kaprogli)</strong>
                <span class="text-muted">Mengkoordinasikan mutu pembelajaran teknis dan operasional bengkel/lab pada masing-masing jurusan.</span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 bg-light rounded-3 border">
                <strong class="text-dark d-block mb-1"><i class="bi bi-folder2-open text-primary me-1"></i>Kepala Tata Usaha (TU)</strong>
                <span class="text-muted">Melayani administrasi umum, kepegawaian, surat-menyurat, persuratan siswa, dan kearsipan resmi.</span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 bg-light rounded-3 border">
                <strong class="text-dark d-block mb-1"><i class="bi bi-book text-primary me-1"></i>Kepala Perpustakaan</strong>
                <span class="text-muted">Mengelola koleksi literasi, sumber belajar digital, dan layanan peminjaman buku referensi kejuruan.</span>
              </div>
            </div>
            <div class="col-md-6">
              <div class="p-3 bg-light rounded-3 border">
                <strong class="text-dark d-block mb-1"><i class="bi bi-pc-display text-primary me-1"></i>Kepala Laboratorium Komputer</strong>
                <span class="text-muted">Menjamin kesiapan perangkat keras, jaringan internet, dan perangkat lunak untuk praktikum siswa.</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Profile Navigation & Sidebar Widgets -->
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
            <a href="<?= e(BASE_URL) ?>/profil/visi-misi" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small text-dark">
              <span><i class="bi bi-bullseye me-2 text-primary"></i>Visi &amp; Misi</span>
              <i class="bi bi-chevron-right text-muted"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/struktur-organisasi" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small fw-bold text-primary" style="background:#f8fafc; padding-left:10px !important; border-radius:6px;">
              <span><i class="bi bi-diagram-3 me-2 text-primary"></i>Struktur Organisasi</span>
              <i class="bi bi-chevron-right text-primary"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/guru-tendik" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-0 small text-dark">
              <span><i class="bi bi-people me-2 text-primary"></i>Guru &amp; Tendik</span>
              <i class="bi bi-chevron-right text-muted"></i>
            </a>
          </div>
        </div>

        <!-- Tata Usaha Contact Widget -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white reveal-right">
          <h2 class="h6 fw-bold text-primary mb-3"><i class="bi bi-headset me-2"></i>Layanan Administrasi (TU)</h2>
          <p class="small text-muted mb-3">Butuh surat keterangan aktif, legalisir ijazah, atau layanan administrasi lainnya? Hubungi unit Tata Usaha:</p>
          <?php if (!empty($profile['whatsapp_tu'])): ?>
            <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $profile['whatsapp_tu'])) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success w-100 fw-semibold mb-2 d-flex align-items-center justify-content-center gap-1.5">
              <i class="bi bi-whatsapp"></i> Chat WhatsApp TU
            </a>
          <?php endif; ?>
          <a href="<?= e(BASE_URL) ?>/kontak" class="btn btn-sm btn-outline-primary-custom w-100 fw-semibold">
            <i class="bi bi-envelope me-1"></i> Formulir Kontak Sekolah
          </a>
        </div>

        <!-- PPDB Callout -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-primary text-white text-center reveal-right" style="background-color: #0F2C59;">
          <div class="p-3 bg-white bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:55px;height:55px;">
            <i class="bi bi-mortarboard fs-2 text-warning"></i>
          </div>
          <h2 class="h5 fw-bold text-white mb-2">Pendaftaran PPDB Online</h2>
          <p class="small text-white-50 mb-3">Penerimaan Peserta Didik Baru telah dibuka. Daftarkan diri Anda sekarang dan raih masa depan gemilang!</p>
          <a href="<?= e(BASE_URL) ?>/ppdb/daftar" class="btn btn-accent btn-sm w-100 fw-bold py-2">
            <i class="bi bi-arrow-right-circle me-1"></i> Daftar Sekarang
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
