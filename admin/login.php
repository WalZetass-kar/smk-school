<?php
require_once __DIR__ . '/../config/config.php';

// Already logged in? Skip straight to the dashboard.
if (!empty($_SESSION['admin_id'])) {
    redirect(BASE_URL . '/admin/dashboard.php');
}

$errors = [];
$remainingLockout = 0;
$ip = getClientIP();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $rateKey  = 'login_admin_' . md5($ip . '_' . $username);

    if (!checkRateLimit($rateKey, 5, 600, $remainingLockout)) {
        $mins = ceil($remainingLockout / 60);
        $errors[] = "Terlalu banyak percobaan login gagal. Akun/IP terkunci sementara demi keamanan. Silakan tunggu {$mins} menit ({$remainingLockout} detik).";
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
            logActivity('LOGIN_FAILED', 'auth', 'Percobaan login gagal untuk username: ' . substr($username, 0, 50));
            $errors[] = 'Username atau password salah.';
        } else {
            // Reset rate limit on successful authentication
            resetRateLimit($rateKey);

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
<title>Login Administrator — <?= e(getSchoolProfile()['school_name'] ?? 'SMK') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="<?= e(ASSETS_URL . '/vendor/bootstrap/bootstrap.min.css?v=5.3.3') ?>" rel="stylesheet">
<link href="<?= e(ASSETS_URL . '/vendor/bootstrap-icons/bootstrap-icons.css?v=' . filemtime(BASE_PATH . '/assets/vendor/bootstrap-icons/bootstrap-icons.css')) ?>" rel="stylesheet">
<link href="<?= e(ASSETS_URL . '/css/style.css') ?>" rel="stylesheet">
<link href="<?= e(ASSETS_URL . '/css/admin.css') ?>" rel="stylesheet">
</head>
<body>
<div class="login-wrapper">
  <div class="login-card">
    <div class="text-center mb-4">
      <i class="bi bi-mortarboard-fill" style="font-size:2.2rem; color: var(--color-accent);"></i>
      <h1 class="h5 text-primary mt-2 mb-0">Login Administrator</h1>
      <p class="text-muted small"><?= e(getSchoolProfile()['school_name'] ?? '') ?></p>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-danger py-2 small">
        <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= e(BASE_URL) ?>/admin/login.php">
      <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
      <div class="mb-3">
        <label class="form-label small fw-semibold">Username</label>
        <input type="text" name="username" class="form-control" required autofocus>
      </div>
      <div class="mb-4">
        <label class="form-label small fw-semibold">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-accent w-100">Masuk</button>
    </form>
    <p class="text-center small text-muted mt-4 mb-0"><a href="<?= e(BASE_URL) ?>/">&larr; Kembali ke Website</a></p>
  </div>
</div>
</body>
</html>
