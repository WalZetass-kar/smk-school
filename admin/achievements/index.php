<?php
/**
 * admin/achievements/index.php
 * Daftar Prestasi Siswa & Sekolah
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$items = $db->query("SELECT * FROM achievements ORDER BY year DESC, id DESC")->fetchAll();

$pageTitle = 'Manajemen Prestasi Siswa';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>

<?= flashMessage() ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold text-primary mb-1">Prestasi Siswa (Hall of Fame)</h1>
    <p class="text-muted small mb-0">Kelola riwayat kejuaraan, medali, dan penghargaan sekolah.</p>
  </div>
  <a href="form.php" class="btn btn-accent"><i class="bi bi-plus-lg me-1"></i>Tambah Prestasi Baru</a>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Foto / Judul Prestasi</th>
          <th>Nama Siswa / Tim</th>
          <th>Nama Kompetisi</th>
          <th>Tingkat</th>
          <th>Tahun</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($items): ?>
          <?php foreach ($items as $item): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <?php if (!empty($item['photo'])): ?>
                    <img src="<?= e(BASE_URL . '/' . $item['photo']) ?>" alt="" style="width: 50px; height: 40px; object-fit: cover;" class="rounded border">
                  <?php else: ?>
                    <div class="rounded border bg-light text-center" style="width: 50px; height: 40px; line-height: 40px;"><i class="bi bi-trophy text-warning"></i></div>
                  <?php endif; ?>
                  <div>
                    <div class="fw-bold text-dark"><?= e($item['title']) ?></div>
                    <span class="badge bg-warning text-dark"><?= e($item['rank_position']) ?></span>
                  </div>
                </div>
              </td>
              <td class="small fw-semibold"><?= e($item['student_name']) ?></td>
              <td class="small text-muted"><?= e($item['competition_name']) ?></td>
              <td><span class="badge bg-primary text-uppercase"><?= e($item['level']) ?></span></td>
              <td class="small fw-bold"><?= e($item['year']) ?></td>
              <td class="text-end">
                <a href="form.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                <form action="delete.php" method="post" class="d-inline" data-confirm-delete="Yakin ingin menghapus data prestasi ini?">
                  <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                  <input type="hidden" name="id" value="<?= $item['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data prestasi siswa.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
