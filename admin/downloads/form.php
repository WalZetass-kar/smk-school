<?php
/**
 * admin/downloads/form.php
 * Upload / Edit Dokumen Unduhan
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;
$item = null;
$errors = [];

if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM downloads WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) {
        setFlash('error', 'Dokumen tidak ditemukan.');
        redirect(BASE_URL . '/admin/downloads/index.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan muat ulang halaman.';
    } else {
        $title       = trim($_POST['title'] ?? '');
        $category    = trim($_POST['category'] ?? 'Akademik');
        $description = trim($_POST['description'] ?? '');

        if ($title === '')    $errors[] = 'Judul dokumen wajib diisi.';
        if ($category === '') $errors[] = 'Kategori dokumen wajib diisi.';

        $filePath = $item['file_path'] ?? null;
        $fileSize = $item['file_size'] ?? null;
        $fileExt  = $item['file_ext'] ?? 'pdf';

        if (!empty($_FILES['file']['name'])) {
            $uploadError = null;
            $formattedSize = null;
            $detectedExt = null;
            $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', 'jpg', 'png'];

            $uploaded = handleGenericFileUpload(
                $_FILES['file'],
                $allowed,
                20 * 1024 * 1024, // max 20MB
                'documents',
                $uploadError,
                $formattedSize,
                $detectedExt
            );

            if ($uploaded) {
                if ($filePath && file_exists(BASE_PATH . '/' . $filePath)) {
                    @unlink(BASE_PATH . '/' . $filePath);
                }
                $filePath = $uploaded;
                $fileSize = $formattedSize;
                $fileExt  = $detectedExt;
            } else {
                $errors[] = 'File: ' . $uploadError;
            }
        } elseif (!$isEdit) {
            $errors[] = 'File dokumen wajib diunggah.';
        }

        if (empty($errors)) {
            if ($isEdit) {
                $stmt = $db->prepare("
                    UPDATE downloads
                    SET title = ?, category = ?, file_path = ?, file_size = ?, file_ext = ?, description = ?
                    WHERE id = ?
                ");
                $stmt->execute([$title, $category, $filePath, $fileSize, $fileExt, $description, $id]);
                setFlash('success', 'Dokumen berhasil diperbarui.');
            } else {
                $stmt = $db->prepare("
                    INSERT INTO downloads (title, category, file_path, file_size, file_ext, description)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$title, $category, $filePath, $fileSize, $fileExt, $description]);
                setFlash('success', 'Dokumen baru berhasil diunggah.');
            }
            redirect(BASE_URL . '/admin/downloads/index.php');
        }

        $item = [
            'title'       => $title,
            'category'    => $category,
            'file_path'   => $filePath,
            'file_size'   => $fileSize,
            'file_ext'    => $fileExt,
            'description' => $description,
        ];
    }
}

$pageTitle = ($isEdit ? 'Edit' : 'Upload') . ' Dokumen Unduhan';
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
        <label class="form-label small fw-semibold">Judul / Nama Dokumen <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" value="<?= e($item['title'] ?? '') ?>" required placeholder="Contoh: Kalender Akademik 2026/2027">
      </div>

      <div class="col-md-4">
        <label class="form-label small fw-semibold">Kategori Dokumen <span class="text-danger">*</span></label>
        <input type="text" name="category" class="form-control" list="catList" value="<?= e($item['category'] ?? 'Akademik') ?>" required>
        <datalist id="catList">
          <option value="Akademik">
          <option value="PPDB">
          <option value="Formulir Surat">
          <option value="Modul & Silabus">
          <option value="Regulasi & Tata Tertib">
        </datalist>
      </div>

      <div class="col-12">
        <label class="form-label small fw-semibold">Keterangan / Deskripsi Singkat</label>
        <textarea name="description" rows="3" class="form-control" placeholder="Penjelasan singkat mengenai isi dokumen..."><?= e($item['description'] ?? '') ?></textarea>
      </div>

      <div class="col-md-8">
        <label class="form-label small fw-semibold">File Berkas <?= $isEdit ? '(Biarkan kosong jika tidak diganti)' : '<span class="text-danger">*</span>' ?></label>
        <input type="file" name="file" class="form-control" <?= $isEdit ? '' : 'required' ?>>
        <div class="form-text">Format didukung: PDF, DOCX, XLSX, PPTX, ZIP. Ukuran maksimal: 20MB.</div>
      </div>

      <div class="col-md-4">
        <?php if (!empty($item['file_path'])): ?>
          <div class="mt-4 p-2 bg-light border rounded small">
            <div><strong>File Saat Ini:</strong></div>
            <a href="<?= e(BASE_URL . '/' . $item['file_path']) ?>" target="_blank" class="text-truncate d-block">
              <?= e(basename($item['file_path'])) ?> (<?= e($item['file_size']) ?>)
            </a>
          </div>
        <?php endif; ?>
      </div>

      <div class="col-12 pt-3 border-top">
        <button type="submit" class="btn btn-accent px-4"><i class="bi bi-save me-1"></i>Simpan Dokumen</button>
        <a href="index.php" class="btn btn-outline-secondary px-3 ms-2">Batal</a>
      </div>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
