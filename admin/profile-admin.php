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
        $name     = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $photo    = $me['photo'] ?? null;

        if ($name === '') { $errors[] = 'Nama wajib diisi.'; }
        if ($username === '') { $errors[] = 'Username wajib diisi.'; }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Email tidak valid.'; }

        if (empty($errors)) {
            $dupCheck = $db->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $dupCheck->execute([$username, $email, $me['id']]);
            if ($dupCheck->fetch()) { $errors[] = 'Username atau email sudah digunakan admin lain.'; }
        }

        if (!empty($_FILES['photo']['name'])) {
            $uploadError = null;
            $path = handleImageUpload($_FILES['photo'], $uploadError);
            if ($path) { $photo = $path; } else { $errors[] = 'Foto: ' . $uploadError; }
        }

        if (empty($errors)) {
            $db->prepare("UPDATE users SET name=?, username=?, email=?, photo=? WHERE id=?")
               ->execute([$name, $username, $email, $photo, $me['id']]);
            $_SESSION['admin_name'] = $name;
            setFlash('success', 'Profil berhasil diperbarui.');
            redirect(BASE_URL . '/admin/profile-admin.php');
        }
        $me = array_merge($me, compact('name', 'username', 'email', 'photo'));
    }
}

$pageTitle = 'Profil Admin';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
require __DIR__ . '/includes/topbar.php';
?>
<?= flashMessage() ?>
<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="admin-card" style="max-width:560px;">
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <div class="row g-3">
      <div class="col-12 text-center">
        <?php if (!empty($me['photo'])): ?>
          <img src="<?= e(BASE_URL . '/' . $me['photo']) ?>" class="rounded-circle mb-2" style="width:90px;height:90px;object-fit:cover;" alt="">
        <?php else: ?>
          <i class="bi bi-person-circle" style="font-size:5rem; color:var(--color-border);"></i>
        <?php endif; ?>
        <input type="file" name="photo" class="form-control mt-2" accept=".jpg,.jpeg,.png,.webp">
      </div>
      <div class="col-12">
        <label class="form-label small fw-semibold">Nama Lengkap</label>
        <input type="text" name="name" class="form-control" value="<?= e($me['name'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Username</label>
        <input type="text" name="username" class="form-control" value="<?= e($me['username'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Email</label>
        <input type="email" name="email" class="form-control" value="<?= e($me['email'] ?? '') ?>" required>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-accent px-4">Simpan Perubahan</button>
      </div>
    </div>
  </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
