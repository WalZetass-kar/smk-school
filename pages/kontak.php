<?php
/**
 * pages/kontak.php — /kontak
 * Halaman Kontak Resmi Sekolah & Formulir Pesan
 */
$db      = getDB();
$profile = getSchoolProfile();
$socials = getSocialMedia();

$errors = [];
$old    = ['name' => '', 'email' => '', 'phone' => '', 'subject' => '', 'message' => ''];

// Generate a fresh simple math captcha for this page load if none pending.
if (empty($_SESSION['captcha_a']) || empty($_SESSION['captcha_b'])) {
    $_SESSION['captcha_a'] = random_int(2, 9);
    $_SESSION['captcha_b'] = random_int(1, 9);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = getClientIP();
    $rateKey = 'contact_submit_' . md5($ip);
    $remainingLockout = 0;

    $old['name']    = trim($_POST['name'] ?? '');
    $old['email']   = trim($_POST['email'] ?? '');
    $old['phone']   = trim($_POST['phone'] ?? '');
    $old['subject'] = trim($_POST['subject'] ?? '');
    $old['message'] = trim($_POST['message'] ?? '');
    $captchaAnswer  = trim($_POST['captcha_answer'] ?? '');

    if (!checkRateLimit($rateKey, 5, 600, $remainingLockout)) {
        $mins = ceil($remainingLockout / 60);
        $errors[] = "Terlalu banyak pengiriman pesan. Silakan tunggu {$mins} menit sebelum mengirim pesan baru.";
    } elseif (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form sudah kadaluarsa atau tidak valid, silakan coba lagi.';
    }
    if ($old['name'] === '') {
        $errors[] = 'Nama lengkap wajib diisi.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format alamat email tidak valid.';
    }
    if ($old['message'] === '') {
        $errors[] = 'Isi pesan wajib diisi.';
    }
    if ((int) $captchaAnswer !== (int) $_SESSION['captcha_a'] + (int) $_SESSION['captcha_b']) {
        $errors[] = 'Jawaban verifikasi keamanan (captcha) salah. Silakan hitung kembali.';
    }

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO contacts (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$old['name'], $old['email'], $old['phone'], $old['subject'], $old['message']]);
        
        recordRateLimitAttempt($rateKey, 600);

        // Reset captcha
        $_SESSION['captcha_a'] = random_int(2, 9);
        $_SESSION['captcha_b'] = random_int(1, 9);
        
        setFlash('success', 'Pesan Anda berhasil dikirim! Tim kami akan segera menanggapi melalui email atau nomor telepon yang Anda cantumkan.');
        redirect(BASE_URL . '/kontak');
    }
    
    // On failure, regenerate captcha
    $_SESSION['captcha_a'] = random_int(2, 9);
    $_SESSION['captcha_b'] = random_int(1, 9);
}

$pageTitle       = 'Kontak & Informasi';
$pageDescription = 'Hubungi ' . e($profile['school_name'] ?? 'kami') . ' — alamat, layanan WhatsApp, email, peta lokasi, dan formulir kontak online.';
$canonicalPath   = '/kontak';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<?= flashMessage() ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small mb-0">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
    <li class="breadcrumb-item active" aria-current="page">Kontak Kami</li>
  </ol>
</nav>

