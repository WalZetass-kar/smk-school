<?php
/**
 * pages/ppdb-daftar.php — /ppdb/daftar
 * Formulir Pendaftaran Online PPDB SMK
 */
$db       = getDB();
$profile  = getSchoolProfile();
$settings = getPpdbSettings();
$majors   = $db->query("SELECT * FROM majors WHERE status = 1 ORDER BY id ASC")->fetchAll();

if (empty($settings['is_open'])) {
    $pageTitle = 'Pendaftaran PPDB Ditutup';
    require BASE_PATH . '/includes/header.php';
    require BASE_PATH . '/includes/navbar.php';
    ?>
    <section class="section text-center py-5">
      <div class="container my-5">
        <div class="p-3 bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;">
          <i class="bi bi-lock-fill fs-1 text-muted"></i>
        </div>
        <h1 class="h3 fw-bold text-primary mb-2">Pendaftaran PPDB Ditutup</h1>
        <p class="text-muted small mx-auto mb-4" style="max-width: 480px;">Mohon maaf, masa pendaftaran Penerimaan Peserta Didik Baru saat ini belum dibuka atau telah berakhir.</p>
        <div class="d-flex justify-content-center gap-2">
          <a href="<?= e(BASE_URL) ?>/ppdb" class="btn btn-accent px-4 fw-semibold">
            <i class="bi bi-info-circle me-1"></i> Informasi Jadwal PPDB
          </a>
          <a href="<?= e(BASE_URL) ?>/ppdb/cek-status" class="btn btn-outline-primary-custom px-4 fw-semibold">
            <i class="bi bi-search me-1"></i> Cek Status Pendaftaran
          </a>
        </div>
      </div>
    </section>
    <?php
    require BASE_PATH . '/includes/footer.php';
    exit;
}

