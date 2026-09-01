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
    if (!$item) {
        setFlash('error', 'Data mitra kerja sama tidak ditemukan.');
        redirect(BASE_URL . '/admin/partners/index.php');
    }
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

        if ($name === '') {
            $errors[] = 'Nama instansi / perusahaan mitra wajib diisi.';
        }

        // Upload Logo
        if (!empty($_FILES['logo']['name'])) {
            $uploadError = null;
            $path = handleImageUpload($_FILES['logo'], $uploadError, 'partners');
            if ($path) {
                // Delete old custom logo if replaced
                if (!empty($item['logo']) && !str_starts_with($item['logo'], 'assets/images/')) {
                    @unlink(BASE_PATH . '/' . $item['logo']);
                }
                $logo = $path;
            } else {
                $errors[] = 'Logo Mitra: ' . $uploadError;
            }
        } elseif (!$item && empty($logo)) {
            $errors[] = 'Logo instansi / perusahaan mitra wajib diunggah.';
        }

        if (empty($errors)) {
            if ($item) {
                $db->prepare("UPDATE partners SET name=?, logo=?, website=?, description=?, status=?, sort_order=? WHERE id=?")
                   ->execute([$name, $logo, $website, $description, $status, $sortOrder, $item['id']]);
                logActivity('UPDATE_PARTNER', 'partners', "Memperbarui mitra kerja sama: $name");
                setFlash('success', 'Data mitra kerja sama "' . e($name) . '" berhasil diperbarui.');
            } else {
                $db->prepare("INSERT INTO partners (name, logo, website, description, status, sort_order) VALUES (?,?,?,?,?,?)")
                   ->execute([$name, $logo, $website, $description, $status, $sortOrder]);
                logActivity('CREATE_PARTNER', 'partners', "Menambahkan mitra kerja sama baru: $name");
                setFlash('success', 'Mitra kerja sama baru "' . e($name) . '" berhasil ditambahkan.');
            }
            redirect(BASE_URL . '/admin/partners/index.php');
        }
        $item = compact('name', 'website', 'description', 'status', 'logo');
        $item['sort_order'] = $sortOrder;
    }
}

$pageTitle = $id ? 'Edit Mitra Kerja Sama' : 'Tambah Mitra Kerja Sama';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?= flashMessage() ?>

<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="admin-card">
      <div class="d-flex align-items-center justify-content-between pb-3 mb-4 border-bottom">
        <div>
          <h2 class="h5 fw-bold text-primary mb-1">
            <i class="bi <?= $id ? 'bi-pencil-square' : 'bi-plus-circle-fill' ?> text-accent me-2"></i>
            <?= $id ? 'Edit Data Mitra Kerja Sama' : 'Tambah Mitra Kerja Sama Baru' ?>
          </h2>
          <p class="text-muted small mb-0">Lengkapi informasi mitra industri / instansi kerja sama sekolah.</p>
        </div>
        <a href="index.php" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
      </div>

      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

        <div class="row g-3">
          <!-- Logo Upload & Preview -->
          <div class="col-12 text-center pb-2">
            <label class="form-label small fw-semibold d-block">Logo Mitra / Perusahaan <span class="text-danger">*</span></label>
            <div class="p-2 bg-light border rounded d-inline-flex align-items-center justify-content-center mb-2" style="width:140px; height:90px;">
              <?php if (!empty($item['logo']) && file_exists(BASE_PATH . '/' . $item['logo'])): ?>
                <img src="<?= e(BASE_URL . '/' . $item['logo']) ?>" id="logoPreview" style="max-height: 75px; max-width: 120px; object-fit: contain;" alt="">
              <?php else: ?>
                <i class="bi bi-image text-muted fs-1" id="logoPlaceholder"></i>
              <?php endif; ?>
            </div>
            <div class="col-md-6 mx-auto">
              <input type="file" name="logo" id="logoInput" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp,.svg" <?= !$item ? 'required' : '' ?>>
              <div class="form-text small text-muted">Format: PNG (disarankan transparan), JPG, WEBP, atau SVG. Maksimal 2MB.</div>
            </div>
          </div>

          <!-- Name -->
          <div class="col-md-12">
            <label class="form-label small fw-semibold">Nama Perusahaan / Instansi Mitra <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="bi bi-building"></i></span>
              <input type="text" name="name" class="form-control" value="<?= e($item['name'] ?? '') ?>" required placeholder="Contoh: PT Telkom Indonesia, PT Astra Honda Motor">
            </div>
          </div>

          <!-- Website -->
          <div class="col-md-12">
            <label class="form-label small fw-semibold">Alamat Website Resmi</label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="bi bi-globe"></i></span>
              <input type="url" name="website" class="form-control" value="<?= e($item['website'] ?? '') ?>" placeholder="https://www.perusahaan.com">
            </div>
          </div>

          <!-- Description / Field of Cooperation -->
          <div class="col-12">
            <label class="form-label small fw-semibold">Bidang / Bentuk Kerja Sama</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Contoh: Kerja sama pelaksanaan Prakerin / PKL siswa jurusan RPL & TKJ, perekrutan tenaga kerja, dan sinkronisasi kurikulum industri."><?= e($item['description'] ?? '') ?></textarea>
          </div>

          <!-- Sort Order -->
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Urutan Tampilan (Sort Order)</label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="bi bi-sort-numeric-down"></i></span>
              <input type="number" name="sort_order" class="form-control" value="<?= (int) ($item['sort_order'] ?? 0) ?>" min="0">
            </div>
            <div class="form-text small text-muted">Angka lebih kecil akan tampil lebih awal di halaman depan.</div>
          </div>

          <!-- Status Switch -->
          <div class="col-md-6 d-flex align-items-center pt-md-3">
            <div class="form-check form-switch p-3 border rounded-3 bg-light w-100">
              <input class="form-check-input ms-0 me-2" type="checkbox" name="status" id="statusSwitch" value="1" <?= ($item['status'] ?? 1) ? 'checked' : '' ?>>
              <label class="form-check-label fw-semibold text-dark" for="statusSwitch">
                <i class="bi bi-eye text-primary me-1"></i> Tampilkan di Website (Aktif)
              </label>
            </div>
          </div>

          <!-- Submit Buttons -->
          <div class="col-12 pt-3 border-top d-flex gap-2">
            <button type="submit" class="btn btn-accent px-4 fw-semibold">
              <i class="bi bi-check2-circle me-1"></i> <?= $id ? 'Simpan Perubahan' : 'Tambahkan Mitra' ?>
            </button>
            <a href="index.php" class="btn btn-outline-secondary px-4">Batal</a>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Live Image Preview
const logoInput = document.getElementById('logoInput');
if (logoInput) {
  logoInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(evt) {
        let img = document.getElementById('logoPreview');
        const placeholder = document.getElementById('logoPlaceholder');
        if (!img && placeholder) {
          img = document.createElement('img');
          img.id = 'logoPreview';
          img.style.maxHeight = '75px';
          img.style.maxWidth = '120px';
          img.style.objectFit = 'contain';
          placeholder.replaceWith(img);
        }
        if (img) {
          img.src = evt.target.result;
        }
      };
      reader.readAsDataURL(file);
    }
  });
}
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
