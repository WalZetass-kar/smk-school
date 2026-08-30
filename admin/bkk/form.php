<?php
/**
 * admin/bkk/form.php
 * Form Tambah / Edit Lowongan Kerja BKK
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;
$job = null;
$errors = [];

if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM bkk_jobs WHERE id = ?");
    $stmt->execute([$id]);
    $job = $stmt->fetch();
    if (!$job) {
        setFlash('error', 'Data lowongan tidak ditemukan.');
        redirect(BASE_URL . '/admin/bkk/index.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan muat ulang halaman.';
    } else {
        $title       = trim($_POST['title'] ?? '');
        $companyName = trim($_POST['company_name'] ?? '');
        $jobType     = $_POST['job_type'] ?? 'fulltime';
        $location    = trim($_POST['location'] ?? '');
        $salary      = trim($_POST['salary'] ?? '') ?: null;
        $description = trim($_POST['description'] ?? '');
        $requirements= trim($_POST['requirements'] ?? '');
        $applyUrl    = trim($_POST['apply_url'] ?? '') ?: null;
        $deadline    = trim($_POST['deadline'] ?? '') ?: null;
        $status      = $_POST['status'] ?? 'open';

        if ($title === '')       $errors[] = 'Posisi / Judul pekerjaan wajib diisi.';
        if ($companyName === '') $errors[] = 'Nama perusahaan wajib diisi.';
        if ($location === '')    $errors[] = 'Lokasi penempatan wajib diisi.';
        if ($description === '') $errors[] = 'Deskripsi pekerjaan wajib diisi.';

        $logoPath = $job['company_logo'] ?? null;
        if (!empty($_FILES['company_logo']['name'])) {
            $uploadError = null;
            $uploaded = handleImageUpload($_FILES['company_logo'], 'bkk', $uploadError);
            if ($uploaded) {
                if ($logoPath && file_exists(BASE_PATH . '/' . $logoPath)) {
                    @unlink(BASE_PATH . '/' . $logoPath);
                }
                $logoPath = $uploaded;
            } else {
                $errors[] = 'Logo: ' . $uploadError;
            }
        }

        if (empty($errors)) {
            $baseSlug = slugify($title . ' ' . $companyName);
            if ($isEdit) {
                $stmt = $db->prepare("SELECT COUNT(*) FROM bkk_jobs WHERE slug = ? AND id != ?");
                $stmt->execute([$baseSlug, $id]);
                $slug = ((int)$stmt->fetchColumn() > 0) ? $baseSlug . '-' . $id : $baseSlug;

                $stmt = $db->prepare("
                    UPDATE bkk_jobs
                    SET title = ?, slug = ?, company_name = ?, company_logo = ?, job_type = ?,
                        location = ?, salary = ?, description = ?, requirements = ?, apply_url = ?,
                        deadline = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $title, $slug, $companyName, $logoPath, $jobType,
                    $location, $salary, $description, $requirements, $applyUrl,
                    $deadline, $status, $id
                ]);
                setFlash('success', 'Lowongan pekerjaan berhasil diperbarui.');
            } else {
                $stmt = $db->prepare("SELECT COUNT(*) FROM bkk_jobs WHERE slug = ?");
                $stmt->execute([$baseSlug]);
                $slug = ((int)$stmt->fetchColumn() > 0) ? $baseSlug . '-' . time() : $baseSlug;

                $stmt = $db->prepare("
                    INSERT INTO bkk_jobs (title, slug, company_name, company_logo, job_type, location, salary, description, requirements, apply_url, deadline, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $title, $slug, $companyName, $logoPath, $jobType,
                    $location, $salary, $description, $requirements, $applyUrl,
                    $deadline, $status
                ]);
                setFlash('success', 'Lowongan pekerjaan baru berhasil ditambahkan.');
            }
            redirect(BASE_URL . '/admin/bkk/index.php');
        }

        $job = [
            'title'        => $title,
            'company_name' => $companyName,
            'company_logo' => $logoPath,
            'job_type'     => $jobType,
            'location'     => $location,
            'salary'       => $salary,
            'description'  => $description,
            'requirements' => $requirements,
            'apply_url'    => $applyUrl,
            'deadline'     => $deadline,
            'status'       => $status,
        ];
    }
}

$pageTitle = ($isEdit ? 'Edit' : 'Tambah') . ' Lowongan Kerja';
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
        <label class="form-label small fw-semibold">Posisi / Judul Pekerjaan <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" value="<?= e($job['title'] ?? '') ?>" required placeholder="Contoh: Junior Web Developer">
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Nama Perusahaan / Instansi <span class="text-danger">*</span></label>
        <input type="text" name="company_name" class="form-control" value="<?= e($job['company_name'] ?? '') ?>" required placeholder="Contoh: PT Nusantara Digital">
      </div>

      <div class="col-md-4">
        <label class="form-label small fw-semibold">Tipe Pekerjaan <span class="text-danger">*</span></label>
        <select name="job_type" class="form-select" required>
          <option value="fulltime" <?= ($job['job_type'] ?? '') === 'fulltime' ? 'selected' : '' ?>>Full Time (Penuh Waktu)</option>
          <option value="internship" <?= ($job['job_type'] ?? '') === 'internship' ? 'selected' : '' ?>>Internship (Magang / PKL)</option>
          <option value="contract" <?= ($job['job_type'] ?? '') === 'contract' ? 'selected' : '' ?>>Kontrak Kerja</option>
          <option value="parttime" <?= ($job['job_type'] ?? '') === 'parttime' ? 'selected' : '' ?>>Part Time (Paruh Waktu)</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label small fw-semibold">Lokasi Penempatan <span class="text-danger">*</span></label>
        <input type="text" name="location" class="form-control" value="<?= e($job['location'] ?? '') ?>" required placeholder="Contoh: Pekanbaru / Remote">
      </div>

      <div class="col-md-4">
        <label class="form-label small fw-semibold">Gaji / Uang Saku (Opsional)</label>
        <input type="text" name="salary" class="form-control" value="<?= e($job['salary'] ?? '') ?>" placeholder="Contoh: Rp 4.000.000 - Rp 5.000.000">
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Batas Akhir Pendaftaran (Deadline)</label>
        <input type="date" name="deadline" class="form-control" value="<?= e($job['deadline'] ?? '') ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Status Lowongan</label>
        <select name="status" class="form-select">
          <option value="open" <?= ($job['status'] ?? 'open') === 'open' ? 'selected' : '' ?>>Open (Terbuka / Aktif)</option>
          <option value="closed" <?= ($job['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed (Tutup)</option>
        </select>
      </div>

      <div class="col-12">
        <label class="form-label small fw-semibold">Tautan / Kontak Lamaran (URL / Email / No. WA)</label>
        <input type="text" name="apply_url" class="form-control" value="<?= e($job['apply_url'] ?? '') ?>" placeholder="https://... atau hrd@perusahaan.com atau https://wa.me/62812...">
      </div>

      <div class="col-12">
        <label class="form-label small fw-semibold">Deskripsi Pekerjaan &amp; Tanggung Jawab <span class="text-danger">*</span></label>
        <textarea name="description" rows="6" class="form-control wysiwyg-editor"><?= e($job['description'] ?? '') ?></textarea>
      </div>

      <div class="col-12">
        <label class="form-label small fw-semibold">Kualifikasi &amp; Persyaratan Pelamar</label>
        <textarea name="requirements" rows="6" class="form-control wysiwyg-editor"><?= e($job['requirements'] ?? '') ?></textarea>
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Logo Perusahaan</label>
        <input type="file" name="company_logo" class="form-control" accept="image/*" data-preview="#logoPreview">
        <div class="form-text">Format: JPG, PNG, WEBP. Maks 2MB.</div>
      </div>

      <div class="col-md-6 text-center">
        <?php if (!empty($job['company_logo'])): ?>
          <img id="logoPreview" src="<?= e(BASE_URL . '/' . $job['company_logo']) ?>" alt="Preview" class="img-thumbnail mt-2" style="max-height: 100px;">
        <?php else: ?>
          <img id="logoPreview" src="" alt="Preview" class="img-thumbnail mt-2 d-none" style="max-height: 100px;">
        <?php endif; ?>
      </div>

      <div class="col-12 pt-3 border-top">
        <button type="submit" class="btn btn-accent px-4"><i class="bi bi-save me-1"></i>Simpan Lowongan</button>
        <a href="index.php" class="btn btn-outline-secondary px-3 ms-2">Batal</a>
      </div>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
