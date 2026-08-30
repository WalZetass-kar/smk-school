<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$id = (int) ($_GET['id'] ?? 0);
$item = null;
if ($id) {
    $stmt = $db->prepare("SELECT * FROM galleries WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
}
$categories = $db->query("SELECT * FROM gallery_categories ORDER BY name ASC")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $categoryId  = (int) ($_POST['category_id'] ?? 0) ?: null;
        $image       = $item['image'] ?? null;

        if ($title === '') { $errors[] = 'Judul wajib diisi.'; }

        if (!empty($_FILES['image']['name'])) {
            $uploadError = null;
            $path = handleImageUpload($_FILES['image'], $uploadError);
            if ($path) { $image = $path; } else { $errors[] = 'Gambar: ' . $uploadError; }
        } elseif (!$item && empty($image)) {
            $errors[] = 'Gambar wajib diunggah.';
        }

        if (empty($errors)) {
            if ($item) {
                $db->prepare("UPDATE galleries SET category_id=?, title=?, description=?, image=? WHERE id=?")
                   ->execute([$categoryId, $title, $description, $image, $item['id']]);
            } else {
                $db->prepare("INSERT INTO galleries (category_id, title, description, image) VALUES (?,?,?,?)")
                   ->execute([$categoryId, $title, $description, $image]);
            }
            setFlash('success', 'Foto berhasil disimpan.');
            redirect(BASE_URL . '/admin/galleries/index.php');
        }
        $item = ['title' => $title, 'description' => $description, 'category_id' => $categoryId, 'image' => $image];
    }
}

$pageTitle = $id ? 'Edit Foto' : 'Tambah Foto';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="admin-card">
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label small fw-semibold">Judul</label>
        <input type="text" name="title" class="form-control" value="<?= e($item['title'] ?? '') ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Kategori</label>
        <select name="category_id" class="form-select">
          <option value="">— Tanpa kategori —</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= (int) ($item['category_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label small fw-semibold">Deskripsi</label>
        <textarea name="description" rows="2" class="form-control"><?= e($item['description'] ?? '') ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Gambar</label>
        <?php if (!empty($item['image'])): ?><img src="<?= e(BASE_URL . '/' . $item['image']) ?>" class="thumb mb-2 d-block" style="width:100px;height:100px;" alt="" data-preview="#previewImg"><?php endif; ?>
        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp" data-preview="#previewImg">
        <img id="previewImg" class="thumb mt-2 d-none" style="width:100px;height:100px;" alt="Pratinjau">
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-accent px-4">Simpan</button>
        <a href="index.php" class="btn btn-outline-primary-custom px-4">Batal</a>
      </div>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
