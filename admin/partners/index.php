<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if (!isModuleActive('partners')) {
    setFlash('danger', 'Fitur Mitra Kerja Sama DU/DI tidak tersedia pada paket lisensi sekolah ini.');
    redirect(BASE_URL . '/admin/dashboard.php');
}

$db = getDB();

$search = trim($_GET['q'] ?? '');
$statusFilter = $_GET['status'] ?? '';

$where = ["1=1"];
$params = [];

if ($search !== '') {
    $where[] = "(name LIKE ? OR website LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($statusFilter !== '') {
    $where[] = "status = ?";
    $params[] = (int) $statusFilter;
}

$whereSql = implode(' AND ', $where);
$stmt = $db->prepare("SELECT * FROM partners WHERE $whereSql ORDER BY sort_order ASC, id DESC");
$stmt->execute($params);
$partners = $stmt->fetchAll();

$totalPartners = (int) $db->query("SELECT COUNT(*) FROM partners")->fetchColumn();
$activePartners = (int) $db->query("SELECT COUNT(*) FROM partners WHERE status = 1")->fetchColumn();

$pageTitle = 'Mitra Kerja Sama (DU/DI)';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?= flashMessage() ?>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
  <div class="col-md-6 col-xl-4">
    <div class="stat-card">
      <div class="stat-icon" style="background-color: rgba(37, 99, 235, 0.12); color: var(--color-secondary);">
        <i class="bi bi-buildings"></i>
      </div>
      <div>
        <div class="stat-value"><?= $totalPartners ?></div>
        <div class="stat-label">Total Mitra Kerja Sama</div>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-xl-4">
    <div class="stat-card">
      <div class="stat-icon" style="background-color: rgba(16, 185, 129, 0.12); color: #10B981;">
        <i class="bi bi-check-circle-fill"></i>
      </div>
      <div>
        <div class="stat-value"><?= $activePartners ?></div>
        <div class="stat-label">Mitra Ditampilkan (Aktif)</div>
      </div>
    </div>
  </div>
</div>

<!-- Main Content Card -->
<div class="admin-card">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
      <h2 class="h5 fw-bold text-primary mb-1">Daftar Mitra Kerja Sama &amp; Industri (DU/DI)</h2>
      <p class="text-muted small mb-0">Kelola dan edit logo, nama instansi/perusahaan, tautan website, dan bidang kerjasama sekolah.</p>
    </div>
    <a href="form.php" class="btn btn-accent px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
      <i class="bi bi-plus-circle-fill"></i> Tambah Mitra Baru
    </a>
  </div>

  <!-- Search & Filter Bar -->
  <form method="get" class="row g-2 mb-4">
    <div class="col-md-6">
      <div class="input-group input-group-sm">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" name="q" class="form-control" placeholder="Cari nama perusahaan, instansi, atau bidang kerjasama..." value="<?= e($search) ?>">
      </div>
    </div>
    <div class="col-md-3">
      <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">— Semua Status —</option>
        <option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>Aktif (Ditampilkan)</option>
        <option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>Nonaktif (Disembunyikan)</option>
      </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
      <button type="submit" class="btn btn-sm btn-primary px-3">Filter</button>
      <?php if ($search !== '' || $statusFilter !== ''): ?>
        <a href="index.php" class="btn btn-sm btn-outline-secondary">Reset</a>
      <?php endif; ?>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead>
        <tr>
          <th style="width: 90px;">Logo</th>
          <th>Nama Perusahaan / Instansi</th>
          <th>Bidang / Deskripsi Kerjasama</th>
          <th>Website Resmi</th>
          <th style="width: 80px;" class="text-center">Urutan</th>
          <th style="width: 100px;">Status</th>
          <th style="width: 120px;" class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($partners)): ?>
          <?php foreach ($partners as $p): ?>
          <tr>
            <td>
              <div class="p-1 bg-white border rounded d-flex align-items-center justify-content-center" style="width:70px; height:50px;">
                <?php if (!empty($p['logo']) && file_exists(BASE_PATH . '/' . $p['logo'])): ?>
                  <img src="<?= e(BASE_URL . '/' . $p['logo']) ?>" class="img-fluid" style="max-height: 40px; max-width: 60px; object-fit: contain;" alt="<?= e($p['name']) ?>">
                <?php else: ?>
                  <i class="bi bi-building text-muted fs-4"></i>
                <?php endif; ?>
              </div>
            </td>
            <td>
              <div class="fw-bold text-dark"><?= e($p['name']) ?></div>
            </td>
            <td>
              <div class="small text-muted text-truncate" style="max-width: 260px;">
                <?= e($p['description'] ?: '-') ?>
              </div>
            </td>
            <td>
              <?php if (!empty($p['website'])): ?>
                <a href="<?= e($p['website']) ?>" target="_blank" rel="noopener" class="small d-inline-flex align-items-center gap-1 text-primary text-decoration-none">
                  <i class="bi bi-link-45deg"></i> <?= e($p['website']) ?>
                </a>
              <?php else: ?>
                <span class="text-muted small">-</span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <span class="badge bg-light text-dark border"><?= (int) $p['sort_order'] ?></span>
            </td>
            <td>
              <span class="badge <?= $p['status'] ? 'badge-status-active' : 'badge-status-inactive' ?>">
                <i class="bi <?= $p['status'] ? 'bi-check-circle-fill' : 'bi-dash-circle' ?> me-1"></i>
                <?= $p['status'] ? 'Aktif' : 'Nonaktif' ?>
              </span>
            </td>
            <td class="text-end">
              <div class="btn-group btn-group-sm">
                <a href="form.php?id=<?= (int) $p['id'] ?>" class="btn btn-outline-primary-custom" title="Edit Data Mitra">
                  <i class="bi bi-pencil-square"></i>
                </a>
                <form method="post" action="delete.php" class="d-inline" data-confirm-delete="Apakah Anda yakin ingin menghapus data mitra '<?= e($p['name']) ?>'?">
                  <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                  <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                  <button type="submit" class="btn btn-outline-danger" title="Hapus Mitra">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="text-center text-muted py-5">
              <i class="bi bi-buildings fs-2 d-block mb-2 text-muted"></i>
              Belum ada data mitra kerja sama yang sesuai.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
