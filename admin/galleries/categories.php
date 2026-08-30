<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$errors = [];
$editId = (int) ($_GET['edit'] ?? 0);
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } elseif (($_POST['action'] ?? '') === 'delete') {
        $delId = (int) ($_POST['id'] ?? 0);
        $db->prepare("UPDATE galleries SET category_id = NULL WHERE category_id = ?")->execute([$delId]);
        $db->prepare("DELETE FROM gallery_categories WHERE id = ?")->execute([$delId]);
        setFlash('success', 'Kategori berhasil dihapus.');
        redirect(BASE_URL . '/admin/galleries/categories.php');
    } else {
        $name = trim($_POST['name'] ?? '');
        $id   = (int) ($_POST['id'] ?? 0);
        if ($name === '') {
            $errors[] = 'Nama kategori wajib diisi.';
        } else {
            $slug = slugify($name);
            $base = $slug; $n = 1;
            while (true) {
                $check = $db->prepare("SELECT id FROM gallery_categories WHERE slug = ? AND id != ?");
                $check->execute([$slug, $id]);
                if (!$check->fetch()) break;
                $slug = $base . '-' . (++$n);
            }
            if ($id) {
                $db->prepare("UPDATE gallery_categories SET name=?, slug=? WHERE id=?")->execute([$name, $slug, $id]);
                setFlash('success', 'Kategori berhasil diperbarui.');
            } else {
                $db->prepare("INSERT INTO gallery_categories (name, slug) VALUES (?,?)")->execute([$name, $slug]);
                setFlash('success', 'Kategori berhasil ditambahkan.');
            }
            redirect(BASE_URL . '/admin/galleries/categories.php');
        }
    }
}

if ($editId) {
    $stmt = $db->prepare("SELECT * FROM gallery_categories WHERE id = ?");
    $stmt->execute([$editId]);
    $editing = $stmt->fetch();
}

$categories = $db->query("SELECT c.*, (SELECT COUNT(*) FROM galleries WHERE category_id = c.id) AS total_photos FROM gallery_categories c ORDER BY name ASC")->fetchAll();

$pageTitle = 'Kategori Galeri';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?= flashMessage() ?>
<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="admin-card">
      <h2 class="h6 text-primary mb-3"><?= $editing ? 'Edit Kategori' : 'Tambah Kategori' ?></h2>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
        <div class="mb-3">
          <label class="form-label small fw-semibold">Nama Kategori</label>
          <input type="text" name="name" class="form-control" value="<?= e($editing['name'] ?? '') ?>" required>
        </div>
        <button type="submit" class="btn btn-accent px-4">Simpan</button>
        <?php if ($editing): ?><a href="categories.php" class="btn btn-outline-primary-custom px-4">Batal</a><?php endif; ?>
      </form>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="admin-card">
      <div class="table-responsive">
        <table class="table table-admin align-middle">
          <thead><tr><th>Nama</th><th>Slug</th><th>Jumlah Foto</th><th>Aksi</th></tr></thead>
          <tbody>
            <?php foreach ($categories as $c): ?>
            <tr>
              <td><?= e($c['name']) ?></td>
              <td><code><?= e($c['slug']) ?></code></td>
              <td><?= (int) $c['total_photos'] ?></td>
              <td>
                <a href="?edit=<?= (int) $c['id'] ?>" class="btn btn-sm btn-outline-primary-custom"><i class="bi bi-pencil"></i></a>
                <form method="post" class="d-inline" data-confirm-delete="Hapus kategori <?= e($c['name']) ?>?">
                  <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($categories)): ?><tr><td colspan="4" class="text-center text-muted py-4">Belum ada kategori.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
