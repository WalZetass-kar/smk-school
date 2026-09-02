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
            'theme_color_primary'    => trim($_POST['theme_color_primary'] ?? '#0F2C59'),
            'theme_color_secondary'  => trim($_POST['theme_color_secondary'] ?? '#2563EB'),
            'theme_color_accent'     => trim($_POST['theme_color_accent'] ?? '#F59E0B'),
            'theme_preset'           => trim($_POST['theme_preset'] ?? 'navy_classic'),
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
                        theme_color_primary=?, theme_color_secondary=?, theme_color_accent=?, theme_preset=?,
                        map_url=?, accreditation=?, founded_year=?, running_text=?, is_running_text_active=?, 
                        video_title=?, video_description=?, video_profile_url=?, video_features=?, 
                        whatsapp_ppdb=?, whatsapp_bkk=?, whatsapp_tu=?, logo=?, favicon=? 
                        WHERE id=?
                ");
                $stmt->execute([...array_values($data), $profile['id']]);
            } else {
                $stmt = $db->prepare("
                    INSERT INTO school_profile 
                    (school_name, npsn, address, phone, email, website, description, theme_color_primary, theme_color_secondary, theme_color_accent, theme_preset, map_url, accreditation, founded_year, running_text, is_running_text_active, video_title, video_description, video_profile_url, video_features, whatsapp_ppdb, whatsapp_bkk, whatsapp_tu, logo, favicon) 
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
                ");
                $stmt->execute(array_values($data));
            }
            logActivity('UPDATE_WEBSITE_SETTINGS', 'profile', 'Memperbarui profil sekolah dan tema warna website (' . $data['school_name'] . ')');
            setFlash('success', 'Pengaturan website dan tema warna berhasil disimpan.');
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

    <!-- ================= THEME COLOR CUSTOMIZER ================= -->
    <h2 class="h6 fw-bold text-primary mb-3 pb-2 border-bottom"><i class="bi bi-palette-fill me-1"></i>Tema &amp; Warna Utama Website</h2>
    <p class="small text-muted mb-3">Pilih palet warna bawaan yang sesuai dengan identitas sekolah (SMK, SMA, MA/Madrasah, SMP, SD) atau kustomisasi warna sendiri secara manual.</p>

    <!-- Preset Selector Buttons -->
    <div class="row g-2 mb-4" id="themePresetsGrid">
      <div class="col-6 col-md-4 col-lg-2">
        <button type="button" class="btn btn-outline-secondary w-100 p-2 text-start rounded-3 theme-preset-card <?= ($profile['theme_preset'] ?? 'navy_classic') === 'navy_classic' ? 'border-primary shadow-xs' : '' ?>" data-preset="navy_classic" data-p="#0F2C59" data-s="#2563EB" data-a="#F59E0B">
          <div class="d-flex gap-1 mb-1.5">
            <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #0F2C59;"></span>
            <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #2563EB;"></span>
            <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #F59E0B;"></span>
          </div>
          <div class="small fw-bold text-dark text-truncate" style="font-size: 11px;">Navy Classic</div>
        </button>
      </div>

      <div class="col-6 col-md-4 col-lg-2">
        <button type="button" class="btn btn-outline-secondary w-100 p-2 text-start rounded-3 theme-preset-card <?= ($profile['theme_preset'] ?? '') === 'emerald_islamic' ? 'border-primary shadow-xs' : '' ?>" data-preset="emerald_islamic" data-p="#065F46" data-s="#059669" data-a="#D97706">
          <div class="d-flex gap-1 mb-1.5">
            <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #065F46;"></span>
            <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #059669;"></span>
            <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #D97706;"></span>
          </div>
          <div class="small fw-bold text-dark text-truncate" style="font-size: 11px;">Emerald Islamic</div>
        </button>
      </div>

      <div class="col-6 col-md-4 col-lg-2">
        <button type="button" class="btn btn-outline-secondary w-100 p-2 text-start rounded-3 theme-preset-card <?= ($profile['theme_preset'] ?? '') === 'royal_maroon' ? 'border-primary shadow-xs' : '' ?>" data-preset="royal_maroon" data-p="#7F1D1D" data-s="#DC2626" data-a="#F59E0B">
          <div class="d-flex gap-1 mb-1.5">
            <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #7F1D1D;"></span>
            <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #DC2626;"></span>
            <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #F59E0B;"></span>
          </div>
          <div class="small fw-bold text-dark text-truncate" style="font-size: 11px;">Royal Maroon</div>
        </button>
      </div>

      <div class="col-6 col-md-4 col-lg-2">
        <button type="button" class="btn btn-outline-secondary w-100 p-2 text-start rounded-3 theme-preset-card <?= ($profile['theme_preset'] ?? '') === 'modern_teal' ? 'border-primary shadow-xs' : '' ?>" data-preset="modern_teal" data-p="#115E59" data-s="#0D9488" data-a="#EAB308">
          <div class="d-flex gap-1 mb-1.5">
            <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #115E59;"></span>
            <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #0D9488;"></span>
            <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #EAB308;"></span>
          </div>
          <div class="small fw-bold text-dark text-truncate" style="font-size: 11px;">Modern Teal</div>
        </button>
      </div>

      <div class="col-6 col-md-4 col-lg-2">
        <button type="button" class="btn btn-outline-secondary w-100 p-2 text-start rounded-3 theme-preset-card <?= ($profile['theme_preset'] ?? '') === 'ocean_sapphire' ? 'border-primary shadow-xs' : '' ?>" data-preset="ocean_sapphire" data-p="#1E3A8A" data-s="#3B82F6" data-a="#F59E0B">
          <div class="d-flex gap-1 mb-1.5">
            <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #1E3A8A;"></span>
            <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #3B82F6;"></span>
            <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #F59E0B;"></span>
          </div>
          <div class="small fw-bold text-dark text-truncate" style="font-size: 11px;">Ocean Sapphire</div>
        </button>
      </div>

      <div class="col-6 col-md-4 col-lg-2">
        <button type="button" class="btn btn-outline-secondary w-100 p-2 text-start rounded-3 theme-preset-card <?= ($profile['theme_preset'] ?? '') === 'slate_corporate' ? 'border-primary shadow-xs' : '' ?>" data-preset="slate_corporate" data-p="#0F172A" data-s="#334155" data-a="#38BDF8">
          <div class="d-flex gap-1 mb-1.5">
            <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #0F172A;"></span>
            <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #334155;"></span>
            <span class="rounded-circle d-inline-block" style="width: 14px; height: 14px; background: #38BDF8;"></span>
          </div>
          <div class="small fw-bold text-dark text-truncate" style="font-size: 11px;">Slate Modern</div>
        </button>
      </div>
    </div>

    <input type="hidden" name="theme_preset" id="themePresetInput" value="<?= e($profile['theme_preset'] ?? 'navy_classic') ?>">

    <!-- Color Inputs -->
    <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Warna Utama (Primary Color)</label>
        <div class="input-group">
          <input type="color" class="form-control form-control-color" id="primaryColorPicker" value="<?= e($profile['theme_color_primary'] ?? '#0F2C59') ?>" style="width: 48px;">
          <input type="text" name="theme_color_primary" id="primaryColorText" class="form-control font-monospace" value="<?= e($profile['theme_color_primary'] ?? '#0F2C59') ?>" placeholder="#0F2C59" required>
        </div>
        <div class="form-text small" style="font-size: 11px;">Warna navbar, footer, heading, dan brand utama.</div>
      </div>

      <div class="col-md-4">
        <label class="form-label small fw-semibold">Warna Sekunder (Secondary Color)</label>
        <div class="input-group">
          <input type="color" class="form-control form-control-color" id="secondaryColorPicker" value="<?= e($profile['theme_color_secondary'] ?? '#2563EB') ?>" style="width: 48px;">
          <input type="text" name="theme_color_secondary" id="secondaryColorText" class="form-control font-monospace" value="<?= e($profile['theme_color_secondary'] ?? '#2563EB') ?>" placeholder="#2563EB" required>
        </div>
        <div class="form-text small" style="font-size: 11px;">Warna tombol link, badge kategori, dan aksen hover.</div>
      </div>

      <div class="col-md-4">
        <label class="form-label small fw-semibold">Warna Aksen (Accent Color)</label>
        <div class="input-group">
          <input type="color" class="form-control form-control-color" id="accentColorPicker" value="<?= e($profile['theme_color_accent'] ?? '#F59E0B') ?>" style="width: 48px;">
          <input type="text" name="theme_color_accent" id="accentColorText" class="form-control font-monospace" value="<?= e($profile['theme_color_accent'] ?? '#F59E0B') ?>" placeholder="#F59E0B" required>
        </div>
        <div class="form-text small" style="font-size: 11px;">Warna tombol CTA PPDB, bintang, dan highlight emas.</div>
      </div>
    </div>

    <h2 class="h6 fw-bold text-primary mb-3 pb-2 border-bottom"><i class="bi bi-image me-1"></i>Peta &amp; Logo</h2>
    <div class="row g-3">
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
        <button type="submit" class="btn btn-accent px-4 py-2.5 fw-semibold shadow-xs"><i class="bi bi-save me-1"></i>Simpan Pengaturan &amp; Tema</button>
      </div>
    </div>
  </form>
</div>

<script>
// Theme Presets Click Handler
document.querySelectorAll('.theme-preset-card').forEach(btn => {
  btn.addEventListener('click', function() {
    const p = this.dataset.p;
    const s = this.dataset.s;
    const a = this.dataset.a;
    const preset = this.dataset.preset;

    document.getElementById('primaryColorText').value = p;
    document.getElementById('primaryColorPicker').value = p;
    document.getElementById('secondaryColorText').value = s;
    document.getElementById('secondaryColorPicker').value = s;
    document.getElementById('accentColorText').value = a;
    document.getElementById('accentColorPicker').value = a;
    document.getElementById('themePresetInput').value = preset;

    document.querySelectorAll('.theme-preset-card').forEach(b => b.classList.remove('border-primary', 'shadow-xs'));
    this.classList.add('border-primary', 'shadow-xs');
  });
});

// Sync Color Pickers with Text Inputs
function bindColorSync(pickerId, textId) {
  const picker = document.getElementById(pickerId);
  const text = document.getElementById(textId);
  if (!picker || !text) return;

  picker.addEventListener('input', () => {
    text.value = picker.value.toUpperCase();
    document.getElementById('themePresetInput').value = 'custom';
    document.querySelectorAll('.theme-preset-card').forEach(b => b.classList.remove('border-primary', 'shadow-xs'));
  });

  text.addEventListener('input', () => {
    if (/^#[0-9A-F]{6}$/i.test(text.value)) {
      picker.value = text.value;
      document.getElementById('themePresetInput').value = 'custom';
      document.querySelectorAll('.theme-preset-card').forEach(b => b.classList.remove('border-primary', 'shadow-xs'));
    }
  });
}

bindColorSync('primaryColorPicker', 'primaryColorText');
bindColorSync('secondaryColorPicker', 'secondaryColorText');
bindColorSync('accentColorPicker', 'accentColorText');
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
