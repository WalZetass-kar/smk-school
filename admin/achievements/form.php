<?php
/**
 * admin/achievements/form.php
 * Tambah / Edit Prestasi Siswa
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;
$item = null;
$errors = [];

if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM achievements WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) {
        setFlash('error', 'Data tidak ditemukan.');
        redirect(BASE_URL . '/admin/achievements/index.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan muat ulang halaman.';
    } else {
        $title           = trim($_POST['title'] ?? '');
        $studentName     = trim($_POST['student_name'] ?? '');
        $competitionName = trim($_POST['competition_name'] ?? '');
        $level           = $_POST['level'] ?? 'kota';
        $rankPosition    = trim($_POST['rank_position'] ?? '');
        $year            = trim($_POST['year'] ?? date('Y'));
        $description     = trim($_POST['description'] ?? '');

        if ($title === '')           $errors[] = 'Judul prestasi wajib diisi.';
        if ($studentName === '')     $errors[] = 'Nama siswa / peraih prestasi wajib diisi.';
        if ($competitionName === '') $errors[] = 'Nama perlombaan / kompetisi wajib diisi.';
        if ($rankPosition === '')    $errors[] = 'Peringkat / gelar juara wajib diisi.';

        $photoPath = $item['photo'] ?? null;
        if (!empty($_FILES['photo']['name'])) {
            $uploadError = null;
            $uploaded = handleImageUpload($_FILES['photo'], 'prestasi', $uploadError);
            if ($uploaded) {
                if ($photoPath && file_exists(BASE_PATH . '/' . $photoPath)) {
                    @unlink(BASE_PATH . '/' . $photoPath);
                }
                $photoPath = $uploaded;
            } else {
                $errors[] = 'Foto: ' . $uploadError;
            }
        }

        if (empty($errors)) {
            $baseSlug = slugify($title . ' ' . $year);
            if ($isEdit) {
                $stmt = $db->prepare("SELECT COUNT(*) FROM achievements WHERE slug = ? AND id != ?");
                $stmt->execute([$baseSlug, $id]);
                $slug = ((int)$stmt->fetchColumn() > 0) ? $baseSlug . '-' . $id : $baseSlug;

                $stmt = $db->prepare("
                    UPDATE achievements
                    SET title = ?, slug = ?, student_name = ?, competition_name = ?,
                        level = ?, rank_position = ?, year = ?, photo = ?, description = ?
                    WHERE id = ?
                ");
                $stmt->execute([$title, $slug, $studentName, $competitionName, $level, $rankPosition, $year, $photoPath, $description, $id]);
                setFlash('success', 'Data prestasi berhasil diperbarui.');
            } else {
                $stmt = $db->prepare("SELECT COUNT(*) FROM achievements WHERE slug = ?");
                $stmt->execute([$baseSlug]);
                $slug = ((int)$stmt->fetchColumn() > 0) ? $baseSlug . '-' . time() : $baseSlug;

                $stmt = $db->prepare("
                    INSERT INTO achievements (title, slug, student_name, competition_name, level, rank_position, year, photo, description)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$title, $slug, $studentName, $competitionName, $level, $rankPosition, $year, $photoPath, $description]);
                setFlash('success', 'Prestasi baru berhasil ditambahkan.');
            }
            redirect(BASE_URL . '/admin/achievements/index.php');
        }

        $item = [
            'title'            => $title,
            'student_name'     => $studentName,
            'competition_name' => $competitionName,
            'level'            => $level,
            'rank_position'    => $rankPosition,
            'year'             => $year,
            'photo'            => $photoPath,
            'description'      => $description,
        ];
    }
}

$pageTitle = ($isEdit ? 'Edit' : 'Tambah') . ' Prestasi Siswa';
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
      <div class="col-md-8">
        <label class="form-label small fw-semibold">Judul Prestasi <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" value="<?= e($item['title'] ?? '') ?>" required placeholder="Contoh: Juara 1 LKS SMK Web Technologies">
      </div>

      <div class="col-md-4">
        <label class="form-label small fw-semibold">Peringkat / Gelar Juara <span class="text-danger">*</span></label>
        <input type="text" name="rank_position" class="form-control" value="<?= e($item['rank_position'] ?? 'Juara 1') ?>" required placeholder="Contoh: Juara 1 / Medali Emas">
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Nama Siswa / Tim Pemenang <span class="text-danger">*</span></label>
        <input type="text" name="student_name" class="form-control" value="<?= e($item['student_name'] ?? '') ?>" required placeholder="Contoh: Rian Hidayat (Kelas XII RPL)">
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Nama Acara / Kompetisi <span class="text-danger">*</span></label>
        <input type="text" name="competition_name" class="form-control" value="<?= e($item['competition_name'] ?? '') ?>" required placeholder="Contoh: Lomba Kompetensi Siswa (LKS) SMK Tingkat Provinsi">
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Tingkat Perlombaan <span class="text-danger">*</span></label>
        <select name="level" class="form-select" required>
          <option value="internasional" <?= ($item['level'] ?? '') === 'internasional' ? 'selected' : '' ?>>Tingkat Internasional</option>
          <option value="nasional" <?= ($item['level'] ?? '') === 'nasional' ? 'selected' : '' ?>>Tingkat Nasional</option>
          <option value="provinsi" <?= ($item['level'] ?? '') === 'provinsi' ? 'selected' : '' ?>>Tingkat Provinsi</option>
          <option value="kota" <?= ($item['level'] ?? 'kota') === 'kota' ? 'selected' : '' ?>>Tingkat Kota / Kabupaten</option>
          <option value="sekolah" <?= ($item['level'] ?? '') === 'sekolah' ? 'selected' : '' ?>>Tingkat Sekolah</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Tahun Pencapaian <span class="text-danger">*</span></label>
        <input type="text" name="year" class="form-control" value="<?= e($item['year'] ?? date('Y')) ?>" required placeholder="Contoh: 2026">
      </div>

      <div class="col-12">
        <label class="form-label small fw-semibold">Deskripsi / Ulasan Prestasi</label>
        <textarea name="description" rows="4" class="form-control"><?= e($item['description'] ?? '') ?></textarea>
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Foto Dokumentasi / Piala / Piagam</label>
        <input type="file" name="photo" class="form-control" accept="image/*" data-preview="#photoPreview">
        <div class="form-text">Format: JPG, PNG, WEBP. Maks 2MB.</div>
      </div>

      <div class="col-md-6 text-center">
        <?php if (!empty($item['photo'])): ?>
          <img id="photoPreview" src="<?= e(BASE_URL . '/' . $item['photo']) ?>" alt="Preview" class="img-thumbnail mt-2" style="max-height: 120px;">
        <?php else: ?>
          <img id="photoPreview" src="" alt="Preview" class="img-thumbnail mt-2 d-none" style="max-height: 120px;">
        <?php endif; ?>
      </div>

      <div class="col-12 pt-3 border-top">
        <button type="submit" class="btn btn-accent px-4"><i class="bi bi-save me-1"></i>Simpan Prestasi</button>
        <a href="index.php" class="btn btn-outline-secondary px-3 ms-2">Batal</a>
      </div>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
