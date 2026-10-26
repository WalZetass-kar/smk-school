<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$me = $stmt->fetch();

if (!$me) {
    redirect(BASE_URL . '/admin/login.php');
}

$errors = [];
$passErrors = [];
$activeTab = $_POST['form_action'] ?? 'profile';

// Total admin count for quick badge info
$totalAdmins = (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $action = $_POST['form_action'] ?? 'profile';

        // 1. UPDATE PROFILE ACTION
        if ($action === 'profile') {
            $activeTab = 'profile';
            $name     = trim($_POST['name'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $photo    = $me['photo'] ?? null;

            if ($name === '') { $errors[] = 'Nama lengkap wajib diisi.'; }
            if ($username === '') { $errors[] = 'Username wajib diisi.'; }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Alamat email tidak valid.'; }

            if (empty($errors)) {
                $dupCheck = $db->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
                $dupCheck->execute([$username, $email, $me['id']]);
                if ($dupCheck->fetch()) {
                    $errors[] = 'Username atau email sudah digunakan oleh akun admin lain.';
                }
            }

            if (!empty($_FILES['photo']['name'])) {
                $uploadError = null;
                $path = handleImageUpload($_FILES['photo'], $uploadError, 'admins');
                if ($path) {
                    // Delete old custom photo if exists
                    if (!empty($me['photo']) && !str_starts_with($me['photo'], 'assets/images/')) {
                        @unlink(BASE_PATH . '/' . $me['photo']);
                    }
                    $photo = $path;
                } else {
                    $errors[] = 'Foto: ' . $uploadError;
                }
            }

            // Option to remove current photo
            if (isset($_POST['remove_photo']) && $_POST['remove_photo'] === '1') {
                if (!empty($me['photo']) && !str_starts_with($me['photo'], 'assets/images/')) {
                    @unlink(BASE_PATH . '/' . $me['photo']);
                }
                $photo = null;
            }

            if (empty($errors)) {
                $db->prepare("UPDATE users SET name=?, username=?, email=?, photo=? WHERE id=?")
                   ->execute([$name, $username, $email, $photo, $me['id']]);
                $_SESSION['admin_name'] = $name;
                logActivity('UPDATE_PROFILE', 'users', "Admin memperbarui data profil akun ({$username})");
                setFlash('success', 'Informasi profil admin berhasil diperbarui.');
                redirect(BASE_URL . '/admin/profile-admin.php');
            }
            $me = array_merge($me, compact('name', 'username', 'email', 'photo'));
        }

        // 2. CHANGE PASSWORD ACTION
        if ($action === 'password') {
            $activeTab = 'password';
            $oldPassword     = $_POST['old_password'] ?? '';
            $newPassword     = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $passRateKey = 'pass_change_' . md5((string)$me['id']);
            $passLockout = 0;

            if ($oldPassword === '') { $passErrors[] = 'Password saat ini wajib diisi.'; }
            if ($newPassword === '') { $passErrors[] = 'Password baru wajib diisi.'; }
            if (strlen($newPassword) < 8) { 
                $passErrors[] = 'Password baru minimal 8 karakter demi keamanan.'; 
            } elseif (!preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
                $passErrors[] = 'Password baru wajib mengombinasikan huruf dan angka.';
            }
            if ($newPassword !== $confirmPassword) { $passErrors[] = 'Konfirmasi password baru tidak cocok.'; }

            if (!checkRateLimit($passRateKey, 5, 600, $passLockout)) {
                $mins = ceil($passLockout / 60);
                $passErrors[] = "Terlalu banyak percobaan ganti password gagal. Silakan tunggu {$mins} menit.";
            }

            if (empty($passErrors)) {
                // Verify old password
                if (!password_verify($oldPassword, $me['password'])) {
                    recordRateLimitAttempt($passRateKey, 600);
                    logActivity('FAILED_PASSWORD_CHANGE', 'users', 'Percobaan ganti password gagal (password lama salah)');
                    $passErrors[] = 'Password saat ini salah. Silakan periksa kembali.';
                } else {
                    resetRateLimit($passRateKey);
                    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
                    $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$newHash, $me['id']]);
                    logActivity('CHANGE_PASSWORD', 'users', 'Admin berhasil mengganti password akun.');
                    setFlash('success', 'Password akun Anda berhasil diperbarui.');
                    redirect(BASE_URL . '/admin/profile-admin.php');
                }
            }
        }
    }
}

