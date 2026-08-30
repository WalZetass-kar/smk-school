<?php
/**
 * pages/ppdb.php — /ppdb
 * Halaman Informasi & Panduan PPDB Online
 */
$db       = getDB();
$profile  = getSchoolProfile();
$settings = getPpdbSettings();
$majors   = $db->query("SELECT * FROM majors WHERE status = 1 ORDER BY id ASC")->fetchAll();

$pageTitle       = 'PPDB Online ' . e($settings['academic_year']);
$pageDescription = 'Informasi Penerimaan Peserta Didik Baru (PPDB) SMK Negeri Contoh Indonesia Tahun Ajaran ' . e($settings['academic_year']);
$canonicalPath   = '/ppdb';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<!-- Header Banner -->
<section class="section section-alt py-5 border-bottom">
  <div class="container text-center">
    <h1 class="section-title mb-3">PPDB Online Tahun Ajaran <?= e($settings['academic_year']) ?></h1>
    <p class="section-subtitle mx-auto mb-4" style="max-width: 680px;">
      Selamat datang di portal resmi pendaftaran calon peserta didik baru <?= e($profile['school_name'] ?? '') ?>. Siapkan dirimu menjadi generasi terampil, kompeten, dan siap kerja!
    </p>

    <div class="d-flex justify-content-center gap-3 flex-wrap">
      <?php if (!empty($settings['is_open'])): ?>
        <a href="<?= e(BASE_URL) ?>/ppdb/daftar" class="btn btn-accent btn-lg px-4">
          <i class="bi bi-pencil-square me-2"></i>Daftar Sekarang
        </a>
      <?php else: ?>
        <button class="btn btn-secondary btn-lg px-4" disabled>
          <i class="bi bi-lock me-2"></i>Pendaftaran Ditutup
        </button>
      <?php endif; ?>
      <a href="<?= e(BASE_URL) ?>/ppdb/cek-status" class="btn btn-outline-primary-custom btn-lg px-4">
        <i class="bi bi-search me-2"></i>Cek Status Pendaftaran
      </a>
    </div>
  </div>
</section>

<!-- Timeline / Jadwal PPDB -->
<section class="section py-5">
  <div class="container">
    <div class="text-center mb-5 reveal-up">
      <h2 class="section-title">Timeline Penting PPDB</h2>
    </div>

    <div class="row g-4 justify-content-center reveal-group">
      <div class="col-md-4">
        <div class="jurusan-card hover-lift p-4 text-center h-100">
          <div class="stat-icon mx-auto mb-3" style="width:60px;height:60px;background:rgba(15,39,68,0.1);color:var(--color-primary);font-size:1.6rem;">
            <i class="bi bi-calendar-check"></i>
          </div>
          <h3 class="h5 fw-bold text-primary mb-2">Pendaftaran Online</h3>
          <p class="text-muted small mb-2">Pendaftaran & Unggah Berkas</p>
          <div class="fw-bold fs-6 text-accent">
            <?= formatTanggalIndo($settings['start_date']) ?> s/d <?= formatTanggalIndo($settings['end_date']) ?>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="jurusan-card hover-lift p-4 text-center h-100">
          <div class="stat-icon mx-auto mb-3" style="width:60px;height:60px;background:rgba(245,158,11,0.1);color:var(--color-accent);font-size:1.6rem;">
            <i class="bi bi-file-earmark-check"></i>
          </div>
          <h3 class="h5 fw-bold text-primary mb-2">Verifikasi Berkas</h3>
          <p class="text-muted small mb-2">Pemeriksaan oleh Panitia PPDB</p>
          <div class="fw-bold fs-6 text-accent">
            1 - 2 Hari Kerja setelah mendaftar
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="jurusan-card hover-lift p-4 text-center h-100">
          <div class="stat-icon mx-auto mb-3" style="width:60px;height:60px;background:rgba(91,141,190,0.1);color:var(--color-secondary);font-size:1.6rem;">
            <i class="bi bi-megaphone"></i>
          </div>
          <h3 class="h5 fw-bold text-primary mb-2">Pengumuman Kelulusan</h3>
          <p class="text-muted small mb-2">Pengumuman Hasil Seleksi Online</p>
          <div class="fw-bold fs-6 text-accent">
            <?= formatTanggalIndo($settings['announcement_date']) ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Alur Pendaftaran & Persyaratan -->
