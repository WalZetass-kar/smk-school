<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireSuperAdmin();

$db = getDB();

$moduleFilter = trim($_GET['module'] ?? '');
$search       = trim($_GET['q'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 25;
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
$totalPages = ceil($total / $perPage);

$query = "SELECT * FROM activity_logs $whereSql ORDER BY id DESC LIMIT $perPage OFFSET $offset";
$stmt = $db->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Distinct modules for filter
$modules = $db->query("SELECT DISTINCT module FROM activity_logs ORDER BY module ASC")->fetchAll(PDO::FETCH_COLUMN);

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
    <p class="text-muted small mb-0">Catatan riwayat lengkap keamanan, autentikasi, dan perubahan data oleh administrator.</p>
  </div>
  <div>
    <form method="post" action="<?= e(BASE_URL) ?>/admin/activity-logs/delete.php" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan seluruh riwayat log aktivitas? Aksi ini tidak dapat dibatalkan.');" class="d-inline">
      <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
      <button type="submit" class="btn btn-sm btn-outline-danger">
        <i class="bi bi-trash3 me-1"></i> Bersihkan Semua Log
      </button>
    </form>
  </div>
</div>

<!-- Filter Bar -->
<div class="admin-card p-3 mb-4">
  <form method="get" class="row g-2 align-items-center">
    <div class="col-md-4">
      <div class="input-group input-group-sm">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" name="q" class="form-control" placeholder="Cari user, aksi, IP, atau detail..." value="<?= e($search) ?>">
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
    <div class="col-md-3 d-flex gap-2">
      <button type="submit" class="btn btn-sm btn-primary fw-semibold"><i class="bi bi-filter me-1"></i> Filter</button>
      <?php if ($search !== '' || $moduleFilter !== ''): ?>
        <a href="<?= e(BASE_URL) ?>/admin/activity-logs/index.php" class="btn btn-sm btn-light border">Reset</a>
      <?php endif; ?>
    </div>
    <div class="col-md-2 text-md-end text-muted small">
      Total: <strong><?= number_format($total, 0, ',', '.') ?></strong> catatan
    </div>
  </form>
</div>

<!-- Log Table -->
<div class="admin-card p-0 overflow-hidden">
  <div class="table-responsive">
    <table class="table table-admin table-hover align-middle mb-0" style="font-size:0.87rem;">
      <thead class="table-light">
        <tr>
          <th style="width:170px;">Waktu (WIB)</th>
          <th style="width:140px;">Pengguna</th>
          <th style="width:110px;">Modul</th>
          <th style="width:160px;">Aksi</th>
          <th>Detail Informasi</th>
          <th style="width:120px;">IP Address</th>
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
                <span class="badge bg-light text-dark border" style="font-size:0.7rem;"><?= e($l['user_role']) ?></span>
              </td>
              <td>
                <span class="badge bg-secondary text-uppercase fw-semibold" style="font-size:0.72rem;"><?= e($l['module']) ?></span>
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
              Belum ada log aktivitas yang tercatat.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($totalPages > 1): ?>
  <div class="mt-4">
    <?php
      $paginationBase = BASE_URL . '/admin/activity-logs/index.php?' . http_build_query([
          'q' => $search,
          'module' => $moduleFilter
      ]);
      echo renderPagination($page, $totalPages, $paginationBase);
    ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
