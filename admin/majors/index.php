<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if (!isModuleActive('majors')) {
    setFlash('danger', 'Fitur Program Jurusan / Peminatan tidak tersedia pada paket lisensi sekolah ini.');
    redirect(BASE_URL . '/admin/dashboard.php');
}

$majors = getDB()->query("SELECT * FROM majors ORDER BY name ASC")->fetchAll();

$pageTitle = 'Daftar Jurusan';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?= flashMessage() ?>
<div class="d-flex justify-content-end mb-3">
  <a href="form.php" class="btn btn-accent"><i class="bi bi-plus-lg"></i> Tambah Jurusan</a>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead><tr><th>Foto</th><th>Kode</th><th>Nama Jurusan</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($majors as $m): ?>
        <tr>
          <td><img src="<?= e(BASE_URL . '/' . $m['image']) ?>" class="thumb" alt=""></td>
          <td><span class="badge text-bg-light border font-monospace"><?= e($m['code']) ?></span></td>
          <td><?= e($m['name']) ?></td>
          <td><span class="badge <?= $m['status'] ? 'badge-status-active' : 'badge-status-inactive' ?>"><?= $m['status'] ? 'Aktif' : 'Nonaktif' ?></span></td>
          <td>
            <a href="<?= e(BASE_URL) ?>/jurusan/detail/<?= e($m['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
            <a href="form.php?id=<?= (int) $m['id'] ?>" class="btn btn-sm btn-outline-primary-custom"><i class="bi bi-pencil"></i></a>
            <form method="post" action="delete.php" class="d-inline" data-confirm-delete="Hapus jurusan <?= e($m['name']) ?>?">
              <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
              <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($majors)): ?><tr><td colspan="5" class="text-center text-muted py-4">Belum ada jurusan.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
