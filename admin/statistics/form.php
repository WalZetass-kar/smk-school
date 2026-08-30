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

        if ($title === '') { $errors[] = 'Judul wajib diisi.'; }

        if (empty($errors)) {
            if ($item) {
                $db->prepare("UPDATE statistics SET title=?, value=?, icon=?, sort_order=?, status=? WHERE id=?")
                   ->execute([$title, $value, $icon, $sortOrder, $status, $item['id']]);
            } else {
                $db->prepare("INSERT INTO statistics (title, value, icon, sort_order, status) VALUES (?,?,?,?,?)")
                   ->execute([$title, $value, $icon, $sortOrder, $status]);
            }
            setFlash('success', 'Statistik berhasil disimpan.');
            redirect(BASE_URL . '/admin/statistics/index.php');
        }
        $item = compact('title', 'value', 'icon', 'sortOrder', 'status');
        $item['sort_order'] = $sortOrder;
    }
}

$pageTitle = $id ? 'Edit Statistik' : 'Tambah Statistik';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="admin-card">
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Judul</label>
        <input type="text" name="title" class="form-control" value="<?= e($item['title'] ?? '') ?>" placeholder="mis. Jumlah Siswa" required>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Nilai</label>
        <input type="number" name="value" class="form-control" value="<?= (int) ($item['value'] ?? 0) ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Ikon <span class="text-muted fw-normal">(nama class Bootstrap Icons)</span></label>
        <input type="text" name="icon" class="form-control" value="<?= e($item['icon'] ?? 'bi-bar-chart') ?>" placeholder="bi-people-fill">
        <div class="form-text">Lihat daftar ikon di <a href="https://icons.getbootstrap.com" target="_blank" rel="noopener">icons.getbootstrap.com</a>.</div>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Urutan</label>
        <input type="number" name="sort_order" class="form-control" value="<?= (int) ($item['sort_order'] ?? 0) ?>">
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
          <input type="checkbox" name="status" class="form-check-input" id="statusCheck" <?= ($item['status'] ?? 1) ? 'checked' : '' ?>>
          <label class="form-check-label" for="statusCheck">Tampilkan (aktif)</label>
        </div>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-accent px-4">Simpan</button>
        <a href="index.php" class="btn btn-outline-primary-custom px-4">Batal</a>
      </div>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
