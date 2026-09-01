<?php
/**
 * admin/whatsapp/index.php
 * Pengaturan WhatsApp Gateway & Template Notifikasi Otomatis
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$settings = getWAGatewaySettings();
$profile = getSchoolProfile();

$errors = [];
$testResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $action = $_POST['action'] ?? 'save_settings';

        if ($action === 'test_send') {
            $testPhone   = trim($_POST['test_phone'] ?? '');
            $testMessage = trim($_POST['test_message'] ?? '');

            if ($testPhone === '' || $testMessage === '') {
                $errors[] = 'Nomor tujuan dan isi pesan uji coba wajib diisi.';
            } else {
                $res = sendWhatsAppNotification($testPhone, $testMessage);
                if ($res['status']) {
                    logActivity('TEST_WA_GATEWAY', 'whatsapp', "Berhasil mengirim pesan uji coba WA ke $testPhone");
                    setFlash('success', "Pesan uji coba WhatsApp berhasil dikirim ke $testPhone.");
                } else {
                    $errors[] = "Gagal mengirim pesan: " . ($res['message'] ?? 'Periksa kembali Token API dan koneksi Anda.');
                }
            }
        } else {
            // Save Settings
            $provider             = trim($_POST['provider'] ?? 'fonnte');
            $apiToken             = trim($_POST['api_token'] ?? '');
            $apiEndpoint          = trim($_POST['api_endpoint'] ?? 'https://api.fonnte.com/send');
            $isActive             = isset($_POST['is_active']) ? 1 : 0;
            $ppdbSuccessTemplate  = trim($_POST['ppdb_success_template'] ?? '');
            $ppdbStatusTemplate   = trim($_POST['ppdb_status_template'] ?? '');

            $stmt = $db->prepare("
                UPDATE wa_gateway_settings
                SET provider = ?, api_token = ?, api_endpoint = ?, is_active = ?, ppdb_success_template = ?, ppdb_status_template = ?
                WHERE id = 1
            ");
            $stmt->execute([$provider, $apiToken, $apiEndpoint, $isActive, $ppdbSuccessTemplate, $ppdbStatusTemplate]);

            logActivity('UPDATE_WA_SETTINGS', 'whatsapp', "Memperbarui konfigurasi WhatsApp Gateway (Status: " . ($isActive ? 'ON' : 'OFF') . ")");
            setFlash('success', 'Pengaturan WhatsApp Gateway & template pesan berhasil disimpan.');
            redirect(BASE_URL . '/admin/whatsapp/index.php');
        }
    }
}

$pageTitle = 'WhatsApp Gateway';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>

<?= flashMessage() ?>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger mb-4">
    <ul class="mb-0">
      <?php foreach ($errors as $err): ?>
        <li><?= e($err) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="row g-4 mb-4">
  <!-- Left Column: Main Gateway Configuration -->
  <div class="col-lg-8">
    <div class="admin-card p-4 bg-white border-0 shadow-sm rounded-4">
      <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
        <div>
          <h2 class="h5 fw-bold text-primary mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-whatsapp text-success fs-4"></i> Konfigurasi WhatsApp Gateway
          </h2>
          <span class="text-muted small">Hubungkan website dengan penyedia WA Gateway (seperti Fonnte, Wablas, dll) untuk mengirim notifikasi otomatis ke siswa &amp; orang tua.</span>
        </div>
      </div>

      <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="save_settings">

        <div class="row g-3">
          <div class="col-12">
            <div class="p-3 rounded-3 bg-light border d-flex align-items-center justify-content-between flex-wrap gap-2">
              <div>
                <div class="fw-bold text-dark mb-0">Status Notifikasi WhatsApp</div>
                <small class="text-muted">Aktifkan untuk mulai mengirim notifikasi otomatis pendaftaran &amp; verifikasi.</small>
              </div>
              <div class="form-check form-switch fs-5">
                <input class="form-check-input" type="checkbox" role="switch" id="waActiveSwitch" name="is_active" value="1" <?= !empty($settings['is_active']) ? 'checked' : '' ?>>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label small fw-semibold">Penyedia Layanan (Provider)</label>
            <select name="provider" class="form-select">
              <option value="fonnte" <?= ($settings['provider'] ?? '') === 'fonnte' ? 'selected' : '' ?>>Fonnte (Rekomendasi - api.fonnte.com)</option>
              <option value="wablas" <?= ($settings['provider'] ?? '') === 'wablas' ? 'selected' : '' ?>>Wablas</option>
              <option value="custom" <?= ($settings['provider'] ?? '') === 'custom' ? 'selected' : '' ?>>Custom REST API</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label small fw-semibold">API Endpoint URL</label>
            <input type="text" name="api_endpoint" class="form-control" value="<?= e($settings['api_endpoint'] ?? 'https://api.fonnte.com/send') ?>" placeholder="https://api.fonnte.com/send">
          </div>

          <div class="col-12">
            <label class="form-label small fw-semibold">API Key / Token Rahasia <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="password" name="api_token" id="waApiTokenInput" class="form-control" value="<?= e($settings['api_token'] ?? '') ?>" placeholder="Masukkan Token API dari Dashboard Provider Anda">
              <button class="btn btn-outline-secondary toggle-pass-btn" type="button" data-target="waApiTokenInput">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <div class="form-text small text-muted">Dapatkan Token gratis atau berbayar di penyedia WA Gateway Anda (misal: <a href="https://fonnte.com" target="_blank" rel="noopener">fonnte.com</a>).</div>
          </div>

          <!-- Template 1: PPDB Success Template -->
          <div class="col-12 pt-3 border-top">
            <label class="form-label small fw-bold text-dark d-flex align-items-center justify-content-between">
              <span><i class="bi bi-chat-left-dots me-1 text-primary"></i> Template: Konfirmasi Pendaftaran PPDB Baru</span>
              <span class="badge bg-light text-muted border">Otomatis saat submit form</span>
            </label>
            <textarea name="ppdb_success_template" rows="4" class="form-control font-monospace small"><?= e($settings['ppdb_success_template'] ?? '') ?></textarea>
            <div class="form-text small text-muted">
              Tag yang dapat digunakan: <code>{nama_siswa}</code>, <code>{no_registrasi}</code>, <code>{nama_sekolah}</code>, <code>{pilihan_jurusan}</code>, <code>{link_cetak}</code>
            </div>
          </div>

          <!-- Template 2: PPDB Status Change Template -->
          <div class="col-12 pt-3 border-top">
            <label class="form-label small fw-bold text-dark d-flex align-items-center justify-content-between">
              <span><i class="bi bi-patch-check me-1 text-success"></i> Template: Pembaruan Status Verifikasi PPDB</span>
              <span class="badge bg-light text-muted border">Saat admin ubah status</span>
            </label>
            <textarea name="ppdb_status_template" rows="4" class="form-control font-monospace small"><?= e($settings['ppdb_status_template'] ?? '') ?></textarea>
            <div class="form-text small text-muted">
              Tag yang dapat digunakan: <code>{nama_siswa}</code>, <code>{no_registrasi}</code>, <code>{nama_sekolah}</code>, <code>{status_baru}</code>, <code>{catatan}</code>, <code>{link_cek}</code>
            </div>
          </div>

          <div class="col-12 pt-3">
            <button type="submit" class="btn btn-accent px-4 py-2 fw-semibold">
              <i class="bi bi-save me-1"></i> Simpan Konfigurasi WhatsApp
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Right Column: Test Send & Quick Help -->
  <div class="col-lg-4">
    <!-- Test Sender Card -->
    <div class="admin-card p-4 bg-white border-0 shadow-sm rounded-4 mb-4">
      <h3 class="h6 fw-bold text-primary mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-send-check text-warning"></i> Uji Coba Pengiriman WA
      </h3>
      <p class="text-muted small mb-3">Tes koneksi API token dengan mengirim pesan uji coba ke nomor WhatsApp Anda sendiri:</p>
      
      <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="test_send">

        <div class="mb-3">
          <label class="form-label small fw-semibold">Nomor WhatsApp Tujuan</label>
          <input type="text" name="test_phone" class="form-control form-control-sm" placeholder="Contoh: 081234567890" required>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold">Isi Pesan Uji Coba</label>
          <textarea name="test_message" rows="3" class="form-control form-control-sm" required>Tes Notifikasi WhatsApp dari Website <?= e($profile['school_name'] ?? 'Sekolah') ?>. Layanan WA Gateway aktif dan siap digunakan!</textarea>
        </div>

        <button type="submit" class="btn btn-sm btn-outline-success w-100 fw-semibold py-2">
          <i class="bi bi-send me-1"></i> Kirim Pesan Uji Coba
        </button>
      </form>
    </div>

    <!-- Quick Help Card -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-light">
      <h4 class="h6 fw-bold text-dark mb-2"><i class="bi bi-info-circle text-primary me-1"></i> Panduan Singkat:</h4>
      <ol class="small text-muted ps-3 mb-0" style="line-height: 1.6;">
        <li>Daftar akun di penyedia seperti <a href="https://fonnte.com" target="_blank" rel="noopener">Fonnte</a>.</li>
        <li>Hubungkan perangkat WhatsApp (*Scan QR Code*).</li>
        <li>Salin <strong>API Token</strong> dan tempel di formulir sebelah kiri.</li>
        <li>Aktifkan saklar status dan klik <strong>Simpan</strong>.</li>
      </ol>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.toggle-pass-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    const target = document.getElementById(this.dataset.target);
    if (!target) return;
    const isPass = target.type === 'password';
    target.type = isPass ? 'text' : 'password';
    const icon = this.querySelector('i');
    if (icon) {
      icon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
    }
  });
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
