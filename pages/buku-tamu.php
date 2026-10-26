<?php
/**
 * pages/buku-tamu.php
 * Buku Tamu Digital Sekolah
 */
require_once __DIR__ . '/../config/config.php';
$db = getDB();

$errors = [];
$old = [
    'name'           => '',
    'institution'    => '',
    'phone'          => '',
    'email'          => '',
    'purpose'        => 'Dinas / Instansi',
    'person_to_meet' => 'Kepala Sekolah / Tata Usaha',
    'notes'          => '',
];

if (empty($_SESSION['guest_captcha_a']) || empty($_SESSION['guest_captcha_b'])) {
    $_SESSION['guest_captcha_a'] = rand(1, 9);
    $_SESSION['guest_captcha_b'] = rand(1, 9);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = getClientIP();
    $rateKey = 'guestbook_submit_' . md5($ip);
    $remainingLockout = 0;

    // Honeypot check: automated spam bots fill invisible fields
    if (!empty($_POST['guest_website_hp'])) {
        setFlash('success', 'Terima kasih, data kunjungan Anda telah berhasil disimpan.');
        redirect(BASE_URL . '/buku-tamu');
    }

    if (!checkRateLimit($rateKey, 5, 600, $remainingLockout)) {
        $mins = ceil($remainingLockout / 60);
        $errors[] = "Terlalu banyak pengisian buku tamu dari perangkat Anda. Silakan tunggu {$mins} menit sebelum mencoba lagi.";
    } elseif (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Sesi form kadaluarsa. Silakan muat ulang halaman.';
    }

    $old = [
        'name'           => trim($_POST['name'] ?? ''),
        'institution'    => trim($_POST['institution'] ?? ''),
        'phone'          => trim($_POST['phone'] ?? ''),
        'email'          => trim($_POST['email'] ?? ''),
        'purpose'        => trim($_POST['purpose'] ?? 'Umum'),
        'person_to_meet' => trim($_POST['person_to_meet'] ?? ''),
        'notes'          => trim($_POST['notes'] ?? ''),
    ];

    $captcha = (int)($_POST['captcha_answer'] ?? 0);
    $expected = (int)$_SESSION['guest_captcha_a'] + (int)$_SESSION['guest_captcha_b'];

    if ($old['name'] === '') $errors[] = 'Nama lengkap wajib diisi.';
    if ($old['phone'] === '') $errors[] = 'Nomor WhatsApp / HP wajib diisi.';
    if ($old['person_to_meet'] === '') $errors[] = 'Pihak yang ingin ditemui wajib diisi.';
    if ($captcha !== $expected) $errors[] = 'Jawaban verifikasi angka tidak tepat.';

    if (empty($errors)) {
        $stmt = $db->prepare("
            INSERT INTO guestbooks (name, institution, phone, email, purpose, person_to_meet, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $old['name'],
            $old['institution'],
            $old['phone'],
            $old['email'],
            $old['purpose'],
            $old['person_to_meet'],
            $old['notes'],
        ]);

        recordRateLimitAttempt($rateKey, 600);

        $_SESSION['guest_captcha_a'] = rand(1, 9);
        $_SESSION['guest_captcha_b'] = rand(1, 9);

        setFlash('success', 'Terima kasih, data kunjungan Anda telah berhasil dicatat dalam Buku Tamu Digital.');
        redirect(BASE_URL . '/buku-tamu');
    }
}

$pageTitle = 'Buku Tamu Digital Sekolah';
require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<section class="section py-5">
  <div class="container">
    <div class="text-center mx-auto mb-5" style="max-width:760px;">
      <h1 class="section-title mb-2">Buku Tamu Digital</h1>
      <p class="text-muted">Selamat datang di <?= e($profile['school_name'] ?? 'SMK Negeri Contoh') ?>. Silakan mengisi formulir kehadiran kunjungan di bawah ini.</p>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-8">
        <?= flashMessage() ?>

        <?php if ($errors): ?>
          <div class="alert alert-danger rounded-3 shadow-sm mb-4">
            <ul class="mb-0 ps-3">
              <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-5">
          <form method="post" action="<?= e(BASE_URL) ?>/buku-tamu" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <!-- Honeypot anti-spam trap (hidden from real users) -->
            <div style="display:none!important;visibility:hidden;position:absolute;left:-9999px;" aria-hidden="true">
              <input type="text" name="guest_website_hp" tabindex="-1" autocomplete="off">
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Nama Lengkap Tamu <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?= e($old['name']) ?>" placeholder="Contoh: Drs. Bambang Sutrisno" required>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold">Instansi / Lembaga / Perusahaan</label>
                <input type="text" name="institution" class="form-control" value="<?= e($old['institution']) ?>" placeholder="Contoh: Dinas Pendidikan / PT Maju Jaya">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold">No. HP / WhatsApp <span class="text-danger">*</span></label>
                <input type="text" name="phone" class="form-control" value="<?= e($old['phone']) ?>" placeholder="08123456789" required>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold">Alamat Email</label>
                <input type="email" name="email" class="form-control" value="<?= e($old['email']) ?>" placeholder="email@instansi.com">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold">Kategori Keperluan <span class="text-danger">*</span></label>
                <select name="purpose" class="form-select" required>
                  <option value="Dinas / Instansi" <?= $old['purpose'] === 'Dinas / Instansi' ? 'selected' : '' ?>>Dinas / Instansi Pemerintah</option>
                  <option value="Kemitraan Industri" <?= $old['purpose'] === 'Kemitraan Industri' ? 'selected' : '' ?>>Kemitraan Industri / DUDI</option>
                  <option value="Orang Tua / Wali Siswa" <?= $old['purpose'] === 'Orang Tua / Wali Siswa' ? 'selected' : '' ?>>Orang Tua / Wali Siswa</option>
                  <option value="Alumni" <?= $old['purpose'] === 'Alumni' ? 'selected' : '' ?>>Alumni</option>
                  <option value="Umum" <?= $old['purpose'] === 'Umum' ? 'selected' : '' ?>>Kunjungan Umum / Lainnya</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold">Pihak yang Dituju / Ditemui <span class="text-danger">*</span></label>
                <input type="text" name="person_to_meet" class="form-control" value="<?= e($old['person_to_meet']) ?>" placeholder="Contoh: Kepala Sekolah / Bagian TU" required>
              </div>

              <div class="col-12">
                <label class="form-label small fw-semibold">Maksud &amp; Tujuan Kunjungan</label>
                <textarea name="notes" rows="4" class="form-control" placeholder="Tuliskan keterangan singkat maksud kedatangan Anda..."><?= e($old['notes']) ?></textarea>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold">Verifikasi: berapa <?= (int)$_SESSION['guest_captcha_a'] ?> + <?= (int)$_SESSION['guest_captcha_b'] ?>?</label>
                <input type="text" name="captcha_answer" class="form-control" style="max-width:140px;" placeholder="Jawaban..." required>
              </div>

              <div class="col-12 pt-3 border-top">
                <button type="submit" class="btn btn-accent px-4 py-2 fw-semibold">
                  <i class="bi bi-send me-1"></i>Simpan Buku Tamu
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
