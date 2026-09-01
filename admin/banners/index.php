<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$banners = $db->query("SELECT * FROM banners ORDER BY sort_order ASC, id DESC")->fetchAll();

$totalBanners = count($banners);
$activeBanners = count(array_filter($banners, fn($b) => (int)$b['status'] === 1));
$inactiveBanners = $totalBanners - $activeBanners;

$pageTitle = 'Kelola Banner Slider';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>

<?= flashMessage() ?>

<!-- Overview Status Cards -->
<div class="row g-3 mb-4">
  <div class="col-sm-4">
    <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <span class="text-muted small fw-semibold">Total Banner</span>
          <h3 class="h4 fw-bold text-dark mb-0 mt-1"><?= $totalBanners ?></h3>
        </div>
        <div class="p-2.5 rounded-3 bg-primary bg-opacity-10 text-primary fs-4">
          <i class="bi bi-images"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <span class="text-muted small fw-semibold">Banner Aktif (ON)</span>
          <h3 class="h4 fw-bold text-success mb-0 mt-1"><?= $activeBanners ?></h3>
        </div>
        <div class="p-2.5 rounded-3 bg-success bg-opacity-10 text-success fs-4">
          <i class="bi bi-check-circle-fill"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <span class="text-muted small fw-semibold">Banner Nonaktif (OFF)</span>
          <h3 class="h4 fw-bold text-secondary mb-0 mt-1"><?= $inactiveBanners ?></h3>
        </div>
        <div class="p-2.5 rounded-3 bg-secondary bg-opacity-10 text-secondary fs-4">
          <i class="bi bi-pause-circle-fill"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div>
    <h2 class="h5 fw-bold text-primary mb-0">Daftar Banner Beranda</h2>
    <span class="text-muted small">Klik tombol <strong>ON / OFF</strong> untuk mengaktifkan atau menonaktifkan banner secara instan.</span>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= e(BASE_URL) ?>/" target="_blank" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1.5">
      <i class="bi bi-box-arrow-up-right"></i> Lihat Beranda
    </a>
    <a href="form.php" class="btn btn-accent btn-sm d-flex align-items-center gap-1.5">
      <i class="bi bi-plus-lg"></i> Tambah Banner
    </a>
  </div>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-admin align-middle mb-0">
      <thead>
        <tr>
          <th style="width: 100px;">Gambar</th>
          <th>Judul &amp; Deskripsi</th>
          <th>Tombol &amp; Link</th>
          <th style="width: 80px;" class="text-center">Urutan</th>
          <th style="width: 140px;" class="text-center">Status (ON/OFF)</th>
          <th style="width: 110px;" class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($banners as $b): ?>
        <tr>
          <td>
            <a href="<?= e(BASE_URL . '/' . $b['image']) ?>" target="_blank">
              <img src="<?= e(BASE_URL . '/' . $b['image']) ?>" class="rounded border shadow-xs" style="width: 85px; height: 50px; object-fit: cover;" alt="<?= e($b['title']) ?>">
            </a>
          </td>
          <td>
            <div class="fw-bold text-dark"><?= e($b['title']) ?></div>
            <?php if (!empty($b['description'])): ?>
              <small class="text-muted d-block text-truncate" style="max-width: 320px;"><?= e($b['description']) ?></small>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!empty($b['button_text'])): ?>
              <span class="badge bg-light text-dark border"><?= e($b['button_text']) ?></span>
              <small class="text-muted d-block mt-0.5"><?= e($b['button_url'] ?? '#') ?></small>
            <?php else: ?>
              <span class="text-muted small">Tanpa tombol</span>
            <?php endif; ?>
          </td>
          <td class="text-center fw-semibold"><?= (int) $b['sort_order'] ?></td>
          <td class="text-center">
            <!-- 1-Click Toggle Form Button -->
            <form method="post" action="toggle.php" class="d-inline">
              <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
              <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
              <?php if ($b['status']): ?>
                <button type="submit" class="btn btn-sm btn-success px-2.5 py-1 rounded-pill fw-semibold shadow-xs d-inline-flex align-items-center gap-1" title="Klik untuk menonaktifkan (OFF)">
                  <i class="bi bi-toggle-on fs-6"></i>
                  <span>ON (Aktif)</span>
                </button>
              <?php else: ?>
                <button type="submit" class="btn btn-sm btn-outline-secondary px-2.5 py-1 rounded-pill fw-semibold shadow-xs d-inline-flex align-items-center gap-1" title="Klik untuk mengaktifkan (ON)">
                  <i class="bi bi-toggle-off fs-6"></i>
                  <span>OFF (Nonaktif)</span>
                </button>
              <?php endif; ?>
            </form>
          </td>
          <td class="text-center">
            <div class="btn-group btn-group-sm">
              <a href="form.php?id=<?= (int) $b['id'] ?>" class="btn btn-outline-primary-custom" title="Edit Banner">
                <i class="bi bi-pencil"></i>
              </a>
              <form method="post" action="delete.php" class="d-inline" data-confirm-delete="Hapus banner '<?= e($b['title']) ?>'?">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                <button type="submit" class="btn btn-outline-danger" title="Hapus Banner">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($banners)): ?>
          <tr>
            <td colspan="6" class="text-center text-muted py-4">
              <i class="bi bi-images fs-3 d-block mb-1 text-muted"></i>
              Belum ada banner slider yang dibuat.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>

