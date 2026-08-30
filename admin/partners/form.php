<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$id = (int) ($_GET['id'] ?? 0);
$item = null;
if ($id) {
    $stmt = $db->prepare("SELECT * FROM partners WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $name        = trim($_POST['name'] ?? '');
        $website     = trim($_POST['website'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sortOrder   = (int) ($_POST['sort_order'] ?? 0);
        $status      = isset($_POST['status']) ? 1 : 0;
        $logo        = $item['logo'] ?? null;

        if ($name === '') { $errors[] = 'Nama partner wajib diisi.'; }

        if (!empty($_FILES['logo']['name'])) {
            $uploadError = null;
            $path = handleImageUpload($_FILES['logo'], $uploadError);
            if ($path) { $logo = $path; } else { $errors[] = 'Logo: ' . $uploadError; }
        } elseif (!$item && empty($logo)) {
            $errors[] = 'Logo wajib diunggah.';
        }

        if (empty($errors)) {
            if ($item) {
                $db->prepare("UPDATE partners SET name=?, logo=?, website=?, description=?, status=?, sort_order=? WHERE id=?")
                   ->execute([$name, $logo, $website, $description, $status, $sortOrder, $item['id']]);
            } else {
                $db->prepare("INSERT INTO partners (name, logo, website, description, status, sort_order) VALUES (?,?,?,?,?,?)")
                   ->execute([$name, $logo, $website, $description, $status, $sortOrder]);
            }
            setFlash('success', 'Partner berhasil disimpan.');
            redirect(BASE_URL . '/admin/partners/index.php');
        }
        $item = compact('name', 'website', 'description', 'sortOrder', 'status', 'logo');
        $item['sort_order'] = $sortOrder;
    }
}

$pageTitle = $id ? 'Edit Partner' : 'Tambah Partner';
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
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Nama Perusahaan/Instansi</label>
        <input type="text" name="name" class="form-control" value="<?= e($item['name'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Website</label>
        <input type="text" name="website" class="form-control" value="<?= e($item['website'] ?? '') ?>" placeholder="https://">
      </div>
      <div class="col-12">
        <label class="form-label small fw-semibold">Deskripsi</label>
        <input type="text" name="description" class="form-control" value="<?= e($item['description'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Urutan</label>
        <input type="number" name="sort_order" class="form-control" value="<?= (int) ($item['sort_order'] ?? 0) ?>">
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <div class="form-check">
          <input type="checkbox" name="status" class="form-check-input" id="statusCheck" <?= ($item['status'] ?? 1) ? 'checked' : '' ?>>
          <label class="form-check-label" for="statusCheck">Tampilkan (aktif)</label>
        </div>
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Logo</label>
        <?php if (!empty($item['logo'])): ?><img src="<?= e(BASE_URL . '/' . $item['logo']) ?>" class="thumb mb-2 d-block" style="width:80px;height:80px;object-fit:contain;background:#f5f5f5;" alt=""><?php endif; ?>
        <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-accent px-4">Simpan</button>
        <a href="index.php" class="btn btn-outline-primary-custom px-4">Batal</a>
      </div>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
