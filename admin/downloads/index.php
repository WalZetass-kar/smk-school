<?php
/**
 * admin/downloads/index.php
 * Daftar Berkas & Dokumen Unduhan
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$items = $db->query("SELECT * FROM downloads ORDER BY id DESC")->fetchAll();

$pageTitle = 'Manajemen Pusat Unduhan';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>

<?= flashMessage() ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold text-primary mb-1">Pusat Unduhan (Download Center)</h1>
    <p class="text-muted small mb-0">Kelola berkas publik, formulir surat, kalender akademik, dan modul pembelajaran.</p>
  </div>
  <a href="form.php" class="btn btn-accent"><i class="bi bi-cloud-arrow-up me-1"></i>Upload Dokumen Baru</a>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Tipe / Judul Dokumen</th>
          <th>Kategori</th>
          <th>Ukuran File</th>
          <th>Diunduh</th>
          <th>Waktu Upload</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($items): ?>
          <?php foreach ($items as $item): ?>
            <?php
              $ext = strtolower($item['file_ext']);
              $icon = match($ext) {
                'pdf'  => 'bi-file-earmark-pdf text-danger',
                'doc', 'docx' => 'bi-file-earmark-word text-primary',
                'xls', 'xlsx' => 'bi-file-earmark-excel text-success',
                'zip', 'rar'  => 'bi-file-earmark-zip text-warning',
                default       => 'bi-file-earmark-text text-secondary'
              };
            ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <i class="bi <?= $icon ?> fs-2"></i>
                  <div>
                    <div class="fw-bold text-dark"><?= e($item['title']) ?></div>
                    <a href="<?= e(BASE_URL . '/' . $item['file_path']) ?>" target="_blank" class="small text-muted text-decoration-none">
                      <i class="bi bi-box-arrow-up-right me-1"></i>Lihat File
                    </a>
                  </div>
                </div>
              </td>
              <td><span class="badge bg-light text-dark border"><?= e($item['category']) ?></span></td>
              <td class="small"><?= e($item['file_size'] ?: '-') ?></td>
              <td><span class="badge bg-secondary"><?= number_format($item['downloads_count']) ?>x</span></td>
              <td class="small text-muted"><?= formatTanggalIndo($item['created_at']) ?></td>
              <td class="text-end">
                <a href="form.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                <form action="delete.php" method="post" class="d-inline" data-confirm-delete="Yakin ingin menghapus dokumen <?= e($item['title']) ?>?">
                  <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                  <input type="hidden" name="id" value="<?= $item['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada dokumen yang diunggah.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
