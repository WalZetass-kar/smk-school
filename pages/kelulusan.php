<?php
/**
 * pages/kelulusan.php
 * Pengumuman Kelulusan Siswa Online & Validasi SKL
 */
require_once __DIR__ . '/../config/config.php';
$db = getDB();

if (!isModuleActive('kelulusan')) {
    setFlash('info', 'Portal Pengumuman Kelulusan (SKL) saat ini sedang dinonaktifkan.');
    redirect(BASE_URL . '/');
}

$settings = $db->query("SELECT * FROM graduation_settings WHERE id = 1")->fetch() ?: [
    'academic_year'     => '2026/2027',
    'announcement_date' => date('Y-m-d H:i:s'),
    'is_active'         => 1,
    'header_info'       => 'Pengumuman Resmi Kelulusan Siswa.',
    'signature_city'    => 'Pekanbaru',
    'signature_date'    => date('Y-m-d'),
];

$announcementTimestamp = !empty($settings['announcement_date']) ? strtotime($settings['announcement_date']) : time();
$now = time();
$isOpen = ($now >= $announcementTimestamp) && !empty($settings['is_active']);

$student = null;
$searched = false;
$searchError = null;

if ($isOpen && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = getClientIP();
    $rateKey = 'kelulusan_search_' . md5($ip);
    $remainingLockout = 0;

    if (!checkRateLimit($rateKey, 20, 60, $remainingLockout)) {
        $searchError = "Terlalu banyak permintaan pencarian. Mohon tunggu {$remainingLockout} detik.";
        $searched = true;
    } else {
        recordRateLimitAttempt($rateKey, 60);
        $searchKey = trim($_POST['search_key'] ?? '');
        $searched = true;
        if ($searchKey === '') {
            $searchError = 'Silakan masukkan NISN atau Nomor Peserta Ujian.';
        } else {
            $stmt = $db->prepare("SELECT * FROM graduations WHERE nisn = ? OR exam_number = ? LIMIT 1");
            $stmt->execute([$searchKey, $searchKey]);
            $student = $stmt->fetch();
            if (!$student) {
                $searchError = 'Data siswa dengan NISN / Nomor Ujian "' . htmlspecialchars($searchKey) . '" tidak ditemukan. Mohon periksa kembali nomor yang Anda masukkan.';
            }
        }
    }
}

