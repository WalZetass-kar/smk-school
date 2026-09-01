<?php
/**
 * pages/bkk.php — /bkk
 * Portal Bursa Kerja Khusus (BKK) & Info Lowongan Kerja/Magang
 */
$db = getDB();
$profile = getSchoolProfile();

if (!isModuleActive('bkk')) {
    setFlash('info', 'Layanan Bursa Kerja Khusus (BKK) saat ini sedang dinonaktifkan.');
    redirect(BASE_URL . '/');
}

$typeFilter = $_GET['type'] ?? '';
$search     = trim($_GET['search'] ?? '');
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 9;
$offset     = ($page - 1) * $perPage;

$where = ["status = 'open'"];
$params = [];

if ($typeFilter !== '' && in_array($typeFilter, ['fulltime','parttime','internship','contract'], true)) {
    $where[] = "job_type = ?";
    $params[] = $typeFilter;
}
if ($search !== '') {
    $where[] = "(title LIKE ? OR company_name LIKE ? OR location LIKE ? OR description LIKE ?)";
    $s = "%$search%";
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
}

$whereSql = implode(' AND ', $where);

// Count
$countStmt = $db->prepare("SELECT COUNT(*) FROM bkk_jobs WHERE $whereSql");
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $perPage);

// Fetch
$sql = "SELECT * FROM bkk_jobs WHERE $whereSql ORDER BY id DESC LIMIT $perPage OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

$pageTitle       = 'Bursa Kerja Khusus (BKK)';
$pageDescription = 'Informasi lowongan kerja, magang, dan penyaluran karir lulusan ' . e($profile['school_name'] ?? '');
$canonicalPath   = '/bkk';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';

$baseParams = array_filter(['type' => $typeFilter, 'search' => $search]);
$baseUrl = '/bkk' . ($baseParams ? '?' . http_build_query($baseParams) : '');
?>

<section class="section section-alt py-5 border-bottom">
  <div class="container text-center">
    <h1 class="section-title mb-3">Bursa Kerja Khusus (BKK)</h1>
    <p class="section-subtitle mx-auto" style="max-width: 680px;">
      Pusat informasi lowongan kerja, peluang magang, dan penyaluran tenaga kerja bagi alumni dan siswa <?= e($profile['school_name'] ?? '') ?> bekerja sama dengan mitra industri terkemuka.
    </p>
  </div>
</section>

<section class="section pt-4 pb-5">
  <div class="container">
    <!-- Filter & Search Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-5 reveal-up">
      <form method="get" class="row g-3 align-items-center">
        <div class="col-md-5">
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" name="search" class="form-control border-start-0" placeholder="Cari posisi kerja, perusahaan, atau kota..." value="<?= e($search) ?>">
          </div>
        </div>

        <div class="col-md-4">
          <select name="type" class="form-select">
            <option value="">— Semua Tipe Pekerjaan —</option>
            <option value="fulltime" <?= $typeFilter === 'fulltime' ? 'selected' : '' ?>>Full Time (Penuh Waktu)</option>
            <option value="internship" <?= $typeFilter === 'internship' ? 'selected' : '' ?>>Internship (Magang / PKL)</option>
            <option value="contract" <?= $typeFilter === 'contract' ? 'selected' : '' ?>>Kontrak Kerja</option>
            <option value="parttime" <?= $typeFilter === 'parttime' ? 'selected' : '' ?>>Part Time (Paruh Waktu)</option>
          </select>
        </div>

        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="btn btn-accent w-100 fw-semibold">
            <i class="bi bi-filter me-1"></i>Terapkan Filter
          </button>
          <?php if ($search || $typeFilter): ?>
            <a href="<?= e(BASE_URL) ?>/bkk" class="btn btn-outline-secondary" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <!-- Job Cards Grid -->
    <?php if ($jobs): ?>
      <div class="row g-4 reveal-group">
        <?php foreach ($jobs as $j): ?>
          <?php
            $typeBadge = match($j['job_type']) {
              'internship' => 'bg-info text-dark',
              'contract'   => 'bg-warning text-dark',
              'parttime'   => 'bg-secondary',
              default      => 'bg-primary'
            };
            $typeLabel = match($j['job_type']) {
              'internship' => 'Magang / PKL',
              'contract'   => 'Kontrak',
              'parttime'   => 'Paruh Waktu',
              default      => 'Full Time'
            };
          ?>
          <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column hover-lift p-3 p-sm-4">
              <div class="d-flex align-items-start gap-3 mb-3">
                <div class="img-skeleton-wrapper rounded p-2 bg-light d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; flex-shrink: 0;">
                  <?php if (!empty($j['company_logo'])): ?>
                    <img src="<?= e(BASE_URL . '/' . $j['company_logo']) ?>" alt="<?= e($j['company_name']) ?>" style="max-height: 42px; max-width: 42px; object-fit: contain;">
                  <?php else: ?>
                    <i class="bi bi-building fs-3 text-primary"></i>
                  <?php endif; ?>
                </div>
                <div>
                  <span class="badge <?= $typeBadge ?> mb-1 fs-8"><?= $typeLabel ?></span>
                  <h2 class="h6 fw-bold text-primary mb-0"><?= e($j['title']) ?></h2>
                  <div class="small text-muted"><?= e($j['company_name']) ?></div>
                </div>
              </div>

              <div class="small text-muted mb-3 flex-grow-1">
                <div class="mb-1"><i class="bi bi-geo-alt me-1 text-danger"></i><?= e($j['location']) ?></div>
                <?php if ($j['salary']): ?>
                  <div class="mb-1 text-success fw-semibold"><i class="bi bi-cash-stack me-1"></i><?= e($j['salary']) ?></div>
                <?php endif; ?>
                <p class="mt-2 mb-0 text-dark"><?= e(mb_strimwidth(strip_tags($j['description']), 0, 110, '...')) ?></p>
              </div>

              <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                <span class="small text-muted" style="font-size: 11.5px;">
                  <i class="bi bi-calendar-event me-1"></i>Batas: <?= $j['deadline'] ? date('d/m/Y', strtotime($j['deadline'])) : 'Terbuka' ?>
                </span>
                <a href="<?= e(BASE_URL) ?>/bkk/<?= e($j['slug']) ?>" class="btn btn-sm btn-outline-primary-custom fw-semibold">
                  Rincian <i class="bi bi-arrow-right"></i>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="mt-5">
        <?= renderPagination($page, $totalPages, $baseUrl) ?>
      </div>
    <?php else: ?>
      <div class="text-center py-5">
        <i class="bi bi-briefcase fs-1 text-muted d-block mb-3"></i>
        <h2 class="h5 fw-bold">Belum Ada Lowongan yang Sesuai</h2>
        <p class="text-muted small">Coba ganti kata kunci pencarian atau tipe lowongan pekerjaan.</p>
        <a href="<?= e(BASE_URL) ?>/bkk" class="btn btn-accent btn-sm mt-2">Lihat Semua Lowongan</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
