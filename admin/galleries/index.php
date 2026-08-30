<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$galleries = $db->query("SELECT g.*, gc.name AS category_name FROM galleries g
                          LEFT JOIN gallery_categories gc ON gc.id = g.category_id
                          ORDER BY g.id DESC")->fetchAll();

$pageTitle = 'Semua Galeri';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?= flashMessage() ?>
<div class="d-flex justify-content-end mb-3">
  <a href="form.php" class="btn btn-accent"><i class="bi bi-plus-lg"></i> Tambah Foto</a>
</div>

<div class="admin-card">
  <div class="row g-3">
    <?php foreach ($galleries as $g): ?>
      <div class="col-lg-3 col-md-4 col-6">
        <div class="border rounded p-2 h-100 d-flex flex-column">
          <img src="<?= e(BASE_URL . '/' . $g['image']) ?>" class="rounded mb-2" style="height:130px; object-fit:cover; width:100%;" alt="">
          <div class="small fw-semibold flex-grow-1"><?= e($g['title']) ?></div>
          <div class="text-muted" style="font-size:0.75rem;"><?= e($g['category_name'] ?? '-') ?></div>
          <div class="d-flex gap-1 mt-2">
            <a href="form.php?id=<?= (int) $g['id'] ?>" class="btn btn-sm btn-outline-primary-custom flex-fill"><i class="bi bi-pencil"></i></a>
            <form method="post" action="delete.php" class="flex-fill" data-confirm-delete="Hapus foto ini?">
              <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
              <input type="hidden" name="id" value="<?= (int) $g['id'] ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-trash"></i></button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (empty($galleries)): ?><p class="text-muted text-center py-4">Belum ada foto.</p><?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
