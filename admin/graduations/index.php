<?php
/**
 * admin/graduations/index.php
 * Kelola Data Kelulusan Siswa & SKL Online
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();

$db = getDB();
$pageTitle = 'Kelola Kelulusan Siswa';
$currentAdminNav = 'graduations';

$settings = $db->query("SELECT * FROM graduation_settings WHERE id = 1")->fetch() ?: [
    'academic_year'     => '2026/2027',
    'announcement_date' => date('Y-m-d H:i:s'),
    'is_active'         => 1,
];

$q = trim($_GET['q'] ?? '');
$statusFilter = trim($_GET['status'] ?? 'all');

$where = ["1=1"];
$params = [];

if ($q !== '') {
    $where[] = "(student_name LIKE ? OR nisn LIKE ? OR exam_number LIKE ? OR major_name LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

if ($statusFilter !== 'all' && in_array($statusFilter, ['lulus', 'tidak_lulus', 'ditunda'])) {
    $where[] = "status = ?";
    $params[] = $statusFilter;
}

$whereSql = implode(' AND ', $where);
$stmt = $db->prepare("SELECT * FROM graduations WHERE $whereSql ORDER BY student_name ASC");
$stmt->execute($params);
$students = $stmt->fetchAll();

// Stats
$totalStudents = (int)$db->query("SELECT COUNT(*) FROM graduations")->fetchColumn();
$totalLulus    = (int)$db->query("SELECT COUNT(*) FROM graduations WHERE status = 'lulus'")->fetchColumn();
$totalTidak    = (int)$db->query("SELECT COUNT(*) FROM graduations WHERE status = 'tidak_lulus'")->fetchColumn();
$totalDitunda  = (int)$db->query("SELECT COUNT(*) FROM graduations WHERE status = 'ditunda'")->fetchColumn();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>

<?= flashMessage() ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold text-primary mb-1"><i class="bi bi-mortarboard-fill me-2"></i>Kelulusan Siswa (SKL Online)</h1>
    <p class="text-muted small mb-0">Tahun Ajaran: <strong><?= e($settings['academic_year']) ?></strong> | Rilis: <strong><?= date('d M Y, H:i', strtotime($settings['announcement_date'])) ?> WIB</strong></p>
  </div>
  <div class="d-flex gap-2 flex-wrap align-items-center">
    <a href="<?= e(BASE_URL) ?>/admin/graduations/settings.php" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-gear me-1"></i>Pengaturan Jadwal
    </a>
    <a href="<?= e(BASE_URL) ?>/admin/graduations/import.php" class="btn btn-outline-success btn-sm">
      <i class="bi bi-file-earmark-arrow-up me-1"></i>Import CSV / Excel
    </a>
    <div class="btn-group btn-group-sm">
      <a href="<?= e(BASE_URL) ?>/admin/graduations/export.php?format=csv" class="btn btn-outline-primary">
        <i class="bi bi-download me-1"></i>Export Data
      </a>
      <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="visually-hidden">Toggle Dropdown</span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow-sm">
        <li><h6 class="dropdown-header small">Pilihan Ekspor</h6></li>
        <li><a class="dropdown-item small" href="<?= e(BASE_URL) ?>/admin/graduations/export.php?format=csv"><i class="bi bi-filetype-csv me-2 text-success"></i>Export Format CSV (.csv)</a></li>
        <li><a class="dropdown-item small" href="<?= e(BASE_URL) ?>/admin/graduations/export.php?format=excel"><i class="bi bi-file-earmark-excel me-2 text-primary"></i>Export Format Excel (.xls)</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><h6 class="dropdown-header small">Template Kosong</h6></li>
        <li><a class="dropdown-item small" href="<?= e(BASE_URL) ?>/admin/graduations/template.php?format=csv"><i class="bi bi-file-earmark-arrow-down me-2 text-success"></i>Unduh Template CSV</a></li>
        <li><a class="dropdown-item small" href="<?= e(BASE_URL) ?>/admin/graduations/template.php?format=excel"><i class="bi bi-file-earmark-excel me-2 text-primary"></i>Unduh Template Excel</a></li>
      </ul>
    </div>
    <a href="<?= e(BASE_URL) ?>/admin/graduations/form.php" class="btn btn-accent btn-sm">
      <i class="bi bi-plus-lg me-1"></i>Tambah Siswa
    </a>
  </div>
</div>

<?= flashMessage() ?>

<!-- STATS CARDS -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="admin-card p-3 border-start border-primary border-4">
      <div class="text-muted small fw-semibold">Total Siswa</div>
      <div class="h4 fw-bold text-dark mb-0"><?= $totalStudents ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="admin-card p-3 border-start border-success border-4">
      <div class="text-muted small fw-semibold">Lulus</div>
      <div class="h4 fw-bold text-success mb-0"><?= $totalLulus ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="admin-card p-3 border-start border-danger border-4">
      <div class="text-muted small fw-semibold">Tidak Lulus</div>
      <div class="h4 fw-bold text-danger mb-0"><?= $totalTidak ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="admin-card p-3 border-start border-warning border-4">
      <div class="text-muted small fw-semibold">Ditunda</div>
      <div class="h4 fw-bold text-warning mb-0"><?= $totalDitunda ?></div>
    </div>
  </div>
</div>

<!-- FILTER & SEARCH -->
<div class="admin-card mb-4 p-3">
  <form method="get" action="<?= e(BASE_URL) ?>/admin/graduations/index.php" class="row g-2 align-items-center">
    <div class="col-md-7">
      <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari nama siswa, NISN, No. Ujian, jurusan..." value="<?= e($q) ?>">
    </div>
    <div class="col-md-3">
      <select name="status" class="form-select form-select-sm">
        <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>Semua Status</option>
        <option value="lulus" <?= $statusFilter === 'lulus' ? 'selected' : '' ?>>Lulus</option>
        <option value="tidak_lulus" <?= $statusFilter === 'tidak_lulus' ? 'selected' : '' ?>>Tidak Lulus</option>
        <option value="ditunda" <?= $statusFilter === 'ditunda' ? 'selected' : '' ?>>Ditunda</option>
      </select>
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
    </div>
  </form>
</div>

<!-- TABLE -->
<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>No</th>
          <th>NISN / No. Ujian</th>
          <th>Nama Siswa</th>
          <th>Jurusan</th>
          <th>Nilai Rata-rata</th>
          <th>Status</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($students)): ?>
          <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data siswa kelulusan.</td></tr>
        <?php else: ?>
          <?php $no = 1; foreach ($students as $s): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td>
                <div class="fw-semibold text-dark"><?= e($s['nisn']) ?></div>
                <div class="small text-muted"><?= e($s['exam_number']) ?></div>
              </td>
              <td>
                <div class="fw-bold text-dark"><?= e($s['student_name']) ?></div>
                <div class="small text-muted"><?= e($s['birth_place'] ?? '-') ?>, <?= !empty($s['birth_date']) ? date('d/m/Y', strtotime($s['birth_date'])) : '-' ?></div>
              </td>
              <td><?= e($s['major_name']) ?></td>
              <td><span class="badge bg-light text-dark border"><?= e(number_format((float)$s['average_score'], 2)) ?></span></td>
              <td>
                <?php if ($s['status'] === 'lulus'): ?>
                  <span class="badge bg-success">Lulus</span>
                <?php elseif ($s['status'] === 'tidak_lulus'): ?>
                  <span class="badge bg-danger">Tidak Lulus</span>
                <?php else: ?>
                  <span class="badge bg-warning text-dark">Ditunda</span>
                <?php endif; ?>
              </td>
              <td class="text-end">
                <a href="<?= e(BASE_URL) ?>/kelulusan/cetak?nisn=<?= urlencode($s['nisn']) ?>" target="_blank" class="btn btn-sm btn-outline-info p-1 px-2" title="Lihat SKL">
                  <i class="bi bi-printer"></i>
                </a>
                <a href="<?= e(BASE_URL) ?>/admin/graduations/form.php?id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline-primary p-1 px-2" title="Edit">
                  <i class="bi bi-pencil"></i>
                </a>
                <form action="<?= e(BASE_URL) ?>/admin/graduations/delete.php" method="post" class="d-inline" data-confirm-delete="Hapus data siswa <?= e($s['student_name']) ?>?">
                  <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                  <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger p-1 px-2" title="Hapus">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
