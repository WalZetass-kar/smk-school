<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        setFlash('error', 'Permintaan tidak valid.');
        redirect(BASE_URL . '/admin/contacts/index.php');
    }
    $id     = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($id && $action === 'delete') {
        $contactName = $db->query("SELECT name FROM contacts WHERE id = $id")->fetchColumn() ?: "ID #$id";
        $db->prepare("DELETE FROM contacts WHERE id = ?")->execute([$id]);
        logActivity('DELETE_CONTACT_MESSAGE', 'contacts', "Menghapus pesan kontak masuk dari: $contactName (ID: $id)");
        setFlash('success', 'Pesan berhasil dihapus.');
    } elseif ($id && $action === 'mark_read') {
        $db->prepare("UPDATE contacts SET is_read = 1 WHERE id = ?")->execute([$id]);
        logActivity('READ_CONTACT_MESSAGE', 'contacts', "Menandai pesan kontak telah dibaca (ID: $id)");
        setFlash('success', 'Pesan ditandai sudah dibaca.');
    }
    redirect(BASE_URL . '/admin/contacts/index.php');
}

$contacts = $db->query("SELECT * FROM contacts ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'Pesan Masuk';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?= flashMessage() ?>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-admin align-middle">
      <thead><tr><th>Status</th><th>Nama</th><th>Email</th><th>Subjek</th><th>Tanggal</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($contacts as $c): ?>
        <tr class="<?= $c['is_read'] ? '' : 'fw-semibold' ?>">
          <td><span class="badge <?= $c['is_read'] ? 'badge-status-inactive' : 'badge-status-active' ?>"><?= $c['is_read'] ? 'Dibaca' : 'Baru' ?></span></td>
          <td><?= e($c['name']) ?></td>
          <td><?= e($c['email']) ?></td>
          <td style="max-width:200px;"><?= e($c['subject'] ?: '-') ?></td>
          <td class="small"><?= formatTanggalIndo($c['created_at']) ?></td>
          <td>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#msgModal<?= (int) $c['id'] ?>"><i class="bi bi-envelope-open"></i></button>
            <?php if (!$c['is_read']): ?>
              <form method="post" class="d-inline">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="action" value="mark_read">
                <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-primary-custom"><i class="bi bi-check2"></i></button>
              </form>
            <?php endif; ?>
            <form method="post" class="d-inline" data-confirm-delete="Hapus pesan ini?">
              <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($contacts)): ?><tr><td colspan="6" class="text-center text-muted py-4">Belum ada pesan masuk.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php foreach ($contacts as $c): ?>
<div class="modal fade" id="msgModal<?= (int) $c['id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= e($c['subject'] ?: 'Pesan dari ' . $c['name']) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <p class="mb-1"><strong>Nama:</strong> <?= e($c['name']) ?></p>
        <p class="mb-1"><strong>Email:</strong> <?= e($c['email']) ?></p>
        <?php if ($c['phone']): ?><p class="mb-1"><strong>Telepon:</strong> <?= e($c['phone']) ?></p><?php endif; ?>
        <p class="mb-3"><strong>Tanggal:</strong> <?= formatTanggalIndo($c['created_at']) ?></p>
        <p style="white-space:pre-line;"><?= nl2br(e($c['message'])) ?></p>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
