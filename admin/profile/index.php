<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$profile = $db->query("SELECT * FROM school_profile ORDER BY id LIMIT 1")->fetch();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $data = [
            'school_name'            => trim($_POST['school_name'] ?? ''),
            'npsn'                   => trim($_POST['npsn'] ?? ''),
            'address'                => trim($_POST['address'] ?? ''),
            'phone'                  => trim($_POST['phone'] ?? ''),
            'email'                  => trim($_POST['email'] ?? ''),
            'website'                => trim($_POST['website'] ?? ''),
            'description'            => trim($_POST['description'] ?? ''),
            'map_url'                => trim($_POST['map_url'] ?? ''),
            'accreditation'          => trim($_POST['accreditation'] ?? ''),
            'founded_year'           => trim($_POST['founded_year'] ?? ''),
            'running_text'           => trim($_POST['running_text'] ?? ''),
            'is_running_text_active' => isset($_POST['is_running_text_active']) ? 1 : 0,
            'video_title'            => trim($_POST['video_title'] ?? 'Video Profil & Fasilitas Sekolah'),
            'video_description'      => trim($_POST['video_description'] ?? ''),
            'video_profile_url'      => trim($_POST['video_profile_url'] ?? ''),
            'video_features'         => trim($_POST['video_features'] ?? ''),
            'whatsapp_ppdb'          => trim($_POST['whatsapp_ppdb'] ?? ''),
            'whatsapp_bkk'           => trim($_POST['whatsapp_bkk'] ?? ''),
            'whatsapp_tu'            => trim($_POST['whatsapp_tu'] ?? ''),
            'logo'                   => $profile['logo'] ?? null,
            'favicon'                => $profile['favicon'] ?? null,
        ];

        if ($data['school_name'] === '') {
            $errors[] = 'Nama sekolah wajib diisi.';
        }

        if (!empty($_FILES['logo']['name'])) {
            $uploadError = null;
            $path = handleImageUpload($_FILES['logo'], $uploadError, 'profile');
            if ($path) { $data['logo'] = $path; } else { $errors[] = 'Logo: ' . $uploadError; }
        }
        if (!empty($_FILES['favicon']['name'])) {
            $uploadError = null;
            $path = handleImageUpload($_FILES['favicon'], $uploadError, 'profile');
            if ($path) { $data['favicon'] = $path; } else { $errors[] = 'Favicon: ' . $uploadError; }
        }

        if (empty($errors)) {
            if ($profile) {
                $stmt = $db->prepare("
                    UPDATE school_profile 
                    SET school_name=?, npsn=?, address=?, phone=?, email=?, website=?, description=?, 
                        map_url=?, accreditation=?, founded_year=?, running_text=?, is_running_text_active=?, 
                        video_title=?, video_description=?, video_profile_url=?, video_features=?, 
                        whatsapp_ppdb=?, whatsapp_bkk=?, whatsapp_tu=?, logo=?, favicon=? 
                    WHERE id=?
                ");
                $stmt->execute([...array_values($data), $profile['id']]);
            } else {
                $stmt = $db->prepare("
                    INSERT INTO school_profile 
                    (school_name, npsn, address, phone, email, website, description, map_url, accreditation, founded_year, running_text, is_running_text_active, video_title, video_description, video_profile_url, video_features, whatsapp_ppdb, whatsapp_bkk, whatsapp_tu, logo, favicon) 
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
                ");
                $stmt->execute(array_values($data));
            }
            logActivity('UPDATE_WEBSITE_SETTINGS', 'profile', 'Memperbarui profil sekolah dan pengaturan website (' . $data['school_name'] . ')');
            setFlash('success', 'Pengaturan website berhasil disimpan.');
            redirect(BASE_URL . '/admin/profile/index.php');
        }
        $profile = array_merge($profile ?? [], $data);
    }
}

$pageTitle = 'Pengaturan Website';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?= flashMessage() ?>

<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<!-- Quick Banner to Statistics -->
<div class="alert bg-primary-subtle border border-primary-subtle rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 p-3">
  <div class="d-flex align-items-center gap-2">
    <i class="bi bi-bar-chart-line-fill text-primary fs-4"></i>
    <div>
      <strong class="text-primary d-block">Ingin mengubah angka jumlah siswa, guru, rombel, dll?</strong>
      <span class="small text-muted">Jumlah Siswa, Guru &amp; Tendik, Rombel, Fasilitas, dan Alumni dapat diedit di menu Statistik Sekolah.</span>
    </div>
  </div>
  <a href="<?= e(BASE_URL) ?>/admin/statistics/index.php" class="btn btn-sm btn-primary fw-semibold px-3">
    <i class="bi bi-pencil-square me-1"></i> Edit Jumlah Siswa &amp; Guru
  </a>
</div>

