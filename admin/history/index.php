<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$history = $db->query("SELECT * FROM history ORDER BY id LIMIT 1")->fetch();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $title   = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        if ($title === '') { $errors[] = 'Judul wajib diisi.'; }
        if ($content === '') { $errors[] = 'Isi sejarah wajib diisi.'; }

        if (empty($errors)) {
            if ($history) {
                $db->prepare("UPDATE history SET title=?, content=? WHERE id=?")->execute([$title, $content, $history['id']]);
            } else {
                $db->prepare("INSERT INTO history (title, content) VALUES (?,?)")->execute([$title, $content]);
            }
            logActivity('UPDATE_HISTORY', 'profile', "Memperbarui sejarah sekolah ($title)");
            setFlash('success', 'Sejarah berhasil disimpan.');
            redirect(BASE_URL . '/admin/history/index.php');
        }
        $history = ['title' => $title, 'content' => $content];
    }
}

$pageTitle = 'Sejarah';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?= flashMessage() ?>
<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="admin-card">
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <div class="mb-3">
      <label class="form-label small fw-semibold">Judul</label>
      <input type="text" name="title" class="form-control" value="<?= e($history['title'] ?? 'Sejarah Sekolah') ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label small fw-semibold">Isi Sejarah</label>
      <textarea name="content" rows="10" class="form-control wysiwyg-editor" required><?= e($history['content'] ?? '') ?></textarea>
    </div>
    <button type="submit" class="btn btn-accent px-4">Simpan Perubahan</button>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
