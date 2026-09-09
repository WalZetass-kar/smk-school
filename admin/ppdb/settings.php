<?php
/**
 * admin/ppdb/settings.php
 * Pengaturan PPDB Online (Tahun Ajaran, Tanggal, Buka/Tutup, Syarat)
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$settings = $db->query("SELECT * FROM ppdb_settings ORDER BY id LIMIT 1")->fetch();
$profile  = getSchoolProfile();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $academicYear      = trim($_POST['academic_year'] ?? '');
        $isOpen            = isset($_POST['is_open']) ? 1 : 0;
        $hasMajorSelection = isset($_POST['has_major_selection']) ? 1 : 0;
        $startDate         = trim($_POST['start_date'] ?? '') ?: null;
        $endDate           = trim($_POST['end_date'] ?? '') ?: null;
        $announcementDate  = trim($_POST['announcement_date'] ?? '') ?: null;
        $information       = trim($_POST['information'] ?? '');
        $contactPerson     = trim($_POST['contact_person'] ?? '');

        if ($academicYear === '') {
            $errors[] = 'Tahun ajaran wajib diisi.';
        }

        if (empty($errors)) {
            if ($settings) {
                $stmt = $db->prepare("
                    UPDATE ppdb_settings
                    SET academic_year = ?, is_open = ?, has_major_selection = ?, start_date = ?, end_date = ?, announcement_date = ?, information = ?, contact_person = ?
                    WHERE id = ?
                ");
                $stmt->execute([$academicYear, $isOpen, $hasMajorSelection, $startDate, $endDate, $announcementDate, $information, $contactPerson, $settings['id']]);
            } else {
                $stmt = $db->prepare("
                    INSERT INTO ppdb_settings (academic_year, is_open, has_major_selection, start_date, end_date, announcement_date, information, contact_person)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$academicYear, $isOpen, $hasMajorSelection, $startDate, $endDate, $announcementDate, $information, $contactPerson]);
            }

            logActivity('UPDATE_PPDB_SETTINGS', 'ppdb', "Memperbarui jadwal & pengaturan PPDB online TA $academicYear");
            setFlash('success', 'Pengaturan PPDB berhasil disimpan.');
            redirect(BASE_URL . '/admin/ppdb/settings.php');
        }

        $settings = [
            'academic_year'     => $academicYear,
            'is_open'           => $isOpen,
            'start_date'        => $startDate,
            'end_date'          => $endDate,
            'announcement_date' => $announcementDate,
            'information'       => $information,
            'contact_person'    => $contactPerson,
        ];
    }
}

$pageTitle = 'Pengaturan PPDB';
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

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h4 fw-bold text-primary mb-0">Pengaturan PPDB Online</h1>
  <a href="index.php" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Kembali ke Data Pendaftar
  </a>
</div>

<div class="admin-card">
  <form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label small fw-semibold">Tahun Ajaran <span class="text-danger">*</span></label>
        <input type="text" name="academic_year" class="form-control" value="<?= e($settings['academic_year'] ?? '2026/2027') ?>" placeholder="Contoh: 2026/2027" required>
      </div>

      <div class="col-md-6 d-flex align-items-center pt-md-4">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="isOpenSwitch" name="is_open" value="1" <?= !empty($settings['is_open']) ? 'checked' : '' ?>>
          <label class="form-check-label fw-semibold" for="isOpenSwitch">Buka Pendaftaran Online (PPDB Aktif)</label>
        </div>
      </div>

      <div class="col-12">
        <div class="p-3 bg-light rounded-3 border d-flex align-items-start justify-content-between gap-3">
          <div>
            <label class="form-check-label fw-bold text-dark mb-1" for="hasMajorSelectionSwitch">
              <i class="bi bi-mortarboard me-1 text-primary"></i> Wajibkan Pilihan Jurusan / Peminatan Saat Pendaftaran
            </label>
            <div class="text-muted small" style="line-height: 1.5;">
              <span class="badge bg-success-subtle text-success me-1">ON (SMK)</span> Aktifkan jika sekolah memiliki penjurusan kejuruan langsung (calon siswa wajib memilih jurusan).<br>
              <span class="badge bg-primary-subtle text-primary me-1">OFF (SMA / SMP / SD)</span> <strong>Matikan saklar ini untuk SMA (Kurikulum Merdeka Kelas 10 belum ada jurusan)</strong>, SMP, atau SD agar kolom pilihan jurusan tidak muncul di formulir pendaftaran siswa.
            </div>
          </div>
          <div class="form-check form-switch fs-5 mb-0 flex-shrink-0">
            <input class="form-check-input" type="checkbox" role="switch" id="hasMajorSelectionSwitch" name="has_major_selection" value="1" <?= (!isset($settings['has_major_selection']) || !empty($settings['has_major_selection'])) ? 'checked' : '' ?>>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <label class="form-label small fw-semibold">Tanggal Mulai Pendaftaran</label>
        <input type="date" name="start_date" class="form-control" value="<?= e($settings['start_date'] ?? '') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label small fw-semibold">Tanggal Selesai Pendaftaran</label>
        <input type="date" name="end_date" class="form-control" value="<?= e($settings['end_date'] ?? '') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label small fw-semibold">Tanggal Pengumuman Hasil</label>
        <input type="date" name="announcement_date" class="form-control" value="<?= e($settings['announcement_date'] ?? '') ?>">
      </div>

      <div class="col-12">
        <label class="form-label small fw-semibold">Informasi &amp; Alur / Petunjuk PPDB</label>
        <textarea name="information" class="form-control wysiwyg-editor"><?= e($settings['information'] ?? '') ?></textarea>
      </div>

      <div class="col-12">
        <label class="form-label small fw-semibold">Kontak Pusat Bantuan / Hotline Panitia</label>
        <input type="text" name="contact_person" class="form-control" value="<?= e($settings['contact_person'] ?? '') ?>" placeholder="Contoh: (0761) 123456 / 0812-3456-7890 (Pak Bambang)">
      </div>

      <div class="col-12 pt-3">
        <button type="submit" class="btn btn-accent px-4 py-2 fw-semibold">
          <i class="bi bi-save me-1"></i>Simpan Pengaturan PPDB
        </button>
      </div>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
