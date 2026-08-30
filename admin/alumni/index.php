<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$alumni = getDB()->query("SELECT * FROM alumni_testimonials ORDER BY id DESC")->fetchAll();

$pageTitle = 'Testimoni Alumni';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?= flashMessage() ?>
<div class="d-flex justify-content-end mb-3">
  <a href="form.php" class="btn btn-accent"><i class="bi bi-plus-lg"></i> Tambah Testimoni</a>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead><tr><th>Foto</th><th>Nama</th><th>Angkatan</th><th>Jurusan</th><th>Pekerjaan</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($alumni as $a): ?>
        <tr>
          <td><img src="<?= e(BASE_URL . '/' . $a['photo']) ?>" class="thumb" alt=""></td>
          <td><?= e($a['name']) ?></td>
          <td><?= e($a['graduation_year']) ?></td>
          <td><?= e($a['major']) ?></td>
          <td><?= e($a['job']) ?></td>
          <td><span class="badge <?= $a['status'] ? 'badge-status-active' : 'badge-status-inactive' ?>"><?= $a['status'] ? 'Aktif' : 'Nonaktif' ?></span></td>
          <td>
            <a href="form.php?id=<?= (int) $a['id'] ?>" class="btn btn-sm btn-outline-primary-custom"><i class="bi bi-pencil"></i></a>
            <form method="post" action="delete.php" class="d-inline" data-confirm-delete="Hapus testimoni <?= e($a['name']) ?>?">
              <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
              <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($alumni)): ?><tr><td colspan="7" class="text-center text-muted py-4">Belum ada testimoni.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
