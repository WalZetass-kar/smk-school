<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$partners = getDB()->query("SELECT * FROM partners ORDER BY sort_order ASC, id DESC")->fetchAll();

$pageTitle = 'Data Partner';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?= flashMessage() ?>
<div class="d-flex justify-content-end mb-3">
  <a href="form.php" class="btn btn-accent"><i class="bi bi-plus-lg"></i> Tambah Partner</a>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead><tr><th>Logo</th><th>Nama</th><th>Website</th><th>Urutan</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($partners as $p): ?>
        <tr>
          <td><img src="<?= e(BASE_URL . '/' . $p['logo']) ?>" class="thumb" style="object-fit:contain; background:#f5f5f5;" alt=""></td>
          <td><?= e($p['name']) ?></td>
          <td><a href="<?= e($p['website'] ?? '#') ?>" target="_blank" class="small"><?= e($p['website'] ?? '-') ?></a></td>
          <td><?= (int) $p['sort_order'] ?></td>
          <td><span class="badge <?= $p['status'] ? 'badge-status-active' : 'badge-status-inactive' ?>"><?= $p['status'] ? 'Aktif' : 'Nonaktif' ?></span></td>
          <td>
            <a href="form.php?id=<?= (int) $p['id'] ?>" class="btn btn-sm btn-outline-primary-custom"><i class="bi bi-pencil"></i></a>
            <form method="post" action="delete.php" class="d-inline" data-confirm-delete="Hapus partner <?= e($p['name']) ?>?">
              <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
              <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($partners)): ?><tr><td colspan="6" class="text-center text-muted py-4">Belum ada partner.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
