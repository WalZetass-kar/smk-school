<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$id = (int) ($_GET['id'] ?? 0);
$item = null;
if ($id) {
    $stmt = $db->prepare("SELECT * FROM statistics WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) {
        setFlash('error', 'Data statistik tidak ditemukan.');
        redirect(BASE_URL . '/admin/statistics/index.php');
    }
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $title     = trim($_POST['title'] ?? '');
        $value     = (int) ($_POST['value'] ?? 0);
        $icon      = trim($_POST['icon'] ?? 'bi-bar-chart');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $status    = isset($_POST['status']) ? 1 : 0;

        if ($title === '') {
            $errors[] = 'Judul statistik wajib diisi (contoh: Jumlah Siswa, Jumlah Guru).';
        }

        if (empty($errors)) {
            if ($item) {
                $db->prepare("UPDATE statistics SET title=?, value=?, icon=?, sort_order=?, status=? WHERE id=?")
                   ->execute([$title, $value, $icon, $sortOrder, $status, $item['id']]);
                logActivity('UPDATE_STATISTIC', 'statistics', "Memperbarui statistik: $title ($value)");
                setFlash('success', 'Data statistik "' . e($title) . '" berhasil diperbarui.');
            } else {
                $db->prepare("INSERT INTO statistics (title, value, icon, sort_order, status) VALUES (?,?,?,?,?)")
                   ->execute([$title, $value, $icon, $sortOrder, $status]);
                logActivity('CREATE_STATISTIC', 'statistics', "Menambahkan statistik baru: $title ($value)");
                setFlash('success', 'Data statistik baru "' . e($title) . '" berhasil ditambahkan.');
            }
            redirect(BASE_URL . '/admin/statistics/index.php');
        }
        $item = compact('title', 'value', 'icon', 'sortOrder', 'status');
        $item['sort_order'] = $sortOrder;
    }
}