$preselectedMajor = (int) ($_GET['jurusan'] ?? 0);
$errors = [];
$formData = [
    'full_name'       => '',
    'nisn'            => '',
    'nik'             => '',
    'gender'          => 'L',
    'birth_place'     => '',
    'birth_date'      => '',
    'religion'        => 'Islam',
    'address'         => '',
    'phone'           => '',
    'email'           => '',
    'school_origin'   => '',
    'graduation_year' => date('Y'),
    'average_score'   => '',
    'parent_name'     => '',
    'parent_phone'    => '',
    'parent_job'      => '',
    'major_choice_1'  => $preselectedMajor ?: ($majors[0]['id'] ?? 0),
    'major_choice_2'  => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = getClientIP();
    $rateKey = 'ppdb_submit_' . md5($ip);
    $remainingLockout = 0;

    if (!checkRateLimit($rateKey, 5, 3600, $remainingLockout)) {
        $mins = ceil($remainingLockout / 60);
        $errors[] = "Anda telah mencapai batas pengiriman formulir. Demi keamanan, silakan tunggu {$mins} menit sebelum mencoba mendaftar kembali.";
    } elseif (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi formulir telah kedaluwarsa, silakan coba kirim ulang formulir.';
    } else {
        $formData['full_name']       = trim($_POST['full_name'] ?? '');
        $formData['nisn']            = trim($_POST['nisn'] ?? '');
        $formData['nik']             = trim($_POST['nik'] ?? '');
        $formData['gender']          = in_array($_POST['gender'] ?? '', ['L','P'], true) ? $_POST['gender'] : 'L';
        $formData['birth_place']     = trim($_POST['birth_place'] ?? '');
        $formData['birth_date']      = trim($_POST['birth_date'] ?? '');
        $formData['religion']        = trim($_POST['religion'] ?? 'Islam');
        $formData['address']         = trim($_POST['address'] ?? '');
        $formData['phone']           = trim($_POST['phone'] ?? '');
        $formData['email']           = trim($_POST['email'] ?? '');
        $formData['school_origin']   = trim($_POST['school_origin'] ?? '');
        $formData['graduation_year'] = trim($_POST['graduation_year'] ?? date('Y'));
        $formData['average_score']   = trim($_POST['average_score'] ?? '');
        $formData['parent_name']     = trim($_POST['parent_name'] ?? '');
        $formData['parent_phone']    = trim($_POST['parent_phone'] ?? '');
        $formData['parent_job']      = trim($_POST['parent_job'] ?? '');
        $formData['major_choice_1']  = (int) ($_POST['major_choice_1'] ?? 0);
        $formData['major_choice_2']  = (int) ($_POST['major_choice_2'] ?? 0) ?: null;

        // Validasi Wajib & Format Ketat
        if ($formData['full_name'] === '')     { $errors[] = 'Nama lengkap calon siswa wajib diisi.'; }
        if ($formData['nisn'] === '')          { $errors[] = 'NISN (10 Digit) wajib diisi.'; }
        elseif (!preg_match('/^[0-9]{10}$/', $formData['nisn'])) { $errors[] = 'Format NISN tidak valid (harus tepat 10 digit angka).'; }

        if ($formData['nik'] === '')           { $errors[] = 'NIK (16 Digit) wajib diisi.'; }
        elseif (!preg_match('/^[0-9]{16}$/', $formData['nik'])) { $errors[] = 'Format NIK tidak valid (harus tepat 16 digit angka).'; }

        if ($formData['birth_place'] === '')   { $errors[] = 'Tempat lahir wajib diisi.'; }
        if ($formData['birth_date'] === '')    { $errors[] = 'Tanggal lahir wajib diisi.'; }
        if ($formData['address'] === '')       { $errors[] = 'Alamat tempat tinggal wajib diisi.'; }
        if ($formData['phone'] === '')         { $errors[] = 'Nomor WhatsApp / HP siswa wajib diisi.'; }
        if ($formData['school_origin'] === '') { $errors[] = 'Nama SMP / MTs asal wajib diisi.'; }
        if ($formData['parent_name'] === '')   { $errors[] = 'Nama Orang Tua / Wali wajib diisi.'; }
        if ($formData['parent_phone'] === '')  { $errors[] = 'Nomor WhatsApp / HP Orang Tua wajib diisi.'; }
        if (!$formData['major_choice_1'])      { $errors[] = 'Pilihan Jurusan Utama wajib dipilih.'; }

        if ($formData['email'] !== '' && !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format alamat email tidak valid.';
        }

        // Cek apakah NISN sudah pernah terdaftar
        if ($formData['nisn'] !== '' && empty($errors)) {
            $checkNisn = $db->prepare("SELECT id, registration_no FROM ppdb_registrations WHERE nisn = ?");
            $checkNisn->execute([$formData['nisn']]);
            if ($existing = $checkNisn->fetch()) {
                $errors[] = 'NISN ' . e($formData['nisn']) . ' sudah terdaftar sebelumnya dengan Nomor Registrasi: ' . e($existing['registration_no']) . '. Silakan periksa status pendaftaran Anda di menu Cek Status.';
            }
        }

        // Upload berkas
        $docPhoto = null;
        $docKk = null;
        $docRapor = null;

        if (!empty($_FILES['document_photo']['name'])) {
            $err = null;
            $docPhoto = handleDocumentUpload($_FILES['document_photo'], $err);
            if (!$docPhoto) { $errors[] = 'Pas Foto: ' . $err; }
        }
        if (!empty($_FILES['document_kk']['name'])) {
            $err = null;
            $docKk = handleDocumentUpload($_FILES['document_kk'], $err);
            if (!$docKk) { $errors[] = 'Kartu Keluarga: ' . $err; }
        }
        if (!empty($_FILES['document_rapor']['name'])) {
            $err = null;
            $docRapor = handleDocumentUpload($_FILES['document_rapor'], $err);
            if (!$docRapor) { $errors[] = 'Rapor / SKL: ' . $err; }
        }

        if (empty($errors)) {
            // Generate nomor registrasi unik: PPDB-YYYYMM-XXXX
            $prefix = 'PPDB-' . date('Ym') . '-';
            $countStmt = $db->query("SELECT COUNT(*) FROM ppdb_registrations");
            $nextNum = ((int)$countStmt->fetchColumn()) + 1001;
            $regNo = $prefix . $nextNum;

            while (true) {
                $checkReg = $db->prepare("SELECT id FROM ppdb_registrations WHERE registration_no = ?");
                $checkReg->execute([$regNo]);
                if (!$checkReg->fetch()) break;
                $nextNum++;
                $regNo = $prefix . $nextNum;
            }

            $avgScore = $formData['average_score'] !== '' ? (float)$formData['average_score'] : null;

            $insertStmt = $db->prepare("
                INSERT INTO ppdb_registrations (
                    registration_no, full_name, nisn, nik, gender, birth_place, birth_date,
                    religion, address, phone, email, school_origin, graduation_year,
                    parent_name, parent_phone, parent_job, major_choice_1, major_choice_2,
                    average_score, document_photo, document_kk, document_rapor, status
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, 'pending'
                )
            ");

            $insertStmt->execute([
                $regNo,
                $formData['full_name'],
                $formData['nisn'],
                $formData['nik'],
                $formData['gender'],
                $formData['birth_place'],
                $formData['birth_date'],
                $formData['religion'],
                $formData['address'],
                $formData['phone'],
                $formData['email'] ?: null,
                $formData['school_origin'],
                $formData['graduation_year'],
                $formData['parent_name'],
                $formData['parent_phone'],
                $formData['parent_job'] ?: null,
                $formData['major_choice_1'],
                $formData['major_choice_2'],
                $avgScore,
                $docPhoto,
                $docKk,
                $docRapor
            ]);

            recordRateLimitAttempt($rateKey, 3600);
            logActivity('PPDB_REGISTER', 'ppdb', 'Pendaftaran PPDB Baru: ' . $formData['full_name'] . ' (No: ' . $regNo . ', NISN: ' . $formData['nisn'] . ')');

            // Send automated WhatsApp confirmation notification if gateway is active
            try {
                $waSettings = getWAGatewaySettings();
                if (!empty($waSettings['is_active']) && !empty($formData['phone'])) {
                    $majorChoiceName = '-';
                    if (!empty($formData['major_choice_1'])) {
                        $mStmt = $db->prepare("SELECT name FROM majors WHERE id = ?");
                        $mStmt->execute([(int)$formData['major_choice_1']]);
                        $majorChoiceName = $mStmt->fetchColumn() ?: '-';
                    }
                    $schoolTitle = $profile['school_name'] ?? 'Sekolah';
                    $printUrl = BASE_URL . '/ppdb/cetak?reg=' . urlencode($regNo);
                    $checkUrl = BASE_URL . '/ppdb/cek-status?reg=' . urlencode($regNo);

                    $template = !empty($waSettings['ppdb_success_template']) ? $waSettings['ppdb_success_template'] : "Halo *{nama_siswa}*, pendaftaran Anda di *{nama_sekolah}* telah berhasil.\nNo: *{no_registrasi}*\nCetak: {link_cetak}";

                    $waMsg = str_replace(
                        ['{nama_siswa}', '{no_registrasi}', '{nama_sekolah}', '{pilihan_jurusan}', '{link_cetak}', '{link_cek}'],
                        [$formData['full_name'], $regNo, $schoolTitle, $majorChoiceName, $printUrl, $checkUrl],
                        $template
                    );

                    sendWhatsAppNotification($formData['phone'], $waMsg);
                }
            } catch (Throwable $e) {
                error_log('PPDB WA Notification Error: ' . $e->getMessage());
            }

            setFlash('success', 'Selamat! Formulir pendaftaran Anda berhasil terkirim.');
            redirect(BASE_URL . '/ppdb/sukses?no=' . urlencode($regNo));
        }
    }
}

$pageTitle       = 'Formulir Pendaftaran PPDB Online ' . ($settings['academic_year'] ?? '');
$pageDescription = 'Formulir Pendaftaran Siswa Baru ' . e($profile['school_name'] ?? '');
$canonicalPath   = '/ppdb/daftar';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small mb-0">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/ppdb">PPDB</a></li>
    <li class="breadcrumb-item active" aria-current="page">Formulir Pendaftaran</li>
  </ol>
</nav>

<section class="section pt-3 pb-5">
  <div class="container" style="max-width: 960px;">
    <!-- Page Header -->
    <div class="text-center mx-auto mb-4 reveal-up" style="max-width: 740px;">
      <h1 class="section-title mb-2">Formulir Pendaftaran Siswa Baru</h1>
      <p class="section-subtitle mx-auto">Tahun Ajaran <?= e($settings['academic_year']) ?> — Lengkapi data calon siswa di bawah ini dengan teliti dan benar sesuai dokumen resmi.</p>
    </div>

    <!-- 5 Step Quick Tracker -->
    <div class="row g-2 mb-4 d-none d-md-flex text-center small fw-semibold">
      <div class="col">
        <div class="p-2.5 rounded-3 bg-white border shadow-xs d-flex align-items-center justify-content-center gap-2 text-primary">
          <span class="badge bg-primary rounded-circle">1</span> Jurusan
        </div>
      </div>
      <div class="col">
        <div class="p-2.5 rounded-3 bg-white border shadow-xs d-flex align-items-center justify-content-center gap-2 text-primary">
          <span class="badge bg-primary rounded-circle">2</span> Data Diri
        </div>
      </div>
      <div class="col">
        <div class="p-2.5 rounded-3 bg-white border shadow-xs d-flex align-items-center justify-content-center gap-2 text-primary">
          <span class="badge bg-primary rounded-circle">3</span> Asal Sekolah
        </div>
      </div>
      <div class="col">
        <div class="p-2.5 rounded-3 bg-white border shadow-xs d-flex align-items-center justify-content-center gap-2 text-primary">
          <span class="badge bg-primary rounded-circle">4</span> Orang Tua
        </div>
      </div>
      <div class="col">
        <div class="p-2.5 rounded-3 bg-white border shadow-xs d-flex align-items-center justify-content-center gap-2 text-primary">
          <span class="badge bg-primary rounded-circle">5</span> Berkas
        </div>
      </div>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-danger shadow-sm mb-4 rounded-4 border-0">
        <div class="fw-bold mb-2 d-flex align-items-center gap-2">
          <i class="bi bi-exclamation-triangle-fill fs-5"></i> Harap periksa kesalahan berikut:
        </div>
        <ul class="mb-0 ps-3 small">
          <?php foreach ($errors as $err): ?>
            <li><?= e($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
      <div class="card-header bg-primary text-white py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-file-earmark-person-fill fs-5 text-warning"></i>
          <h2 class="h6 mb-0 text-white fw-bold">Data Calon Peserta Didik Baru</h2>
        </div>
        <span class="small text-white-50">Tanda bintang (<span class="text-danger fw-bold">*</span>) wajib diisi</span>
      </div>

      <div class="card-body p-3 p-sm-4 p-md-5">
        <form method="post" enctype="multipart/form-data" id="ppdbForm">
          <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

          <!-- Bagian 1: Pilihan Jurusan -->
          <div class="mb-5">
            <h3 class="h6 fw-bold text-primary border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
              <i class="bi bi-mortarboard-fill text-accent"></i> 1. Pilihan Program Keahlian (Jurusan)
            </h3>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Pilihan Jurusan Utama <span class="text-danger">*</span></label>
                <select name="major_choice_1" class="form-select form-select-lg fs-6" required>
                  <option value="">— Pilih Jurusan Utama —</option>
                  <?php foreach ($majors as $m): ?>
                    <option value="<?= (int) $m['id'] ?>" <?= (int) $formData['major_choice_1'] === (int) $m['id'] ? 'selected' : '' ?>>
                      <?= e($m['name']) ?> (<?= e($m['code']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
                <div class="form-text small text-muted">Jurusan prioritas yang paling diminati calon siswa.</div>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold">Pilihan Jurusan Alternatif <span class="text-muted fw-normal">(Opsional)</span></label>
                <select name="major_choice_2" class="form-select form-select-lg fs-6">
                  <option value="">— Tanpa Pilihan Cadangan —</option>
                  <?php foreach ($majors as $m): ?>
                    <option value="<?= (int) $m['id'] ?>" <?= (int) $formData['major_choice_2'] === (int) $m['id'] ? 'selected' : '' ?>>
                      <?= e($m['name']) ?> (<?= e($m['code']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
                <div class="form-text small text-muted">Pilihan kedua jika kuota jurusan utama telah terpenuhi.</div>
              </div>
            </div>
          </div>

          <!-- Bagian 2: Data Pribadi Siswa -->
          <div class="mb-5">
            <h3 class="h6 fw-bold text-primary border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
              <i class="bi bi-person-vcard-fill text-accent"></i> 2. Data Pribadi Calon Siswa
            </h3>
            <div class="row g-3">
              <div class="col-md-12">
                <label class="form-label small fw-semibold">Nama Lengkap Siswa <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control" value="<?= e($formData['full_name']) ?>" required placeholder="Sesuai Akta Kelahiran / Ijazah SMP">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold">NISN (10 Digit Angka) <span class="text-danger">*</span></label>
                <input type="text" name="nisn" class="form-control" value="<?= e($formData['nisn']) ?>" required maxlength="15" placeholder="Contoh: 0081234567">
                <div class="form-text small text-muted">Nomor Induk Siswa Nasional dari sekolah asal.</div>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold">NIK Siswa (16 Digit KTP/KK) <span class="text-danger">*</span></label>
                <input type="text" name="nik" class="form-control" value="<?= e($formData['nik']) ?>" required maxlength="20" placeholder="Nomor Induk Kependudukan di KK">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                <select name="gender" class="form-select" required>
                  <option value="L" <?= $formData['gender'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                  <option value="P" <?= $formData['gender'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold">Tempat Lahir <span class="text-danger">*</span></label>
                <input type="text" name="birth_place" class="form-control" value="<?= e($formData['birth_place']) ?>" required placeholder="Kota / Kabupaten Kelahiran">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
                <input type="date" name="birth_date" class="form-control" value="<?= e($formData['birth_date']) ?>" required>
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold">Agama</label>
                <select name="religion" class="form-select">
                  <?php foreach (['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'] as $rel): ?>
                    <option value="<?= $rel ?>" <?= $formData['religion'] === $rel ? 'selected' : '' ?>><?= $rel ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold">Nomor WhatsApp / HP Siswa <span class="text-danger">*</span></label>
                <input type="tel" name="phone" class="form-control" value="<?= e($formData['phone']) ?>" required placeholder="08xxxxxxxxxx">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold">Alamat Email <span class="text-muted fw-normal">(Opsional)</span></label>
                <input type="email" name="email" class="form-control" value="<?= e($formData['email']) ?>" placeholder="siswa@email.com">
              </div>

              <div class="col-12">
                <label class="form-label small fw-semibold">Alamat Tempat Tinggal Lengkap <span class="text-danger">*</span></label>
                <textarea name="address" rows="2" class="form-control" required placeholder="Jl. Nama Jalan No., RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten"><?= e($formData['address']) ?></textarea>
              </div>
            </div>
          </div>

          <!-- Bagian 3: Asal Sekolah -->
          <div class="mb-5">
            <h3 class="h6 fw-bold text-primary border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
              <i class="bi bi-buildings text-accent"></i> 3. Asal Sekolah &amp; Nilai Rapor
            </h3>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Nama SMP / MTs Asal <span class="text-danger">*</span></label>
                <input type="text" name="school_origin" class="form-control" value="<?= e($formData['school_origin']) ?>" required placeholder="Contoh: SMP Negeri 1 Pekanbaru">
              </div>

              <div class="col-md-3">
                <label class="form-label small fw-semibold">Tahun Lulus <span class="text-danger">*</span></label>
                <input type="text" name="graduation_year" class="form-control" value="<?= e($formData['graduation_year']) ?>" required placeholder="<?= date('Y') ?>">
              </div>

              <div class="col-md-3">
                <label class="form-label small fw-semibold">Rata-rata Nilai Rapor <span class="text-muted fw-normal">(Skala 100)</span></label>
                <input type="number" step="0.01" name="average_score" class="form-control" value="<?= e($formData['average_score']) ?>" placeholder="mis. 86.50">
              </div>
            </div>
          </div>

          <!-- Bagian 4: Data Orang Tua / Wali -->
          <div class="mb-5">
            <h3 class="h6 fw-bold text-primary border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
              <i class="bi bi-people-fill text-accent"></i> 4. Data Orang Tua / Wali
            </h3>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Nama Orang Tua / Wali <span class="text-danger">*</span></label>
                <input type="text" name="parent_name" class="form-control" value="<?= e($formData['parent_name']) ?>" required placeholder="Nama Ayah / Ibu / Wali">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold">Nomor WhatsApp Orang Tua <span class="text-danger">*</span></label>
                <input type="tel" name="parent_phone" class="form-control" value="<?= e($formData['parent_phone']) ?>" required placeholder="08xxxxxxxxxx">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold">Pekerjaan Orang Tua</label>
                <input type="text" name="parent_job" class="form-control" value="<?= e($formData['parent_job']) ?>" placeholder="Wiraswasta / Karyawan / PNS / dll.">
              </div>
            </div>
          </div>

          <!-- Bagian 5: Upload Dokumen Persyaratan -->
          <div class="mb-4">
            <h3 class="h6 fw-bold text-primary border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
              <i class="bi bi-cloud-arrow-up-fill text-accent"></i> 5. Unggah Berkas Persyaratan
            </h3>
            <p class="small text-muted mb-3">Format berkas yang didukung: JPG, PNG, WEBP, atau PDF (Maks. 4MB per berkas).</p>
            
            <div class="row g-3">
              <!-- Pas Foto -->
              <div class="col-md-4">
                <div class="card p-3 border rounded-3 bg-light h-100">
                  <label class="form-label small fw-bold text-dark d-flex align-items-center gap-1.5">
                    <i class="bi bi-camera text-primary"></i> Pas Foto 3x4
                  </label>
                  <input type="file" name="document_photo" class="form-control form-control-sm mb-2" accept=".jpg,.jpeg,.png,.webp" id="photoInput">
                  <div class="form-text small" style="font-size:11px;">Foto berseragam SMP/MTs latar merah atau biru.</div>
                </div>
              </div>

              <!-- Kartu Keluarga -->
              <div class="col-md-4">
                <div class="card p-3 border rounded-3 bg-light h-100">
                  <label class="form-label small fw-bold text-dark d-flex align-items-center gap-1.5">
                    <i class="bi bi-file-earmark-person text-primary"></i> Scan Kartu Keluarga (KK)
                  </label>
                  <input type="file" name="document_kk" class="form-control form-control-sm mb-2" accept=".jpg,.jpeg,.png,.webp,.pdf">
                  <div class="form-text small" style="font-size:11px;">Pastikan NIK dan nama keluarga terbaca jelas.</div>
                </div>
              </div>

              <!-- Rapor / SKL -->
              <div class="col-md-4">
                <div class="card p-3 border rounded-3 bg-light h-100">
                  <label class="form-label small fw-bold text-dark d-flex align-items-center gap-1.5">
                    <i class="bi bi-file-earmark-ruled text-primary"></i> Scan Rapor / SKL / Ijazah
                  </label>
                  <input type="file" name="document_rapor" class="form-control form-control-sm mb-2" accept=".jpg,.jpeg,.png,.webp,.pdf">
                  <div class="form-text small" style="font-size:11px;">Surat Keterangan Lulus atau Ijazah SMP.</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Pernyataan Kebenaran Data -->
          <div class="p-3 bg-light rounded-3 border mb-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="agreementCheck" required>
              <label class="form-check-label small text-muted" for="agreementCheck">
                Saya menyatakan bahwa seluruh data yang diisikan dalam formulir ini adalah benar, sah, dan dapat dipertanggungjawabkan sesuai dokumen aslinya.
              </label>
            </div>
          </div>

          <!-- Tombol Aksi Formulir -->
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 pt-2">
            <a href="<?= e(BASE_URL) ?>/ppdb" class="btn btn-outline-secondary px-4 fw-semibold">
              <i class="bi bi-arrow-left me-1"></i> Batal
            </a>
            <button type="submit" class="btn btn-accent btn-lg px-5 fw-bold shadow-sm">
              <i class="bi bi-send-check me-2"></i> Kirim Formulir Pendaftaran
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Help & Assistance Callout -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-light text-center border">
      <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
        <i class="bi bi-headset fs-5 text-primary"></i>
        <h3 class="h6 fw-bold text-primary mb-0">Butuh Bantuan Pengisian Formulir?</h3>
      </div>
      <p class="small text-muted mb-3 mx-auto" style="max-width:560px;">Jika Anda mengalami kendala teknis saat mengunggah berkas atau mengisi data, hubungi panitia PPDB kami:</p>
      <div>
        <?php if (!empty($profile['whatsapp_ppdb'])): ?>
          <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $profile['whatsapp_ppdb'])) ?>?text=<?= urlencode('Halo Panitia PPDB, saya butuh bantuan mengenai pengisian formulir pendaftaran.') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-success px-3.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5">
            <i class="bi bi-whatsapp"></i> Chat WhatsApp Panitia PPDB
          </a>
        <?php endif; ?>
        <a href="<?= e(BASE_URL) ?>/ppdb/cek-status" class="btn btn-sm btn-outline-primary-custom px-3.5 py-1.5 fw-semibold ms-2">
          <i class="bi bi-search me-1"></i> Cek Status Pendaftaran
        </a>
      </div>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
