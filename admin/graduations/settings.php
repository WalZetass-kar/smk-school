<?php
/**
 * admin/graduations/settings.php
 * Pengaturan Jadwal Rilis Kelulusan
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();

$db = getDB();
$settings = $db->query("SELECT * FROM graduation_settings WHERE id = 1")->fetch() ?: [
    'id'                => 1,
    'academic_year'     => '2026/2027',
    'announcement_date' => date('Y-m-d H:i:s'),
    'is_active'         => 1,
    'header_info'       => 'Pengumuman Resmi Kelulusan Siswa.',
    'signature_city'    => 'Pekanbaru',
    'signature_date'    => date('Y-m-d'),
    'signature_name'    => null,
    'signature_nip'     => null,
];

// Principal default
$principalDefault = $db->query("SELECT principal_name FROM principal_message ORDER BY id LIMIT 1")->fetchColumn() ?: 'Kepala Sekolah';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Sesi form kadaluarsa.';
    }

    $data = [
        'academic_year'     => trim($_POST['academic_year'] ?? '2026/2027'),
        'announcement_date' => trim($_POST['announcement_date'] ?? date('Y-m-d H:i:s')),
        'is_active'         => isset($_POST['is_active']) ? 1 : 0,
        'header_info'       => trim($_POST['header_info'] ?? ''),
        'signature_city'    => trim($_POST['signature_city'] ?? 'Pekanbaru'),
        'signature_date'    => trim($_POST['signature_date'] ?? date('Y-m-d')),
        'signature_name'    => trim($_POST['signature_name'] ?? ''),
        'signature_nip'     => trim($_POST['signature_nip'] ?? ''),
    ];

    if (empty($errors)) {
        $stmt = $db->prepare("
            UPDATE graduation_settings 
            SET academic_year=?, announcement_date=?, is_active=?, header_info=?, signature_city=?, signature_date=?, signature_name=?, signature_nip=?
            WHERE id=1
        ");
        $stmt->execute([
            $data['academic_year'], $data['announcement_date'], $data['is_active'], 
            $data['header_info'], $data['signature_city'], $data['signature_date'],
            $data['signature_name'] !== '' ? $data['signature_name'] : null,
            $data['signature_nip'] !== '' ? $data['signature_nip'] : null,
        ]);
        logActivity('UPDATE_GRADUATION_SETTINGS', 'graduations', "Memperbarui jadwal & pengaturan kelulusan TA {$data['academic_year']}");
        setFlash('success', 'Pengaturan kelulusan & pejabat penandatangan SKL berhasil disimpan.');
        redirect(BASE_URL . '/admin/graduations/settings.php');
    }
}

$pageTitle = 'Pengaturan Jadwal Kelulusan & SKL';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h4 fw-bold text-primary mb-0"><i class="bi bi-gear-fill me-2"></i>Pengaturan Jadwal Kelulusan &amp; SKL</h1>
  <a href="<?= e(BASE_URL) ?>/admin/graduations/index.php" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Kembali
  </a>
</div>

<?= flashMessage() ?>

<div class="admin-card">
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Tahun Pelajaran / Ajaran <span class="text-danger">*</span></label>
        <input type="text" name="academic_year" class="form-control" value="<?= e($settings['academic_year']) ?>" required placeholder="2026/2027">
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Waktu / Tanggal Rilis Pengumuman (Hitung Mundur) <span class="text-danger">*</span></label>
        <input type="datetime-local" name="announcement_date" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($settings['announcement_date'])) ?>" required>
        <div class="form-text small">Sebelum waktu ini tiba, halaman publik akan menampilkan hitung mundur otomatis.</div>
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Kota Penandatangan SKL</label>
        <input type="text" name="signature_city" class="form-control" value="<?= e($settings['signature_city'] ?? 'Pekanbaru') ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Tanggal Surat Keterangan Lulus (SKL)</label>
        <input type="date" name="signature_date" class="form-control" value="<?= e($settings['signature_date'] ?? date('Y-m-d')) ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Nama Kepala Sekolah Penandatangan SKL</label>
        <input type="text" name="signature_name" class="form-control" value="<?= e($settings['signature_name'] ?? '') ?>" placeholder="Default otomatis: <?= e($principalDefault) ?>">
        <div class="form-text small">Kosongkan jika ingin otomatis mengikuti nama di menu <strong>Sambutan Kepala Sekolah</strong> (<?= e($principalDefault) ?>).</div>
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">NIP Kepala Sekolah Penandatangan</label>
        <input type="text" name="signature_nip" class="form-control" value="<?= e($settings['signature_nip'] ?? '') ?>" placeholder="Contoh: 19750815 200003 1 002">
        <div class="form-text small">Nomor Induk Pegawai kepala sekolah yang tercetak di bawah tanda tangan SKL.</div>
      </div>

      <div class="col-12">
        <label class="form-label small fw-semibold">Informasi Pengantar / Maklumat Kelulusan</label>
        <textarea name="header_info" rows="3" class="form-control"><?= e($settings['header_info']) ?></textarea>
      </div>

      <div class="col-12">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="activeSwitch" name="is_active" value="1" <?= !empty($settings['is_active']) ? 'checked' : '' ?>>
          <label class="form-check-label fw-semibold small" for="activeSwitch">Aktifkan Modul Kelulusan Online</label>
        </div>
      </div>

      <div class="col-12 pt-3 border-top">
        <button type="submit" class="btn btn-accent px-4"><i class="bi bi-save me-1"></i>Simpan Pengaturan</button>
      </div>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
