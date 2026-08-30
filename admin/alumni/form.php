<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$id = (int) ($_GET['id'] ?? 0);
$item = null;
if ($id) {
    $stmt = $db->prepare("SELECT * FROM alumni_testimonials WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $name            = trim($_POST['name'] ?? '');
        $graduationYear  = trim($_POST['graduation_year'] ?? '');
        $major           = trim($_POST['major'] ?? '');
        $job             = trim($_POST['job'] ?? '');
        $testimonial     = trim($_POST['testimonial'] ?? '');
        $status          = isset($_POST['status']) ? 1 : 0;
        $photo           = $item['photo'] ?? null;

        if ($name === '') { $errors[] = 'Nama wajib diisi.'; }
        if ($testimonial === '') { $errors[] = 'Isi testimoni wajib diisi.'; }

        if (!empty($_FILES['photo']['name'])) {
            $uploadError = null;
            $path = handleImageUpload($_FILES['photo'], $uploadError);
            if ($path) { $photo = $path; } else { $errors[] = 'Foto: ' . $uploadError; }
        } elseif (!$item && empty($photo)) {
            $photo = 'assets/images/og-default.jpg';
        }

        if (empty($errors)) {
            if ($item) {
                $db->prepare("UPDATE alumni_testimonials SET name=?, graduation_year=?, major=?, job=?, photo=?, testimonial=?, status=? WHERE id=?")
                   ->execute([$name, $graduationYear, $major, $job, $photo, $testimonial, $status, $item['id']]);
            } else {
                $db->prepare("INSERT INTO alumni_testimonials (name, graduation_year, major, job, photo, testimonial, status) VALUES (?,?,?,?,?,?,?)")
                   ->execute([$name, $graduationYear, $major, $job, $photo, $testimonial, $status]);
            }
            setFlash('success', 'Testimoni berhasil disimpan.');
            redirect(BASE_URL . '/admin/alumni/index.php');
        }
        $item = compact('name', 'graduationYear', 'major', 'job', 'photo', 'testimonial', 'status');
        $item['graduation_year'] = $graduationYear;
    }
}

$pageTitle = $id ? 'Edit Testimoni' : 'Tambah Testimoni';
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
        <input type="text" name="name" class="form-control" value="<?= e($item['name'] ?? '') ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Tahun Lulus</label>
        <input type="text" name="graduation_year" class="form-control" value="<?= e($item['graduation_year'] ?? '') ?>">
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
          <input type="checkbox" name="status" class="form-check-input" id="statusCheck" <?= ($item['status'] ?? 1) ? 'checked' : '' ?>>
          <label class="form-check-label" for="statusCheck">Tampilkan (aktif)</label>
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Jurusan</label>
        <input type="text" name="major" class="form-control" value="<?= e($item['major'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Pekerjaan / Posisi</label>
        <input type="text" name="job" class="form-control" value="<?= e($item['job'] ?? '') ?>">
      </div>
      <div class="col-12">
        <label class="form-label small fw-semibold">Testimoni</label>
        <textarea name="testimonial" rows="4" class="form-control" required><?= e($item['testimonial'] ?? '') ?></textarea>
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Foto</label>
        <?php if (!empty($item['photo'])): ?><img src="<?= e(BASE_URL . '/' . $item['photo']) ?>" class="thumb mb-2 d-block" style="width:80px;height:80px;" alt=""><?php endif; ?>
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
