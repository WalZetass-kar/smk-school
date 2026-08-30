<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$id = (int) ($_GET['id'] ?? 0);
$major = null;
if ($id) {
    $stmt = $db->prepare("SELECT * FROM majors WHERE id = ?");
    $stmt->execute([$id]);
    $major = $stmt->fetch();
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $name             = trim($_POST['name'] ?? '');
        $code             = trim(strtoupper($_POST['code'] ?? ''));
        $shortDescription = trim($_POST['short_description'] ?? '');
        $description      = trim($_POST['description'] ?? '');
        $competency       = trim($_POST['competency'] ?? '');
        $career           = trim($_POST['career'] ?? '');
        $facilities       = trim($_POST['facilities'] ?? '');
        $status           = isset($_POST['status']) ? 1 : 0;
        $image            = $major['image'] ?? null;

        if ($name === '') { $errors[] = 'Nama jurusan wajib diisi.'; }

        // Auto slug, made unique against other rows (excluding this one on edit).
        $slug = slugify($name);
        $base = $slug;
        $n = 1;
        while (true) {
            $check = $db->prepare("SELECT id FROM majors WHERE slug = ? AND id != ?");
            $check->execute([$slug, $major['id'] ?? 0]);
            if (!$check->fetch()) break;
            $slug = $base . '-' . (++$n);
        }

        if (!empty($_FILES['image']['name'])) {
            $uploadError = null;
            $path = handleImageUpload($_FILES['image'], $uploadError);
            if ($path) { $image = $path; } else { $errors[] = 'Gambar: ' . $uploadError; }
        } elseif (!$major && empty($image)) {
            $errors[] = 'Gambar jurusan wajib diunggah.';
        }

        if (empty($errors)) {
            if ($major) {
                $db->prepare("UPDATE majors SET name=?, slug=?, code=?, image=?, short_description=?, description=?, competency=?, career=?, facilities=?, status=? WHERE id=?")
                   ->execute([$name, $slug, $code, $image, $shortDescription, $description, $competency, $career, $facilities, $status, $major['id']]);
            } else {
                $db->prepare("INSERT INTO majors (name, slug, code, image, short_description, description, competency, career, facilities, status) VALUES (?,?,?,?,?,?,?,?,?,?)")
                   ->execute([$name, $slug, $code, $image, $shortDescription, $description, $competency, $career, $facilities, $status]);
            }
            setFlash('success', 'Jurusan berhasil disimpan.');
            redirect(BASE_URL . '/admin/majors/index.php');
        }
        $major = compact('name', 'code', 'image', 'shortDescription', 'description', 'competency', 'career', 'facilities', 'status');
        $major['short_description'] = $shortDescription;
    }
}

$pageTitle = $id ? 'Edit Jurusan' : 'Tambah Jurusan';
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
        <label class="form-label small fw-semibold">Nama Jurusan</label>
        <input type="text" name="name" class="form-control" value="<?= e($major['name'] ?? '') ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Kode</label>
        <input type="text" name="code" class="form-control text-uppercase" value="<?= e($major['code'] ?? '') ?>" placeholder="mis. TKJ">
      </div>
      <div class="col-12">
        <label class="form-label small fw-semibold">Deskripsi Singkat</label>
        <input type="text" name="short_description" class="form-control" value="<?= e($major['short_description'] ?? '') ?>">
      </div>
      <div class="col-12">
        <label class="form-label small fw-semibold">Deskripsi Lengkap</label>
        <textarea name="description" rows="4" class="form-control wysiwyg-editor"><?= e($major['description'] ?? '') ?></textarea>
      </div>
      <div class="col-12">
        <label class="form-label small fw-semibold">Kompetensi Keahlian</label>
        <textarea name="competency" rows="3" class="form-control wysiwyg-editor"><?= e($major['competency'] ?? '') ?></textarea>
      </div>
      <div class="col-12">
        <label class="form-label small fw-semibold">Prospek Kerja</label>
        <textarea name="career" rows="3" class="form-control wysiwyg-editor"><?= e($major['career'] ?? '') ?></textarea>
      </div>
      <div class="col-12">
        <label class="form-label small fw-semibold">Fasilitas</label>
        <textarea name="facilities" rows="3" class="form-control wysiwyg-editor"><?= e($major['facilities'] ?? '') ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Gambar</label>
        <?php if (!empty($major['image'])): ?><img src="<?= e(BASE_URL . '/' . $major['image']) ?>" class="thumb mb-2 d-block" style="width:100px;height:100px;" alt=""><?php endif; ?>
        <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp">
      </div>
      <div class="col-md-6 d-flex align-items-end">
        <div class="form-check">
          <input type="checkbox" name="status" class="form-check-input" id="statusCheck" <?= ($major['status'] ?? 1) ? 'checked' : '' ?>>
          <label class="form-check-label" for="statusCheck">Tampilkan di website (aktif)</label>
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
