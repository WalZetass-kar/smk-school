<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireSuperAdmin();

$users = getDB()->query("SELECT * FROM users ORDER BY id ASC")->fetchAll();

$pageTitle = 'Manajemen Admin';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?= flashMessage() ?>
<div class="d-flex justify-content-end mb-3">
  <a href="form.php" class="btn btn-accent"><i class="bi bi-plus-lg"></i> Tambah Admin</a>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead><tr><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?= e($u['name']) ?></td>
          <td><?= e($u['username']) ?></td>
          <td><?= e($u['email']) ?></td>
          <td><span class="badge text-bg-light border text-capitalize"><?= e($u['role']) ?></span></td>
          <td>
            <a href="form.php?id=<?= (int) $u['id'] ?>" class="btn btn-sm btn-outline-primary-custom"><i class="bi bi-pencil"></i></a>
            <?php if ((int) $u['id'] !== (int) $_SESSION['admin_id']): ?>
              <form method="post" action="delete.php" class="d-inline" data-confirm-delete="Hapus akun <?= e($u['name']) ?>?">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
