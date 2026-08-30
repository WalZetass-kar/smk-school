<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireSuperAdmin();

$db = getDB();
$id = (int) ($_GET['id'] ?? 0);
$item = null;
if ($id) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $name     = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $role     = in_array($_POST['role'] ?? '', ['admin', 'superadmin'], true) ? $_POST['role'] : 'admin';
        $password = $_POST['password'] ?? '';

        if ($name === '') { $errors[] = 'Nama wajib diisi.'; }
        if ($username === '') { $errors[] = 'Username wajib diisi.'; }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Email tidak valid.'; }
        if (!$item && $password === '') { $errors[] = 'Password wajib diisi untuk akun baru.'; }
        if ($password !== '' && strlen($password) < 6) { $errors[] = 'Password minimal 6 karakter.'; }

        if (empty($errors)) {
            $dupCheck = $db->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $dupCheck->execute([$username, $email, $item['id'] ?? 0]);
            if ($dupCheck->fetch()) {
                $errors[] = 'Username atau email sudah digunakan admin lain.';
            }
        }

        if (empty($errors)) {
            if ($item) {
                if ($password !== '') {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $db->prepare("UPDATE users SET name=?, username=?, email=?, role=?, password=? WHERE id=?")
                       ->execute([$name, $username, $email, $role, $hash, $item['id']]);
                } else {
                    $db->prepare("UPDATE users SET name=?, username=?, email=?, role=? WHERE id=?")
                       ->execute([$name, $username, $email, $role, $item['id']]);
                }
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $db->prepare("INSERT INTO users (name, username, email, password, role) VALUES (?,?,?,?,?)")
                   ->execute([$name, $username, $email, $hash, $role]);
            }
            setFlash('success', 'Akun admin berhasil disimpan.');
            redirect(BASE_URL . '/admin/users/index.php');
        }
        $item = compact('name', 'username', 'email', 'role');
    }
}

$pageTitle = $id ? 'Edit Admin' : 'Tambah Admin';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="admin-card" style="max-width:560px;">
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label small fw-semibold">Nama Lengkap</label>
        <input type="text" name="name" class="form-control" value="<?= e($item['name'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Username</label>
        <input type="text" name="username" class="form-control" value="<?= e($item['username'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Role</label>
        <select name="role" class="form-select">
          <option value="admin" <?= ($item['role'] ?? 'admin') === 'admin' ? 'selected' : '' ?>>Admin</option>
          <option value="superadmin" <?= ($item['role'] ?? '') === 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label small fw-semibold">Email</label>
        <input type="email" name="email" class="form-control" value="<?= e($item['email'] ?? '') ?>" required>
      </div>
      <div class="col-12">
        <label class="form-label small fw-semibold">Password <?= $item ? '<span class="text-muted fw-normal">(kosongkan jika tidak diubah)</span>' : '' ?></label>
        <input type="password" name="password" class="form-control" <?= $item ? '' : 'required' ?>>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-accent px-4">Simpan</button>
        <a href="index.php" class="btn btn-outline-primary-custom px-4">Batal</a>
      </div>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
