<?php
/**
 * pages/unduhan.php — /unduhan
 * Pusat Unduhan Dokumen Publik (Download Center)
 */
$db = getDB();
$profile = getSchoolProfile();

$catFilter = $_GET['category'] ?? '';
$search    = trim($_GET['search'] ?? '');

$where = ["1=1"];
$params = [];

if ($catFilter !== '') {
    $where[] = "category = ?";
    $params[] = $catFilter;
}
if ($search !== '') {
    $where[] = "(title LIKE ? OR description LIKE ?)";
    $s = "%$search%";
    $params[] = $s;
    $params[] = $s;
}
$whereSql = implode(' AND ', $where);

$stmt = $db->prepare("SELECT * FROM downloads WHERE $whereSql ORDER BY id DESC");
$stmt->execute($params);
$downloads = $stmt->fetchAll();

$categories = $db->query("SELECT DISTINCT category FROM downloads ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle       = 'Pusat Unduhan (Download Center)';
$pageDescription = 'Unduh dokumen, formulir, panduan, dan berkas resmi ' . e($profile['school_name'] ?? '');
$canonicalPath   = '/unduhan';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/">Beranda</a></li>
    <li class="breadcrumb-item active" aria-current="page">Pusat Unduhan</li>
  </ol>
</nav>

<section class="section pt-3 pb-5">
  <div class="container">
    <div class="text-center mx-auto mb-4 reveal-up" style="max-width:680px;">
      <h1 class="section-title">Pusat Unduhan</h1>
      <p class="section-subtitle mx-auto">Akses dan unduh formulir resmi, kalender akademik, modul pembelajaran, dan dokumen penting sekolah.</p>
    </div>

    <!-- Search & Filter -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-5 reveal-up">
      <form method="get" class="row g-3 align-items-center">
        <div class="col-md-6">
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama dokumen atau berkas..." value="<?= e($search) ?>">
          </div>
        </div>

        <div class="col-md-4">
          <select name="category" class="form-select">
            <option value="">— Semua Kategori Berkas —</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= e($c) ?>" <?= $catFilter === $c ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-accent w-100 fw-semibold">Cari</button>
          <?php if ($search || $catFilter): ?>
            <a href="<?= e(BASE_URL) ?>/unduhan" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <!-- Documents List -->
    <?php if ($downloads): ?>
      <div class="row g-3 reveal-group">
        <?php foreach ($downloads as $d): ?>
          <?php
            $ext = strtolower($d['file_ext']);
            $icon = match($ext) {
              'pdf'  => 'bi-file-earmark-pdf-fill text-danger',
              'doc', 'docx' => 'bi-file-earmark-word-fill text-primary',
              'xls', 'xlsx' => 'bi-file-earmark-excel-fill text-success',
              'zip', 'rar'  => 'bi-file-earmark-zip-fill text-warning',
              default       => 'bi-file-earmark-text-fill text-secondary'
            };
          ?>
          <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 hover-lift">
              <div class="row align-items-center g-3">
                <div class="col-auto">
                  <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                    <i class="bi <?= $icon ?> fs-2"></i>
                  </div>
                </div>

                <div class="col">
                  <span class="badge bg-light text-dark border mb-1"><?= e($d['category']) ?></span>
                  <h2 class="h6 fw-bold text-primary mb-1"><?= e($d['title']) ?></h2>
                  <?php if ($d['description']): ?>
                    <p class="text-muted small mb-1"><?= e($d['description']) ?></p>
                  <?php endif; ?>
                  <div class="small text-muted d-flex gap-3 flex-wrap">
                    <span><i class="bi bi-hdd me-1"></i><?= e($d['file_size'] ?: 'Berkas Dokumen') ?></span>
                    <span><i class="bi bi-download me-1"></i><?= number_format($d['downloads_count']) ?> kali diunduh</span>
                    <span><i class="bi bi-clock me-1"></i><?= formatTanggalIndo($d['created_at']) ?></span>
                  </div>
                </div>

                <div class="col-12 col-md-auto text-md-end">
                  <a href="<?= e(BASE_URL) ?>/unduhan/download/<?= (int) $d['id'] ?>" class="btn btn-primary w-100 w-md-auto px-4 fw-semibold" target="_blank">
                    <i class="bi bi-download me-1"></i>Unduh Berkas
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-5">
        <i class="bi bi-folder-x fs-1 text-muted d-block mb-3"></i>
        <h2 class="h5 fw-bold">Tidak Ada Dokumen yang Ditemukan</h2>
        <p class="text-muted small">Coba ganti kata kunci atau pilih semua kategori.</p>
        <a href="<?= e(BASE_URL) ?>/unduhan" class="btn btn-accent btn-sm mt-2">Lihat Semua Dokumen</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