$profile = getSchoolProfile();
$pageTitle = 'Pengumuman Kelulusan Siswa Online Tahun Ajaran ' . $settings['academic_year'];
require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<section class="section py-4 py-md-5">
  <div class="container">
    <?= flashMessage() ?>
    <div class="text-center mx-auto mb-4 mb-md-5" style="max-width:720px;">
      <span class="section-badge mb-2">
        <i class="bi bi-mortarboard-fill"></i> SKL Online
      </span>
      <h1 class="h2 fw-bold text-primary mb-2">Pengumuman Kelulusan Siswa</h1>
      <p class="text-muted small mb-3">
        <?= e($profile['school_name'] ?? 'SMK Negeri Contoh') ?> &bull; Tahun Ajaran <?= e($settings['academic_year']) ?>
      </p>
      <?php if (!empty($settings['header_info'])): ?>
        <div class="alert alert-info py-2 px-3 small border-0 bg-primary-subtle text-primary fw-medium rounded-pill d-inline-block">
          <i class="bi bi-info-circle me-1"></i><?= e($settings['header_info']) ?>
        </div>
      <?php endif; ?>
    </div>

    <?php if (!$isOpen): ?>
      <!-- COUNTDOWN TIMER -->
      <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
          <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-light">
            <div class="mb-3">
              <i class="bi bi-clock-history text-primary fs-1"></i>
            </div>
            <h2 class="h4 fw-bold text-dark mb-2">Pengumuman Belum Dibuka</h2>
            <p class="text-muted mb-4">
              Pengumuman kelulusan akan dibuka secara serentak pada tanggal:<br>
              <strong class="text-primary fs-5"><?= date('d F Y, H:i', $announcementTimestamp) ?> WIB</strong>
            </p>

            <div class="d-flex justify-content-center gap-3 gap-md-4 mb-4 flex-wrap" id="countdownWrapper" data-target="<?= date('c', $announcementTimestamp) ?>">
              <div class="bg-white p-3 rounded-3 shadow-sm text-center" style="min-width:80px;">
                <span class="d-block display-6 fw-bold text-primary" id="cdDays">00</span>
                <span class="small text-muted text-uppercase fw-semibold">Hari</span>
              </div>
              <div class="bg-white p-3 rounded-3 shadow-sm text-center" style="min-width:80px;">
                <span class="d-block display-6 fw-bold text-primary" id="cdHours">00</span>
                <span class="small text-muted text-uppercase fw-semibold">Jam</span>
              </div>
              <div class="bg-white p-3 rounded-3 shadow-sm text-center" style="min-width:80px;">
                <span class="d-block display-6 fw-bold text-primary" id="cdMinutes">00</span>
                <span class="small text-muted text-uppercase fw-semibold">Menit</span>
              </div>
              <div class="bg-white p-3 rounded-3 shadow-sm text-center" style="min-width:80px;">
                <span class="d-block display-6 fw-bold text-primary" id="cdSeconds">00</span>
                <span class="small text-muted text-uppercase fw-semibold">Detik</span>
              </div>
            </div>

            <p class="small text-muted mb-0">Halaman ini akan otomatis diperbarui saat hitung mundur selesai.</p>
          </div>
        </div>
      </div>

      <script>
        document.addEventListener('DOMContentLoaded', () => {
          const wrapper = document.getElementById('countdownWrapper');
          if (!wrapper) return;
          const targetTime = new Date(wrapper.dataset.target).getTime();

          function updateCountdown() {
            const now = new Date().getTime();
            const diff = targetTime - now;

            if (diff <= 0) {
              window.location.reload();
              return;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            document.getElementById('cdDays').textContent = String(days).padStart(2, '0');
            document.getElementById('cdHours').textContent = String(hours).padStart(2, '0');
            document.getElementById('cdMinutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('cdSeconds').textContent = String(seconds).padStart(2, '0');
          }

          updateCountdown();
          setInterval(updateCountdown, 1000);
        });
      </script>

    <?php else: ?>
      <!-- PENCARIAN SISWA -->
      <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8 mx-auto">
          <div class="card border-0 shadow-sm rounded-4 p-3 p-sm-4 bg-white mb-4">
            <form method="post" action="<?= e(BASE_URL) ?>/kelulusan" class="mb-0">
              <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
              <label for="search_key" class="form-label fw-bold text-dark mb-2 small text-uppercase" style="letter-spacing: 0.5px;">
                <i class="bi bi-search me-1 text-primary"></i>Cek Status Kelulusan Anda
              </label>
              <div class="d-flex flex-column flex-sm-row gap-2">
                <div class="position-relative flex-grow-1">
                  <i class="bi bi-person-vcard position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-5"></i>
                  <input type="text" id="search_key" name="search_key" class="form-control ps-5 py-2.5 rounded-3 border fs-6" placeholder="Ketik NISN atau No. Ujian..." value="<?= e($_POST['search_key'] ?? '') ?>" required autofocus>
                </div>
                <button type="submit" class="btn btn-accent px-4 py-2.5 fw-bold rounded-3 d-flex align-items-center justify-content-center gap-2 flex-shrink-0 text-nowrap">
                  <i class="bi bi-search"></i><span>Periksa</span>
                </button>
              </div>
              <div class="form-text small text-muted mt-2">
                Contoh: NISN <code>0011223344</code> atau No. Ujian <code>UJN-2026-001</code>
              </div>
            </form>
          </div>

          <?php if ($searched && $searchError): ?>
            <div class="alert alert-danger rounded-3 shadow-sm border-0 d-flex align-items-center gap-3 p-3 mb-4">
              <i class="bi bi-exclamation-triangle-fill fs-3 text-danger flex-shrink-0"></i>
              <div><?= e($searchError) ?></div>
            </div>
          <?php endif; ?>

          <?php if ($student): ?>
            <?php
              $st = $student['status'] ?? 'ditunda';
              $statusConfig = match($st) {
                'lulus' => [
                  'color'     => '#15803D',
                  'badgeBg'   => 'bg-success text-white',
                  'badgeIcon' => 'bi-check-circle-fill',
                  'badgeText' => 'LULUS',
                  'subTitle'  => 'Selamat atas kelulusan yang telah diraih!'
                ],
                'ditunda' => [
                  'color'     => '#B45309',
                  'badgeBg'   => 'bg-warning text-dark',
                  'badgeIcon' => 'bi-hourglass-split',
                  'badgeText' => 'STATUS DITUNDA',
                  'subTitle'  => 'Status kelulusan memerlukan verifikasi persyaratan tertentu.'
                ],
                default => [
                  'color'     => '#DC2626',
                  'badgeBg'   => 'bg-danger text-white',
                  'badgeIcon' => 'bi-x-circle-fill',
                  'badgeText' => 'TIDAK LULUS',
                  'subTitle'  => 'Belum memenuhi standar kriteria kelulusan tahun ini.'
                ],
              };

              $contactWa = !empty($profile['whatsapp_tu']) 
                  ? $profile['whatsapp_tu'] 
                  : (!empty($profile['whatsapp_ppdb']) ? $profile['whatsapp_ppdb'] : '');
              $waTextDitunda = "Halo Tim Layanan Sekolah " . ($profile['school_name'] ?? '') . ",\n\n"
                  . "Saya ingin berkonsultasi mengenai status kelulusan saya yang saat ini berstatus *DITUNDA*:\n"
                  . "• Nama Lengkap: " . $student['student_name'] . "\n"
                  . "• NISN: " . $student['nisn'] . "\n"
                  . "• No. Peserta Ujian: " . $student['exam_number'] . "\n"
                  . "• Jurusan: " . $student['major_name'] . "\n\n"
                  . "Mohon arahan mengenai persyaratan administrasi atau berkas yang perlu saya lengkapi. Terima kasih.";

              $waTextTidakLulus = "Halo Tim Bimbingan & Layanan Sekolah " . ($profile['school_name'] ?? '') . ",\n\n"
                  . "Saya ingin berkonsultasi mengenai hasil pengumuman kelulusan saya:\n"
                  . "• Nama Lengkap: " . $student['student_name'] . "\n"
                  . "• NISN: " . $student['nisn'] . "\n"
                  . "• No. Peserta Ujian: " . $student['exam_number'] . "\n"
                  . "• Jurusan: " . $student['major_name'] . "\n\n"
                  . "Saya ingin mendapatkan informasi mengenai jadwal bimbingan tatap muka atau opsi solusi pendidikan lanjutan. Terima kasih.";
            ?>

            <!-- HASIL KELULUSAN -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
              <!-- Header Card Dinamis dengan Warna Status -->
              <div class="card-header p-3.5 p-md-4 text-white border-0" style="background-color: <?= $statusConfig['color'] ?>;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2.5">
                  <span class="badge bg-white bg-opacity-20 text-white px-2.5 py-1 rounded-pill small fw-semibold">
                    <i class="bi bi-mortarboard me-1"></i>TA <?= e($settings['academic_year']) ?>
                  </span>
                  <span class="badge <?= $statusConfig['badgeBg'] ?> px-3 py-1.5 rounded-pill fw-bold shadow-sm" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                    <i class="bi <?= $statusConfig['badgeIcon'] ?> me-1"></i><?= $statusConfig['badgeText'] ?>
                  </span>
                </div>
                <h2 class="h5 fw-bold text-white mb-2"><?= e($student['student_name']) ?></h2>
                <div class="d-flex flex-wrap gap-1.5">
                  <span class="badge bg-black bg-opacity-20 text-white fw-normal px-2.5 py-1 rounded-2">
                    NISN: <strong class="fw-bold text-white"><?= e($student['nisn']) ?></strong>
                  </span>
                  <span class="badge bg-black bg-opacity-20 text-white fw-normal px-2.5 py-1 rounded-2">
                    No. Ujian: <strong class="fw-bold text-white"><?= e($student['exam_number']) ?></strong>
                  </span>
                </div>
              </div>

              <!-- Card Body: Grid Informasi Siswa Responsive -->
              <div class="card-body p-3 p-md-4 bg-white">
                <!-- Info Siswa Box -->
                <div class="bg-light rounded-3 border p-3 mb-4">
                  <div class="row g-3">
                    <div class="col-12 col-md-6">
                      <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-white border p-2 text-primary shadow-xs d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                          <i class="bi bi-mortarboard fs-6"></i>
                        </div>
                        <div>
                          <div class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Program Keahlian / Jurusan</div>
                          <div class="fw-bold text-dark" style="font-size: 0.95rem;"><?= e($student['major_name']) ?></div>
                        </div>
                      </div>
                    </div>

                    <div class="col-6 col-md-3">
                      <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-white border p-2 text-primary shadow-xs d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                          <i class="bi bi-award fs-6"></i>
                        </div>
                        <div>
                          <div class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Nilai Rata-rata</div>
                          <div class="fw-bold text-dark fs-6"><?= e(number_format((float)$student['average_score'], 2)) ?></div>
                        </div>
                      </div>
                    </div>

                    <div class="col-6 col-md-3">
                      <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle bg-white border p-2 text-primary shadow-xs d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                          <i class="bi bi-calendar-event fs-6"></i>
                        </div>
                        <div>
                          <div class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Tanggal Lahir</div>
                          <div class="fw-bold text-dark small">
                            <?= !empty($student['birth_date']) ? date('d/m/Y', strtotime($student['birth_date'])) : '-' ?>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="col-12 border-top pt-2">
                      <div class="small text-muted d-flex align-items-center gap-1.5 flex-wrap">
                        <i class="bi bi-geo-alt text-secondary"></i>
                        <span>Tempat Lahir: <strong class="text-dark"><?= e($student['birth_place'] ?? '-') ?></strong></span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Catatan Umum -->
                <div class="p-3 rounded-3 bg-light border mb-4">
                  <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                    <i class="bi bi-info-circle me-1 text-primary"></i>Keterangan Kelulusan
                  </div>
                  <div class="small fw-medium <?= $st === 'ditunda' ? 'text-warning-emphasis' : ($st === 'tidak_lulus' ? 'text-danger' : 'text-dark') ?>" style="line-height: 1.5;">
                    <?php if (!empty($student['remarks'])): ?>
                      <?= nl2br(e($student['remarks'])) ?>
                    <?php elseif ($st === 'lulus'): ?>
                      Selamat atas kelulusan yang telah diraih. Tetap berprestasi dan jaga nama baik almamater.
                    <?php elseif ($st === 'ditunda'): ?>
                      Status kelulusan Anda saat ini sedang ditunda. Silakan ikuti petunjuk verifikasi di bawah ini.
                    <?php else: ?>
                      Belum memenuhi kriteria kelulusan tahun ajaran ini. Silakan pelajari opsi bimbingan dan solusi di bawah ini.
                    <?php endif; ?>
                  </div>
                </div>

                <?php if ($st === 'lulus'): ?>
                  <!-- KELULUSAN SUKSES: CETAK SKL -->
                  <div class="p-3 p-md-3.5 rounded-3 bg-success-subtle border border-success-subtle d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                    <div>
                      <div class="fw-bold text-success-emphasis d-flex align-items-center gap-1.5">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <span>Surat Keterangan Lulus (SKL) Resmi</span>
                      </div>
                      <div class="small text-muted mt-0.5">Dokumen SKL dilengkapi kop resmi, pengesahan, dan barcode verifikasi.</div>
                    </div>
                    <a href="<?= e(BASE_URL) ?>/kelulusan/cetak?nisn=<?= urlencode($student['nisn']) ?>" target="_blank" class="btn btn-success px-4 py-2.5 fw-bold w-100 w-sm-auto text-nowrap d-flex align-items-center justify-content-center gap-2 shadow-xs">
                      <i class="bi bi-printer"></i><span>Cetak SKL</span>
                    </a>
                  </div>

                <?php elseif ($st === 'ditunda'): ?>
                  <!-- STATUS DITUNDA: PETUNJUK, SARAN & VERIFIKASI -->
                  <div class="rounded-3 border border-warning-subtle p-3 p-md-3.5 mb-2" style="background-color: #fffdf2;">
                    <div class="d-flex align-items-center gap-2.5 mb-3">
                      <div class="rounded-circle bg-warning bg-opacity-20 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                        <i class="bi bi-hourglass-split fs-5 text-warning-emphasis"></i>
                      </div>
                      <div>
                        <h3 class="h6 fw-bold text-dark mb-0">Petunjuk &amp; Langkah Verifikasi</h3>
                        <div class="text-muted small" style="font-size: 0.78rem;">Lengkapi persyaratan di bawah ini agar SKL dapat diproses:</div>
                      </div>
                    </div>

                    <?php if (!empty($student['remarks'])): ?>
                      <div class="alert alert-warning border border-warning-subtle rounded-3 p-2.5 px-3 mb-3 d-flex align-items-start gap-2 shadow-xs">
                        <i class="bi bi-megaphone-fill text-warning-emphasis fs-6 flex-shrink-0 mt-0.5"></i>
                        <div class="small flex-grow-1">
                          <strong class="text-warning-emphasis d-block mb-0.5">Catatan Khusus dari Sekolah:</strong>
                          <span class="text-dark"><?= nl2br(e($student['remarks'])) ?></span>
                        </div>
                      </div>
                    <?php endif; ?>

                    <div class="d-flex flex-column gap-2 mb-3">
                      <div class="p-2.5 px-3 rounded-3 bg-white border d-flex align-items-start gap-2.5 shadow-xs">
                        <span class="badge bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 22px; height: 22px; font-size: 0.75rem;">1</span>
                        <div class="flex-grow-1">
                          <div class="fw-bold text-dark small mb-0.5">Bebas Administrasi &amp; Perpustakaan</div>
                          <div class="text-muted small" style="font-size: 0.8rem; line-height: 1.4;">
                            Pastikan buku perpustakaan atau perlengkapan lab telah dikembalikan dan memperoleh lembar bebas tanggungan resmi dari petugas sekolah.
                          </div>
                        </div>
                      </div>

                      <div class="p-2.5 px-3 rounded-3 bg-white border d-flex align-items-start gap-2.5 shadow-xs">
                        <span class="badge bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 22px; height: 22px; font-size: 0.75rem;">2</span>
                        <div class="flex-grow-1">
                          <div class="fw-bold text-dark small mb-0.5">Kelengkapan Tugas &amp; Nilai Praktik</div>
                          <div class="text-muted small" style="font-size: 0.8rem; line-height: 1.4;">
                            Periksa apakah ada nilai tugas mata pelajaran, portofolio kejuruan, ujian susulan, atau laporan PKL yang masih tertunda.
                          </div>
                        </div>
                      </div>

                      <div class="p-2.5 px-3 rounded-3 bg-white border d-flex align-items-start gap-2.5 shadow-xs">
                        <span class="badge bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 22px; height: 22px; font-size: 0.75rem;">3</span>
                        <div class="flex-grow-1">
                          <div class="fw-bold text-dark small mb-0.5">Konfirmasi Langsung ke Wali Kelas / Tata Usaha</div>
                          <div class="text-muted small" style="font-size: 0.8rem; line-height: 1.4;">
                            Segera temui Wali Kelas atau hadir langsung ke Ruang Tata Usaha / Kurikulum pada jam operasional sekolah untuk validasi kelulusan akhir.
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Action CTA Buttons (Clean, Mobile Full-Width, No Overflow) -->
                    <div class="pt-2">
                      <div class="d-flex flex-column gap-2">
                        <?php if (!empty($contactWa)): ?>
                          <a href="https://wa.me/<?= e(formatWhatsAppNumber($contactWa)) ?>?text=<?= rawurlencode($waTextDitunda) ?>" target="_blank" class="btn btn-success fw-bold py-2.5 px-3 rounded-3 shadow-xs d-flex align-items-center justify-content-center gap-2 w-100">
                            <i class="bi bi-whatsapp fs-5"></i><span>Chat Layanan Tata Usaha</span>
                          </a>
                        <?php endif; ?>
                        <?php if (!empty($profile['phone'])): ?>
                          <a href="tel:<?= e(preg_replace('/[^0-9\+]/', '', $profile['phone'])) ?>" class="btn btn-outline-secondary fw-semibold py-2 px-3 rounded-3 d-flex align-items-center justify-content-center gap-2 w-100">
                            <i class="bi bi-telephone-fill"></i><span>Hubungi Kantor Sekolah</span>
                          </a>
                        <?php endif; ?>
                      </div>
                      <div class="text-center text-muted small mt-2" style="font-size: 0.75rem;">
                        <i class="bi bi-clock me-1"></i>Pelayanan verifikasi berkas: Senin - Jumat (08.00 - 15.00 WIB)
                      </div>
                    </div>
                  </div>

                <?php elseif ($st === 'tidak_lulus'): ?>
                  <!-- STATUS TIDAK LULUS: PANDUAN, SARAN & SOLUSI EDUKATIF -->
                  <div class="rounded-3 border border-danger-subtle p-3 p-md-3.5 mb-2" style="background-color: #fff9f9;">
                    <div class="d-flex align-items-center gap-2.5 mb-3">
                      <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                        <i class="bi bi-heart-pulse-fill fs-5 text-danger"></i>
                      </div>
                      <div>
                        <h3 class="h6 fw-bold text-dark mb-0">Saran, Dukungan &amp; Opsi Solusi</h3>
                        <div class="text-muted small" style="font-size: 0.78rem;">Jangan berkecil hati, sekolah siap mendampingi langkah terbaik Anda:</div>
                      </div>
                    </div>

                    <?php if (!empty($student['remarks'])): ?>
                      <div class="alert alert-danger border border-danger-subtle rounded-3 p-2.5 px-3 mb-3 d-flex align-items-start gap-2 shadow-xs">
                        <i class="bi bi-chat-left-text-fill text-danger fs-6 flex-shrink-0 mt-0.5"></i>
                        <div class="small flex-grow-1">
                          <strong class="text-danger d-block mb-0.5">Catatan Dewan Guru:</strong>
                          <span class="text-dark"><?= nl2br(e($student['remarks'])) ?></span>
                        </div>
                      </div>
                    <?php endif; ?>

                    <div class="d-flex flex-column gap-2 mb-3">
                      <div class="p-2.5 px-3 rounded-3 bg-white border d-flex align-items-start gap-2.5 shadow-xs">
                        <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 22px; height: 22px; font-size: 0.75rem;">
                          <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="flex-grow-1">
                          <div class="fw-bold text-dark small mb-0.5">Konsultasi Terbuka Guru BK &amp; Wali Kelas</div>
                          <div class="text-muted small" style="font-size: 0.8rem; line-height: 1.4;">
                            Sekolah memfasilitasi pendampingan konseling bagi siswa dan orang tua untuk mendiskusikan capaian belajar secara terbuka dan kekeluargaan.
                          </div>
                        </div>
                      </div>

                      <div class="p-2.5 px-3 rounded-3 bg-white border d-flex align-items-start gap-2.5 shadow-xs">
                        <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 22px; height: 22px; font-size: 0.75rem;">
                          <i class="bi bi-arrow-repeat"></i>
                        </div>
                        <div class="flex-grow-1">
                          <div class="fw-bold text-dark small mb-0.5">Peluang Program Remedial &amp; Pengulangan</div>
                          <div class="text-muted small" style="font-size: 0.8rem; line-height: 1.4;">
                            Pelajari peluang remedial penuntasan kompetensi atau kesempatan mengulang pembelajaran pada tahun ajaran berikutnya dengan persiapan matang.
                          </div>
                        </div>
                      </div>

                      <div class="p-2.5 px-3 rounded-3 bg-white border d-flex align-items-start gap-2.5 shadow-xs">
                        <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 22px; height: 22px; font-size: 0.75rem;">
                          <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <div class="flex-grow-1">
                          <div class="fw-bold text-dark small mb-0.5">Alternatif Jalur Pendidikan Kesetaraan</div>
                          <div class="text-muted small" style="font-size: 0.8rem; line-height: 1.4;">
                            Tersedia alternatif jalur resmi pendidikan kesetaraan (Paket C) sesuai ketentuan dan arahan Dinas Pendidikan setempat.
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Action CTA Buttons (Clean, Mobile Full-Width, No Overflow) -->
                    <div class="pt-2">
                      <div class="d-flex flex-column gap-2">
                        <?php if (!empty($contactWa)): ?>
                          <a href="https://wa.me/<?= e(formatWhatsAppNumber($contactWa)) ?>?text=<?= rawurlencode($waTextTidakLulus) ?>" target="_blank" class="btn btn-danger fw-bold py-2.5 px-3 rounded-3 shadow-xs d-flex align-items-center justify-content-center gap-2 w-100">
                            <i class="bi bi-whatsapp fs-5"></i><span>Konsultasi dengan Guru BK</span>
                          </a>
                        <?php endif; ?>
                        <?php if (!empty($profile['phone'])): ?>
                          <a href="tel:<?= e(preg_replace('/[^0-9\+]/', '', $profile['phone'])) ?>" class="btn btn-outline-secondary fw-semibold py-2 px-3 rounded-3 d-flex align-items-center justify-content-center gap-2 w-100">
                            <i class="bi bi-telephone-fill"></i><span>Hubungi Kantor Sekolah</span>
                          </a>
                        <?php endif; ?>
                      </div>
                      <div class="text-center text-muted small mt-2" style="font-size: 0.75rem;">
                        <i class="bi bi-chat-heart me-1"></i>Pendampingan tatap muka dilayani di Ruang Bimbingan Konseling (BK)
                      </div>
                    </div>
                  </div>

                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