<div class="admin-card">
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    
    <h2 class="h6 fw-bold text-primary mb-3 pb-2 border-bottom"><i class="bi bi-building me-1"></i>Informasi Utama Sekolah</h2>
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Nama Sekolah <span class="text-danger">*</span></label>
        <input type="text" name="school_name" class="form-control" value="<?= e($profile['school_name'] ?? '') ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">NPSN</label>
        <input type="text" name="npsn" class="form-control" value="<?= e($profile['npsn'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Akreditasi</label>
        <input type="text" name="accreditation" class="form-control" value="<?= e($profile['accreditation'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Alamat</label>
        <input type="text" name="address" class="form-control" value="<?= e($profile['address'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Telepon Kantor</label>
        <input type="text" name="phone" class="form-control" value="<?= e($profile['phone'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Tahun Berdiri</label>
        <input type="text" name="founded_year" class="form-control" value="<?= e($profile['founded_year'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Email Sekolah</label>
        <input type="email" name="email" class="form-control" value="<?= e($profile['email'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Website</label>
        <input type="text" name="website" class="form-control" value="<?= e($profile['website'] ?? '') ?>">
      </div>
      <div class="col-12">
        <label class="form-label small fw-semibold">Deskripsi Singkat Sekolah</label>
        <textarea name="description" rows="3" class="form-control"><?= e($profile['description'] ?? '') ?></textarea>
      </div>
    </div>

    <h2 class="h6 fw-bold text-primary mb-3 pb-2 border-bottom"><i class="bi bi-megaphone me-1"></i>Running Text &amp; Video Profil</h2>
    <div class="row g-3 mb-4">
      <div class="col-md-8">
        <label class="form-label small fw-semibold">Teks Pengumuman Berjalan (Running Text Navbar)</label>
        <input type="text" name="running_text" class="form-control" value="<?= e($profile['running_text'] ?? '') ?>" placeholder="Pengumuman penting yang akan berjalan di puncak layar...">
      </div>
      <div class="col-md-4 d-flex align-items-center pt-md-4">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="rtSwitch" name="is_running_text_active" value="1" <?= !empty($profile['is_running_text_active']) ? 'checked' : '' ?>>
          <label class="form-check-label fw-semibold small" for="rtSwitch">Aktifkan Running Text</label>
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Judul Seksi Video</label>
        <input type="text" name="video_title" class="form-control" value="<?= e($profile['video_title'] ?? 'Video Profil & Fasilitas Sekolah') ?>" placeholder="Video Profil & Fasilitas Sekolah">
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">URL Video YouTube (Embed / Link)</label>
        <input type="text" name="video_profile_url" class="form-control" value="<?= e($profile['video_profile_url'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=... atau https://youtu.be/...">
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Deskripsi Seksi Video</label>
        <textarea name="video_description" rows="3" class="form-control" placeholder="Saksikan suasana pembelajaran modern dan fasilitas praktik..."><?= e($profile['video_description'] ?? '') ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Poin Keunggulan / Fasilitas (Satu per baris)</label>
        <textarea name="video_features" rows="3" class="form-control" placeholder="Kurikulum Berstandar Industri&#10;Laboratorium Komputer Lengkap&#10;Sertifikasi BNSP"><?= e($profile['video_features'] ?? '') ?></textarea>
      </div>
    </div>

    <h2 class="h6 fw-bold text-primary mb-3 pb-2 border-bottom"><i class="bi bi-whatsapp me-1 text-success"></i>Kontak Cepat WhatsApp Multi-Layanan</h2>
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <label class="form-label small fw-semibold">No. WA Panitia PPDB</label>
        <input type="text" name="whatsapp_ppdb" class="form-control" value="<?= e($profile['whatsapp_ppdb'] ?? '') ?>" placeholder="6281234567890">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">No. WA Bursa Kerja (BKK)</label>
        <input type="text" name="whatsapp_bkk" class="form-control" value="<?= e($profile['whatsapp_bkk'] ?? '') ?>" placeholder="6281234567891">
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">No. WA Tata Usaha (TU)</label>
        <input type="text" name="whatsapp_tu" class="form-control" value="<?= e($profile['whatsapp_tu'] ?? '') ?>" placeholder="6281234567892">
      </div>
    </div>

    <h2 class="h6 fw-bold text-primary mb-3 pb-2 border-bottom"><i class="bi bi-image me-1"></i>Peta &amp; Logo</h2>
      <div class="col-12">
        <label class="form-label small fw-semibold">URL / Link Google Maps Lokasi Sekolah</label>
        <input type="text" name="map_url" class="form-control" value="<?= e($profile['map_url'] ?? '') ?>" placeholder="Contoh: https://maps.app.goo.gl/... atau link embed Google Maps">
        <div class="form-text small text-muted">
          <i class="bi bi-info-circle me-1"></i>Bisa berupa link Share Google Maps (contoh: <code>https://maps.app.goo.gl/...</code>), link alamat di browser, ataupun kode <code>&lt;iframe src="..."&gt;</code>. Sistem akan otomatis mengubahnya menjadi peta interaktif.
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Logo Sekolah</label>
        <?php if (!empty($profile['logo'])): ?><img src="<?= e(BASE_URL . '/' . $profile['logo']) ?>" class="thumb mb-2 d-block" alt="Logo saat ini" style="max-height:60px;"><?php endif; ?>
        <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Favicon Browser</label>
        <?php if (!empty($profile['favicon'])): ?><img src="<?= e(BASE_URL . '/' . $profile['favicon']) ?>" class="thumb mb-2 d-block" alt="Favicon saat ini" style="max-height:32px;"><?php endif; ?>
        <input type="file" name="favicon" class="form-control" accept=".jpg,.jpeg,.png,.webp,.ico">
      </div>
      <div class="col-12 pt-3 border-top">
        <button type="submit" class="btn btn-accent px-4"><i class="bi bi-save me-1"></i>Simpan Pengaturan</button>
      </div>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
