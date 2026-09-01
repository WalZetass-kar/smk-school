<?php
/**
 * pages/kelulusan.php
 * Pengumuman Kelulusan Siswa Online & Validasi SKL
 */
require_once __DIR__ . '/../config/config.php';
$db = getDB();

$settings = $db->query("SELECT * FROM graduation_settings WHERE id = 1")->fetch() ?: [
    'academic_year'     => '2026/2027',
    'announcement_date' => date('Y-m-d H:i:s'),
    'is_active'         => 1,
    'header_info'       => 'Pengumuman Resmi Kelulusan Siswa.',
    'signature_city'    => 'Pekanbaru',
    'signature_date'    => date('Y-m-d'),
];

$announcementTimestamp = strtotime($settings['announcement_date']);
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

$pageTitle = 'Pengumuman Kelulusan Siswa Online Tahun Ajaran ' . $settings['academic_year'];
require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<section class="section py-5">
  <div class="container">
    <div class="text-center mx-auto mb-5" style="max-width:760px;">
      <h1 class="section-title mb-2">Pengumuman Kelulusan Online</h1>
      <p class="text-muted">
        Tahun Ajaran <?= e($settings['academic_year']) ?> — <?= e($profile['school_name'] ?? 'SMK Negeri Contoh') ?>
      </p>
      <?php if (!empty($settings['header_info'])): ?>
        <div class="alert alert-info py-2 px-3 small d-inline-block border-0 bg-primary-subtle text-primary fw-medium">
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
        <div class="col-lg-7">
          <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
            <form method="post" action="<?= e(BASE_URL) ?>/kelulusan" class="mb-0">
              <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
              <label class="form-label fw-bold text-dark mb-2">
                <i class="bi bi-search me-1 text-primary"></i>Cek Status Kelulusan Anda
              </label>
              <div class="input-group input-group-lg shadow-none">
                <input type="text" name="search_key" class="form-control" placeholder="Masukkan NISN atau No. Ujian..." value="<?= e($_POST['search_key'] ?? '') ?>" required autofocus>
                <button type="submit" class="btn btn-accent px-4 fw-semibold">
                  <i class="bi bi-search me-1"></i>Periksa
                </button>
              </div>
              <div class="form-text small text-muted mt-2">
                Contoh: ketik NISN <code>0051234567</code> atau No. Ujian <code>UJN-2026-001</code>
              </div>
            </form>
          </div>

          <?php if ($searched && $searchError): ?>
            <div class="alert alert-danger rounded-3 shadow-sm border-0 d-flex align-items-center gap-3 p-3">
              <i class="bi bi-exclamation-triangle-fill fs-3 text-danger flex-shrink-0"></i>
              <div><?= e($searchError) ?></div>
            </div>
          <?php endif; ?>

          <?php if ($student): ?>
            <!-- HASIL KELULUSAN -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 reveal-up">
              <div class="card-header p-4 bg-primary text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                  <span class="badge bg-white text-primary fw-bold text-uppercase small mb-1">Hasil Verifikasi Kelulusan</span>
                  <h2 class="h5 mb-0 text-white fw-bold"><?= e($student['student_name']) ?></h2>
                </div>
                <div>
                  <?php if ($student['status'] === 'lulus'): ?>
                    <span class="badge bg-success fs-6 px-3 py-2 border border-light">
                      <i class="bi bi-check-circle-fill me-1"></i>LULUS
                    </span>
                  <?php elseif ($student['status'] === 'tidak_lulus'): ?>
                    <span class="badge bg-danger fs-6 px-3 py-2">
                      <i class="bi bi-x-circle-fill me-1"></i>TIDAK LULUS
                    </span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                      <i class="bi bi-hourglass-split me-1"></i>DITUNDA
                    </span>
                  <?php endif; ?>
                </div>
              </div>

              <div class="card-body p-4 p-md-5 bg-white">
                <div class="table-responsive mb-4">
                  <table class="table table-bordered align-middle mb-0">
                    <tbody>
                      <tr>
                        <th class="bg-light" style="width: 38%;">Nama Lengkap</th>
                        <td class="fw-bold text-dark"><?= e($student['student_name']) ?></td>
                      </tr>
                      <tr>
                        <th class="bg-light">NISN</th>
                        <td><?= e($student['nisn']) ?></td>
                      </tr>
                      <tr>
                        <th class="bg-light">Nomor Peserta Ujian</th>
                        <td><?= e($student['exam_number']) ?></td>
                      </tr>
                      <tr>
                        <th class="bg-light">Tempat, Tanggal Lahir</th>
                        <td><?= e($student['birth_place'] ?? '-') ?>, <?= !empty($student['birth_date']) ? date('d F Y', strtotime($student['birth_date'])) : '-' ?></td>
                      </tr>
                      <tr>
                        <th class="bg-light">Program Keahlian / Jurusan</th>
                        <td class="fw-semibold text-primary"><?= e($student['major_name']) ?></td>
                      </tr>
                      <tr>
                        <th class="bg-light">Nilai Rata-rata Ujian</th>
                        <td><span class="badge bg-light text-dark border fs-6"><?= e(number_format((float)$student['average_score'], 2)) ?></span></td>
                      </tr>
                      <tr>
                        <th class="bg-light">Keterangan</th>
                        <td><?= !empty($student['remarks']) ? e($student['remarks']) : 'Selamat atas kelulusan yang telah diraih. Tetap berprestasi dan jaga nama baik almamater.' ?></td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <?php if ($student['status'] === 'lulus'): ?>
                  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 p-3 rounded-3 bg-light border">
                    <div>
                      <div class="fw-bold text-dark"><i class="bi bi-file-earmark-pdf me-1 text-danger"></i>Surat Keterangan Lulus (SKL)</div>
                      <div class="small text-muted">Cetak dokumen SKL resmi lengkap dengan kop surat dan barcode validasi.</div>
                    </div>
                    <a href="<?= e(BASE_URL) ?>/kelulusan/cetak?nisn=<?= urlencode($student['nisn']) ?>" target="_blank" class="btn btn-accent px-4 py-2">
                      <i class="bi bi-printer me-1"></i>Cetak Dokumen SKL
                    </a>
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
