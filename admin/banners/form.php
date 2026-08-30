<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$id = (int) ($_GET['id'] ?? 0);
$item = null;
if ($id) {
    $stmt = $db->prepare("SELECT * FROM banners WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $buttonText  = trim($_POST['button_text'] ?? '');
        $buttonUrl   = trim($_POST['button_url'] ?? '');
        $sortOrder   = (int) ($_POST['sort_order'] ?? 0);
        $status      = isset($_POST['status']) ? 1 : 0;
        $image       = $item['image'] ?? null;

        if ($title === '') { $errors[] = 'Judul wajib diisi.'; }

        if (!empty($_FILES['image']['name'])) {
            $uploadError = null;
            $path = handleImageUpload($_FILES['image'], $uploadError);
            if ($path) { $image = $path; } else { $errors[] = 'Gambar: ' . $uploadError; }
        } elseif (!$item && empty($image)) {
            $errors[] = 'Gambar banner wajib diunggah.';
        }

        if (empty($errors)) {
            if ($item) {
                $db->prepare("UPDATE banners SET title=?, description=?, image=?, button_text=?, button_url=?, sort_order=?, status=? WHERE id=?")
                   ->execute([$title, $description, $image, $buttonText, $buttonUrl, $sortOrder, $status, $item['id']]);
            } else {
                $db->prepare("INSERT INTO banners (title, description, image, button_text, button_url, sort_order, status) VALUES (?,?,?,?,?,?,?)")
                   ->execute([$title, $description, $image, $buttonText, $buttonUrl, $sortOrder, $status]);
            }
            setFlash('success', 'Banner berhasil disimpan.');
            redirect(BASE_URL . '/admin/banners/index.php');
        }
        $item = compact('title', 'description', 'buttonText', 'buttonUrl', 'sortOrder', 'status', 'image');
        $item['button_text'] = $buttonText;
        $item['button_url']  = $buttonUrl;
        $item['sort_order']  = $sortOrder;
    }
}

$pageTitle = $id ? 'Edit Banner' : 'Tambah Banner';
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
      <div class="col-12">
        <label class="form-label small fw-semibold">Judul</label>
        <input type="text" name="title" class="form-control" value="<?= e($item['title'] ?? '') ?>" required>
      </div>
      <div class="col-12">
        <label class="form-label small fw-semibold">Deskripsi Singkat</label>
        <input type="text" name="description" class="form-control" value="<?= e($item['description'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Teks Tombol</label>
        <input type="text" name="button_text" class="form-control" value="<?= e($item['button_text'] ?? '') ?>" placeholder="mis. Selengkapnya">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Link Tombol</label>
        <input type="text" name="button_url" class="form-control" value="<?= e($item['button_url'] ?? '') ?>" placeholder="/profil">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Urutan</label>
        <input type="number" name="sort_order" class="form-control" value="<?= (int) ($item['sort_order'] ?? 0) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Gambar Banner</label>
        <?php if (!empty($item['image'])): ?><img src="<?= e(BASE_URL . '/' . $item['image']) ?>" class="d-block mb-2" style="max-width:240px;" alt=""><?php endif; ?>
        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
      </div>
      <div class="col-md-6 d-flex align-items-end">
        <div class="form-check">
          <input type="checkbox" name="status" class="form-check-input" id="statusCheck" <?= ($item['status'] ?? 1) ? 'checked' : '' ?>>
          <label class="form-check-label" for="statusCheck">Tampilkan (aktif)</label>
        </div>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-accent px-4">Simpan</button>
        <a href="index.php" class="btn btn-outline-primary-custom px-4">Batal</a>
      </div>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