<!-- Hero Section -->
<section class="section pt-3 pb-4">
  <div class="container">
    <div class="text-center mx-auto mb-5 reveal-up" style="max-width:720px;">
      <h1 class="section-title mb-2">Hubungi Kami</h1>
      <p class="section-subtitle mx-auto">Kami siap melayani kebutuhan informasi seputar pendaftaran PPDB, administrasi akademik, kerja sama industri (BKK), dan layanan sekolah lainnya.</p>
    </div>

    <!-- 3 Quick Contact Cards -->
    <div class="row g-3 g-md-4 mb-5 reveal-group">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white hover-lift text-center">
          <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width:60px; height:60px;">
            <i class="bi bi-geo-alt-fill fs-3"></i>
          </div>
          <h2 class="h6 fw-bold text-primary mb-1">Alamat Kampus</h2>
          <p class="small text-muted mb-0"><?= e($profile['address'] ?? 'Alamat sekolah belum diatur') ?></p>
          <?php if (!empty($profile['npsn'])): ?>
            <span class="badge bg-light text-dark border mt-2 small">NPSN: <?= e($profile['npsn']) ?></span>
          <?php endif; ?>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white hover-lift text-center">
          <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width:60px; height:60px;">
            <i class="bi bi-telephone-inbound-fill fs-3"></i>
          </div>
          <h2 class="h6 fw-bold text-primary mb-1">Kontak Resmi</h2>
          <p class="small text-muted mb-1">
            <i class="bi bi-telephone me-1 text-primary"></i> <?= e($profile['phone'] ?? '-') ?>
          </p>
          <p class="small text-muted mb-0">
            <i class="bi bi-envelope me-1 text-primary"></i> <?= e($profile['email'] ?? '-') ?>
          </p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white hover-lift text-center">
          <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width:60px; height:60px;">
            <i class="bi bi-clock-history fs-3"></i>
          </div>
          <h2 class="h6 fw-bold text-primary mb-1">Jam Pelayanan Kantor</h2>
          <p class="small text-muted mb-1"><strong>Senin – Kamis:</strong> 07.30 – 15.30 WIB</p>
          <p class="small text-muted mb-0"><strong>Jumat:</strong> 07.30 – 15.00 WIB</p>
        </div>
      </div>
    </div>

    <!-- Two-Column Main Contact Info & Form -->
    <div class="row g-4 g-lg-5">
      <!-- Left Column: WhatsApp Channels, Social Media & Map -->
      <div class="col-lg-5 reveal-left">
        <!-- WhatsApp Direct Channels -->
        <?php if (!empty($profile['whatsapp_tu']) || !empty($profile['whatsapp_ppdb']) || !empty($profile['whatsapp_bkk'])): ?>
          <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <h2 class="h6 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
              <i class="bi bi-whatsapp text-success fs-5"></i> Layanan WhatsApp Cepat
            </h2>
            <div class="d-flex flex-column gap-2.5">
              <?php if (!empty($profile['whatsapp_tu'])): ?>
                <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $profile['whatsapp_tu'])) ?>?text=<?= urlencode('Halo Tata Usaha ' . ($profile['school_name'] ?? 'SMK') . ', saya ingin bertanya mengenai administrasi:') ?>" target="_blank" rel="noopener" class="text-decoration-none d-flex align-items-center justify-content-between p-3 rounded-3 bg-light hover-lift border">
                  <div>
                    <div class="small fw-bold text-dark"><i class="bi bi-building me-1 text-success"></i>Tata Usaha &amp; Administrasi</div>
                    <div class="small text-muted" style="font-size:11.5px;">Surat keterangan, legalisir, surat aktif</div>
                  </div>
                  <span class="badge bg-success text-white px-2.5 py-1.5 rounded-pill small d-flex align-items-center gap-1">
                    <i class="bi bi-chat-dots-fill"></i> Chat
                  </span>
                </a>
              <?php endif; ?>

              <?php if (!empty($profile['whatsapp_ppdb'])): ?>
                <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $profile['whatsapp_ppdb'])) ?>?text=<?= urlencode('Halo Panitia PPDB ' . ($profile['school_name'] ?? 'SMK') . ', saya ingin bertanya mengenai pendaftaran:') ?>" target="_blank" rel="noopener" class="text-decoration-none d-flex align-items-center justify-content-between p-3 rounded-3 bg-light hover-lift border">
                  <div>
                    <div class="small fw-bold text-dark"><i class="bi bi-mortarboard me-1 text-primary"></i>Panitia PPDB Online</div>
                    <div class="small text-muted" style="font-size:11.5px;">Informasi syarat, biaya &amp; jalur pendaftaran</div>
                  </div>
                  <span class="badge bg-primary text-white px-2.5 py-1.5 rounded-pill small d-flex align-items-center gap-1">
                    <i class="bi bi-chat-dots-fill"></i> Chat
                  </span>
                </a>
              <?php endif; ?>

              <?php if (!empty($profile['whatsapp_bkk'])): ?>
                <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $profile['whatsapp_bkk'])) ?>?text=<?= urlencode('Halo BKK ' . ($profile['school_name'] ?? 'SMK') . ', saya ingin bertanya mengenai lowongan/kerja sama:') ?>" target="_blank" rel="noopener" class="text-decoration-none d-flex align-items-center justify-content-between p-3 rounded-3 bg-light hover-lift border">
                  <div>
                    <div class="small fw-bold text-dark"><i class="bi bi-briefcase me-1 text-warning"></i>Bursa Kerja (BKK) &amp; Magang</div>
                    <div class="small text-muted" style="font-size:11.5px;">Kerja sama industri &amp; lowongan alumni</div>
                  </div>
                  <span class="badge bg-warning text-dark px-2.5 py-1.5 rounded-pill small fw-semibold d-flex align-items-center gap-1">
                    <i class="bi bi-chat-dots-fill"></i> Chat
                  </span>
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Social Media Links -->
        <?php if (!empty($socials)): ?>
          <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <h2 class="h6 fw-bold text-primary mb-3">Media Sosial Resmi</h2>
            <div class="d-flex flex-wrap gap-2">
              <?php foreach ($socials as $s): ?>
                <a class="btn btn-sm btn-light border d-inline-flex align-items-center gap-2 hover-lift" href="<?= e($s['url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($s['platform']) ?>">
                  <i class="bi <?= e($s['icon']) ?> text-primary"></i>
                  <span class="small fw-medium"><?= e($s['platform']) ?></span>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Google Maps Embed -->
        <?php if (!empty($profile['map_url'])): ?>
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white p-3">
            <div class="d-flex justify-content-between align-items-center mb-2 px-1">
              <h2 class="h6 fw-bold text-primary mb-0"><i class="bi bi-map me-1 text-danger"></i>Lokasi di Peta</h2>
              <a href="<?= e($profile['map_url']) ?>" target="_blank" rel="noopener" class="small text-decoration-none text-primary fw-medium">
                Buka Peta Penuh <i class="bi bi-box-arrow-up-right ms-1"></i>
              </a>
            </div>
            <div class="ratio ratio-16x9 rounded-3 overflow-hidden border">
              <iframe src="<?= e($profile['map_url']) ?>" style="border:0;" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Peta Lokasi <?= e($profile['school_name'] ?? 'Sekolah') ?>"></iframe>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Right Column: Contact Message Form -->
      <div class="col-lg-7 reveal-right">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
          <div class="card-header bg-primary text-white py-3 px-4 d-flex align-items-center justify-content-between">
            <h2 class="h6 mb-0 text-white d-flex align-items-center gap-2">
              <i class="bi bi-envelope-paper-fill text-warning"></i> Kirim Pesan Langsung
            </h2>
            <span class="badge bg-white bg-opacity-25 text-white small">Online Form</span>
          </div>

          <div class="card-body p-3 p-sm-4 p-md-5">
            <?php if ($errors): ?>
              <div class="alert alert-danger rounded-3 mb-4">
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-octagon-fill me-1"></i>Mohon periksa kembali:</div>
                <ul class="mb-0 ps-3 small">
                  <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <form method="post" action="<?= e(BASE_URL) ?>/kontak" novalidate>
              <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
              
              <div class="row g-3">
                <!-- Name -->
                <div class="col-md-6">
                  <label class="form-label small fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-person text-muted"></i></span>
                    <input type="text" name="name" class="form-control" value="<?= e($old['name']) ?>" required placeholder="Nama Anda">
                  </div>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                  <label class="form-label small fw-semibold">Alamat Email Aktif <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" name="email" class="form-control" value="<?= e($old['email']) ?>" required placeholder="nama@email.com">
                  </div>
                </div>

                <!-- Phone -->
                <div class="col-md-6">
                  <label class="form-label small fw-semibold">Nomor WhatsApp / HP</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-phone text-muted"></i></span>
                    <input type="tel" name="phone" class="form-control" value="<?= e($old['phone']) ?>" placeholder="08xxxxxxxxxx">
                  </div>
                </div>

                <!-- Subject -->
                <div class="col-md-6">
                  <label class="form-label small fw-semibold">Subjek / Keperluan Pesan</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-chat-left-text text-muted"></i></span>
                    <input type="text" name="subject" class="form-control" value="<?= e($old['subject']) ?>" placeholder="Contoh: Pertanyaan PPDB, Legalisir">
                  </div>
                </div>

                <!-- Message -->
                <div class="col-12">
                  <label class="form-label small fw-semibold">Isi Pesan / Pertanyaan <span class="text-danger">*</span></label>
                  <textarea name="message" rows="5" class="form-control" required placeholder="Tuliskan pertanyaan atau pesan Anda secara lengkap di sini..."><?= e($old['message']) ?></textarea>
                </div>

                <!-- Math Captcha Anti-Spam -->
                <div class="col-12">
                  <div class="p-3 bg-light border rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                      <label class="form-label small fw-semibold mb-1 d-block">
                        <i class="bi bi-shield-check text-success me-1"></i>Verifikasi Keamanan (Anti-Spam):
                      </label>
                      <span class="small text-muted">Berapa hasil dari penjumlahan: </span>
                      <span class="badge bg-warning text-dark fs-6 fw-bold px-2 py-1">
                        <?= (int) $_SESSION['captcha_a'] ?> + <?= (int) $_SESSION['captcha_b'] ?> = ?
                      </span>
                    </div>
                    <div style="width: 130px;">
                      <input type="number" name="captcha_answer" class="form-control text-center fw-bold" placeholder="Hasil..." required>
                    </div>
                  </div>
                </div>

                <!-- Submit Button -->
                <div class="col-12 pt-2">
                  <button type="submit" class="btn btn-accent btn-lg w-100 py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-send-fill"></i> Kirim Pesan Sekarang
                  </button>
                  <div class="form-text small text-center text-muted mt-2">
                    <i class="bi bi-lock-fill me-1"></i>Data dan pesan Anda dijamin kerahasiaannya oleh pihak sekolah.
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- FAQ Accordion (Tanya Jawab Umum) -->
    <div class="mt-5 pt-4 reveal-up">
      <div class="text-center mx-auto mb-4" style="max-width: 600px;">
        <h2 class="h4 fw-bold text-primary mb-1">Pertanyaan yang Sering Diajukan (FAQ)</h2>
        <p class="text-muted small">Temukan jawaban cepat atas pertanyaan seputar layanan administrasi dan akademik sekolah.</p>
      </div>

      <div class="accordion accordion-flush bg-white rounded-4 shadow-sm p-3 p-md-4 border" id="faqAccordion">
        <div class="accordion-item border-bottom">
          <h2 class="accordion-header" id="faqHeading1">
            <button class="accordion-button fw-bold text-primary collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1" aria-expanded="false" aria-controls="faqCollapse1">
              Bagaimana cara mendaftar sebagai calon peserta didik baru (PPDB Online)?
            </button>
          </h2>
          <div id="faqCollapse1" class="accordion-collapse collapse" aria-labelledby="faqHeading1" data-bs-parent="#faqAccordion">
            <div class="accordion-body text-muted small lh-lg">
              Anda dapat mendaftar secara mandiri melalui menu <a href="<?= e(BASE_URL) ?>/ppdb/daftar" class="fw-semibold text-primary">PPDB Online &gt; Daftar Sekarang</a>. Isi formulir pendaftaran, unggah berkas persyaratan (Kartu Keluarga, Pas Foto, Rapor), lalu simpan dan cetak bukti pendaftaran Anda.
            </div>
          </div>
        </div>

        <div class="accordion-item border-bottom">
          <h2 class="accordion-header" id="faqHeading2">
            <button class="accordion-button fw-bold text-primary collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
              Berapa lama proses legalisir ijazah atau pembuatan surat keterangan di Tata Usaha?
            </button>
          </h2>
          <div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#faqAccordion">
            <div class="accordion-body text-muted small lh-lg">
              Pelayanan legalisir dan pembuatan surat keterangan aktif siswa dapat diproses pada hari kerja (Senin – Jumat) dalam waktu 1 – 2 hari kerja setelah berkas asli diverifikasi oleh petugas Tata Usaha.
            </div>
          </div>
        </div>

        <div class="accordion-item">
          <h2 class="accordion-header" id="faqHeading3">
            <button class="accordion-button fw-bold text-primary collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
              Bagaimana prosedur kerja sama industri untuk magang siswa (PKL) atau perekrutan kerja BKK?
            </button>
          </h2>
          <div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#faqAccordion">
            <div class="accordion-body text-muted small lh-lg">
              Perusahaan atau instansi mitra dapat menghubungi langsung unit Bursa Kerja Khusus (BKK) melalui saluran WhatsApp BKK atau mengirimkan proposal kerja sama ke email resmi sekolah: <a href="mailto:<?= e($profile['email'] ?? '') ?>" class="fw-semibold text-primary"><?= e($profile['email'] ?? '-') ?></a>.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
