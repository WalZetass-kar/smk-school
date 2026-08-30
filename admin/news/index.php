<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db     = getDB();
$status = $_GET['status'] ?? '';
$where  = '1=1';
$params = [];
if (in_array($status, ['draft', 'publish'], true)) {
    $where = 'n.status = ?';
    $params[] = $status;
}
$stmt = $db->prepare("SELECT n.*, c.name AS category_name FROM news n
                       LEFT JOIN news_categories c ON c.id = n.category_id
                       WHERE $where ORDER BY n.created_at DESC");
$stmt->execute($params);
$news = $stmt->fetchAll();

$pageTitle = 'Semua Berita';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?= flashMessage() ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="btn-group">
    <a href="?" class="btn btn-sm <?= $status === '' ? 'btn-accent' : 'btn-outline-primary-custom' ?>">Semua</a>
    <a href="?status=publish" class="btn btn-sm <?= $status === 'publish' ? 'btn-accent' : 'btn-outline-primary-custom' ?>">Publish</a>
    <a href="?status=draft" class="btn btn-sm <?= $status === 'draft' ? 'btn-accent' : 'btn-outline-primary-custom' ?>">Draft</a>
  </div>
  <a href="form.php" class="btn btn-accent"><i class="bi bi-plus-lg"></i> Tambah Berita</a>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead><tr><th>Gambar</th><th>Judul</th><th>Kategori</th><th>Tanggal</th><th>Dilihat</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($news as $n): ?>
        <tr>
          <td><img src="<?= e(BASE_URL . '/' . $n['featured_image']) ?>" class="thumb" alt=""></td>
          <td style="max-width:260px;"><?= e($n['title']) ?></td>
          <td><?= e($n['category_name'] ?? '-') ?></td>
          <td><?= formatTanggalIndo($n['published_at']) ?></td>
          <td><?= (int) $n['views'] ?></td>
          <td><span class="badge <?= $n['status'] === 'publish' ? 'badge-status-publish' : 'badge-status-draft' ?>"><?= e(ucfirst($n['status'])) ?></span></td>
          <td>
            <?php if ($n['status'] === 'publish'): ?>
              <a href="<?= e(BASE_URL) ?>/berita/<?= e($n['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
            <?php endif; ?>
            <a href="form.php?id=<?= (int) $n['id'] ?>" class="btn btn-sm btn-outline-primary-custom"><i class="bi bi-pencil"></i></a>
            <form method="post" action="delete.php" class="d-inline" data-confirm-delete="Hapus berita ini?">
              <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
              <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($news)): ?><tr><td colspan="7" class="text-center text-muted py-4">Belum ada berita.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
