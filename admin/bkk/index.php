<?php
/**
 * admin/bkk/index.php
 * Daftar Lowongan Kerja (Bursa Kerja Khusus)
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = ["1=1"];
$params = [];

if ($status !== '' && in_array($status, ['open', 'closed'], true)) {
    $where[] = "status = ?";
    $params[] = $status;
}
if ($search !== '') {
    $where[] = "(title LIKE ? OR company_name LIKE ? OR location LIKE ?)";
    $s = "%$search%";
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
}

$whereSql = implode(' AND ', $where);

$countStmt = $db->prepare("SELECT COUNT(*) FROM bkk_jobs WHERE $whereSql");
$countStmt->execute($params);
$totalRows = (int) $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $perPage);

$sql = "SELECT * FROM bkk_jobs WHERE $whereSql ORDER BY id DESC LIMIT $perPage OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

$pageTitle = 'Manajemen Lowongan Kerja (BKK)';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>

<?= flashMessage() ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold text-primary mb-1">Bursa Kerja Khusus (BKK)</h1>
    <p class="text-muted small mb-0">Kelola info lowongan kerja, magang, dan penyaluran karir alumni.</p>
  </div>
  <a href="form.php" class="btn btn-accent">
    <i class="bi bi-plus-lg me-1"></i>Tambah Lowongan Baru
  </a>
</div>

<!-- Filters -->
<div class="admin-card mb-4 p-3">
  <form method="get" class="row g-2 align-items-center">
    <div class="col-md-5">
      <div class="input-group input-group-sm">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" name="search" class="form-control" placeholder="Cari posisi kerja, perusahaan..." value="<?= e($search) ?>">
      </div>
    </div>
    <div class="col-md-4">
      <select name="status" class="form-select form-select-sm">
        <option value="">— Semua Status —</option>
        <option value="open" <?= $status === 'open' ? 'selected' : '' ?>>Open (Buka)</option>
        <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed (Tutup)</option>
      </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
      <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
      <?php if ($search || $status): ?>
        <a href="index.php" class="btn btn-sm btn-outline-secondary">Reset</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Logo / Perusahaan</th>
          <th>Posisi / Judul</th>
          <th>Tipe</th>
          <th>Lokasi</th>
          <th>Batas Akhir</th>
          <th>Status</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($jobs): ?>
          <?php foreach ($jobs as $j): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <?php if (!empty($j['company_logo'])): ?>
                    <img src="<?= e(BASE_URL . '/' . $j['company_logo']) ?>" alt="" style="width: 38px; height: 38px; object-fit: contain;" class="rounded border p-1 bg-white">
                  <?php else: ?>
                    <div class="rounded border p-1 bg-light text-center text-muted" style="width: 38px; height: 38px;"><i class="bi bi-building"></i></div>
                  <?php endif; ?>
                  <span class="fw-semibold small"><?= e($j['company_name']) ?></span>
                </div>
              </td>
              <td>
                <div class="fw-bold text-dark"><?= e($j['title']) ?></div>
                <a href="<?= e(BASE_URL) ?>/bkk/<?= e($j['slug']) ?>" target="_blank" class="small text-muted text-decoration-none">
                  <i class="bi bi-box-arrow-up-right me-1"></i>Lihat Halaman
                </a>
              </td>
              <td><span class="badge bg-secondary"><?= ucfirst($j['job_type']) ?></span></td>
              <td class="small"><?= e($j['location']) ?></td>
              <td class="small"><?= $j['deadline'] ? date('d/m/Y', strtotime($j['deadline'])) : '-' ?></td>
              <td>
                <?php if ($j['status'] === 'open'): ?>
                  <span class="badge bg-success">Buka</span>
                <?php else: ?>
                  <span class="badge bg-danger">Tutup</span>
                <?php endif; ?>
              </td>
              <td class="text-end">
                <a href="form.php?id=<?= $j['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                  <i class="bi bi-pencil"></i>
                </a>
                <form action="delete.php" method="post" class="d-inline" data-confirm-delete="Yakin ingin menghapus lowongan <?= e($j['title']) ?>?">
                  <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                  <input type="hidden" name="id" value="<?= $j['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data lowongan kerja.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
