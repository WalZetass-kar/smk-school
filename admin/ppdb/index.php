<?php
/**
 * admin/ppdb/index.php
 * Manajemen Data Pendaftar PPDB Online
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();

// Filter & Search
$statusFilter = $_GET['status'] ?? '';
$majorFilter  = (int) ($_GET['major_id'] ?? 0);
$search       = trim($_GET['q'] ?? '');
$page         = max(1, (int) ($_GET['page'] ?? 1));
$perPage      = 15;
$offset       = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($statusFilter !== '' && in_array($statusFilter, ['pending','verified','accepted','rejected','reserve'], true)) {
    $where[] = "r.status = ?";
    $params[] = $statusFilter;
}
if ($majorFilter > 0) {
    $where[] = "(r.major_choice_1 = ? OR r.major_choice_2 = ?)";
    $params[] = $majorFilter;
    $params[] = $majorFilter;
}
if ($search !== '') {
    $where[] = "(r.full_name LIKE ? OR r.registration_no LIKE ? OR r.nisn LIKE ? OR r.school_origin LIKE ?)";
    $s = "%$search%";
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total
$countStmt = $db->prepare("SELECT COUNT(*) FROM ppdb_registrations r $whereSql");
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $perPage);

// Fetch data
$sql = "
    SELECT r.*, m1.name AS major1_name, m1.code AS major1_code, m2.name AS major2_name, m2.code AS major2_code
    FROM ppdb_registrations r
    LEFT JOIN majors m1 ON m1.id = r.major_choice_1
    LEFT JOIN majors m2 ON m2.id = r.major_choice_2
    $whereSql
    ORDER BY r.id DESC
    LIMIT $perPage OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$registrations = $stmt->fetchAll();

// Summary counts
$summary = [
    'all'      => (int) $db->query("SELECT COUNT(*) FROM ppdb_registrations")->fetchColumn(),
    'pending'  => (int) $db->query("SELECT COUNT(*) FROM ppdb_registrations WHERE status = 'pending'")->fetchColumn(),
    'verified' => (int) $db->query("SELECT COUNT(*) FROM ppdb_registrations WHERE status = 'verified'")->fetchColumn(),
    'accepted' => (int) $db->query("SELECT COUNT(*) FROM ppdb_registrations WHERE status = 'accepted'")->fetchColumn(),
    'rejected' => (int) $db->query("SELECT COUNT(*) FROM ppdb_registrations WHERE status = 'rejected'")->fetchColumn(),
    'reserve'  => (int) $db->query("SELECT COUNT(*) FROM ppdb_registrations WHERE status = 'reserve'")->fetchColumn(),
];

$majors = $db->query("SELECT id, name, code FROM majors WHERE status = 1 ORDER BY name ASC")->fetchAll();

$pageTitle = 'Data Pendaftar PPDB';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>

<?= flashMessage() ?>

<!-- Stat Ringkasan PPDB -->
<div class="row g-3 mb-4">
  <div class="col-md-2 col-6">
    <a href="index.php" class="text-decoration-none">
      <div class="admin-card p-3 text-center border-bottom border-3 border-primary">
        <div class="small text-muted mb-1">Total Pendaftar</div>
        <div class="h4 fw-bold mb-0 text-primary"><?= number_format($summary['all']) ?></div>
      </div>
    </a>
  </div>
  <div class="col-md-2 col-6">
    <a href="index.php?status=pending" class="text-decoration-none">
      <div class="admin-card p-3 text-center border-bottom border-3 border-secondary">
        <div class="small text-muted mb-1">Pending (Baru)</div>
        <div class="h4 fw-bold mb-0 text-secondary"><?= number_format($summary['pending']) ?></div>
      </div>
    </a>
  </div>
  <div class="col-md-2 col-6">
    <a href="index.php?status=verified" class="text-decoration-none">
      <div class="admin-card p-3 text-center border-bottom border-3 border-info">
        <div class="small text-muted mb-1">Terverifikasi</div>
        <div class="h4 fw-bold mb-0 text-info"><?= number_format($summary['verified']) ?></div>
      </div>
    </a>
  </div>
  <div class="col-md-2 col-6">
    <a href="index.php?status=accepted" class="text-decoration-none">
      <div class="admin-card p-3 text-center border-bottom border-3 border-success">
        <div class="small text-muted mb-1">Diterima</div>
        <div class="h4 fw-bold mb-0 text-success"><?= number_format($summary['accepted']) ?></div>
      </div>
    </a>
  </div>
  <div class="col-md-2 col-6">
    <a href="index.php?status=reserve" class="text-decoration-none">
      <div class="admin-card p-3 text-center border-bottom border-3 border-warning">
        <div class="small text-muted mb-1">Cadangan</div>
        <div class="h4 fw-bold mb-0 text-warning"><?= number_format($summary['reserve']) ?></div>
      </div>
    </a>
  </div>
  <div class="col-md-2 col-6">
    <a href="index.php?status=rejected" class="text-decoration-none">
      <div class="admin-card p-3 text-center border-bottom border-3 border-danger">
        <div class="small text-muted mb-1">Ditolak</div>
        <div class="h4 fw-bold mb-0 text-danger"><?= number_format($summary['rejected']) ?></div>
      </div>
    </a>
  </div>
</div>

<div class="admin-card">
  <!-- Filter & Action Bar -->
  <div class="row g-2 justify-content-between align-items-center mb-3">
    <div class="col-md-7">
      <form method="get" class="row g-2">
        <div class="col-sm-4">
          <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">— Semua Status —</option>
            <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="verified" <?= $statusFilter === 'verified' ? 'selected' : '' ?>>Terverifikasi</option>
            <option value="accepted" <?= $statusFilter === 'accepted' ? 'selected' : '' ?>>Diterima</option>
            <option value="reserve" <?= $statusFilter === 'reserve' ? 'selected' : '' ?>>Cadangan</option>
            <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
          </select>
        </div>
        <div class="col-sm-4">
          <select name="major_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="0">— Semua Jurusan —</option>
            <?php foreach ($majors as $m): ?>
              <option value="<?= (int)$m['id'] ?>" <?= $majorFilter === (int)$m['id'] ? 'selected' : '' ?>><?= e($m['code']) ?> - <?= e($m['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-sm-4">
          <div class="input-group input-group-sm">
            <input type="text" name="q" class="form-control" placeholder="Cari nama/NISN..." value="<?= e($search) ?>">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
          </div>
        </div>
      </form>
    </div>

    <div class="col-md-5 text-md-end">
      <a href="export.php" class="btn btn-sm btn-success me-1">
        <i class="bi bi-file-earmark-excel me-1"></i>Export Data (CSV)
      </a>
      <a href="settings.php" class="btn btn-sm btn-outline-primary-custom">
        <i class="bi bi-sliders me-1"></i>Pengaturan PPDB
      </a>
    </div>
  </div>

  <!-- Table -->
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>No. Registrasi</th>
          <th>Nama Lengkap</th>
          <th>NISN / Asal SMP</th>
          <th>Pilihan Jurusan</th>
          <th>Status</th>
          <th>Tgl Daftar</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($registrations): ?>
          <?php foreach ($registrations as $r): ?>
            <tr>
              <td>
                <span class="fw-bold text-primary font-monospace"><?= e($r['registration_no']) ?></span>
              </td>
              <td>
                <div class="fw-semibold"><?= e($r['full_name']) ?></div>
                <div class="small text-muted"><?= e($r['phone']) ?></div>
              </td>
              <td>
                <div><?= e($r['nisn']) ?></div>
                <div class="small text-muted"><?= e($r['school_origin']) ?></div>
              </td>
              <td>
                <div><span class="badge bg-primary fs-7">P1: <?= e($r['major1_code']) ?></span></div>
                <?php if ($r['major2_code']): ?>
                  <div class="mt-1"><span class="badge bg-secondary fs-7">P2: <?= e($r['major2_code']) ?></span></div>
                <?php endif; ?>
              </td>
              <td>
                <?php
                  $badge = match($r['status']) {
                    'verified' => 'bg-info text-dark',
                    'accepted' => 'bg-success',
                    'rejected' => 'bg-danger',
                    'reserve'  => 'bg-warning text-dark',
                    default    => 'bg-secondary'
                  };
                  $label = match($r['status']) {
                    'verified' => 'Terverifikasi',
                    'accepted' => 'Diterima',
                    'rejected' => 'Ditolak',
                    'reserve'  => 'Cadangan',
                    default    => 'Pending'
                  };
                ?>
                <span class="badge <?= $badge ?>"><?= $label ?></span>
              </td>
              <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
              <td class="text-end">
                <a href="detail.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-primary me-1" title="Detail & Verifikasi">
                  <i class="bi bi-eye"></i> Detail
                </a>
                <a href="<?= e(BASE_URL) ?>/ppdb/cetak?no=<?= urlencode($r['registration_no']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary me-1" title="Cetak Kartu">
                  <i class="bi bi-printer"></i>
                </a>
                <form action="delete.php" method="post" class="d-inline" data-confirm-delete="Hapus pendaftar <?= e($r['full_name']) ?>?">
                  <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="text-center text-muted py-4">Belum ada data pendaftar PPDB.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($totalPages > 1): ?>
    <div class="mt-3">
      <?php
        $qParams = [];
        if ($statusFilter !== '') $qParams[] = 'status=' . urlencode($statusFilter);
        if ($majorFilter > 0) $qParams[] = 'major_id=' . $majorFilter;
        if ($search !== '') $qParams[] = 'q=' . urlencode($search);
        $baseUrl = 'index.php' . ($qParams ? '?' . implode('&', $qParams) : '');
        echo renderPagination($page, $totalPages, $baseUrl);
      ?>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
