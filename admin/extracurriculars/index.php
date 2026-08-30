<?php
/**
 * admin/extracurriculars/index.php
 * Daftar Ekstrakurikuler
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$items = $db->query("SELECT * FROM extracurriculars ORDER BY category ASC, name ASC")->fetchAll();

$pageTitle = 'Manajemen Ekstrakurikuler';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>

<?= flashMessage() ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold text-primary mb-1">Ekstrakurikuler</h1>
    <p class="text-muted small mb-0">Kelola kegiatan ekstrakurikuler, jadwal latihan, dan nama pembina.</p>
  </div>
  <a href="form.php" class="btn btn-accent"><i class="bi bi-plus-lg me-1"></i>Tambah Ekstrakurikuler</a>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Foto / Nama Ekskul</th>
          <th>Kategori</th>
          <th>Jadwal</th>
          <th>Pembina / Pelatih</th>
          <th>Status</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($items): ?>
          <?php foreach ($items as $item): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <?php if (!empty($item['image'])): ?>
                    <img src="<?= e(BASE_URL . '/' . $item['image']) ?>" alt="" style="width: 50px; height: 40px; object-fit: cover;" class="rounded border">
                  <?php else: ?>
                    <div class="rounded border bg-light text-center" style="width: 50px; height: 40px; line-height: 40px;"><i class="bi bi-activity text-muted"></i></div>
                  <?php endif; ?>
                  <div>
                    <div class="fw-bold text-dark"><?= e($item['name']) ?></div>
                    <div class="text-muted small"><i class="bi <?= e($item['icon']) ?> me-1"></i><?= e($item['icon']) ?></div>
                  </div>
                </div>
              </td>
              <td><span class="badge bg-light text-dark border"><?= e($item['category']) ?></span></td>
              <td class="small"><?= e($item['schedule'] ?: '-') ?></td>
              <td class="small fw-semibold"><?= e($item['coach_name'] ?: '-') ?></td>
              <td>
                <?php if ($item['status']): ?>
                  <span class="badge bg-success">Aktif</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Nonaktif</span>
                <?php endif; ?>
              </td>
              <td class="text-end">
                <a href="form.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                <form action="delete.php" method="post" class="d-inline" data-confirm-delete="Yakin ingin menghapus ekskul <?= e($item['name']) ?>?">
                  <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                  <input type="hidden" name="id" value="<?= $item['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data ekstrakurikuler.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
