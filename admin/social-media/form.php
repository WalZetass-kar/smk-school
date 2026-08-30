<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$id = (int) ($_GET['id'] ?? 0);
$item = null;
if ($id) {
    $stmt = $db->prepare("SELECT * FROM social_media WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $platform = trim($_POST['platform'] ?? '');
        $url      = trim($_POST['url'] ?? '');
        $icon     = trim($_POST['icon'] ?? 'bi-link-45deg');
        $status   = isset($_POST['status']) ? 1 : 0;

        if ($platform === '') { $errors[] = 'Nama platform wajib diisi.'; }
        if (!filter_var($url, FILTER_VALIDATE_URL)) { $errors[] = 'URL tidak valid.'; }

        if (empty($errors)) {
            if ($item) {
                $db->prepare("UPDATE social_media SET platform=?, url=?, icon=?, status=? WHERE id=?")
                   ->execute([$platform, $url, $icon, $status, $item['id']]);
            } else {
                $db->prepare("INSERT INTO social_media (platform, url, icon, status) VALUES (?,?,?,?)")
                   ->execute([$platform, $url, $icon, $status]);
            }
            setFlash('success', 'Tautan berhasil disimpan.');
            redirect(BASE_URL . '/admin/social-media/index.php');
        }
        $item = compact('platform', 'url', 'icon', 'status');
    }
}

$pageTitle = $id ? 'Edit Social Media' : 'Tambah Social Media';
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
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Platform</label>
        <input type="text" name="platform" class="form-control" value="<?= e($item['platform'] ?? '') ?>" placeholder="mis. Instagram" required>
      </div>
      <div class="col-md-5">
        <label class="form-label small fw-semibold">URL</label>
        <input type="text" name="url" class="form-control" value="<?= e($item['url'] ?? '') ?>" placeholder="https://instagram.com/..." required>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Ikon</label>
        <input type="text" name="icon" class="form-control" value="<?= e($item['icon'] ?? 'bi-link-45deg') ?>" placeholder="bi-instagram">
      </div>
      <div class="col-12">
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
