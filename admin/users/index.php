<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireSuperAdmin();

$db = getDB();

$search = trim($_GET['q'] ?? '');
$roleFilter = trim($_GET['role'] ?? '');

$where = ["1=1"];
$params = [];

if ($search !== '') {
    $where[] = "(name LIKE ? OR username LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($roleFilter !== '' && in_array($roleFilter, ['admin', 'superadmin'], true)) {
    $where[] = "role = ?";
    $params[] = $roleFilter;
}

$whereSql = implode(' AND ', $where);
$stmt = $db->prepare("SELECT * FROM users WHERE $whereSql ORDER BY role DESC, id ASC");
$stmt->execute($params);
$users = $stmt->fetchAll();

$totalSuper = (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'superadmin'")->fetchColumn();
$totalAdmin = (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$totalAll   = $totalSuper + $totalAdmin;

$pageTitle = 'Manajemen Administrator';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?= flashMessage() ?>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="stat-card">
      <div class="stat-icon"><i class="bi bi-people-fill text-primary"></i></div>
      <div>
        <div class="stat-value"><?= $totalAll ?></div>
        <div class="stat-label">Total Akun Pengguna</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card">
      <div class="stat-icon" style="background-color: rgba(245, 158, 11, 0.12); color: var(--color-accent);"><i class="bi bi-shield-fill-check"></i></div>
      <div>
        <div class="stat-value"><?= $totalSuper ?></div>
        <div class="stat-label">Super Administrator</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card">
      <div class="stat-icon" style="background-color: rgba(37, 99, 235, 0.12); color: var(--color-secondary);"><i class="bi bi-person-badge-fill"></i></div>
      <div>
        <div class="stat-value"><?= $totalAdmin ?></div>
        <div class="stat-label">Admin Konten / Staf</div>
      </div>
    </div>
  </div>
</div>

<!-- Main Table Card -->
<div class="admin-card">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
      <h2 class="h5 fw-bold text-primary mb-1">Daftar Akun Administrator</h2>
      <p class="text-muted small mb-0">Kelola hak akses akun yang dapat masuk dan mengelola konten website sekolah.</p>
    </div>
    <div class="d-flex gap-2">
      <a href="form.php" class="btn btn-accent px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
        <i class="bi bi-person-plus-fill"></i> Tambah Admin Baru
      </a>
    </div>
  </div>

  <!-- Search & Filter Bar -->
  <form method="get" class="row g-2 mb-4">
    <div class="col-md-5">
      <div class="input-group input-group-sm">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" name="q" class="form-control" placeholder="Cari nama, username, atau email..." value="<?= e($search) ?>">
      </div>
    </div>
    <div class="col-md-4">
      <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">— Semua Tingkat Role —</option>
        <option value="superadmin" <?= $roleFilter === 'superadmin' ? 'selected' : '' ?>>Super Administrator</option>
        <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin Konten</option>
      </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
      <button type="submit" class="btn btn-sm btn-primary px-3">Filter</button>
      <?php if ($search !== '' || $roleFilter !== ''): ?>
        <a href="index.php" class="btn btn-sm btn-outline-secondary">Reset</a>
      <?php endif; ?>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead>
        <tr>
          <th style="width: 60px;">Foto</th>
          <th>Nama &amp; Email</th>
          <th>Username</th>
          <th>Hak Akses (Role)</th>
          <th>Terdaftar</th>
          <th style="width: 140px;" class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($users)): ?>
          <?php foreach ($users as $u): ?>
            <?php $isCurrent = ((int)$u['id'] === (int)$_SESSION['admin_id']); ?>
            <tr>
              <td>
                <?php if (!empty($u['photo']) && file_exists(BASE_PATH . '/' . $u['photo'])): ?>
                  <img src="<?= e(BASE_URL . '/' . $u['photo']) ?>" alt="<?= e($u['name']) ?>" class="thumb" style="width:42px;height:42px;border-radius:50%;object-fit:cover;">
                <?php else: ?>
                  <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border" style="width:42px;height:42px;">
                    <i class="bi bi-person text-secondary fs-5"></i>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <div class="fw-bold text-dark d-flex align-items-center gap-2">
                  <?= e($u['name']) ?>
                  <?php if ($isCurrent): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.7rem;">Anda</span>
                  <?php endif; ?>
                </div>
                <div class="small text-muted"><i class="bi bi-envelope me-1"></i><?= e($u['email']) ?></div>
              </td>
              <td>
                <span class="badge bg-light text-dark border font-monospace">@<?= e($u['username']) ?></span>
              </td>
              <td>
                <?php if ($u['role'] === 'superadmin'): ?>
                  <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                    <i class="bi bi-star-fill text-warning me-1"></i> Super Admin
                  </span>
                <?php else: ?>
                  <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                    <i class="bi bi-shield-check me-1"></i> Admin Konten
                  </span>
                <?php endif; ?>
              </td>
              <td class="small text-muted">
                <?= !empty($u['created_at']) ? date('d M Y', strtotime($u['created_at'])) : '-' ?>
              </td>
              <td class="text-end">
                <div class="btn-group btn-group-sm">
                  <a href="form.php?id=<?= (int) $u['id'] ?>" class="btn btn-outline-primary-custom" title="Edit Data Admin">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                  <?php if (!$isCurrent): ?>
                    <form method="post" action="delete.php" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun admin <?= e(addslashes($u['name'])) ?> (@<?= e($u['username']) ?>)?');">
                      <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                      <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                      <button type="submit" class="btn btn-outline-danger" title="Hapus Akun">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  <?php else: ?>
                    <button class="btn btn-outline-secondary" disabled title="Tidak dapat menghapus akun sendiri">
                      <i class="bi bi-lock"></i>
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" class="text-center py-4 text-muted">
              <i class="bi bi-people fs-2 d-block mb-2 text-muted"></i>
              Tidak ada akun admin yang sesuai dengan kriteria pencarian.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
