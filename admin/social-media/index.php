<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$socials = getDB()->query("SELECT * FROM social_media ORDER BY id ASC")->fetchAll();

$pageTitle = 'Social Media';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?= flashMessage() ?>
<div class="d-flex justify-content-end mb-3">
  <a href="form.php" class="btn btn-accent"><i class="bi bi-plus-lg"></i> Tambah Social Media</a>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead><tr><th>Ikon</th><th>Platform</th><th>URL</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($socials as $s): ?>
        <tr>
          <td><i class="bi <?= e($s['icon']) ?> fs-5 text-primary"></i></td>
          <td><?= e($s['platform']) ?></td>
          <td><a href="<?= e($s['url']) ?>" target="_blank" class="small"><?= e($s['url']) ?></a></td>
          <td><span class="badge <?= $s['status'] ? 'badge-status-active' : 'badge-status-inactive' ?>"><?= $s['status'] ? 'Aktif' : 'Nonaktif' ?></span></td>
          <td>
            <a href="form.php?id=<?= (int) $s['id'] ?>" class="btn btn-sm btn-outline-primary-custom"><i class="bi bi-pencil"></i></a>
            <form method="post" action="delete.php" class="d-inline" data-confirm-delete="Hapus tautan <?= e($s['platform']) ?>?">
              <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
              <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($socials)): ?><tr><td colspan="5" class="text-center text-muted py-4">Belum ada tautan.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