$pageTitle = $id ? 'Edit Data Statistik Sekolah' : 'Tambah Data Statistik Sekolah';
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
            <?= $id ? 'Edit Angka & Data Statistik' : 'Tambah Data Statistik Baru' ?>
          </h2>
          <p class="text-muted small mb-0">Ubah jumlah siswa aktif, tenaga pendidik/guru, fasilitas, atau data capaian sekolah.</p>
        </div>
        <a href="index.php" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
      </div>

      <!-- Quick Preset Selection (for quick autofill) -->
      <?php if (!$id): ?>
      <div class="bg-light p-3 rounded-3 mb-4 border">
        <span class="small fw-bold text-dark d-block mb-2"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Pilih Template Cepat (Autofill):</span>
        <div class="d-flex flex-wrap gap-2">
          <button type="button" class="btn btn-sm btn-white border preset-btn" data-title="Jumlah Siswa" data-icon="bi-people-fill">Siswa</button>
          <button type="button" class="btn btn-sm btn-white border preset-btn" data-title="Jumlah Guru" data-icon="bi-person-workspace">Guru</button>
          <button type="button" class="btn btn-sm btn-white border preset-btn" data-title="Tenaga Kependidikan" data-icon="bi-person-badge-fill">Tendik</button>
          <button type="button" class="btn btn-sm btn-white border preset-btn" data-title="Rombongan Belajar (Kelas)" data-icon="bi-door-open-fill">Rombel</button>
          <button type="button" class="btn btn-sm btn-white border preset-btn" data-title="Jumlah Jurusan" data-icon="bi-mortarboard-fill">Jurusan</button>
          <button type="button" class="btn btn-sm btn-white border preset-btn" data-title="Alumni Bekerja" data-icon="bi-briefcase-fill">Alumni</button>
          <button type="button" class="btn btn-sm btn-white border preset-btn" data-title="Partner Industri" data-icon="bi-buildings">Mitra</button>
          <button type="button" class="btn btn-sm btn-white border preset-btn" data-title="Laboratorium &amp; Bengkel" data-icon="bi-pc-display">Lab/Bengkel</button>
        </div>
      </div>
      <?php endif; ?>

      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

        <div class="row g-3">
          <!-- Title -->
          <div class="col-md-7">
            <label class="form-label small fw-semibold">Nama / Judul Statistik <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="bi bi-card-text"></i></span>
              <input type="text" name="title" id="titleInput" class="form-control" value="<?= e($item['title'] ?? '') ?>" placeholder="Contoh: Jumlah Siswa, Jumlah Guru, Ruang Praktik" required>
            </div>
          </div>

          <!-- Value -->
          <div class="col-md-5">
            <label class="form-label small fw-semibold">Jumlah / Nilai Angka <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="bi bi-123"></i></span>
              <input type="number" name="value" id="valueInput" class="form-control fw-bold text-primary" value="<?= (int) ($item['value'] ?? 0) ?>" min="0" required placeholder="0">
            </div>
          </div>

          <!-- Icon & Live Icon Preview -->
          <div class="col-md-7">
            <label class="form-label small fw-semibold">Ikon Bootstrap <span class="text-muted fw-normal">(Nama class CSS)</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="bi bi-palette"></i></span>
              <input type="text" name="icon" id="iconInput" class="form-control font-monospace" value="<?= e($item['icon'] ?? 'bi-bar-chart') ?>" placeholder="bi-people-fill">
            </div>
            <div class="form-text small text-muted">Contoh: <code>bi-people-fill</code>, <code>bi-person-workspace</code>, <code>bi-mortarboard-fill</code>, <code>bi-briefcase-fill</code></div>
          </div>

          <!-- Live Icon Preview Box -->
          <div class="col-md-5">
            <label class="form-label small fw-semibold d-block">Pratinjau Tampilan Ikon</label>
            <div class="p-2 border rounded-3 bg-light d-flex align-items-center gap-3">
              <div class="p-2 bg-white rounded border d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                <i class="bi <?= e($item['icon'] ?? 'bi-bar-chart') ?> fs-4 text-primary" id="iconPreview"></i>
              </div>
              <div class="small lh-sm">
                <span class="fw-bold text-dark d-block" id="previewTitle"><?= e(($item['title'] ?? '') !== '' ? $item['title'] : 'Judul Statistik') ?></span>
                <span class="text-primary fw-bold" id="previewValue"><?= number_format((int) ($item['value'] ?? 0), 0, ',', '.') ?></span>
              </div>
            </div>
          </div>

          <!-- Preset Icon Choices -->
          <div class="col-12">
            <label class="form-label small fw-semibold d-block mb-1">Pilihan Ikon Populer:</label>
            <div class="d-flex flex-wrap gap-2">
              <?php 
              $popularIcons = [
                'bi-people-fill' => 'Siswa',
                'bi-person-workspace' => 'Guru',
                'bi-person-badge-fill' => 'Tendik',
                'bi-mortarboard-fill' => 'Jurusan',
                'bi-door-open-fill' => 'Rombel',
                'bi-briefcase-fill' => 'Alumni/Karir',
                'bi-buildings' => 'Mitra Industri',
                'bi-pc-display' => 'Lab Komputer',
                'bi-trophy-fill' => 'Prestasi',
                'bi-book-half' => 'Buku/Perpus',
                'bi-award-fill' => 'Akreditasi',
                'bi-bar-chart-fill' => 'Statistik'
              ];
              foreach ($popularIcons as $ic => $lbl): ?>
                <button type="button" class="btn btn-sm btn-outline-secondary icon-select-btn d-inline-flex align-items-center gap-1" data-icon="<?= $ic ?>">
                  <i class="bi <?= $ic ?>"></i> <?= $lbl ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Sort Order -->
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Urutan Tampilan (Sort Order)</label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="bi bi-sort-numeric-down"></i></span>
              <input type="number" name="sort_order" class="form-control" value="<?= (int) ($item['sort_order'] ?? 0) ?>" min="0">
            </div>
            <div class="form-text small text-muted">Angka lebih kecil tampil lebih awal di halaman beranda.</div>
          </div>

          <!-- Status Toggle -->
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
              <i class="bi bi-check2-circle me-1"></i> <?= $id ? 'Simpan Perubahan' : 'Tambahkan Statistik' ?>
            </button>
            <a href="index.php" class="btn btn-outline-secondary px-4">Batal</a>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const iconInput = document.getElementById('iconInput');
const iconPreview = document.getElementById('iconPreview');
const titleInput = document.getElementById('titleInput');
const valueInput = document.getElementById('valueInput');
const previewTitle = document.getElementById('previewTitle');
const previewValue = document.getElementById('previewValue');

function updateLivePreview() {
  const iconClass = (iconInput.value || 'bi-bar-chart').trim();
  iconPreview.className = 'bi ' + iconClass + ' fs-4 text-primary';
  previewTitle.textContent = titleInput.value || 'Judul Statistik';
  previewValue.textContent = Number(valueInput.value || 0).toLocaleString('id-ID');
}

if (iconInput) {
  iconInput.addEventListener('input', updateLivePreview);
}
if (titleInput) {
  titleInput.addEventListener('input', updateLivePreview);
}
if (valueInput) {
  valueInput.addEventListener('input', updateLivePreview);
}

// Preset Icon Click
document.querySelectorAll('.icon-select-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    const icon = this.dataset.icon;
    iconInput.value = icon;
    updateLivePreview();
  });
});

// Preset Template Autofill Click
document.querySelectorAll('.preset-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    titleInput.value = this.dataset.title;
    iconInput.value = this.dataset.icon;
    updateLivePreview();
  });
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
