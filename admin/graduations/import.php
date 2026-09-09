<?php
/**
 * admin/graduations/import.php
 * Import Data Siswa Kelulusan Massal dari Berkas CSV & Excel
 * Dilengkapi pendeteksi pemisah otomatis (koma / titik-koma), anti-nyatu,
 * dan pemetaan kolom Bahasa Indonesia yang cerdas.
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();

$db = getDB();
$level = getSchoolLevel();
$errors = [];
$insertedCount = 0;
$updatedCount  = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi formulir telah berakhir, silakan muat ulang halaman dan coba lagi.';
    }

    $fileName = $_FILES['csv_file']['name'] ?? '';
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($ext, ['csv', 'txt', 'xls'], true)) {
        $errors[] = 'Format berkas harus berupa file CSV (.csv) atau Excel (.xls).';
    } elseif (empty($_FILES['csv_file']['tmp_name'])) {
        $errors[] = 'Silakan pilih berkas data kelulusan yang akan diunggah.';
    } else {
        $filePath = $_FILES['csv_file']['tmp_name'];
        $rows = [];

        // Penanganan jika berkas berformat .xls (HTML-based Excel)
        if ($ext === 'xls') {
            $content = file_get_contents($filePath);
            if (preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $content, $trMatches)) {
                foreach ($trMatches[1] as $tr) {
                    if (preg_match_all('/<(?:td|th)[^>]*>(.*?)<\/(?:td|th)>/is', $tr, $tdMatches)) {
                        $row = array_map(function($val) {
                            return trim(html_entity_decode(strip_tags($val), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                        }, $tdMatches[1]);
                        if (!empty(array_filter($row))) {
                            $rows[] = $row;
                        }
                    }
                }
            }
        }

        // Penanganan standar berkas CSV / TXT
        if (empty($rows)) {
            $handle = fopen($filePath, 'r');
            if ($handle !== false) {
                // Deteksi delimiter (koma, titik-koma, atau tab)
                $firstLine = fgets($handle);
                $delimiter = ';'; // default Excel Indonesia

                if ($firstLine !== false) {
                    $trimmedFirst = trim($firstLine);
                    if (str_starts_with($trimmedFirst, 'sep=')) {
                        $customSep = trim(substr($trimmedFirst, 4));
                        if ($customSep !== '') {
                            $delimiter = $customSep;
                        }
                        $firstLine = fgets($handle);
                    } else {
                        $semicolons = substr_count($firstLine, ';');
                        $commas     = substr_count($firstLine, ',');
                        $tabs       = substr_count($firstLine, "\t");

                        if ($semicolons >= $commas && $semicolons >= $tabs && $semicolons > 0) {
                            $delimiter = ';';
                        } elseif ($commas > $semicolons && $commas >= $tabs && $commas > 0) {
                            $delimiter = ',';
                        } elseif ($tabs > 0) {
                            $delimiter = "\t";
                        }
                    }

                    if ($firstLine !== false) {
                        $headerRow = str_getcsv($firstLine, $delimiter);
                        if (!empty(array_filter($headerRow))) {
                            $rows[] = $headerRow;
                        }
                    }
                }

                while (($lineData = fgetcsv($handle, 4096, $delimiter)) !== false) {
                    if (!empty(array_filter($lineData))) {
                        $rows[] = $lineData;
                    }
                }
                fclose($handle);
            }
        }

        if (empty($rows)) {
            $errors[] = 'Berkas kosong atau tidak dapat dibaca oleh sistem.';
        } else {
            // Analisis baris header (Baris pertama)
            $header = array_shift($rows);
            $colMap = [
                'nisn'          => 0,
                'exam_number'   => 1,
                'student_name'  => 2,
                'birth_place'   => 3,
                'birth_date'    => 4,
                'major_name'    => 5,
                'average_score' => 6,
                'status'        => 7,
                'remarks'       => 8
            ];

            // Pencocokan otomatis nama kolom Bahasa Indonesia & Inggris
            foreach ($header as $idx => $colName) {
                $clean = strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $colName)));
                if (str_contains($clean, 'nisn') || $clean === 'nis') {
                    $colMap['nisn'] = $idx;
                } elseif (str_contains($clean, 'ujian') || str_contains($clean, 'exam') || str_contains($clean, 'peserta')) {
                    $colMap['exam_number'] = $idx;
                } elseif (str_contains($clean, 'nama') || str_contains($clean, 'student')) {
                    $colMap['student_name'] = $idx;
                } elseif (str_contains($clean, 'tempat') || str_contains($clean, 'birth_place')) {
                    $colMap['birth_place'] = $idx;
                } elseif (str_contains($clean, 'tanggal') || str_contains($clean, 'tgl') || str_contains($clean, 'birth_date')) {
                    $colMap['birth_date'] = $idx;
                } elseif (str_contains($clean, 'jurusan') || str_contains($clean, 'peminatan') || str_contains($clean, 'major') || str_contains($clean, 'program')) {
                    $colMap['major_name'] = $idx;
                } elseif (str_contains($clean, 'nilai') || str_contains($clean, 'score') || str_contains($clean, 'rata')) {
                    $colMap['average_score'] = $idx;
                } elseif (str_contains($clean, 'status')) {
                    $colMap['status'] = $idx;
                } elseif (str_contains($clean, 'keterangan') || str_contains($clean, 'remarks') || str_contains($clean, 'catatan')) {
                    $colMap['remarks'] = $idx;
                }
            }

            $stmtCheck = $db->prepare("SELECT id FROM graduations WHERE nisn = ? OR exam_number = ? LIMIT 1");
            $stmtInsert = $db->prepare("
                INSERT INTO graduations (nisn, exam_number, student_name, birth_place, birth_date, major_name, average_score, status, remarks)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtUpdate = $db->prepare("
                UPDATE graduations
                SET exam_number = ?, student_name = ?, birth_place = ?, birth_date = ?, major_name = ?, average_score = ?, status = ?, remarks = ?
                WHERE id = ?
            ");

            foreach ($rows as $row) {
                // Lewati baris panduan atau baris kosong
                if (empty(array_filter($row))) continue;

                $nisn = trim(ltrim($row[$colMap['nisn']] ?? '', "'"));
                if ($nisn === '' || str_starts_with(strtolower($nisn), 'nisn') || str_starts_with(strtolower($nisn), 'panduan')) {
                    continue;
                }

                $examNum = trim(ltrim($row[$colMap['exam_number']] ?? '', "'"));
                if ($examNum === '') {
                    $examNum = 'UJ-' . $nisn;
                }

                $name = trim($row[$colMap['student_name']] ?? '');
                if ($name === '') continue;

                $birthPlace = trim($row[$colMap['birth_place']] ?? '');
                $rawDate    = trim($row[$colMap['birth_date']] ?? '');
                $birthDate  = null;

                // Konversi tanggal cerdas (YYYY-MM-DD, DD/MM/YYYY, DD-MM-YYYY)
                if (preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/', $rawDate, $m)) {
                    $birthDate = sprintf('%04d-%02d-%02d', (int)$m[1], (int)$m[2], (int)$m[3]);
                } elseif (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $rawDate, $m)) {
                    $birthDate = sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
                }

                $major = trim($row[$colMap['major_name']] ?? '');
                if ($major === '') {
                    $major = ($level === 'SMA') ? 'MIPA' : 'Umum';
                }

                // Konversi angka nilai rata-rata (mendukung koma 88,50 -> 88.50)
                $rawScore = str_replace(',', '.', trim($row[$colMap['average_score']] ?? '80'));
                $score    = is_numeric($rawScore) ? (float)$rawScore : 80.00;

                // Normalisasi status kelulusan Bahasa Indonesia
                $rawStatus = strtolower(str_replace([' ', '_', '-'], '', trim($row[$colMap['status']] ?? 'lulus')));
                if (in_array($rawStatus, ['lulus', 'l', 'passed', 'pass', 'berhasil'], true)) {
                    $status = 'lulus';
                } elseif (in_array($rawStatus, ['tidaklulus', 'tl', 'gagal', 'tidak', 'failed'], true)) {
                    $status = 'tidak_lulus';
                } elseif (in_array($rawStatus, ['ditunda', 'tunda', 'pending', 'menunggu', 'hold'], true)) {
                    $status = 'ditunda';
                } else {
                    $status = 'lulus';
                }

                $remarks = trim($row[$colMap['remarks']] ?? '');

                try {
                    $stmtCheck->execute([$nisn, $examNum]);
                    $existing = $stmtCheck->fetch();

                    if ($existing) {
                        $stmtUpdate->execute([$examNum, $name, $birthPlace, $birthDate, $major, $score, $status, $remarks, $existing['id']]);
                        $updatedCount++;
                    } else {
                        $stmtInsert->execute([$nisn, $examNum, $name, $birthPlace, $birthDate, $major, $score, $status, $remarks]);
                        $insertedCount++;
                    }
                } catch (Exception $e) {
                    // Abaikan kesalahan baris tertentu agar impor tetap berjalan
                }
            }

            $totalProcessed = $insertedCount + $updatedCount;
            if ($totalProcessed > 0) {
                logActivity('IMPORT_GRADUATION_CSV', 'graduations', "Mengimpor massal data kelulusan: $insertedCount data baru, $updatedCount data diperbarui.");
                setFlash('success', "Berhasil memproses $totalProcessed data siswa: $insertedCount data baru berhasil ditambahkan, dan $updatedCount data lama diperbarui.");
                redirect(BASE_URL . '/admin/graduations/index.php');
            } else {
                $errors[] = 'Tidak ada data siswa yang berhasil diimpor. Pastikan format kolom sesuai dengan template.';
            }
        }
    }
}

$pageTitle = 'Import Data Siswa Kelulusan (CSV & Excel)';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold text-primary mb-1"><i class="bi bi-file-earmark-arrow-up me-2"></i>Import Data Siswa Kelulusan</h1>
    <p class="text-muted small mb-0">Impor massal ratusan data kelulusan siswa sekaligus dari berkas CSV atau Microsoft Excel.</p>
  </div>
  <a href="<?= e(BASE_URL) ?>/admin/graduations/index.php" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar
  </a>
</div>

<?= flashMessage() ?>

<?php if ($errors): ?>
  <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
    <div class="fw-bold mb-2 d-flex align-items-center gap-2">
      <i class="bi bi-exclamation-triangle-fill fs-5"></i> Harap periksa kendala berikut:
    </div>
    <ul class="mb-0 ps-3 small">
      <?php foreach ($errors as $err): ?>
        <li><?= e($err) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="row g-4 mb-4">
  <!-- Kolom Kiri: Formulir Upload -->
  <div class="col-lg-6">
    <div class="admin-card h-100">
      <h2 class="h6 fw-bold text-primary border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-upload text-accent"></i> 1. Unggah Berkas Data Siswa
      </h2>
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

        <div class="mb-4">
          <label class="form-label small fw-semibold">Pilih Berkas CSV atau Excel <span class="text-danger">*</span></label>
          <input type="file" name="csv_file" class="form-control form-control-lg fs-6" accept=".csv, .xls, .txt" required>
          <div class="form-text small text-muted mt-2">
            Mendukung berkas <strong>.CSV</strong> (pemisah koma atau titik-koma) dan <strong>.XLS</strong>.
          </div>
        </div>

        <div class="d-flex align-items-center justify-content-between pt-3 border-top flex-wrap gap-2">
          <button type="submit" class="btn btn-success px-4 fw-semibold py-2">
            <i class="bi bi-cloud-arrow-up me-1"></i> Mulai Import Data Sekarang
          </button>
          <a href="<?= e(BASE_URL) ?>/admin/graduations/index.php" class="btn btn-light border py-2">
            Batal
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Kolom Kanan: Download Template & Panduan -->
  <div class="col-lg-6">
    <div class="admin-card h-100 bg-light border-0">
      <h2 class="h6 fw-bold text-dark border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-file-earmark-spreadsheet text-success fs-5"></i> 2. Unduh Template Resmi (Format Indonesia)
      </h2>
      <p class="small text-muted mb-3" style="line-height: 1.6;">
        Agar kolom data rapi dan <strong>tidak menyatu dalam 1 kolom saat dibuka di Microsoft Excel</strong>, gunakan salah satu template resmi berbahasa Indonesia yang telah disiapkan di bawah ini:
      </p>

      <div class="d-flex flex-column gap-2 mb-4">
        <a href="<?= e(BASE_URL) ?>/admin/graduations/template.php?format=excel" class="btn btn-white border-2 border-primary shadow-sm d-flex align-items-center justify-content-between p-3 rounded-3 text-start text-dark text-decoration-none">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-primary text-white p-2.5 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
              <i class="bi bi-file-earmark-excel-fill fs-3"></i>
            </div>
            <div>
              <div class="d-flex align-items-center gap-2">
                <strong class="text-dark">Unduh Template Excel (.xls)</strong>
                <span class="badge bg-primary text-white px-2 py-0.5 rounded-pill" style="font-size: 10px;">Direkomendasikan</span>
              </div>
              <div class="text-muted small" style="font-size: 11.5px; line-height: 1.4;">
                Format Microsoft Excel (.xls) siap isi &bull; Angka nol di depan NISN tidak hilang.
              </div>
            </div>
          </div>
          <i class="bi bi-download text-primary fs-4"></i>
        </a>

        <a href="<?= e(BASE_URL) ?>/admin/graduations/template.php?format=csv" class="btn btn-white border shadow-xs d-flex align-items-center justify-content-between p-3 rounded-3 text-start text-dark text-decoration-none">
          <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-success bg-opacity-10 text-success p-2.5 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
              <i class="bi bi-filetype-csv fs-3"></i>
            </div>
            <div>
              <div class="fw-bold text-dark">Unduh Template CSV (.csv)</div>
              <div class="text-muted small" style="font-size: 11.5px;">Format teks ringan dengan pemisah titik-koma (;) dan UTF-8 BOM otomatis.</div>
            </div>
          </div>
          <i class="bi bi-download text-success fs-4"></i>
        </a>
      </div>

      <div class="p-3 bg-white rounded-3 border">
        <div class="fw-semibold text-dark small mb-2 d-flex align-items-center gap-1">
          <i class="bi bi-info-circle text-primary"></i> Aturan Pengisian Kolom:
        </div>
        <ul class="mb-0 ps-3 text-muted small" style="line-height: 1.7; font-size: 11.5px;">
          <li><strong>NISN &amp; No. Peserta Ujian</strong>: Wajib diisi dan berbeda untuk tiap siswa.</li>
          <li><strong>Format Tanggal Lahir</strong>: Gunakan format <code>YYYY-MM-DD</code> (contoh: <code>2008-04-15</code>) atau <code>DD/MM/YYYY</code>.</li>
          <li><strong>Pilihan Status Kelulusan</strong>: Cukup ketik <code>LULUS</code>, <code>TIDAK LULUS</code>, atau <code>DITUNDA</code>.</li>
          <li><strong>Nilai Rata-rata</strong>: Dapat berupa angka desimal (contoh: <code>88.50</code> atau <code>88,50</code>).</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
