<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db  = getDB();
$org = $db->query("SELECT * FROM organization ORDER BY id LIMIT 1")->fetch();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $image       = $org['image'] ?? null;
        if ($title === '') { $errors[] = 'Judul wajib diisi.'; }

        if (!empty($_FILES['image']['name'])) {
            $uploadError = null;
            $path = handleImageUpload($_FILES['image'], $uploadError);
            if ($path) { $image = $path; } else { $errors[] = 'Gambar: ' . $uploadError; }
        }

        if (empty($errors)) {
            if ($org) {
                $db->prepare("UPDATE organization SET title=?, image=?, description=? WHERE id=?")->execute([$title, $image, $description, $org['id']]);
            } else {
                $db->prepare("INSERT INTO organization (title, image, description) VALUES (?,?,?)")->execute([$title, $image, $description]);
            }
            setFlash('success', 'Struktur organisasi berhasil disimpan.');
            redirect(BASE_URL . '/admin/organization/index.php');
        }
        $org = ['title' => $title, 'image' => $image, 'description' => $description];
    }
}

$pageTitle = 'Struktur Organisasi';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?= flashMessage() ?>
<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="admin-card">
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <div class="mb-3">
      <label class="form-label small fw-semibold">Judul</label>
      <input type="text" name="title" class="form-control" value="<?= e($org['title'] ?? 'Struktur Organisasi Sekolah') ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label small fw-semibold">Deskripsi</label>
      <textarea name="description" rows="3" class="form-control"><?= e($org['description'] ?? '') ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label small fw-semibold">Gambar Bagan Struktur</label>
      <?php if (!empty($org['image'])): ?><img src="<?= e(BASE_URL . '/' . $org['image']) ?>" class="d-block mb-2" style="max-width:320px;" alt="Bagan saat ini"><?php endif; ?>
      <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp" style="max-width:400px;">
    </div>
    <button type="submit" class="btn btn-accent px-4">Simpan Perubahan</button>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
