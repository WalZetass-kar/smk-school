<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$principal = $db->query("SELECT * FROM principal_message ORDER BY id LIMIT 1")->fetch();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $name     = trim($_POST['principal_name'] ?? '');
        $position = trim($_POST['position'] ?? 'Kepala Sekolah');
        $content  = trim($_POST['content'] ?? '');
        $photo    = $principal['photo'] ?? null;

        if ($name === '') { $errors[] = 'Nama kepala sekolah wajib diisi.'; }
        if ($content === '') { $errors[] = 'Isi sambutan wajib diisi.'; }

        if (!empty($_FILES['photo']['name'])) {
            $uploadError = null;
            $path = handleImageUpload($_FILES['photo'], $uploadError);
            if ($path) { $photo = $path; } else { $errors[] = 'Foto: ' . $uploadError; }
        }

        if (empty($errors)) {
            if ($principal) {
                $stmt = $db->prepare("UPDATE principal_message SET principal_name=?, position=?, photo=?, content=? WHERE id=?");
                $stmt->execute([$name, $position, $photo, $content, $principal['id']]);
            } else {
                $stmt = $db->prepare("INSERT INTO principal_message (principal_name, position, photo, content) VALUES (?,?,?,?)");
                $stmt->execute([$name, $position, $photo, $content]);
            }
            logActivity('UPDATE_PRINCIPAL_MESSAGE', 'profile', "Memperbarui sambutan kepala sekolah: $name");
            setFlash('success', 'Sambutan kepala sekolah berhasil disimpan.');
            redirect(BASE_URL . '/admin/principal/index.php');
        }
        $principal = ['principal_name' => $name, 'position' => $position, 'photo' => $photo, 'content' => $content];
    }
}

$pageTitle = 'Sambutan Kepala Sekolah';
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
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Nama Kepala Sekolah</label>
        <input type="text" name="principal_name" class="form-control" value="<?= e($principal['principal_name'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Jabatan</label>
        <input type="text" name="position" class="form-control" value="<?= e($principal['position'] ?? 'Kepala Sekolah') ?>">
      </div>
      <div class="col-12">
        <label class="form-label small fw-semibold">Isi Sambutan</label>
        <textarea name="content" rows="8" class="form-control wysiwyg-editor" required><?= e($principal['content'] ?? '') ?></textarea>
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Foto</label>
        <?php if (!empty($principal['photo'])): ?><img src="<?= e(BASE_URL . '/' . $principal['photo']) ?>" class="thumb mb-2 d-block" style="width:80px;height:80px;" alt="Foto saat ini"><?php endif; ?>
        <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-accent px-4">Simpan Perubahan</button>
      </div>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
