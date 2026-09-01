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
  <div class="alert alert-danger rounded-3"><ul class="mb-0 ps-3 small"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h5 fw-bold text-primary mb-1">Pengaturan Sambutan Kepala Sekolah</h1>
    <p class="text-muted small mb-0">Kelola naskah sambutan resmi, identitas pimpinan, dan foto profil yang tampil di beranda dan halaman profil.</p>
  </div>
  <a href="<?= e(BASE_URL) ?>/profil/sambutan" target="_blank" class="btn btn-outline-primary btn-sm fw-semibold">
    <i class="bi bi-box-arrow-up-right me-1"></i>Lihat Halaman Publik
  </a>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="admin-card">
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        
        <div class="row g-3 mb-4">
          <div class="col-md-7">
            <label class="form-label small fw-semibold">Nama Lengkap &amp; Gelar <span class="text-danger">*</span></label>
            <input type="text" name="principal_name" class="form-control" value="<?= e($principal['principal_name'] ?? '') ?>" required placeholder="Contoh: Drs. H. Ahmad Dahlan, M.Pd.">
          </div>
          <div class="col-md-5">
            <label class="form-label small fw-semibold">Jabatan / Posisi</label>
            <input type="text" name="position" class="form-control" value="<?= e($principal['position'] ?? 'Kepala Sekolah') ?>" placeholder="Kepala Sekolah">
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label small fw-semibold">Naskah Sambutan Resmi <span class="text-danger">*</span></label>
          <textarea name="content" rows="10" class="form-control wysiwyg-editor" required placeholder="Tuliskan naskah sambutan kepala sekolah..."><?= e($principal['content'] ?? '') ?></textarea>
          <div class="form-text small text-muted">Gunakan editor untuk mengatur paragraf, penekanan teks, dan salam pembuka/penutup yang santun.</div>
        </div>

        <div class="mb-4 p-3 bg-light rounded-3 border">
          <label class="form-label small fw-semibold d-block mb-2"><i class="bi bi-image me-1"></i>Foto Resmi Kepala Sekolah</label>
          <div class="d-flex align-items-center gap-3">
            <?php if (!empty($principal['photo'])): ?>
              <img src="<?= e(BASE_URL . '/' . $principal['photo']) ?>" class="rounded-3 border shadow-xs object-fit-cover" style="width:75px;height:90px;" alt="Foto Kepala Sekolah">
            <?php else: ?>
              <div class="rounded-3 bg-white border d-flex align-items-center justify-content-center text-muted" style="width:75px;height:90px;">
                <i class="bi bi-person fs-1"></i>
              </div>
            <?php endif; ?>
            <div class="flex-grow-1">
              <input type="file" name="photo" class="form-control form-control-sm mb-1" accept=".jpg,.jpeg,.png,.webp">
              <div class="small text-muted" style="font-size: 11.5px;">Format disarankan: JPG, PNG, WEBP. Rasio 4:5 (foto formal jas/pakaian dinas rapi, maks 2MB).</div>
            </div>
          </div>
        </div>

        <div class="pt-3 border-top d-flex gap-2">
          <button type="submit" class="btn btn-accent px-4 fw-semibold"><i class="bi bi-save me-1"></i>Simpan Sambutan</button>
          <a href="<?= e(BASE_URL) ?>/admin" class="btn btn-outline-secondary px-3">Batal</a>
        </div>
      </form>
    </div>
  </div>

  <div class="col-lg-4">
    <!-- Live Preview Card -->
    <div class="admin-card text-center mb-4">
      <h2 class="h6 fw-bold text-primary mb-3 text-start border-bottom pb-2"><i class="bi bi-eye me-1"></i>Pratinjau Profil</h2>
      
      <div class="rounded-3 overflow-hidden border shadow-sm mx-auto mb-3" style="max-width:180px; aspect-ratio:4/5;">
        <?php if (!empty($principal['photo'])): ?>
          <img src="<?= e(BASE_URL . '/' . $principal['photo']) ?>" alt="<?= e($principal['principal_name'] ?? '') ?>" class="w-100 h-100 object-fit-cover">
        <?php else: ?>
          <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center text-muted">
            <i class="bi bi-person fs-1"></i>
          </div>
        <?php endif; ?>
      </div>

      <h3 class="h6 fw-bold text-dark mb-1"><?= e($principal['principal_name'] ?? 'Nama Kepala Sekolah') ?></h3>
      <span class="badge bg-primary bg-opacity-10 text-primary small px-3 py-1 rounded-pill mb-3"><?= e($principal['position'] ?? 'Kepala Sekolah') ?></span>

      <div class="small text-muted text-start border-top pt-2" style="font-size:12px;">
        <div class="d-flex justify-content-between py-1">
          <span>Terakhir Diperbarui:</span>
          <span class="fw-semibold text-dark"><?= !empty($principal['updated_at']) ? date('d/m/Y H:i', strtotime($principal['updated_at'])) : '-' ?></span>
        </div>
        <div class="d-flex justify-content-between py-1">
          <span>Penayangan:</span>
          <span class="badge bg-success-subtle text-success fw-bold">Beranda &amp; Profil</span>
        </div>
      </div>
    </div>

    <!-- Quick Tips Card -->
    <div class="admin-card">
      <h2 class="h6 fw-bold text-primary mb-2"><i class="bi bi-lightbulb text-warning me-1"></i>Tips Penulisan Sambutan</h2>
      <ul class="small text-muted ps-3 mb-0" style="line-height:1.7;">
        <li>Sertakan salam pembuka dan ungkapan selamat datang kepada calon siswa &amp; wali murid.</li>
        <li>Jelaskan komitmen mutu dan keunggulan program keahlian vokasi di sekolah.</li>
        <li>Tegaskan peran kemitraan dunia industri (DU/DI) dalam penyaluran kerja lulusan.</li>
      </ul>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
