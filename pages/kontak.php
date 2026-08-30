<?php
/** pages/kontak.php — /kontak */
$db      = getDB();
$profile = getSchoolProfile();
$socials = getSocialMedia();

$errors = [];
$old    = ['name' => '', 'email' => '', 'phone' => '', 'subject' => '', 'message' => ''];

// Generate a fresh simple math captcha for this page load if none pending.
if (empty($_SESSION['captcha_a'])) {
    $_SESSION['captcha_a'] = random_int(1, 9);
    $_SESSION['captcha_b'] = random_int(1, 9);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name']    = trim($_POST['name'] ?? '');
    $old['email']   = trim($_POST['email'] ?? '');
    $old['phone']   = trim($_POST['phone'] ?? '');
    $old['subject'] = trim($_POST['subject'] ?? '');
    $old['message'] = trim($_POST['message'] ?? '');
    $captchaAnswer  = trim($_POST['captcha_answer'] ?? '');

    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form sudah tidak valid, silakan coba lagi.';
    }
    if ($old['name'] === '') {
        $errors[] = 'Nama wajib diisi.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Alamat email tidak valid.';
    }
    if ($old['message'] === '') {
        $errors[] = 'Pesan wajib diisi.';
    }
    if ((int) $captchaAnswer !== (int) $_SESSION['captcha_a'] + (int) $_SESSION['captcha_b']) {
        $errors[] = 'Jawaban verifikasi salah, silakan coba lagi.';
    }

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO contacts (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$old['name'], $old['email'], $old['phone'], $old['subject'], $old['message']]);
        unset($_SESSION['captcha_a'], $_SESSION['captcha_b']);
        setFlash('success', 'Pesan Anda berhasil dikirim. Terima kasih telah menghubungi kami.');
        redirect(BASE_URL . '/kontak');
    }
    // On failure, regenerate the captcha numbers so the old answer can't be replayed.
    $_SESSION['captcha_a'] = random_int(1, 9);
    $_SESSION['captcha_b'] = random_int(1, 9);
}

$pageTitle       = 'Kontak';
$pageDescription = 'Hubungi ' . ($profile['school_name'] ?? 'kami') . ' — alamat, telepon, email, dan formulir kontak.';
$canonicalPath   = '/kontak';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<?= flashMessage() ?>

<section class="section pt-5">
  <div class="container">
    <div class="text-center mx-auto mb-5" style="max-width:640px;">
      <h1 class="section-title">Kontak Kami</h1>
      <p class="section-subtitle mx-auto">Silakan hubungi kami untuk informasi lebih lanjut atau kirim pesan melalui formulir di bawah.</p>
    </div>

    <div class="row g-5">
      <div class="col-lg-5">
        <div class="profil-fact"><i class="bi bi-geo-alt"></i><div><div class="profil-fact-label">Alamat</div><div class="profil-fact-value" style="font-weight:500;"><?= e($profile['address'] ?? '-') ?></div></div></div>
        <div class="profil-fact"><i class="bi bi-telephone"></i><div><div class="profil-fact-label">Telepon</div><div class="profil-fact-value"><?= e($profile['phone'] ?? '-') ?></div></div></div>
        <div class="profil-fact"><i class="bi bi-envelope"></i><div><div class="profil-fact-label">Email</div><div class="profil-fact-value"><?= e($profile['email'] ?? '-') ?></div></div></div>
        <div class="profil-fact"><i class="bi bi-clock"></i><div><div class="profil-fact-label">Jam Pelayanan</div><div class="profil-fact-value">Senin – Jumat, 07.00 – 15.00 WIB</div></div></div>

        <?php if (!empty($profile['whatsapp_tu']) || !empty($profile['whatsapp_ppdb']) || !empty($profile['whatsapp_bkk'])): ?>
        <div class="mt-4 p-3 rounded-3 bg-white border shadow-sm">
          <div class="fw-bold small text-primary mb-2"><i class="bi bi-whatsapp text-success me-1"></i>Layanan WhatsApp Resmi:</div>
          <div class="d-flex flex-column gap-2">
            <?php if (!empty($profile['whatsapp_tu'])): ?>
              <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $profile['whatsapp_tu'])) ?>" target="_blank" rel="noopener" class="text-decoration-none d-flex align-items-center justify-content-between p-2 rounded bg-light hover-lift">
                <span class="small fw-semibold text-dark"><i class="bi bi-building me-1 text-success"></i>Tata Usaha (TU)</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle small"><?= e($profile['whatsapp_tu']) ?></span>
              </a>
            <?php endif; ?>
            <?php if (!empty($profile['whatsapp_ppdb'])): ?>
              <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $profile['whatsapp_ppdb'])) ?>" target="_blank" rel="noopener" class="text-decoration-none d-flex align-items-center justify-content-between p-2 rounded bg-light hover-lift">
                <span class="small fw-semibold text-dark"><i class="bi bi-mortarboard me-1 text-primary"></i>Panitia PPDB</span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle small"><?= e($profile['whatsapp_ppdb']) ?></span>
              </a>
            <?php endif; ?>
            <?php if (!empty($profile['whatsapp_bkk'])): ?>
              <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $profile['whatsapp_bkk'])) ?>" target="_blank" rel="noopener" class="text-decoration-none d-flex align-items-center justify-content-between p-2 rounded bg-light hover-lift">
                <span class="small fw-semibold text-dark"><i class="bi bi-briefcase me-1 text-warning"></i>Bursa Kerja (BKK)</span>
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle small"><?= e($profile['whatsapp_bkk']) ?></span>
              </a>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>

        <div class="d-flex gap-2 mt-4">
          <?php foreach ($socials as $s): ?>
            <a class="social-icon-circle" style="background:var(--color-bg); color:var(--color-primary);" href="<?= e($s['url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($s['platform']) ?>">
              <i class="bi <?= e($s['icon']) ?>"></i>
            </a>
          <?php endforeach; ?>
        </div>

        <?php if (!empty($profile['map_url'])): ?>
        <div class="ratio ratio-4x3 rounded overflow-hidden mt-4">
          <iframe src="<?= e($profile['map_url']) ?>" style="border:0;" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Lokasi Sekolah"></iframe>
        </div>
        <?php endif; ?>
      </div>

      <div class="col-lg-7">
        <div class="jurusan-card p-4 p-md-5">
          <?php if ($errors): ?>
            <div class="alert alert-danger">
              <ul class="mb-0 ps-3">
                <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form method="post" action="<?= e(BASE_URL) ?>/kontak" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Nama</label>
                <input type="text" name="name" class="form-control" value="<?= e($old['name']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" value="<?= e($old['email']) ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Nomor HP</label>
                <input type="text" name="phone" class="form-control" value="<?= e($old['phone']) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Subjek</label>
                <input type="text" name="subject" class="form-control" value="<?= e($old['subject']) ?>">
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Pesan</label>
                <textarea name="message" rows="5" class="form-control" required><?= e($old['message']) ?></textarea>
              </div>
              <div class="col-12">
                <label class="form-label small fw-semibold">Verifikasi: berapa <?= (int) $_SESSION['captcha_a'] ?> + <?= (int) $_SESSION['captcha_b'] ?>?</label>
                <input type="text" name="captcha_answer" class="form-control" style="max-width:140px;" required>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-accent px-4">Kirim Pesan</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
