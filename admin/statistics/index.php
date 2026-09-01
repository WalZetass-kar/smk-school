<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();

$stats = $db->query("SELECT * FROM statistics ORDER BY sort_order ASC, id ASC")->fetchAll();

$pageTitle = 'Statistik Data Sekolah (Siswa, Guru, dll)';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?= flashMessage() ?>

<!-- Header & Add Button -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
  <div>
    <h1 class="h4 fw-bold text-primary mb-1">Statistik &amp; Data Ringkasan Sekolah</h1>
    <p class="text-muted small mb-0">Kelola angka statistik seperti <strong>Jumlah Siswa, Guru &amp; Tendik, Jurusan, Alumni, Rombel, Fasilitas, dan Mitra</strong> yang ditampilkan di halaman beranda website.</p>
  </div>
  <a href="form.php" class="btn btn-accent px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2">
    <i class="bi bi-plus-circle-fill"></i> Tambah Data Statistik
  </a>
</div>

<!-- Grid Cards Preview -->
<div class="row g-3 mb-4">
  <?php foreach ($stats as $s): ?>
    <div class="col-sm-6 col-lg-4 col-xl-3">
      <div class="stat-card h-100 position-relative border <?= $s['status'] ? 'border-primary-subtle' : 'border-secondary opacity-75' ?>">
        <div class="stat-icon" style="background-color: <?= $s['status'] ? 'rgba(15, 44, 89, 0.08)' : '#f1f5f9' ?>; color: <?= $s['status'] ? 'var(--color-primary)' : '#64748b' ?>;">
          <i class="bi <?= e($s['icon'] ?: 'bi-bar-chart') ?>"></i>
        </div>
        <div class="flex-grow-1 overflow-hidden">
          <div class="stat-value text-truncate"><?= number_format((int) $s['value'], 0, ',', '.') ?></div>
          <div class="stat-label text-truncate"><?= e($s['title']) ?></div>
          <div class="mt-1 d-flex align-items-center gap-2">
            <span class="badge <?= $s['status'] ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary text-white' ?> fs-8">
              <?= $s['status'] ? 'Tampil di Web' : 'Disembunyikan' ?>
            </span>
          </div>
        </div>
        <div class="position-absolute top-0 end-0 p-2">
          <a href="form.php?id=<?= (int) $s['id'] ?>" class="btn btn-sm btn-light text-primary p-1 rounded-circle" title="Edit Data">
            <i class="bi bi-pencil-square fs-6"></i>
          </a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Table View -->
<div class="admin-card">
  <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
    <h2 class="h6 fw-bold text-primary mb-0"><i class="bi bi-table me-2"></i>Tabel Seluruh Data Statistik</h2>
    <span class="text-muted small">Total: <strong><?= count($stats) ?></strong> data</span>
  </div>

  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead>
        <tr>
          <th style="width: 70px;" class="text-center">Ikon</th>
          <th>Nama / Judul Statistik</th>
          <th>Jumlah / Nilai Angka</th>
          <th style="width: 80px;" class="text-center">Urutan</th>
          <th style="width: 120px;">Status</th>
          <th style="width: 120px;" class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($stats)): ?>
          <?php foreach ($stats as $s): ?>
          <tr>
            <td class="text-center">
              <div class="p-2 bg-light rounded d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="bi <?= e($s['icon'] ?: 'bi-bar-chart') ?> fs-5 text-primary"></i>
              </div>
            </td>
            <td>
              <strong class="text-dark"><?= e($s['title']) ?></strong>
              <div class="small text-muted font-monospace"><i class="bi bi-code-slash me-1"></i><?= e($s['icon']) ?></div>
            </td>
            <td>
              <span class="fs-6 fw-bold text-primary"><?= number_format((int) $s['value'], 0, ',', '.') ?></span>
            </td>
            <td class="text-center">
              <span class="badge bg-light text-dark border"><?= (int) $s['sort_order'] ?></span>
            </td>
            <td>
              <span class="badge <?= $s['status'] ? 'badge-status-active' : 'badge-status-inactive' ?>">
                <i class="bi <?= $s['status'] ? 'bi-check-circle-fill' : 'bi-dash-circle' ?> me-1"></i>
                <?= $s['status'] ? 'Aktif' : 'Nonaktif' ?>
              </span>
            </td>
            <td class="text-end">
              <div class="btn-group btn-group-sm">
                <a href="form.php?id=<?= (int) $s['id'] ?>" class="btn btn-outline-primary-custom" title="Edit Angka / Data">
                  <i class="bi bi-pencil-square"></i>
                </a>
                <form method="post" action="delete.php" class="d-inline" data-confirm-delete="Hapus data statistik '<?= e($s['title']) ?>'?">
                  <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                  <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                  <button type="submit" class="btn btn-outline-danger" title="Hapus">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" class="text-center text-muted py-5">
              <i class="bi bi-bar-chart-line fs-2 d-block mb-2 text-muted"></i>
              Belum ada data statistik sekolah.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
