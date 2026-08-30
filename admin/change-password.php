<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$me = $stmt->fetch();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $me['password'])) {
            $errors[] = 'Password saat ini salah.';
        }
        if (strlen($new) < 6) {
            $errors[] = 'Password baru minimal 6 karakter.';
        }
        if ($new !== $confirm) {
            $errors[] = 'Konfirmasi password baru tidak cocok.';
        }

        if (empty($errors)) {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $me['id']]);
            setFlash('success', 'Password berhasil diubah.');
            redirect(BASE_URL . '/admin/change-password.php');
        }
    }
}

$pageTitle = 'Ganti Password';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
require __DIR__ . '/includes/topbar.php';
?>
<?= flashMessage() ?>
<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="admin-card" style="max-width:480px;">
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <div class="mb-3">
      <label class="form-label small fw-semibold">Password Saat Ini</label>
      <input type="password" name="current_password" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label small fw-semibold">Password Baru</label>
      <input type="password" name="new_password" class="form-control" required minlength="6">
    </div>
    <div class="mb-4">
      <label class="form-label small fw-semibold">Konfirmasi Password Baru</label>
      <input type="password" name="confirm_password" class="form-control" required minlength="6">
    </div>
    <button type="submit" class="btn btn-accent px-4">Ubah Password</button>
  </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
