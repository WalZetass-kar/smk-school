<?php
/**
 * admin/extracurriculars/form.php
 * Tambah / Edit Ekstrakurikuler
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;
$item = null;
$errors = [];

if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM extracurriculars WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) {
        setFlash('error', 'Data tidak ditemukan.');
        redirect(BASE_URL . '/admin/extracurriculars/index.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan muat ulang halaman.';
    } else {
        $name        = trim($_POST['name'] ?? '');
        $category    = trim($_POST['category'] ?? 'Olahraga');
        $icon        = trim($_POST['icon'] ?? 'bi-trophy');
        $schedule    = trim($_POST['schedule'] ?? '') ?: null;
        $coachName   = trim($_POST['coach_name'] ?? '') ?: null;
        $description = trim($_POST['description'] ?? '');
        $status      = isset($_POST['status']) ? 1 : 0;

        if ($name === '')        $errors[] = 'Nama ekstrakurikuler wajib diisi.';
        if ($description === '') $errors[] = 'Deskripsi ekstrakurikuler wajib diisi.';

        $imagePath = $item['image'] ?? null;
        if (!empty($_FILES['image']['name'])) {
            $uploadError = null;
            $uploaded = handleImageUpload($_FILES['image'], 'ekskul', $uploadError);
            if ($uploaded) {
                if ($imagePath && file_exists(BASE_PATH . '/' . $imagePath)) {
                    @unlink(BASE_PATH . '/' . $imagePath);
                }
                $imagePath = $uploaded;
            } else {
                $errors[] = 'Foto: ' . $uploadError;
            }
        }

        if (empty($errors)) {
            $baseSlug = slugify($name);
            if ($isEdit) {
                $stmt = $db->prepare("SELECT COUNT(*) FROM extracurriculars WHERE slug = ? AND id != ?");
                $stmt->execute([$baseSlug, $id]);
                $slug = ((int)$stmt->fetchColumn() > 0) ? $baseSlug . '-' . $id : $baseSlug;

                $stmt = $db->prepare("
                    UPDATE extracurriculars
                    SET name = ?, slug = ?, category = ?, icon = ?, image = ?,
                        schedule = ?, coach_name = ?, description = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $slug, $category, $icon, $imagePath, $schedule, $coachName, $description, $status, $id]);
                setFlash('success', 'Ekstrakurikuler berhasil diperbarui.');
            } else {
                $stmt = $db->prepare("SELECT COUNT(*) FROM extracurriculars WHERE slug = ?");
                $stmt->execute([$baseSlug]);
                $slug = ((int)$stmt->fetchColumn() > 0) ? $baseSlug . '-' . time() : $baseSlug;

                $stmt = $db->prepare("
                    INSERT INTO extracurriculars (name, slug, category, icon, image, schedule, coach_name, description, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $slug, $category, $icon, $imagePath, $schedule, $coachName, $description, $status]);
                setFlash('success', 'Ekstrakurikuler baru berhasil ditambahkan.');
            }
            redirect(BASE_URL . '/admin/extracurriculars/index.php');
        }

        $item = [
            'name'        => $name,
            'category'    => $category,
            'icon'        => $icon,
            'image'       => $imagePath,
            'schedule'    => $schedule,
            'coach_name'  => $coachName,
            'description' => $description,
            'status'      => $status,
        ];
    }
}

$pageTitle = ($isEdit ? 'Edit' : 'Tambah') . ' Ekstrakurikuler';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>

<?= flashMessage() ?>

<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h4 fw-bold text-primary mb-0"><?= $pageTitle ?></h1>
  <a href="index.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

<div class="admin-card">
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Nama Ekstrakurikuler <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="<?= e($item['name'] ?? '') ?>" required placeholder="Contoh: IT Club & Robotika">
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Kategori Ekstrakurikuler <span class="text-danger">*</span></label>
        <input type="text" name="category" class="form-control" list="catList" value="<?= e($item['category'] ?? 'Olahraga & Bela Diri') ?>" required>
        <datalist id="catList">
          <option value="Kejuruan & Teknologi">
          <option value="Kepemimpinan & Karakter">
          <option value="Olahraga & Prestasi">
          <option value="Seni, Musik & Budaya">
          <option value="Sosial & Kemanusiaan">
          <option value="Keagamaan & Rohis">
        </datalist>
      </div>

      <div class="col-md-4">
        <label class="form-label small fw-semibold">Icon Bootstrap (bi-*)</label>
        <input type="text" name="icon" class="form-control" value="<?= e($item['icon'] ?? 'bi-trophy') ?>" placeholder="bi-cpu, bi-heart-pulse, bi-dribbble">
      </div>

      <div class="col-md-4">
        <label class="form-label small fw-semibold">Jadwal Latihan</label>
        <input type="text" name="schedule" class="form-control" value="<?= e($item['schedule'] ?? '') ?>" placeholder="Contoh: Setiap Sabtu, 09.00 - 12.00 WIB">
      </div>

      <div class="col-md-4">
        <label class="form-label small fw-semibold">Nama Pembina / Pelatih</label>
        <input type="text" name="coach_name" class="form-control" value="<?= e($item['coach_name'] ?? '') ?>" placeholder="Contoh: Budi Santoso, S.Pd.">
      </div>

      <div class="col-12">
        <label class="form-label small fw-semibold">Deskripsi &amp; Aktivitas Ekskul <span class="text-danger">*</span></label>
        <textarea name="description" rows="5" class="form-control wysiwyg-editor"><?= e($item['description'] ?? '') ?></textarea>
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Foto Dokumentasi Kegiatan</label>
        <input type="file" name="image" class="form-control" accept="image/*" data-preview="#imgPreview">
        <div class="form-text">Format: JPG, PNG, WEBP. Maks 2MB.</div>
      </div>

      <div class="col-md-6 text-center">
        <?php if (!empty($item['image'])): ?>
          <img id="imgPreview" src="<?= e(BASE_URL . '/' . $item['image']) ?>" alt="Preview" class="img-thumbnail mt-2" style="max-height: 120px;">
        <?php else: ?>
          <img id="imgPreview" src="" alt="Preview" class="img-thumbnail mt-2 d-none" style="max-height: 120px;">
        <?php endif; ?>
      </div>

      <div class="col-12">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="statusSwitch" name="status" value="1" <?= ($item['status'] ?? 1) ? 'checked' : '' ?>>
          <label class="form-check-label fw-semibold" for="statusSwitch">Tampilkan di Website Publik (Status Aktif)</label>
        </div>
      </div>

      <div class="col-12 pt-3 border-top">
        <button type="submit" class="btn btn-accent px-4"><i class="bi bi-save me-1"></i>Simpan</button>
        <a href="index.php" class="btn btn-outline-secondary px-3 ms-2">Batal</a>
      </div>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
