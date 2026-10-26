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
    if (!$item) {
        setFlash('error', 'Akun admin tidak ditemukan.');
        redirect(BASE_URL . '/admin/users/index.php');
    }
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $name            = trim($_POST['name'] ?? '');
        $username        = trim($_POST['username'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $role            = in_array($_POST['role'] ?? '', ['admin', 'superadmin'], true) ? $_POST['role'] : 'admin';
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $photo           = $item['photo'] ?? null;

        if ($name === '') { $errors[] = 'Nama lengkap admin wajib diisi.'; }
        if ($username === '') { $errors[] = 'Username wajib diisi.'; }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Format alamat email tidak valid.'; }

        if (!$item && $password === '') {
            $errors[] = 'Password wajib diisi untuk akun admin baru.';
        }
        if ($password !== '' && (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password))) {
            $errors[] = 'Password minimal 8 karakter dan wajib mengombinasikan huruf dan angka.';
        }
        if ($password !== '' && $password !== $confirmPassword) {
            $errors[] = 'Konfirmasi password tidak cocok dengan password yang dimasukkan.';
        }

        if (empty($errors)) {
            $dupCheck = $db->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $dupCheck->execute([$username, $email, $item['id'] ?? 0]);
            if ($dupCheck->fetch()) {
                $errors[] = 'Username atau email sudah terdaftar untuk pengguna lain.';
            }
        }

        // Upload photo
        if (!empty($_FILES['photo']['name'])) {
            $uploadError = null;
            $path = handleImageUpload($_FILES['photo'], $uploadError, 'admins');
            if ($path) {
                if (!empty($item['photo']) && !str_starts_with($item['photo'], 'assets/images/')) {
                    @unlink(BASE_PATH . '/' . $item['photo']);
                }
                $photo = $path;
            } else {
                $errors[] = 'Foto Profil: ' . $uploadError;
            }
        }

        if (isset($_POST['remove_photo']) && $_POST['remove_photo'] === '1') {
            if (!empty($item['photo']) && !str_starts_with($item['photo'], 'assets/images/')) {
                @unlink(BASE_PATH . '/' . $item['photo']);
            }
            $photo = null;
        }

        if (empty($errors)) {
            if ($item) {
                if ($password !== '') {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $db->prepare("UPDATE users SET name=?, username=?, email=?, role=?, photo=?, password=? WHERE id=?")
                       ->execute([$name, $username, $email, $role, $photo, $hash, $item['id']]);
                } else {
                    $db->prepare("UPDATE users SET name=?, username=?, email=?, role=?, photo=? WHERE id=?")
                       ->execute([$name, $username, $email, $role, $photo, $item['id']]);
                }
                // If updating current logged in user name, update session
                if ((int)$item['id'] === (int)$_SESSION['admin_id']) {
                    $_SESSION['admin_name'] = $name;
                }
                logActivity('UPDATE_ADMIN_USER', 'users', "Super Admin mengubah data admin: $name ($username, role: $role)");
                setFlash('success', 'Akun admin "' . e($name) . '" berhasil diperbarui.');
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $db->prepare("INSERT INTO users (name, username, email, password, role, photo) VALUES (?,?,?,?,?,?)")
                   ->execute([$name, $username, $email, $hash, $role, $photo]);
                logActivity('CREATE_ADMIN_USER', 'users', "Super Admin mendaftarkan admin baru: $name ($username, role: $role)");
                setFlash('success', 'Akun admin baru "' . e($name) . '" berhasil ditambahkan.');
            }
            redirect(BASE_URL . '/admin/users/index.php');
        }
        $item = array_merge($item ?? [], compact('name', 'username', 'email', 'role', 'photo'));
    }
}

$pageTitle = $id ? 'Edit Akun Admin' : 'Tambah Administrator Baru';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>
<?= flashMessage() ?>

