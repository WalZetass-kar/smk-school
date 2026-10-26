<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireSuperAdmin();

$db = getDB();

$moduleFilter = trim($_GET['module'] ?? '');
$search       = trim($_GET['q'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 20;
$offset       = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($moduleFilter !== '') {
    $where[] = 'module = ?';
    $params[] = $moduleFilter;
}

if ($search !== '') {
    $where[] = '(user_name LIKE ? OR action LIKE ? OR detail LIKE ? OR ip_address LIKE ?)';
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $db->prepare("SELECT COUNT(*) FROM activity_logs $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

$query = "SELECT * FROM activity_logs $whereSql ORDER BY id DESC LIMIT $perPage OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Distinct modules for filter
$modules = $db->query("SELECT DISTINCT module FROM activity_logs ORDER BY module ASC")->fetchAll(PDO::FETCH_COLUMN);

// Quick Statistics
$totalAll      = (int)$db->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();
$totalToday    = (int)$db->query("SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$totalAuth     = (int)$db->query("SELECT COUNT(*) FROM activity_logs WHERE module = 'auth'")->fetchColumn();
$totalContent  = (int)$db->query("SELECT COUNT(*) FROM activity_logs WHERE action LIKE '%CREATE%' OR action LIKE '%UPDATE%' OR action LIKE '%DELETE%'")->fetchColumn();

$pageTitle = 'Audit Trail & Log Aktivitas Admin';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>

<?= flashMessage() ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
  <div>
    <h1 class="h4 fw-bold text-primary mb-1">
      <i class="bi bi-shield-lock-fill text-accent me-1"></i> Audit Trail &amp; Log Aktivitas
    </h1>
    <p class="text-muted small mb-0">Catatan riwayat rekam jejak autentikasi, modifikasi konten, dan keamanan administrator sekolah.</p>
  </div>
  <div class="d-flex gap-2">
    <?php if (isSuperAdmin()): ?>
      <a href="<?= e(BASE_URL) ?>/admin/activity-logs/export.php<?= ($moduleFilter || $search) ? '?' . http_build_query(array_filter(['module' => $moduleFilter, 'q' => $search])) : '' ?>" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-file-earmark-arrow-down me-1"></i> Export CSV
      </a>
      <form method="post" action="<?= e(BASE_URL) ?>/admin/activity-logs/delete.php" data-confirm="Apakah Anda yakin ingin mengosongkan seluruh riwayat log aktivitas? Tindakan ini bersifat permanen." class="d-inline">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <button type="submit" class="btn btn-sm btn-outline-danger">
          <i class="bi bi-trash3 me-1"></i> Bersihkan Log
        </button>
      </form>
    <?php endif; ?>
  </div>
</div>

<!-- Summary Metric Cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="admin-card p-3 d-flex align-items-center gap-3">
      <div class="p-2 rounded bg-primary-subtle text-primary fs-4 d-flex align-items-center justify-content-center" style="width:46px; height:46px;">
        <i class="bi bi-journal-text"></i>
      </div>
      <div>
        <div class="text-muted small">Total Catatan</div>
        <div class="h5 fw-bold mb-0 text-dark"><?= number_format($totalAll, 0, ',', '.') ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="admin-card p-3 d-flex align-items-center gap-3">
      <div class="p-2 rounded bg-success-subtle text-success fs-4 d-flex align-items-center justify-content-center" style="width:46px; height:46px;">
        <i class="bi bi-calendar-check"></i>
      </div>
      <div>
        <div class="text-muted small">Aktivitas Hari Ini</div>
        <div class="h5 fw-bold mb-0 text-dark"><?= number_format($totalToday, 0, ',', '.') ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="admin-card p-3 d-flex align-items-center gap-3">
      <div class="p-2 rounded bg-info-subtle text-info fs-4 d-flex align-items-center justify-content-center" style="width:46px; height:46px;">
        <i class="bi bi-key-fill"></i>
      </div>
      <div>
        <div class="text-muted small">Sesi Login / Auth</div>
        <div class="h5 fw-bold mb-0 text-dark"><?= number_format($totalAuth, 0, ',', '.') ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="admin-card p-3 d-flex align-items-center gap-3">
      <div class="p-2 rounded bg-warning-subtle text-warning fs-4 d-flex align-items-center justify-content-center" style="width:46px; height:46px;">
        <i class="bi bi-pencil-square"></i>
      </div>
      <div>
        <div class="text-muted small">Perubahan Data</div>
        <div class="h5 fw-bold mb-0 text-dark"><?= number_format($totalContent, 0, ',', '.') ?></div>
      </div>
    </div>
  </div>
</div>

<!-- Filter Bar -->
<div class="admin-card p-3 mb-4">
  <form method="get" class="row g-2 align-items-center">
    <div class="col-md-5">
      <div class="input-group input-group-sm">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" name="q" class="form-control" placeholder="Cari nama admin, aksi, IP, atau keterangan..." value="<?= e($search) ?>">
      </div>
    </div>
    <div class="col-md-3">
      <select name="module" class="form-select form-select-sm">
        <option value="">Semua Modul (<?= count($modules) ?>)</option>
        <?php foreach ($modules as $mod): ?>
          <option value="<?= e($mod) ?>" <?= $moduleFilter === $mod ? 'selected' : '' ?>><?= e(strtoupper($mod)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4 d-flex justify-content-md-end gap-2">
      <button type="submit" class="btn btn-sm btn-primary fw-semibold"><i class="bi bi-filter me-1"></i> Saring Data</button>
      <?php if ($search !== '' || $moduleFilter !== ''): ?>
        <a href="<?= e(BASE_URL) ?>/admin/activity-logs/index.php" class="btn btn-sm btn-light border">Reset</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Log Table -->
<div class="admin-card p-0 overflow-hidden mb-4">
  <div class="table-responsive">
    <table class="table table-admin table-hover align-middle mb-0" style="font-size:0.875rem;">
      <thead class="table-light">
        <tr>
          <th style="width:165px;">Waktu (WIB)</th>
          <th style="width:150px;">Pengguna</th>
          <th style="width:105px;">Modul</th>
          <th style="width:170px;">Aksi</th>
          <th>Detail Informasi</th>
          <th style="width:125px;">IP Address</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($logs)): ?>
          <?php foreach ($logs as $l): ?>
            <?php
              $actionBadge = match (true) {
                  str_contains($l['action'], 'SUCCESS') || str_contains($l['action'], 'CREATE') || str_contains($l['action'], 'ADD') => 'bg-success-subtle text-success border border-success-subtle',
                  str_contains($l['action'], 'FAILED') || str_contains($l['action'], 'DELETE') || str_contains($l['action'], 'UNAUTHORIZED') => 'bg-danger-subtle text-danger border border-danger-subtle',
                  str_contains($l['action'], 'UPDATE') || str_contains($l['action'], 'EDIT') || str_contains($l['action'], 'BACKUP') => 'bg-primary-subtle text-primary border border-primary-subtle',
                  default => 'bg-secondary-subtle text-secondary border border-secondary-subtle'
              };
            ?>
            <tr>
              <td class="text-nowrap text-muted font-monospace" style="font-size:0.8rem;">
                <i class="bi bi-clock me-1"></i><?= date('d/m/Y H:i:s', strtotime($l['created_at'])) ?>
              </td>
              <td>
                <div class="fw-bold text-dark"><?= e($l['user_name']) ?></div>
                <span class="badge bg-light text-secondary border" style="font-size:0.68rem;"><?= e($l['user_role'] ?: 'Guest') ?></span>
              </td>
              <td>
                <span class="badge bg-light text-dark border text-uppercase fw-semibold" style="font-size:0.72rem;"><?= e($l['module']) ?></span>
              </td>
              <td>
                <span class="badge <?= $actionBadge ?> px-2 py-1" style="font-size:0.75rem;">
                  <?= e($l['action']) ?>
                </span>
              </td>
              <td>
                <div class="text-dark"><?= e($l['detail']) ?></div>
                <?php if (!empty($l['user_agent'])): ?>
                  <div class="text-muted text-truncate" style="max-width:320px; font-size:0.72rem;" title="<?= e($l['user_agent']) ?>">
                    <i class="bi bi-display me-1"></i><?= e($l['user_agent']) ?>
                  </div>
                <?php endif; ?>
              </td>
              <td class="font-monospace text-muted small text-nowrap">
                <?= e($l['ip_address']) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" class="text-center py-5 text-muted">
              <i class="bi bi-shield-check fs-1 text-muted d-block mb-2"></i>
              Belum ada data log aktivitas yang sesuai dengan kriteria pencarian.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($totalPages > 1): ?>
  <div class="mb-4">
    <?php
      $queryParams = array_filter(['q' => $search, 'module' => $moduleFilter]);
      $paginationBase = BASE_URL . '/admin/activity-logs/index.php' . (!empty($queryParams) ? '?' . http_build_query($queryParams) : '');
      echo renderPagination($page, $totalPages, $paginationBase);
    ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
