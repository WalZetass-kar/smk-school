<?php
/**
 * pages/prestasi.php — /prestasi
 * Galeri Prestasi & Penghargaan Siswa/Sekolah
 */
$db = getDB();
$profile = getSchoolProfile();

$levelFilter = $_GET['level'] ?? '';
$yearFilter  = $_GET['year'] ?? '';

$where = ["1=1"];
$params = [];

if ($levelFilter !== '' && in_array($levelFilter, ['sekolah','kota','provinsi','nasional','internasional'], true)) {
    $where[] = "level = ?";
    $params[] = $levelFilter;
}
if ($yearFilter !== '') {
    $where[] = "year = ?";
    $params[] = $yearFilter;
}
$whereSql = implode(' AND ', $where);

$stmt = $db->prepare("SELECT * FROM achievements WHERE $whereSql ORDER BY year DESC, id DESC");
$stmt->execute($params);
$achievements = $stmt->fetchAll();

$years = $db->query("SELECT DISTINCT year FROM achievements ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle       = 'Prestasi & Penghargaan Siswa';
$pageDescription = 'Daftar prestasi juara kompetisi akademik dan non-akademik siswa ' . e($profile['school_name'] ?? '');
$canonicalPath   = '/prestasi';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/">Beranda</a></li>
    <li class="breadcrumb-item text-muted">Kesiswaan</li>
    <li class="breadcrumb-item active" aria-current="page">Prestasi Siswa</li>
  </ol>
</nav>

<section class="section pt-3 pb-5">
  <div class="container">
    <div class="text-center mx-auto mb-4 reveal-up" style="max-width:680px;">
      <h1 class="section-title">Prestasi Siswa &amp; Sekolah</h1>
      <p class="section-subtitle mx-auto">Deretan torehan piala, medali, dan penghargaan yang diraih oleh siswa-siswi berprestasi <?= e($profile['school_name'] ?? '') ?>.</p>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-5 reveal-up">
      <form method="get" class="row g-2 justify-content-center align-items-center">
        <div class="col-md-4 col-sm-6">
          <select name="level" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">— Semua Tingkat Lomba —</option>
            <option value="internasional" <?= $levelFilter === 'internasional' ? 'selected' : '' ?>>Tingkat Internasional</option>
            <option value="nasional" <?= $levelFilter === 'nasional' ? 'selected' : '' ?>>Tingkat Nasional</option>
            <option value="provinsi" <?= $levelFilter === 'provinsi' ? 'selected' : '' ?>>Tingkat Provinsi</option>
            <option value="kota" <?= $levelFilter === 'kota' ? 'selected' : '' ?>>Tingkat Kota / Kabupaten</option>
            <option value="sekolah" <?= $levelFilter === 'sekolah' ? 'selected' : '' ?>>Tingkat Sekolah</option>
          </select>
        </div>

        <div class="col-md-3 col-sm-6">
          <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">— Semua Tahun —</option>
            <?php foreach ($years as $y): ?>
              <option value="<?= e($y) ?>" <?= $yearFilter === $y ? 'selected' : '' ?>><?= e($y) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <?php if ($levelFilter || $yearFilter): ?>
        <div class="col-auto">
          <a href="<?= e(BASE_URL) ?>/prestasi" class="btn btn-sm btn-outline-secondary">Reset</a>
        </div>
        <?php endif; ?>
      </form>
    </div>

    <!-- Achievements Grid -->
    <?php if ($achievements): ?>
      <div class="row g-4 reveal-group">
        <?php foreach ($achievements as $a): ?>
          <?php
            $levelBadge = match($a['level']) {
              'internasional' => 'bg-danger text-white',
              'nasional'      => 'bg-warning text-dark',
              'provinsi'      => 'bg-primary text-white',
              'kota'          => 'bg-success text-white',
              default         => 'bg-secondary text-white'
            };
          ?>
          <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column hover-lift overflow-hidden">
              <div class="position-relative img-skeleton-wrapper" style="height: 220px;">
                <img src="<?= e(BASE_URL . '/' . ($a['photo'] ?: 'assets/images/galeri-2.jpg')) ?>" alt="<?= e($a['title']) ?>" class="w-100 h-100" style="object-fit: cover;">
                <span class="badge <?= $levelBadge ?> position-absolute top-0 start-0 m-3 px-3 py-2 shadow-sm fs-8 text-uppercase">
                  <i class="bi bi-award me-1"></i>Tingkat <?= ucfirst($a['level']) ?>
                </span>
                <span class="badge bg-dark position-absolute top-0 end-0 m-3 px-3 py-2 shadow-sm fs-8">
                  Tahun <?= e($a['year']) ?>
                </span>
              </div>
              <div class="card-body p-4 d-flex flex-column flex-grow-1">
                <div class="fw-bold text-accent mb-1 fs-6"><?= e($a['rank_position']) ?></div>
                <h2 class="h5 fw-bold text-primary mb-2"><?= e($a['title']) ?></h2>
                <div class="small fw-semibold text-dark mb-2">
                  <i class="bi bi-person-fill me-1 text-secondary"></i><?= e($a['student_name']) ?>
                </div>
                <div class="small text-muted mb-3">
                  <i class="bi bi-trophy-fill me-1 text-warning"></i><?= e($a['competition_name']) ?>
                </div>
                <?php if ($a['description']): ?>
                  <p class="text-muted small flex-grow-1 mb-0 border-top pt-2"><?= nl2br(e($a['description'])) ?></p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-5">
        <i class="bi bi-trophy fs-1 text-muted d-block mb-3"></i>
        <h2 class="h5 fw-bold">Belum Ada Data Prestasi untuk Filter Terpilih</h2>
        <a href="<?= e(BASE_URL) ?>/prestasi" class="btn btn-accent btn-sm mt-2">Lihat Semua Prestasi</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
