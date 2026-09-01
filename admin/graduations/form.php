<?php
/**
 * admin/graduations/form.php
 * Tambah / Edit Siswa Kelulusan
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$student = null;
if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM graduations WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch();
    if (!$student) redirect(BASE_URL . '/admin/graduations/index.php');
}

$majors = $db->query("SELECT name FROM majors ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN) ?: [
    'Rekayasa Perangkat Lunak (RPL)',
    'Teknik Komputer dan Jaringan (TKJ)',
    'Teknik Kendaraan Ringan (TKR)',
    'Akuntansi dan Keuangan Lembaga (AKL)'
];

$errors = [];
$old = [
    'nisn'          => $student['nisn'] ?? '',
    'exam_number'   => $student['exam_number'] ?? '',
    'student_name'  => $student['student_name'] ?? '',
    'birth_place'   => $student['birth_place'] ?? '',
    'birth_date'    => $student['birth_date'] ?? '',
    'major_name'    => $student['major_name'] ?? ($majors[0] ?? ''),
    'average_score' => $student['average_score'] ?? '85.00',
    'status'        => $student['status'] ?? 'lulus',
    'remarks'       => $student['remarks'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Sesi form kadaluarsa.';
    }

    $old = [
        'nisn'          => trim($_POST['nisn'] ?? ''),
        'exam_number'   => trim($_POST['exam_number'] ?? ''),
        'student_name'  => trim($_POST['student_name'] ?? ''),
        'birth_place'   => trim($_POST['birth_place'] ?? ''),
        'birth_date'    => trim($_POST['birth_date'] ?? ''),
        'major_name'    => trim($_POST['major_name'] ?? ''),
        'average_score' => (float)($_POST['average_score'] ?? 0),
        'status'        => trim($_POST['status'] ?? 'lulus'),
        'remarks'       => trim($_POST['remarks'] ?? ''),
    ];

    if ($old['nisn'] === '') $errors[] = 'NISN wajib diisi.';
    if ($old['exam_number'] === '') $errors[] = 'Nomor ujian wajib diisi.';
    if ($old['student_name'] === '') $errors[] = 'Nama siswa wajib diisi.';

    // Check duplicate NISN
    if (empty($errors)) {
        if ($id > 0) {
            $chk = $db->prepare("SELECT id FROM graduations WHERE (nisn = ? OR exam_number = ?) AND id != ? LIMIT 1");
            $chk->execute([$old['nisn'], $old['exam_number'], $id]);
        } else {
            $chk = $db->prepare("SELECT id FROM graduations WHERE nisn = ? OR exam_number = ? LIMIT 1");
            $chk->execute([$old['nisn'], $old['exam_number']]);
        }
        if ($chk->fetch()) {
            $errors[] = 'NISN atau Nomor Ujian sudah terdaftar pada data siswa lain.';
        }
    }

    if (empty($errors)) {
        if ($id > 0) {
            $stmt = $db->prepare("
                UPDATE graduations 
                SET nisn=?, exam_number=?, student_name=?, birth_place=?, birth_date=?, major_name=?, average_score=?, status=?, remarks=?
                WHERE id=?
            ");
            $stmt->execute([
                $old['nisn'], $old['exam_number'], $old['student_name'], $old['birth_place'], 
                $old['birth_date'] ?: null, $old['major_name'], $old['average_score'], 
                $old['status'], $old['remarks'], $id
            ]);
            setFlash('success', 'Data siswa berhasil diperbarui.');
        } else {
            $stmt = $db->prepare("
                INSERT INTO graduations (nisn, exam_number, student_name, birth_place, birth_date, major_name, average_score, status, remarks)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $old['nisn'], $old['exam_number'], $old['student_name'], $old['birth_place'], 
                $old['birth_date'] ?: null, $old['major_name'], $old['average_score'], 
                $old['status'], $old['remarks']
            ]);
            setFlash('success', 'Data siswa berhasil ditambahkan.');
        }
        redirect(BASE_URL . '/admin/graduations/index.php');
    }
}

$pageTitle = ($id > 0 ? 'Edit' : 'Tambah') . ' Data Siswa Kelulusan';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>

<?= flashMessage() ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h4 fw-bold text-primary mb-0"><?= $id > 0 ? 'Edit' : 'Tambah' ?> Data Siswa Kelulusan</h1>
  <a href="<?= e(BASE_URL) ?>/admin/graduations/index.php" class="btn btn-outline-secondary btn-sm">
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
      <div class="col-md-6">
        <label class="form-label small fw-semibold">NISN (Nomor Induk Siswa Nasional) <span class="text-danger">*</span></label>
        <input type="text" name="nisn" class="form-control" value="<?= e($old['nisn']) ?>" required placeholder="0051234567">
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Nomor Peserta Ujian <span class="text-danger">*</span></label>
        <input type="text" name="exam_number" class="form-control" value="<?= e($old['exam_number']) ?>" required placeholder="UJN-2026-001">
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Nama Lengkap Siswa <span class="text-danger">*</span></label>
        <input type="text" name="student_name" class="form-control" value="<?= e($old['student_name']) ?>" required placeholder="Nama siswa sesuai Ijazah">
      </div>

      <div class="col-md-3">
        <label class="form-label small fw-semibold">Tempat Lahir</label>
        <input type="text" name="birth_place" class="form-control" value="<?= e($old['birth_place']) ?>" placeholder="Pekanbaru">
      </div>

      <div class="col-md-3">
        <label class="form-label small fw-semibold">Tanggal Lahir</label>
        <input type="date" name="birth_date" class="form-control" value="<?= e($old['birth_date']) ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label small fw-semibold">Program Keahlian / Jurusan <span class="text-danger">*</span></label>
        <input type="text" name="major_name" class="form-control" list="majorsList" value="<?= e($old['major_name']) ?>" required>
        <datalist id="majorsList">
          <?php foreach ($majors as $m): ?><option value="<?= e($m) ?>"><?php endforeach; ?>
        </datalist>
      </div>

      <div class="col-md-3">
        <label class="form-label small fw-semibold">Nilai Rata-rata Ujian</label>
        <input type="number" step="0.01" name="average_score" class="form-control" value="<?= e($old['average_score']) ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label small fw-semibold">Status Kelulusan <span class="text-danger">*</span></label>
        <select name="status" class="form-select" required>
          <option value="lulus" <?= $old['status'] === 'lulus' ? 'selected' : '' ?>>LULUS</option>
          <option value="tidak_lulus" <?= $old['status'] === 'tidak_lulus' ? 'selected' : '' ?>>TIDAK LULUS</option>
          <option value="ditunda" <?= $old['status'] === 'ditunda' ? 'selected' : '' ?>>DITUNDA</option>
        </select>
      </div>

      <div class="col-12">
        <label class="form-label small fw-semibold">Keterangan Tambahan / Catatan SKL</label>
        <textarea name="remarks" rows="3" class="form-control" placeholder="Contoh: Dinyatakan Lulus dengan predikat Sangat Memuaskan..."><?= e($old['remarks']) ?></textarea>
      </div>

      <div class="col-12 pt-3 border-top">
        <button type="submit" class="btn btn-accent px-4"><i class="bi bi-save me-1"></i>Simpan Data</button>
      </div>
    </div>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
