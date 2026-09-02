<?php
/**
 * admin/backup.php
 * Pusat Cadangan & Pemulihan Sistem (Disaster Recovery Center)
 * Fitur:
 * 1. 1-Klik Backup Database MySQL (.SQL)
 * 2. 1-Klik Full System Disaster Recovery (.ZIP: Database SQL + Seluruh Berkas assets/uploads/)
 * 3. Superadmin Security Verification Guard (Memerlukan verifikasi password sebelum download)
 * 4. Audit Log Aktivitas
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireSuperAdmin();

$db = getDB();
$dbName = $db->query('SELECT DATABASE()')->fetchColumn() ?: 'smk_school';

// Helper function to build complete SQL dump
function generateSqlDump(PDO $db, string $dbName): string {
    $tables = [];
    $result = $db->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    $sqlDump  = "-- ========================================================\n";
    $sqlDump .= "-- Database Backup: " . $dbName . "\n";
    $sqlDump .= "-- Waktu Pembuatan: " . date('Y-m-d H:i:s') . "\n";
    $sqlDump .= "-- Dibuat oleh Admin: " . ($_SESSION['admin_name'] ?? 'Superadmin') . " (@" . ($_SESSION['admin_username'] ?? 'admin') . ")\n";
    $sqlDump .= "-- Engine: MariaDB / MySQL Native Backup Dump\n";
    $sqlDump .= "-- ========================================================\n\n";
    $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n";
    $sqlDump .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
    $sqlDump .= "SET time_zone = '+07:00';\n\n";

    foreach ($tables as $table) {
        $sqlDump .= "-- --------------------------------------------------------\n";
        $sqlDump .= "-- Struktur tabel `" . $table . "`\n";
        $sqlDump .= "-- --------------------------------------------------------\n";
        $sqlDump .= "DROP TABLE IF EXISTS `" . $table . "`;\n";

        $createStmt = $db->query("SHOW CREATE TABLE `" . $table . "`")->fetch(PDO::FETCH_NUM);
        $sqlDump .= $createStmt[1] . ";\n\n";

        $rows = $db->query("SELECT * FROM `" . $table . "`")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            $sqlDump .= "-- Data untuk tabel `" . $table . "` (" . count($rows) . " baris)\n";
            foreach ($rows as $row) {
                $fields = [];
                foreach ($row as $val) {
                    if ($val === null) {
                        $fields[] = "NULL";
                    } else {
                        $fields[] = $db->quote($val);
                    }
                }
                $sqlDump .= "INSERT INTO `" . $table . "` VALUES (" . implode(', ', $fields) . ");\n";
            }
            $sqlDump .= "\n";
        }
    }

    $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";
    return $sqlDump;
}

// Handle Form Execution (SQL or Full ZIP Download)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        setFlash('danger', 'Sesi verifikasi keamanan telah kedaluwarsa. Silakan coba lagi.');
        redirect(BASE_URL . '/admin/backup.php');
    }

    // Verify Superadmin Password for Security Authorization
    $stmtUser = $db->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
    $stmtUser->execute([$_SESSION['admin_id']]);
    $currentUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$currentUser || !password_verify($confirmPassword, $currentUser['password'])) {
        logActivity('BACKUP_AUTH_FAILED', 'backup', 'Percobaan pengunduhan backup ditolak: Kata sandi otorisasi salah.');
        setFlash('danger', 'Kata sandi otorisasi salah! Pengunduhan dibatalkan demi keamanan data sekolah.');
        redirect(BASE_URL . '/admin/backup.php');
    }

    // ACTION A: Download SQL Only
    if ($action === 'download_sql') {
        $sqlDump = generateSqlDump($db, $dbName);
        $filename = 'backup_db_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $dbName) . '_' . date('Ymd_His') . '.sql';

        logActivity('DATABASE_BACKUP', 'backup', 'Super Admin berhasil mengunduh backup SQL database: ' . $filename);

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($sqlDump));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        echo $sqlDump;
        exit;
    }

    // ACTION B: Download Full ZIP (Database SQL + assets/uploads/)
    if ($action === 'download_full_zip') {
        if (!class_exists('ZipArchive')) {
            setFlash('danger', 'Ekstensi PHP ZipArchive tidak aktif pada server.');
            redirect(BASE_URL . '/admin/backup.php');
        }

        $sqlDump = generateSqlDump($db, $dbName);
        $zipFilename = 'FULL_BACKUP_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $dbName) . '_' . date('Ymd_His') . '.zip';
        $tempZipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'backup_' . uniqid() . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            setFlash('danger', 'Gagal membuat berkas arsip zip sementara di server.');
            redirect(BASE_URL . '/admin/backup.php');
        }

        // 1. Add SQL Dump to root of ZIP
        $zip->addFromString('database_dump.sql', $sqlDump);

        // 2. Add Readme instructions
        $readmeContent = "PANDUAN RESTORE / PEMULIHAN SISTEM\n"
            . "====================================================\n"
            . "Arsip Backup Dibuat Pada: " . date('Y-m-d H:i:s') . "\n"
            . "Nama Database: " . $dbName . "\n"
            . "Dibuat Oleh: " . ($_SESSION['admin_name'] ?? 'Superadmin') . "\n\n"
            . "LANGKAH PEMULIHAN (RESTORE):\n"
            . "1. Buka phpMyAdmin atau MySQL Client di hosting baru.\n"
            . "2. Pilih atau buat database baru dengan charset utf8mb4.\n"
            . "3. Import berkas 'database_dump.sql'.\n"
            . "4. Ekstrak seluruh isi folder 'uploads/' ke dalam direktori 'assets/uploads/' di website.\n"
            . "5. Sesuaikan kredensial koneksi database pada 'config/database.php'.\n"
            . "6. Website siap digunakan kembali 100% lengkap!\n";
        $zip->addFromString('README_CARA_RESTORE.txt', $readmeContent);

        // 3. Add all files in assets/uploads/ recursively
        $uploadsDir = realpath(BASE_PATH . '/assets/uploads');
        if ($uploadsDir && is_dir($uploadsDir)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($uploadsDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = 'uploads/' . ltrim(substr($filePath, strlen($uploadsDir)), '\\/');
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }

        $zip->close();

        if (!file_exists($tempZipPath)) {
            setFlash('danger', 'Berkas ZIP tidak berhasil disimpan di server.');
            redirect(BASE_URL . '/admin/backup.php');
        }

        logActivity('FULL_SYSTEM_BACKUP', 'backup', 'Super Admin berhasil mengunduh Full System Disaster Recovery ZIP: ' . $zipFilename);

        // Stream ZIP file
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
        header('Content-Length: ' . filesize($tempZipPath));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        readfile($tempZipPath);
        @unlink($tempZipPath);
        exit;
    }
}

// Calculate Statistics for UI Display
$tableCount = count($db->query("SHOW TABLES")->fetchAll());

// Count rows across all tables
$totalRows = 0;
$tableStats = $db->query("SELECT table_name, data_length, index_length, table_rows FROM information_schema.tables WHERE table_schema = " . $db->quote($dbName))->fetchAll(PDO::FETCH_ASSOC);
$totalDbBytes = 0;
foreach ($tableStats as $ts) {
    $totalRows += (int)($ts['table_rows'] ?? 0);
    $totalDbBytes += (int)($ts['data_length'] ?? 0) + (int)($ts['index_length'] ?? 0);
}
$dbSizeFormatted = $totalDbBytes > 1048576 
    ? round($totalDbBytes / 1048576, 2) . ' MB' 
    : round($totalDbBytes / 1024, 1) . ' KB';

// Calculate assets/uploads file count and size
$uploadsCount = 0;
$uploadsBytes = 0;
$uploadsDir = realpath(BASE_PATH . '/assets/uploads');
if ($uploadsDir && is_dir($uploadsDir)) {
    $dirIterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($uploadsDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($dirIterator as $file) {
        if ($file->isFile()) {
            $uploadsCount++;
            $uploadsBytes += $file->getSize();
        }
    }
}
$uploadsSizeFormatted = $uploadsBytes > 1048576 
    ? round($uploadsBytes / 1048576, 2) . ' MB' 
    : round($uploadsBytes / 1024, 1) . ' KB';

// Get last backup log
$lastBackup = $db->query("SELECT created_at, detail FROM activity_logs WHERE action IN ('DATABASE_BACKUP', 'FULL_SYSTEM_BACKUP') ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

$pageTitle = 'Backup & Pemulihan Sistem';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
require __DIR__ . '/includes/topbar.php';
?>

<?= flashMessage() ?>

<!-- Intro Header Banner -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
  <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div class="d-flex align-items-start gap-3">
      <div class="p-3 rounded-3 fs-3 flex-shrink-0" style="background-color: var(--color-primary-subtle, #e2e8f0); color: var(--color-primary, #0F2C59);">
        <i class="bi bi-shield-lock-fill"></i>
      </div>
      <div>
        <h2 class="h5 fw-bold text-primary mb-1">Pusat Cadangan &amp; Pemulihan Sistem (Disaster Recovery)</h2>
        <p class="text-muted small mb-0" style="line-height: 1.6; max-width: 780px;">
          Amankan seluruh data sekolah dari risiko kerusakan perangkat keras, serangan virus, atau perpindahan server hosting. Unduh salinan database SQL terstruktur atau arsip paket lengkap (.ZIP) yang mencakup seluruh foto berita, galeri, logo, dan berkas pendaftar PPDB.
        </p>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-semibold">
        <i class="bi bi-check-circle-fill me-1"></i> Proteksi Otorisasi Superadmin
      </span>
    </div>
  </div>
</div>

<!-- System Status Summary Cards -->
<div class="row g-3 mb-4">
  <!-- Database Status Card -->
  <div class="col-lg-4 col-md-6">
    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em;">Database MySQL</span>
        <div class="rounded-2 fs-5 d-flex align-items-center justify-content-center flex-shrink-0" style="width:38px; height:38px; background-color: var(--color-primary-subtle, #e2e8f0); color: var(--color-primary, #0F2C59);">
          <i class="bi bi-database"></i>
        </div>
      </div>
      <div class="h3 fw-bold text-dark mb-1"><?= $tableCount ?> <span class="fs-6 fw-normal text-muted">Tabel</span></div>
      <div class="small text-muted mb-2">
        Kapasitas: <strong><?= e($dbSizeFormatted) ?></strong> &middot; <?= number_format($totalRows, 0, ',', '.') ?> Rekor Data
      </div>
      <div class="pt-2 border-top small text-muted d-flex align-items-center justify-content-between">
        <span>Nama Database:</span>
        <code class="fw-bold text-primary"><?= e($dbName) ?></code>
      </div>
    </div>
  </div>

  <!-- Media Assets Card -->
  <div class="col-lg-4 col-md-6">
    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em;">Berkas Upload &amp; Media</span>
        <div class="p-2 rounded-2 bg-warning bg-opacity-10 text-warning fs-5">
          <i class="bi bi-folder-fill"></i>
        </div>
      </div>
      <div class="h3 fw-bold text-dark mb-1"><?= number_format($uploadsCount, 0, ',', '.') ?> <span class="fs-6 fw-normal text-muted">Berkas</span></div>
      <div class="small text-muted mb-2">
        Ukuran Folder: <strong><?= e($uploadsSizeFormatted) ?></strong> (Foto, PDF, Logo)
      </div>
      <div class="pt-2 border-top small text-muted d-flex align-items-center justify-content-between">
        <span>Lokasi:</span>
        <code class="text-dark">assets/uploads/</code>
      </div>
    </div>
  </div>

  <!-- Engine Health & Last Backup Card -->
  <div class="col-lg-4 col-md-12">
    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.05em;">Status Engine &amp; Backup</span>
        <div class="p-2 rounded-2 bg-success bg-opacity-10 text-success fs-5">
          <i class="bi bi-cpu-fill"></i>
        </div>
      </div>
      <div class="h5 fw-bold text-dark mb-1">PHP <?= phpversion() ?></div>
      <div class="small text-muted mb-2">
        ZipArchive: <span class="badge bg-success text-white py-1">Aktif &amp; Siap</span>
      </div>
      <div class="pt-2 border-top small text-muted">
        <?php if (!empty($lastBackup['created_at'])): ?>
          <span class="d-block text-truncate" title="<?= e($lastBackup['detail'] ?? '') ?>">
            <i class="bi bi-clock-history me-1 text-primary"></i> Terakhir: <?= date('d M Y, H:i', strtotime($lastBackup['created_at'])) ?>
          </span>
        <?php else: ?>
          <span><i class="bi bi-info-circle me-1"></i> Belum ada rekaman riwayat backup.</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Two Powerful Backup Options -->
<div class="row g-4 mb-4">
  
  <!-- Option 1: Database SQL Only -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 d-flex flex-column justify-content-between hover-lift">
      <div>
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="rounded-3 fs-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:54px; height:54px; background-color: var(--color-primary-subtle, #e2e8f0); color: var(--color-primary, #0F2C59);">
            <i class="bi bi-database-down"></i>
          </div>
          <div>
            <span class="badge bg-primary text-white mb-1">Paling Cepat</span>
            <h3 class="h5 fw-bold text-dark mb-0">Cadangan Database (.SQL)</h3>
          </div>
        </div>

        <p class="text-muted small mb-4" style="line-height: 1.65;">
          Mengekspor seluruh skema tabel, relasi foreign key, indeks, dan seluruh rekaman data (berita, akun admin, pendaftar PPDB, pesan buku tamu, dll) ke dalam berkas <code>.sql</code> standar yang siap di-import kembali ke phpMyAdmin kapan saja.
        </p>

        <ul class="list-unstyled small text-muted mb-4">
          <li class="mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill text-success"></i> Ukuran file sangat kecil &amp; unduhan super instan.
          </li>
          <li class="mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill text-success"></i> Dilengkapi perintah <code>DROP TABLE IF EXISTS</code> untuk restore mulus.
          </li>
          <li class="d-flex align-items-center gap-2 text-warning-emphasis">
            <i class="bi bi-exclamation-circle-fill text-warning"></i> Tidak mencakup foto atau berkas media yang di-upload.
          </li>
        </ul>
      </div>

      <button type="button" class="btn btn-outline-primary-custom w-100 py-2.5 fw-bold rounded-3 d-flex align-items-center justify-content-center gap-2" onclick="openBackupModal('download_sql', 'Cadangan Database MySQL (.SQL)', 'Anda akan mengunduh dump database .sql lengkap. Demi keamanan data sekolah, silakan masukkan kata sandi Superadmin Anda.')">
        <i class="bi bi-download fs-5"></i>
        <span>Unduh Database (.SQL)</span>
      </button>
    </div>
  </div>

  <!-- Option 2: Full System Disaster Recovery (ZIP: SQL + Uploads) -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 d-flex flex-column justify-content-between hover-lift border-top border-4 border-warning">
      <div>
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="p-3 rounded-3 bg-warning bg-opacity-10 text-warning-emphasis fs-3 flex-shrink-0">
            <i class="bi bi-file-earmark-zip-fill text-warning"></i>
          </div>
          <div>
            <span class="badge bg-warning text-dark mb-1">Disaster Recovery Lengkap</span>
            <h3 class="h5 fw-bold text-dark mb-0">Full System Backup (.ZIP)</h3>
          </div>
        </div>

        <p class="text-muted small mb-4" style="line-height: 1.65;">
          Paket pemulihan bencana komprehensif 1-klik: sistem akan menggabungkan dump database <code>database_dump.sql</code>, panduan pemulihan <code>README.txt</code>, serta <strong>seluruh foto berita, logo, gambar jurusan, dan dokumen pendaftar PPDB</strong> ke dalam satu berkas arsip <code>.zip</code> resmi.
        </p>

        <ul class="list-unstyled small text-muted mb-4">
          <li class="mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill text-success"></i> 100% data terselamatkan lengkap dengan seluruh media/gambar.
          </li>
          <li class="mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill text-success"></i> Dilengkapi panduan pemulihan langkah-demi-langkah.
          </li>
          <li class="d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill text-success"></i> Sangat cocok untuk migrasi ke server/hosting baru tanpa ribet.
          </li>
        </ul>
      </div>

      <button type="button" class="btn btn-accent w-100 py-2.5 fw-bold rounded-3 d-flex align-items-center justify-content-center gap-2" onclick="openBackupModal('download_full_zip', 'Paket Lengkap Disaster Recovery (.ZIP)', 'Sistem akan mengompresi database SQL dan seluruh folder media uploads ke dalam 1 file arsip .zip. Masukkan kata sandi Superadmin untuk melanjutkan.')">
        <i class="bi bi-file-earmark-zip-fill fs-5"></i>
        <span>Unduh Paket Lengkap (.ZIP)</span>
      </button>
    </div>
  </div>

</div>

<!-- Panduan Pemulihan / Restore Card -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
  <h4 class="h6 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
    <i class="bi bi-question-diamond text-primary"></i> Panduan Pemulihan Data (Cara Restore Data)
  </h4>
  <div class="row g-3">
    <div class="col-md-4">
      <div class="p-3 bg-light rounded-3 h-100">
        <div class="fw-bold text-dark small mb-1">1. Restore Database SQL</div>
        <p class="text-muted small mb-0" style="font-size: 12.5px; line-height: 1.5;">
          Buka <strong>phpMyAdmin</strong> di cPanel/hosting baru, pilih database Anda, lalu klik tab <strong>Import</strong> dan upload file <code>.sql</code> hasil backup.
        </p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="p-3 bg-light rounded-3 h-100">
        <div class="fw-bold text-dark small mb-1">2. Restore Berkas Media Uploads</div>
        <p class="text-muted small mb-0" style="font-size: 12.5px; line-height: 1.5;">
          Jika Anda mengunduh paket <code>.ZIP</code>, ekstrak folder <code>uploads/</code> dan masukkan ke dalam direktori <code>assets/uploads/</code> di website baru.
        </p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="p-3 bg-light rounded-3 h-100">
        <div class="fw-bold text-dark small mb-1">3. Sinkronisasi Konfigurasi</div>
        <p class="text-muted small mb-0" style="font-size: 12.5px; line-height: 1.5;">
          Pastikan username, password, dan nama database pada file <code>config/database.php</code> telah sesuai dengan server baru Anda. Selesai!
        </p>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

<!-- Superadmin Security Verification Modal (Rendered outside .admin-content directly in body) -->
<div class="modal fade" id="backupAuthModal" tabindex="-1" aria-hidden="true" style="z-index: 1065;">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
    <div class="modal-content border-0 shadow-lg rounded-4 p-3 bg-white" style="background-color: #ffffff !important;">
      <div class="modal-body p-3 text-center">
        <div class="d-flex justify-content-end mb-1">
          <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:68px; height:68px; background-color: var(--color-primary-subtle, #e2e8f0); color: var(--color-primary, #0F2C59);">
          <i class="bi bi-shield-lock-fill fs-1"></i>
        </div>

        <h5 class="fw-bold text-dark mb-2" id="modalTitleText">Otorisasi Keamanan Superadmin</h5>
        <p class="small text-muted mb-4" id="modalDescText" style="line-height: 1.55;">
          Masukkan kata sandi akun Superadmin Anda untuk memverifikasi hak akses unduhan cadangan data.
        </p>

        <form method="post" action="<?= e(BASE_URL) ?>/admin/backup.php" id="backupAuthForm" class="text-start">
          <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
          <input type="hidden" name="action" id="backupActionInput" value="download_sql">

          <div class="mb-3">
            <label for="confirmPasswordInput" class="form-label small fw-semibold text-dark mb-1">Kata Sandi Superadmin</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
              <input type="password" name="confirm_password" id="confirmPasswordInput" class="form-control border-start-0 border-end-0" placeholder="Ketik kata sandi Anda..." required autocomplete="current-password">
              <button type="button" class="btn btn-light border border-start-0 text-muted shadow-none" id="toggleAuthPassBtn" tabindex="-1">
                <i class="bi bi-eye" id="toggleAuthPassIcon"></i>
              </button>
            </div>
            <div class="form-text text-muted" style="font-size: 11.5px;">
              Verifikasi ini memastikan berkas cadangan sekolah hanya dapat diunduh oleh pemilik akun sah.
            </div>
          </div>

          <div class="d-flex align-items-center gap-2 mt-4">
            <button type="button" class="btn btn-light border w-50 py-2 fw-semibold rounded-3 shadow-none" data-bs-dismiss="modal">
              Batal
            </button>
            <button type="submit" class="btn btn-primary w-50 py-2 fw-bold rounded-3 d-flex align-items-center justify-content-center gap-2" id="btnSubmitBackup">
              <span id="btnSubmitBackupText">Verifikasi &amp; Unduh</span>
              <span class="spinner-border spinner-border-sm d-none" id="btnSubmitSpinner" role="status" aria-hidden="true"></span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Ensure modal is directly attached to <body> so it never gets trapped below backdrop
document.addEventListener('DOMContentLoaded', function() {
  const modalEl = document.getElementById('backupAuthModal');
  if (modalEl && modalEl.parentNode !== document.body) {
    document.body.appendChild(modalEl);
  }
});

function openBackupModal(actionType, title, description) {
  document.getElementById('backupActionInput').value = actionType;
  document.getElementById('modalTitleText').textContent = title;
  document.getElementById('modalDescText').textContent = description;
  document.getElementById('confirmPasswordInput').value = '';

  const modalEl = document.getElementById('backupAuthModal');
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  modal.show();

  modalEl.addEventListener('shown.bs.modal', function () {
    document.getElementById('confirmPasswordInput').focus();
  }, { once: true });
}

// Toggle Password Eye Icon
const togglePassBtn = document.getElementById('toggleAuthPassBtn');
const passInput = document.getElementById('confirmPasswordInput');
const passIcon = document.getElementById('toggleAuthPassIcon');

if (togglePassBtn && passInput && passIcon) {
  togglePassBtn.addEventListener('click', function () {
    const isPass = passInput.getAttribute('type') === 'password';
    passInput.setAttribute('type', isPass ? 'text' : 'password');
    passIcon.className = isPass ? 'bi bi-eye-slash text-primary' : 'bi bi-eye';
  });
}

// Button loading state on submit
const backupForm = document.getElementById('backupAuthForm');
if (backupForm) {
  backupForm.addEventListener('submit', function () {
    const btn = document.getElementById('btnSubmitBackup');
    const text = document.getElementById('btnSubmitBackupText');
    const spinner = document.getElementById('btnSubmitSpinner');
    if (btn && spinner) {
      btn.disabled = true;
      text.textContent = 'Memproses Arsip...';
      spinner.classList.remove('d-none');
    }
  });
}
</script>

