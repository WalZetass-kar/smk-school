<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db     = getDB();
$filter = $_GET['filter'] ?? 'semua';
$filter = in_array($filter, ['guru', 'tendik'], true) ? $filter : 'semua';

if ($filter === 'semua') {
    $teachers = $db->query("SELECT * FROM teachers ORDER BY category ASC, name ASC")->fetchAll();
} else {
    $stmt = $db->prepare("SELECT * FROM teachers WHERE category = ? ORDER BY name ASC");
    $stmt->execute([$filter]);
    $teachers = $stmt->fetchAll();
}

$pageTitle = 'Guru & Tendik';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?= flashMessage() ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="btn-group">
    <a href="?filter=semua" class="btn btn-sm <?= $filter === 'semua' ? 'btn-accent' : 'btn-outline-primary-custom' ?>">Semua</a>
    <a href="?filter=guru" class="btn btn-sm <?= $filter === 'guru' ? 'btn-accent' : 'btn-outline-primary-custom' ?>">Guru</a>
    <a href="?filter=tendik" class="btn btn-sm <?= $filter === 'tendik' ? 'btn-accent' : 'btn-outline-primary-custom' ?>">Tendik</a>
  </div>
  <a href="form.php" class="btn btn-accent"><i class="bi bi-plus-lg"></i> Tambah</a>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead><tr><th>Foto</th><th>Nama</th><th>Kategori</th><th>Jabatan</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($teachers as $t): ?>
        <tr>
          <td><img src="<?= e(BASE_URL . '/' . $t['photo']) ?>" class="thumb" alt=""></td>
          <td><?= e($t['name']) ?></td>
          <td><span class="text-capitalize"><?= e($t['category']) ?></span></td>
          <td><?= e($t['position'] ?? '-') ?></td>
          <td><span class="badge <?= $t['status'] === 'aktif' ? 'badge-status-active' : 'badge-status-inactive' ?>"><?= e(ucfirst($t['status'])) ?></span></td>
          <td>
            <a href="form.php?id=<?= (int) $t['id'] ?>" class="btn btn-sm btn-outline-primary-custom"><i class="bi bi-pencil"></i></a>
            <form method="post" action="delete.php" class="d-inline" data-confirm-delete="Hapus data <?= e($t['name']) ?>?">
              <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
              <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($teachers)): ?><tr><td colspan="6" class="text-center text-muted py-4">Belum ada data.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