<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="admin-card">
      <div class="d-flex align-items-center justify-content-between pb-3 mb-4 border-bottom">
        <div>
          <h2 class="h5 fw-bold text-primary mb-1">
            <i class="bi <?= $id ? 'bi-person-gear' : 'bi-person-plus-fill' ?> me-2 text-accent"></i>
            <?= $id ? 'Edit Data Administrator' : 'Formulir Pendaftaran Admin Baru' ?>
          </h2>
          <p class="text-muted small mb-0">Isi data akun administrator yang memiliki akses ke dashboard sekolah.</p>
        </div>
        <a href="index.php" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
      </div>

      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

        <div class="row g-3">
          <!-- Photo Upload & Preview -->
          <div class="col-12 text-center pb-3">
            <div class="position-relative d-inline-block mb-2">
              <?php if (!empty($item['photo']) && file_exists(BASE_PATH . '/' . $item['photo'])): ?>
                <img src="<?= e(BASE_URL . '/' . $item['photo']) ?>" id="previewImg" class="rounded-circle shadow-sm" style="width:100px;height:100px;object-fit:cover;border:3px solid #fff;">
              <?php else: ?>
                <div id="previewFallback" class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center border shadow-sm" style="width:100px;height:100px;">
                  <i class="bi bi-person text-secondary" style="font-size:3rem;"></i>
                </div>
              <?php endif; ?>
            </div>
            <div class="col-md-6 mx-auto">
              <input type="file" name="photo" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp">
              <?php if (!empty($item['photo'])): ?>
                <div class="form-check d-inline-block mt-2">
                  <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="removePhotoCheck">
                  <label class="form-check-label small text-danger" for="removePhotoCheck">Hapus Foto Profil</label>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Name -->
          <div class="col-md-12">
            <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
              <input type="text" name="name" class="form-control" value="<?= e($item['name'] ?? '') ?>" required placeholder="mis. Siti Aminah, S.Kom.">
            </div>
          </div>

          <!-- Username & Email -->
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Username <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light">@</span>
              <input type="text" name="username" class="form-control" value="<?= e($item['username'] ?? '') ?>" required placeholder="username_login">
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label small fw-semibold">Alamat Email <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
              <input type="email" name="email" class="form-control" value="<?= e($item['email'] ?? '') ?>" required placeholder="email@smkcontoh.sch.id">
            </div>
          </div>

          <!-- Role Selection -->
          <div class="col-12">
            <label class="form-label small fw-semibold">Tingkat Hak Akses (Role) <span class="text-danger">*</span></label>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="form-check p-3 border rounded-3 bg-light">
                  <input class="form-check-input ms-0 me-2" type="radio" name="role" id="roleAdmin" value="admin" <?= ($item['role'] ?? 'admin') === 'admin' ? 'checked' : '' ?>>
                  <label class="form-check-label fw-bold text-dark" for="roleAdmin">
                    <i class="bi bi-shield-check text-primary me-1"></i> Admin Konten
                  </label>
                  <p class="text-muted small mb-0 mt-1 ps-4">Dapat mengelola berita, jurusan, guru, galeri, PPDB, ekstrakurikuler, dan pesan kontak.</p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check p-3 border rounded-3 bg-light">
                  <input class="form-check-input ms-0 me-2" type="radio" name="role" id="roleSuper" value="superadmin" <?= ($item['role'] ?? '') === 'superadmin' ? 'checked' : '' ?>>
                  <label class="form-check-label fw-bold text-dark" for="roleSuper">
                    <i class="bi bi-star-fill text-warning me-1"></i> Super Administrator
                  </label>
                  <p class="text-muted small mb-0 mt-1 ps-4">Akses penuh termasuk pengaturan profil sekolah, manajemen seluruh akun admin, dan backup database.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Password Fields -->
          <div class="col-md-6">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="form-label small fw-semibold mb-0">Password <?= $item ? '<span class="text-muted fw-normal">(opsional)</span>' : '<span class="text-danger">*</span>' ?></label>
              <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" id="generatePassBtn" style="font-size:0.75rem;"><i class="bi bi-dice-5 me-1"></i>Acak Password</button>
            </div>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
              <input type="password" name="password" id="userPass" class="form-control" <?= $item ? '' : 'required' ?> minlength="6" placeholder="<?= $item ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter' ?>">
              <button class="btn btn-outline-secondary toggle-pass-btn" type="button" data-target="userPass"><i class="bi bi-eye"></i></button>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label small fw-semibold">Konfirmasi Password <?= $item ? '<span class="text-muted fw-normal">(opsional)</span>' : '<span class="text-danger">*</span>' ?></label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
              <input type="password" name="confirm_password" id="userConfirmPass" class="form-control" <?= $item ? '' : 'required' ?> minlength="6" placeholder="Ulangi password di atas">
              <button class="btn btn-outline-secondary toggle-pass-btn" type="button" data-target="userConfirmPass"><i class="bi bi-eye"></i></button>
            </div>
          </div>

          <!-- Submit Buttons -->
          <div class="col-12 pt-3 border-top d-flex gap-2">
            <button type="submit" class="btn btn-accent px-4 fw-semibold">
              <i class="bi bi-check2-circle me-1"></i> <?= $id ? 'Simpan Perubahan' : 'Daftarkan Administrator' ?>
            </button>
            <a href="index.php" class="btn btn-outline-secondary px-4">Batal</a>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Toggle Password Visibility
document.querySelectorAll('.toggle-pass-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    const targetId = this.getAttribute('data-target');
    const input = document.getElementById(targetId);
    const icon = this.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
  });
});

// Random Password Generator
document.getElementById('generatePassBtn').addEventListener('click', function() {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%&*';
  let pass = '';
  for (let i = 0; i < 10; i++) {
    pass += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  const p1 = document.getElementById('userPass');
  const p2 = document.getElementById('userConfirmPass');
  p1.value = pass;
  p2.value = pass;
  p1.type = 'text';
  p2.type = 'text';
  document.querySelectorAll('.toggle-pass-btn i').forEach(icon => {
    icon.classList.replace('bi-eye', 'bi-eye-slash');
  });
  alert('Password acak dibuat: ' + pass + '\nSilakan catat password ini sebelum disimpan.');
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
