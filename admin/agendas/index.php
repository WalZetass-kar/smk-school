<?php
/**
 * admin/agendas/index.php
 * Kelola Agenda & Kalender Kegiatan Sekolah
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();

$db = getDB();
$pageTitle = 'Kelola Agenda Kegiatan';
$currentAdminNav = 'agendas';

$agendas = $db->query("SELECT * FROM agendas ORDER BY start_date DESC")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold text-primary mb-1"><i class="bi bi-calendar-event-fill me-2"></i>Agenda &amp; Kegiatan Sekolah</h1>
    <p class="text-muted small mb-0">Kelola kalender kegiatan akademik, ujian, dan acara sekolah.</p>
  </div>
  <a href="<?= e(BASE_URL) ?>/admin/agendas/form.php" class="btn btn-accent btn-sm">
    <i class="bi bi-plus-lg me-1"></i>Tambah Agenda Baru
  </a>
</div>

<?= flashMessage() ?>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Tanggal</th>
          <th>Nama Kegiatan</th>
          <th>Waktu &amp; Lokasi</th>
          <th>Penyelenggara</th>
          <th>Status</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($agendas)): ?>
          <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada agenda kegiatan.</td></tr>
        <?php else: ?>
          <?php foreach ($agendas as $a): ?>
            <tr>
              <td>
                <div class="fw-bold text-primary"><?= date('d M Y', strtotime($a['start_date'])) ?></div>
                <?php if (!empty($a['end_date']) && $a['end_date'] !== $a['start_date']): ?>
                  <div class="small text-muted">s/d <?= date('d M Y', strtotime($a['end_date'])) ?></div>
                <?php endif; ?>
              </td>
              <td>
                <div class="fw-bold text-dark"><?= e($a['title']) ?></div>
                <div class="small text-muted line-clamp-1"><?= e(strip_tags($a['description'] ?? '')) ?></div>
              </td>
              <td>
                <div><i class="bi bi-clock me-1 text-muted"></i><?= e($a['time_info']) ?></div>
                <div class="small text-muted"><i class="bi bi-geo-alt me-1 text-danger"></i><?= e($a['location']) ?></div>
              </td>
              <td><span class="badge bg-light text-dark border"><?= e($a['organizer']) ?></span></td>
              <td>
                <?= !empty($a['status']) ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' ?>
              </td>
              <td class="text-end">
                <a href="<?= e(BASE_URL) ?>/agenda/<?= e($a['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-info p-1 px-2" title="Lihat">
                  <i class="bi bi-box-arrow-up-right"></i>
                </a>
                <a href="<?= e(BASE_URL) ?>/admin/agendas/form.php?id=<?= (int)$a['id'] ?>" class="btn btn-sm btn-outline-primary p-1 px-2" title="Edit">
                  <i class="bi bi-pencil"></i>
                </a>
                <form action="<?= e(BASE_URL) ?>/admin/agendas/delete.php" method="post" class="d-inline" data-confirm-delete="Hapus agenda <?= e($a['title']) ?>?">
                  <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                  <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger p-1 px-2" title="Hapus">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
