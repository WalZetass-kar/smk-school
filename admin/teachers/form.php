<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$id = (int) ($_GET['id'] ?? 0);
$teacher = null;
if ($id) {
    $stmt = $db->prepare("SELECT * FROM teachers WHERE id = ?");
    $stmt->execute([$id]);
    $teacher = $stmt->fetch();
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $name        = trim($_POST['name'] ?? '');
        $nip         = trim($_POST['nip'] ?? '');
        $nuptk       = trim($_POST['nuptk'] ?? '');
        $subject     = trim($_POST['subject'] ?? '');
        $education   = trim($_POST['education'] ?? '');
        $position    = trim($_POST['position'] ?? '');
        $category    = in_array($_POST['category'] ?? '', ['guru', 'tendik'], true) ? $_POST['category'] : 'guru';
        $status      = in_array($_POST['status'] ?? '', ['aktif', 'nonaktif'], true) ? $_POST['status'] : 'aktif';
        $description = trim($_POST['description'] ?? '');
        $photo       = $teacher['photo'] ?? null;

        if ($name === '') { $errors[] = 'Nama wajib diisi.'; }

        if (!empty($_FILES['photo']['name'])) {
            $uploadError = null;
            $path = handleImageUpload($_FILES['photo'], $uploadError);
            if ($path) { $photo = $path; } else { $errors[] = 'Foto: ' . $uploadError; }
        } elseif (!$teacher && empty($photo)) {
            $photo = 'assets/images/og-default.jpg'; // neutral placeholder until admin uploads a real photo
        }

        if (empty($errors)) {
            if ($teacher) {
                $db->prepare("UPDATE teachers SET name=?, nip=?, nuptk=?, photo=?, subject=?, education=?, position=?, category=?, status=?, description=? WHERE id=?")
                   ->execute([$name, $nip, $nuptk, $photo, $subject, $education, $position, $category, $status, $description, $teacher['id']]);
            } else {
                $db->prepare("INSERT INTO teachers (name, nip, nuptk, photo, subject, education, position, category, status, description) VALUES (?,?,?,?,?,?,?,?,?,?)")
                   ->execute([$name, $nip, $nuptk, $photo, $subject, $education, $position, $category, $status, $description]);
            }
            setFlash('success', 'Data berhasil disimpan.');
            redirect(BASE_URL . '/admin/teachers/index.php');
        }
        $teacher = compact('name', 'nip', 'nuptk', 'photo', 'subject', 'education', 'position', 'category', 'status', 'description');
    }
}

$pageTitle = $teacher && $id ? 'Edit Guru/Tendik' : 'Tambah Guru/Tendik';
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
        <label class="form-label small fw-semibold">Nama</label>
        <input type="text" name="name" class="form-control" value="<?= e($teacher['name'] ?? '') ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Kategori</label>
        <select name="category" class="form-select">
          <option value="guru" <?= ($teacher['category'] ?? 'guru') === 'guru' ? 'selected' : '' ?>>Guru</option>
          <option value="tendik" <?= ($teacher['category'] ?? '') === 'tendik' ? 'selected' : '' ?>>Tendik</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Status</label>
        <select name="status" class="form-select">
          <option value="aktif" <?= ($teacher['status'] ?? 'aktif') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
          <option value="nonaktif" <?= ($teacher['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">NIP</label>
        <input type="text" name="nip" class="form-control" value="<?= e($teacher['nip'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">NUPTK</label>
        <input type="text" name="nuptk" class="form-control" value="<?= e($teacher['nuptk'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Jabatan</label>
        <input type="text" name="position" class="form-control" value="<?= e($teacher['position'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Mata Pelajaran / Bidang</label>
        <input type="text" name="subject" class="form-control" value="<?= e($teacher['subject'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Pendidikan</label>
        <input type="text" name="education" class="form-control" value="<?= e($teacher['education'] ?? '') ?>">
      </div>
      <div class="col-12">
        <label class="form-label small fw-semibold">Deskripsi</label>
        <textarea name="description" rows="3" class="form-control"><?= e($teacher['description'] ?? '') ?></textarea>
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Foto</label>
        <?php if (!empty($teacher['photo'])): ?><img src="<?= e(BASE_URL . '/' . $teacher['photo']) ?>" class="thumb mb-2 d-block" style="width:80px;height:80px;" alt=""><?php endif; ?>
        <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-accent px-4">Simpan</button>
        <a href="index.php" class="btn btn-outline-primary-custom px-4">Batal</a>
      </div>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
