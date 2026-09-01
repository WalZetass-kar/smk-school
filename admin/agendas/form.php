<?php
/**
 * admin/agendas/form.php
 * Tambah / Edit Agenda
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$agenda = null;
if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM agendas WHERE id = ?");
    $stmt->execute([$id]);
    $agenda = $stmt->fetch();
    if (!$agenda) redirect(BASE_URL . '/admin/agendas/index.php');
}

$errors = [];
$old = [
    'title'       => $agenda['title'] ?? '',
    'start_date'  => $agenda['start_date'] ?? date('Y-m-d'),
    'end_date'    => $agenda['end_date'] ?? '',
    'time_info'   => $agenda['time_info'] ?? '08.00 WIB - Selesai',
    'location'    => $agenda['location'] ?? 'Aula Utama Sekolah',
    'organizer'   => $agenda['organizer'] ?? 'Waka Kurikulum / Kesiswaan',
    'description' => $agenda['description'] ?? '',
    'status'      => $agenda['status'] ?? 1,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Sesi form kadaluarsa.';
    }

    $old = [
        'title'       => trim($_POST['title'] ?? ''),
        'start_date'  => trim($_POST['start_date'] ?? date('Y-m-d')),
        'end_date'    => trim($_POST['end_date'] ?? '') ?: null,
        'time_info'   => trim($_POST['time_info'] ?? ''),
        'location'    => trim($_POST['location'] ?? ''),
        'organizer'   => trim($_POST['organizer'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'status'      => isset($_POST['status']) ? 1 : 0,
    ];

    if ($old['title'] === '') $errors[] = 'Judul agenda wajib diisi.';
    if ($old['start_date'] === '') $errors[] = 'Tanggal mulai wajib diisi.';

    $slug = slugify($old['title']);
    if ($id > 0) {
        $chk = $db->prepare("SELECT id FROM agendas WHERE slug = ? AND id != ? LIMIT 1");
        $chk->execute([$slug, $id]);
    } else {
        $chk = $db->prepare("SELECT id FROM agendas WHERE slug = ? LIMIT 1");
        $chk->execute([$slug]);
    }
    if ($chk->fetch()) {
        $slug .= '-' . time();
    }

    if (empty($errors)) {
        $old['description'] = cleanHtml($old['description']);

        if ($id > 0) {
            $stmt = $db->prepare("
                UPDATE agendas 
                SET title=?, slug=?, start_date=?, end_date=?, time_info=?, location=?, organizer=?, description=?, status=?
                WHERE id=?
            ");
            $stmt->execute([
                $old['title'], $slug, $old['start_date'], $old['end_date'], 
                $old['time_info'], $old['location'], $old['organizer'], 
                $old['description'], $old['status'], $id
            ]);
            logActivity('UPDATE_AGENDA', 'agendas', "Memperbarui agenda kegiatan: {$old['title']}");
            setFlash('success', 'Agenda kegiatan berhasil diperbarui.');
        } else {
            $stmt = $db->prepare("
                INSERT INTO agendas (title, slug, start_date, end_date, time_info, location, organizer, description, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $old['title'], $slug, $old['start_date'], $old['end_date'], 
                $old['time_info'], $old['location'], $old['organizer'], 
                $old['description'], $old['status']
            ]);
            logActivity('CREATE_AGENDA', 'agendas', "Menambahkan agenda kegiatan baru: {$old['title']}");
            setFlash('success', 'Agenda kegiatan baru berhasil ditambahkan.');
        }
        redirect(BASE_URL . '/admin/agendas/index.php');
    }
}

$pageTitle = ($id > 0 ? 'Edit' : 'Tambah') . ' Agenda Kegiatan';
$currentAdminNav = 'agendas';
require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h4 fw-bold text-primary mb-0"><?= $id > 0 ? 'Edit' : 'Tambah' ?> Agenda Kegiatan</h1>
  <a href="<?= e(BASE_URL) ?>/admin/agendas/index.php" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Kembali
  </a>
</div>

<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="admin-card">
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

    <div class="row g-3">
      <div class="col-12">
        <label class="form-label small fw-semibold">Judul Kegiatan / Acara <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" value="<?= e($old['title']) ?>" placeholder="Contoh: Penilaian Sumatif Akhir Semester (PSAS)" required>
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
        <input type="date" name="start_date" class="form-control" value="<?= e($old['start_date']) ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Tanggal Selesai (Opsional)</label>
        <input type="date" name="end_date" class="form-control" value="<?= e($old['end_date']) ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label small fw-semibold">Waktu / Jam Pelaksanaan</label>
        <input type="text" name="time_info" class="form-control" value="<?= e($old['time_info']) ?>" placeholder="08.00 - 12.00 WIB">
      </div>

      <div class="col-md-4">
        <label class="form-label small fw-semibold">Tempat / Lokasi Acara</label>
        <input type="text" name="location" class="form-control" value="<?= e($old['location']) ?>" placeholder="Aula Utama / Ruang Lab">
      </div>

      <div class="col-md-4">
        <label class="form-label small fw-semibold">Penyelenggara / Panitia</label>
        <input type="text" name="organizer" class="form-control" value="<?= e($old['organizer']) ?>" placeholder="Panitia Ujian / OSIS">
      </div>

      <div class="col-12">
        <label class="form-label small fw-semibold">Deskripsi &amp; Keterangan Lengkap</label>
        <textarea name="description" rows="5" class="form-control" placeholder="Rincian informasi acara, peserta yang wajib hadir, perlengkapan yang perlu dibawa..."><?= e($old['description']) ?></textarea>
      </div>

      <div class="col-12">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="statusSwitch" name="status" value="1" <?= !empty($old['status']) ? 'checked' : '' ?>>
          <label class="form-check-label fw-semibold small" for="statusSwitch">Tampilkan di Kalender Publik</label>
        </div>
      </div>

      <div class="col-12 pt-3 border-top">
        <button type="submit" class="btn btn-accent px-4"><i class="bi bi-save me-1"></i>Simpan Agenda</button>
      </div>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
