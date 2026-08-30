<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$banners = getDB()->query("SELECT * FROM banners ORDER BY sort_order ASC, id DESC")->fetchAll();

$pageTitle = 'Banner';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?= flashMessage() ?>
<div class="d-flex justify-content-end mb-3">
  <a href="form.php" class="btn btn-accent"><i class="bi bi-plus-lg"></i> Tambah Banner</a>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead><tr><th>Gambar</th><th>Judul</th><th>Tombol</th><th>Urutan</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($banners as $b): ?>
        <tr>
          <td><img src="<?= e(BASE_URL . '/' . $b['image']) ?>" class="thumb" alt=""></td>
          <td style="max-width:220px;"><?= e($b['title']) ?></td>
          <td><?= e($b['button_text'] ?? '-') ?></td>
          <td><?= (int) $b['sort_order'] ?></td>
          <td><span class="badge <?= $b['status'] ? 'badge-status-active' : 'badge-status-inactive' ?>"><?= $b['status'] ? 'Aktif' : 'Nonaktif' ?></span></td>
          <td>
            <a href="form.php?id=<?= (int) $b['id'] ?>" class="btn btn-sm btn-outline-primary-custom"><i class="bi bi-pencil"></i></a>
            <form method="post" action="delete.php" class="d-inline" data-confirm-delete="Hapus banner ini?">
              <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
              <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($banners)): ?><tr><td colspan="6" class="text-center text-muted py-4">Belum ada banner.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
