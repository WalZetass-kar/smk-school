<?php
require_once __DIR__ . '/../config/config.php';

// Already logged in? Skip straight to the dashboard.
if (!empty($_SESSION['admin_id'])) {
    redirect(BASE_URL . '/admin/dashboard.php');
}

$profile = getSchoolProfile();
$schoolName = $profile['school_name'] ?? 'SMK Negeri Contoh Indonesia';
$errors = [];
$remainingLockout = 0;
$ip = getClientIP();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $rateKey  = 'login_admin_' . md5($ip . '_' . $username);
    $ipRateKey = 'login_ip_' . md5($ip);
    $ipLockout = 0;

    if (!checkRateLimit($ipRateKey, 15, 600, $ipLockout)) {
        $mins = ceil($ipLockout / 60);
        $errors[] = "Terlalu banyak percobaan login dari jaringan/IP Anda. Silakan tunggu {$mins} menit.";
    } elseif (!checkRateLimit($rateKey, 5, 600, $remainingLockout)) {
        $mins = ceil($remainingLockout / 60);
        $errors[] = "Terlalu banyak percobaan login gagal untuk akun ini. Silakan tunggu {$mins} menit ({$remainingLockout} detik).";
    } elseif (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form sudah tidak valid atau telah kedaluwarsa. Silakan refresh halaman.';
    } elseif ($username === '' || $password === '') {
        $errors[] = 'Username dan password wajib diisi.';
    } else {
        $stmt = getDB()->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            recordRateLimitAttempt($rateKey, 600);
            recordRateLimitAttempt($ipRateKey, 600);
            logActivity('LOGIN_FAILED', 'auth', 'Percobaan login gagal untuk username: ' . substr($username, 0, 50));
            $errors[] = 'Username atau password yang Anda masukkan salah.';
        } else {
            // Reset rate limit on successful authentication
            resetRateLimit($rateKey);
            resetRateLimit($ipRateKey);

            // Prevent session fixation: get a fresh session ID
            session_regenerate_id(true);
            $_SESSION['admin_id']       = $user['id'];
            $_SESSION['admin_name']     = $user['name'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_role']     = $user['role'];
            $_SESSION['last_activity']  = time();

            logActivity('LOGIN_SUCCESS', 'auth', 'Admin ' . $user['name'] . ' (' . $user['role'] . ') berhasil masuk ke sistem.');
            redirect(BASE_URL . '/admin/dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Login Administrator — <?= e($schoolName) ?></title>

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<!-- Local Bootstrap 5.3 + Bootstrap Icons -->
<link href="<?= e(ASSETS_URL . '/vendor/bootstrap/bootstrap.min.css?v=5.3.3') ?>" rel="stylesheet">
<link href="<?= e(ASSETS_URL . '/vendor/bootstrap-icons/bootstrap-icons.css?v=' . filemtime(BASE_PATH . '/assets/vendor/bootstrap-icons/bootstrap-icons.css')) ?>" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" onerror="this.onerror=null;">

<!-- Base Styles -->
<link href="<?= e(ASSETS_URL . '/css/style.css?v=' . filemtime(BASE_PATH . '/assets/css/style.css')) ?>" rel="stylesheet">
<link href="<?= e(ASSETS_URL . '/css/admin.css?v=' . filemtime(BASE_PATH . '/assets/css/admin.css')) ?>" rel="stylesheet">

<!-- Dynamic Theme Colors Overrides -->
<?= renderDynamicThemeStyles($profile) ?>
</head>
<body class="admin-body">

<div class="login-wrapper">
  <div class="login-container">
    <div class="login-card-master">
      <div class="row g-0">
        
        <!-- Left Side: School Branding & Security Assurances -->
        <div class="col-lg-5 login-brand-side">
          <div>
            <div class="login-brand-badge mb-4">
              <i class="bi bi-shield-lock-fill text-warning"></i>
              <span>PORTAL ADMINISTRATOR</span>
            </div>

            <div class="d-flex align-items-center gap-3 mb-4">
              <?php if (!empty($profile['logo']) && file_exists(BASE_PATH . '/' . $profile['logo'])): ?>
                <img src="<?= e(BASE_URL . '/' . $profile['logo']) ?>" alt="Logo <?= e($schoolName) ?>" class="bg-white p-2 rounded-3 shadow-sm" style="height: 60px; width: auto; max-width: 60px; object-fit: contain;">
              <?php else: ?>
                <div class="bg-white bg-opacity-10 p-2.5 rounded-3 d-inline-flex align-items-center justify-content-center text-warning" style="width: 54px; height: 54px;">
                  <i class="bi bi-building fs-3"></i>
                </div>
              <?php endif; ?>
              <div>
                <h2 class="h5 fw-bold text-white mb-0" style="line-height: 1.25;"><?= e($schoolName) ?></h2>
                <div class="small text-white-50 mt-1">
                  <?php if (!empty($profile['npsn'])): ?>NPSN: <?= e($profile['npsn']) ?> &middot; <?php endif; ?>
                  Akreditasi <?= e($profile['accreditation'] ?? 'A') ?>
                </div>
              </div>
            </div>

            <p class="text-white text-opacity-80 small mb-4" style="line-height: 1.65;">
              Sistem Manajemen Portal Sekolah Terpadu. Kelola penerimaan siswa (PPDB), kelulusan (SKL), publikasi berita, statistik, dan kurikulum akademik dalam satu panel terintegrasi.
            </p>

            <div class="pt-2 border-top border-white border-opacity-10">
              <div class="login-security-pill">
                <i class="bi bi-check-circle-fill"></i>
                <span>Enkripsi Password Bcrypt Standar Industri</span>
              </div>
              <div class="login-security-pill">
                <i class="bi bi-shield-check"></i>
                <span>Proteksi Anti Brute-Force &amp; Rate-Limiting</span>
              </div>
              <div class="login-security-pill mb-0">
                <i class="bi bi-lock-fill"></i>
                <span>Validasi Token Sesi CSRF Berlapis</span>
              </div>
            </div>
          </div>

          <div class="mt-4 pt-3 border-top border-white border-opacity-10 small text-white-50">
            &copy; <?= date('Y') ?> <?= e($schoolName) ?>. Hak Cipta Dilindungi.
          </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="col-lg-7 login-form-side">
          <div class="mb-4">
            <h1 class="h4 fw-bold text-dark mb-1">Masuk ke Panel Kontrol</h1>
            <p class="text-muted small mb-0">Masukkan username dan kata sandi akun administrator Anda untuk melanjutkan.</p>
          </div>

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger border-0 rounded-3 py-2.5 px-3 mb-3 d-flex align-items-start gap-2 shadow-xs">
              <i class="bi bi-exclamation-triangle-fill text-danger fs-5 flex-shrink-0 mt-0.5"></i>
              <div class="small">
                <?php foreach ($errors as $err): ?>
                  <div><?= e($err) ?></div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <form method="post" action="<?= e(BASE_URL) ?>/admin/login.php" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

            <!-- Username Field -->
            <div class="mb-3">
              <label for="usernameInput" class="form-label small fw-semibold text-dark">Username Akun</label>
              <div class="input-group login-input-group">
                <span class="input-group-text"><i class="bi bi-person fs-5"></i></span>
                <input type="text" name="username" id="usernameInput" class="form-control" placeholder="Ketik username admin..." value="<?= e($username ?? '') ?>" required autofocus autocomplete="username">
              </div>
            </div>

            <!-- Password Field with Visibility Toggle -->
            <div class="mb-3">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <label for="passwordInput" class="form-label small fw-semibold text-dark mb-0">Kata Sandi</label>
                <a href="javascript:void(0)" class="small text-muted text-decoration-none" onclick="showHelpModal()" style="font-size: 11.5px;">
                  <i class="bi bi-question-circle me-1"></i>Lupa Sandi?
                </a>
              </div>
              <div class="input-group login-input-group">
                <span class="input-group-text"><i class="bi bi-key fs-5"></i></span>
                <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Ketik kata sandi..." required autocomplete="current-password">
                <button type="button" class="btn btn-light border-0 px-3 text-muted shadow-none" id="togglePasswordBtn" title="Tampilkan / Sembunyikan Password" tabindex="-1">
                  <i class="bi bi-eye" id="togglePasswordIcon"></i>
                </button>
              </div>
            </div>

            <!-- Remember Me Switch -->
            <div class="d-flex align-items-center justify-content-between mb-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="rememberMeCheck">
                <label class="form-check-label small text-muted user-select-none" for="rememberMeCheck">
                  Ingat username di browser ini
                </label>
              </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-accent w-100 py-2.5 fw-bold rounded-3 shadow-xs d-flex align-items-center justify-content-center gap-2" id="loginSubmitBtn">
              <span id="loginBtnText">Masuk ke Dashboard</span>
              <i class="bi bi-box-arrow-in-right fs-5" id="loginBtnIcon"></i>
              <span class="spinner-border spinner-border-sm d-none" id="loginSpinner" role="status" aria-hidden="true"></span>
            </button>
          </form>

          <!-- Back to Website Link -->
          <div class="text-center mt-4 pt-3 border-top">
            <a href="<?= e(BASE_URL) ?>/" class="text-muted text-decoration-none small d-inline-flex align-items-center gap-1.5 fw-semibold hover-primary">
              <i class="bi bi-arrow-left"></i>
              <span>Kembali ke Beranda Sekolah</span>
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Modal Bantuan Lupa Password -->
<div class="modal fade" id="helpModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
    <div class="modal-content border-0 shadow-lg rounded-4 p-3 text-center">
      <div class="modal-body p-4">
        <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
          <i class="bi bi-shield-exclamation fs-2"></i>
        </div>
        <h5 class="fw-bold mb-2">Bantuan Pemulihan Akun</h5>
        <p class="small text-muted mb-4" style="line-height: 1.6;">
          Demi alasan keamanan sistem, reset kata sandi administrator dilakukan langsung oleh <strong>Super Administrator</strong> atau tim IT Sekolah melalui menu Manajemen Akun.
        </p>
        <div class="p-3 bg-light rounded-3 text-start small border mb-3">
          <div class="fw-bold text-dark mb-1"><i class="bi bi-info-circle me-1 text-primary"></i> Langkah Reset:</div>
          <ol class="mb-0 ps-3 text-muted">
            <li>Hubungi Superadmin sekolah Anda.</li>
            <li>Minta pembuatan kata sandi baru melalui menu Manajemen Admin.</li>
            <li>Segera ubah kata sandi sementara setelah berhasil masuk.</li>
          </ol>
        </div>
        <button type="button" class="btn btn-primary w-100 fw-semibold rounded-3" data-bs-dismiss="modal">
          Mengerti
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Local Bootstrap JS -->
<script src="<?= e(ASSETS_URL . '/vendor/bootstrap/bootstrap.bundle.min.js?v=5.3.3') ?>"></script>

<script>
// Toggle Password Visibility
const toggleBtn = document.getElementById('togglePasswordBtn');
const passwordInput = document.getElementById('passwordInput');
const toggleIcon = document.getElementById('togglePasswordIcon');

if (toggleBtn && passwordInput && toggleIcon) {
  toggleBtn.addEventListener('click', function() {
    const isPassword = passwordInput.getAttribute('type') === 'password';
    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
    toggleIcon.className = isPassword ? 'bi bi-eye-slash text-primary' : 'bi bi-eye';
  });
}

// Remember Username via localStorage
const usernameInput = document.getElementById('usernameInput');
const rememberCheck = document.getElementById('rememberMeCheck');

if (usernameInput && rememberCheck) {
  const savedUser = localStorage.getItem('smk_admin_remembered_username');
  if (savedUser && !usernameInput.value) {
    usernameInput.value = savedUser;
    rememberCheck.checked = true;
    if (passwordInput) passwordInput.focus();
  }

  document.getElementById('loginForm').addEventListener('submit', function() {
    if (rememberCheck.checked) {
      localStorage.setItem('smk_admin_remembered_username', usernameInput.value.trim());
    } else {
      localStorage.removeItem('smk_admin_remembered_username');
    }

    // Button Spinner Feedback
    const submitBtn = document.getElementById('loginSubmitBtn');
    const btnText = document.getElementById('loginBtnText');
    const btnIcon = document.getElementById('loginBtnIcon');
    const spinner = document.getElementById('loginSpinner');
    if (submitBtn && spinner) {
      submitBtn.disabled = true;
      btnText.textContent = 'Memverifikasi...';
      btnIcon.classList.add('d-none');
      spinner.classList.remove('d-none');
    }
  });
}

// Help Modal Trigger
function showHelpModal() {
  const modalEl = document.getElementById('helpModal');
  if (modalEl && window.bootstrap) {
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
  }
}
</script>

</body>
</html>