$pageTitle = 'Profil & Keamanan Akun';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
require __DIR__ . '/includes/topbar.php';
?>

<?= flashMessage() ?>

<div class="row g-4">
  <!-- Profile Summary Card (Left Column) -->
  <div class="col-lg-4">
    <div class="admin-card text-center p-4">
      <div class="position-relative d-inline-block mb-3">
        <?php if (!empty($me['photo']) && file_exists(BASE_PATH . '/' . $me['photo'])): ?>
          <img src="<?= e(BASE_URL . '/' . $me['photo']) ?>" alt="<?= e($me['name']) ?>" class="rounded-circle shadow-sm" style="width:120px;height:120px;object-fit:cover;border:4px solid #fff;">
        <?php else: ?>
          <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center shadow-sm" style="width:120px;height:120px;font-size:3.5rem;border:4px solid #fff;">
            <i class="bi bi-person-fill"></i>
          </div>
        <?php endif; ?>
        <span class="position-absolute bottom-0 end-0 badge rounded-pill <?= $me['role'] === 'superadmin' ? 'bg-warning text-dark' : 'bg-primary' ?> p-2 border border-2 border-white shadow-sm" title="<?= e(ucfirst($me['role'])) ?>">
          <i class="bi <?= $me['role'] === 'superadmin' ? 'bi-shield-fill-check' : 'bi-person-badge-fill' ?>"></i>
        </span>
      </div>

      <h2 class="h5 fw-bold text-primary mb-1"><?= e($me['name']) ?></h2>
      <p class="text-muted small mb-2">@<?= e($me['username']) ?></p>

      <div class="mb-3">
        <?php if ($me['role'] === 'superadmin'): ?>
          <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill fw-semibold">
            <i class="bi bi-star-fill text-warning me-1"></i> Super Administrator
          </span>
        <?php else: ?>
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold">
            <i class="bi bi-shield-check me-1"></i> Admin Konten
          </span>
        <?php endif; ?>
      </div>

      <div class="border-top pt-3 text-start small text-muted">
        <div class="d-flex justify-content-between mb-2">
          <span><i class="bi bi-envelope me-1"></i> Email:</span>
          <span class="fw-semibold text-dark"><?= e($me['email']) ?></span>
        </div>
        <div class="d-flex justify-content-between mb-2">
          <span><i class="bi bi-calendar3 me-1"></i> Terdaftar:</span>
          <span class="fw-semibold text-dark"><?= !empty($me['created_at']) ? date('d M Y', strtotime($me['created_at'])) : '-' ?></span>
        </div>
        <div class="d-flex justify-content-between">
          <span><i class="bi bi-shield-check me-1"></i> Status:</span>
          <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
        </div>
      </div>

      <?php if (isSuperAdmin()): ?>
      <div class="mt-4 pt-3 border-top">
        <div class="d-grid gap-2">
          <a href="<?= e(BASE_URL) ?>/admin/users/index.php" class="btn btn-sm btn-outline-primary-custom d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-people-fill"></i> Kelola Admin (<?= $totalAdmins ?> Akun)
          </a>
          <a href="<?= e(BASE_URL) ?>/admin/users/form.php" class="btn btn-sm btn-accent d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-person-plus-fill"></i> Tambah Admin Baru
          </a>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Form Edit Profile & Password (Right Column) -->
  <div class="col-lg-8">
    <div class="admin-card p-0 overflow-hidden">
      <!-- Nav Tabs -->
      <ul class="nav nav-tabs px-4 pt-3 bg-light border-bottom" id="profileTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link fw-semibold <?= $activeTab === 'profile' ? 'active' : '' ?>" id="edit-profile-tab" data-bs-toggle="tab" data-bs-target="#edit-profile" type="button" role="tab" aria-controls="edit-profile" aria-selected="<?= $activeTab === 'profile' ? 'true' : 'false' ?>">
            <i class="bi bi-person-gear me-1"></i> Ubah Biodata Profil
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link fw-semibold <?= $activeTab === 'password' ? 'active' : '' ?>" id="change-pass-tab" data-bs-toggle="tab" data-bs-target="#change-pass" type="button" role="tab" aria-controls="change-pass" aria-selected="<?= $activeTab === 'password' ? 'true' : 'false' ?>">
            <i class="bi bi-shield-lock me-1"></i> Ganti Password
          </button>
        </li>
      </ul>

      <div class="tab-content p-4" id="profileTabsContent">
        <!-- TAB 1: EDIT PROFILE -->
        <div class="tab-pane fade <?= $activeTab === 'profile' ? 'show active' : '' ?>" id="edit-profile" role="tabpanel" aria-labelledby="edit-profile-tab">
          <?php if ($errors && $activeTab === 'profile'): ?>
            <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
          <?php endif; ?>

          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="form_action" value="profile">

            <div class="row g-3">
              <div class="col-md-12">
                <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                  <input type="text" name="name" class="form-control" value="<?= e($me['name'] ?? '') ?>" required placeholder="Masukkan nama lengkap">
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold">Username <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text bg-light">@</span>
                  <input type="text" name="username" class="form-control" value="<?= e($me['username'] ?? '') ?>" required placeholder="username_admin">
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                  <input type="email" name="email" class="form-control" value="<?= e($me['email'] ?? '') ?>" required placeholder="admin@smkcontoh.sch.id">
                </div>
              </div>

              <div class="col-12">
                <label class="form-label small fw-semibold">Foto Profil</label>
                <div class="d-flex align-items-center gap-3">
                  <input type="file" name="photo" id="photoInput" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                  <?php if (!empty($me['photo'])): ?>
                    <div class="form-check text-nowrap">
                      <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="removePhotoCheck">
                      <label class="form-check-label small text-danger" for="removePhotoCheck">
                        Hapus Foto
                      </label>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="form-text small text-muted">Format: JPG, PNG, atau WEBP. Maksimal 2MB.</div>
              </div>

              <div class="col-12 pt-3 border-top">
                <button type="submit" class="btn btn-accent px-4 fw-semibold">
                  <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan Profil
                </button>
              </div>
            </div>
          </form>
        </div>

        <!-- TAB 2: CHANGE PASSWORD -->
        <div class="tab-pane fade <?= $activeTab === 'password' ? 'show active' : '' ?>" id="change-pass" role="tabpanel" aria-labelledby="change-pass-tab">
          <?php if ($passErrors && $activeTab === 'password'): ?>
            <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($passErrors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
          <?php endif; ?>

          <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="form_action" value="password">

            <div class="row g-3">
              <div class="col-12">
                <label class="form-label small fw-semibold">Password Saat Ini <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                  <input type="password" name="old_password" id="oldPass" class="form-control" required placeholder="Masukkan password saat ini">
                  <button class="btn btn-outline-secondary toggle-pass-btn" type="button" data-target="oldPass"><i class="bi bi-eye"></i></button>
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold">Password Baru <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                  <input type="password" name="new_password" id="newPass" class="form-control" required minlength="6" placeholder="Minimal 6 karakter">
                  <button class="btn btn-outline-secondary toggle-pass-btn" type="button" data-target="newPass"><i class="bi bi-eye"></i></button>
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
                  <input type="password" name="confirm_password" id="confirmPass" class="form-control" required minlength="6" placeholder="Ulangi password baru">
                  <button class="btn btn-outline-secondary toggle-pass-btn" type="button" data-target="confirmPass"><i class="bi bi-eye"></i></button>
                </div>
              </div>

              <div class="col-12 pt-3 border-top">
                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                  <i class="bi bi-shield-check me-1"></i> Perbarui Password
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
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
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