<section class="section section-alt py-5">
  <div class="container">
    <div class="row g-5 align-items-start">
      <div class="col-lg-6 reveal-left">
        <h2 class="section-title mb-4">Alur Pendaftaran Online</h2>

        <div class="d-flex flex-column gap-3">
          <div class="d-flex gap-3 align-items-start">
            <span class="badge bg-primary rounded-circle p-2 fs-6" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;">1</span>
            <div>
              <h3 class="h6 fw-bold mb-1">Isi Formulir Online</h3>
              <p class="text-muted small mb-0">Lengkapi data diri, asal sekolah, data orang tua, dan pilihan jurusan pada form pendaftaran.</p>
            </div>
          </div>

          <div class="d-flex gap-3 align-items-start">
            <span class="badge bg-primary rounded-circle p-2 fs-6" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;">2</span>
            <div>
              <h3 class="h6 fw-bold mb-1">Unggah Dokumen Persyaratan</h3>
              <p class="text-muted small mb-0">Upload pas foto terbaru, foto Kartu Keluarga (KK), dan scan Rapor/SKL dalam format gambar atau PDF.</p>
            </div>
          </div>

          <div class="d-flex gap-3 align-items-start">
            <span class="badge bg-primary rounded-circle p-2 fs-6" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;">3</span>
            <div>
              <h3 class="h6 fw-bold mb-1">Cetak Bukti Pendaftaran</h3>
              <p class="text-muted small mb-0">Simpan dan cetak bukti registrasi yang memuat Nomor Pendaftaran resmi Anda.</p>
            </div>
          </div>

          <div class="d-flex gap-3 align-items-start">
            <span class="badge bg-primary rounded-circle p-2 fs-6" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;">4</span>
            <div>
              <h3 class="h6 fw-bold mb-1">Pantau Hasil Seleksi</h3>
              <p class="text-muted small mb-0">Cek status berkas secara berkala melalui menu "Cek Status" menggunakan Nomor Pendaftaran atau NISN.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6 reveal-right">
        <h2 class="section-title mb-4">Persyaratan Dokumen</h2>

        <div class="card border-0 shadow-sm rounded-3 p-4 bg-white">
          <div class="article-body mb-3">
            <?= cleanHtml(nl2br($settings['information'])) ?>
          </div>

          <hr>
          <div class="d-flex align-items-center gap-3">
            <i class="bi bi-headset fs-2 text-accent"></i>
            <div>
              <div class="small fw-bold">Pusat Bantuan & Layanan Informasi PPDB:</div>
              <div class="text-muted small"><?= e($settings['contact_person'] ?? $profile['phone'] ?? '') ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Pilihan Program Keahlian / Jurusan -->
<section class="section py-5">
  <div class="container">
    <div class="text-center mb-5 reveal-up">
      <h2 class="section-title">Program Keahlian yang Dibuka</h2>
      <p class="section-subtitle mx-auto">Pilih jurusan yang sesuai dengan minat dan bakat keahlianmu</p>
    </div>

    <div class="row g-4 reveal-group">
      <?php foreach ($majors as $m): ?>
        <div class="col-lg-3 col-md-6">
          <div class="jurusan-card hover-lift h-100 d-flex flex-column">
            <div class="position-relative img-skeleton-wrapper">
              <img src="<?= e(BASE_URL . '/' . $m['image']) ?>" alt="<?= e($m['name']) ?>" class="jurusan-card-img">
              <span class="jurusan-code-tag"><?= e($m['code']) ?></span>
            </div>
            <div class="jurusan-card-body d-flex flex-column flex-grow-1">
              <h3 class="jurusan-card-title"><?= e($m['name']) ?></h3>
              <p class="jurusan-card-desc flex-grow-1"><?= e($m['short_description']) ?></p>
              <a href="<?= e(BASE_URL) ?>/ppdb/daftar?jurusan=<?= (int) $m['id'] ?>" class="btn btn-sm btn-outline-primary-custom mt-2">Daftar Jurusan Ini</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
