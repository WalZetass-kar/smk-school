<?php
/**
 * admin/graduations/import.php
 * Import Data Siswa Kelulusan Massal dari CSV
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();

$db = getDB();
$errors = [];
$successCount = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Sesi form kadaluarsa.';
    }

    if (empty($_FILES['csv_file']['tmp_name'])) {
        $errors[] = 'Silakan pilih file CSV yang akan diunggah.';
    } else {
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        if ($handle !== false) {
            $header = fgetcsv($handle, 1000, ',');
            // Format expected: nisn, exam_number, student_name, birth_place, birth_date, major_name, average_score, status, remarks
            $stmt = $db->prepare("
                INSERT INTO graduations (nisn, exam_number, student_name, birth_place, birth_date, major_name, average_score, status, remarks)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                  student_name=VALUES(student_name), birth_place=VALUES(birth_place), 
                  birth_date=VALUES(birth_date), major_name=VALUES(major_name), 
                  average_score=VALUES(average_score), status=VALUES(status), remarks=VALUES(remarks)
            ");

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($data) >= 3 && trim($data[0]) !== '') {
                    $nisn        = trim($data[0]);
                    $examNum     = trim($data[1] ?? '');
                    $name        = trim($data[2] ?? '');
                    $birthPlace  = trim($data[3] ?? '');
                    $birthDate   = trim($data[4] ?? '') ?: null;
                    $major       = trim($data[5] ?? 'Umum');
                    $score       = (float)($data[6] ?? 80);
                    $status      = in_array(strtolower(trim($data[7] ?? '')), ['lulus', 'tidak_lulus', 'ditunda']) ? strtolower(trim($data[7])) : 'lulus';
                    $remarks     = trim($data[8] ?? '');

                    try {
                        $stmt->execute([$nisn, $examNum, $name, $birthPlace, $birthDate, $major, $score, $status, $remarks]);
                        $successCount++;
                    } catch (Exception $e) {
                        // ignore row error
                    }
                }
            }
            fclose($handle);
            setFlash('success', "Berhasil mengimpor / memperbarui $successCount data siswa kelulusan.");
            redirect(BASE_URL . '/admin/graduations/index.php');
        } else {
            $errors[] = 'Gagal membaca file CSV.';
        }
    }
}

$pageTitle = 'Import Data Siswa Kelulusan (CSV)';
$currentAdminNav = 'graduations';
require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h4 fw-bold text-primary mb-0"><i class="bi bi-file-earmark-arrow-up me-2"></i>Import Siswa Kelulusan (CSV)</h1>
  <a href="<?= e(BASE_URL) ?>/admin/graduations/index.php" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Kembali
  </a>
</div>

<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="admin-card mb-4">
  <div class="p-3 bg-light rounded-3 mb-4">
    <h2 class="h6 fw-bold text-dark mb-2"><i class="bi bi-info-circle me-1 text-primary"></i>Petunjuk Format File CSV:</h2>
    <p class="small text-muted mb-2">Pastikan urutan kolom file CSV Anda memiliki header baris pertama sebagai berikut:</p>
    <code class="d-block p-2 bg-white rounded border small text-dark mb-2">
      nisn,exam_number,student_name,birth_place,birth_date,major_name,average_score,status,remarks
    </code>
    <p class="small text-muted mb-0">Contoh format tanggal: <code>YYYY-MM-DD</code> (misal: <code>2008-04-12</code>), status: <code>lulus</code>, <code>tidak_lulus</code>, atau <code>ditunda</code>.</p>
  </div>

  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

    <div class="mb-3">
      <label class="form-label small fw-semibold">Pilih File CSV Siswa <span class="text-danger">*</span></label>
      <input type="file" name="csv_file" class="form-control" accept=".csv" required>
    </div>

    <button type="submit" class="btn btn-success px-4"><i class="bi bi-upload me-1"></i>Mulai Import Data</button>
  </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
