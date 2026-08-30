<?php
/**
 * pages/ppdb-daftar.php — /ppdb/daftar
 * Formulir Pendaftaran Online PPDB
 */
$db       = getDB();
$profile  = getSchoolProfile();
$settings = getPpdbSettings();
$majors   = $db->query("SELECT * FROM majors WHERE status = 1 ORDER BY id ASC")->fetchAll();

if (empty($settings['is_open'])) {
    $pageTitle = 'Pendaftaran Ditutup';
    require BASE_PATH . '/includes/header.php';
    require BASE_PATH . '/includes/navbar.php';
    echo '<section class="section text-center"><div class="container py-5"><h1 class="text-primary mb-3">Pendaftaran PPDB Ditutup</h1><p class="section-subtitle mx-auto mb-4">Mohon maaf, saat ini masa pendaftaran peserta didik baru belum dibuka atau telah berakhir.</p><a href="' . e(BASE_URL) . '/ppdb" class="btn btn-accent">Kembali ke Info PPDB</a></div></section>';
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
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form telah kedaluwarsa, silakan ulangi submit form.';
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

        // Validasi wajib
        if ($formData['full_name'] === '')     { $errors[] = 'Nama lengkap calon siswa wajib diisi.'; }
        if ($formData['nisn'] === '')          { $errors[] = 'NISN wajib diisi.'; }
        if ($formData['nik'] === '')           { $errors[] = 'NIK wajib diisi.'; }
        if ($formData['birth_place'] === '')   { $errors[] = 'Tempat lahir wajib diisi.'; }
        if ($formData['birth_date'] === '')    { $errors[] = 'Tanggal lahir wajib diisi.'; }
        if ($formData['address'] === '')       { $errors[] = 'Alamat tempat tinggal wajib diisi.'; }
        if ($formData['phone'] === '')         { $errors[] = 'Nomor WhatsApp / HP calon siswa wajib diisi.'; }
        if ($formData['school_origin'] === '') { $errors[] = 'Nama sekolah asal (SMP/MTs) wajib diisi.'; }
        if ($formData['parent_name'] === '')   { $errors[] = 'Nama Orang Tua / Wali wajib diisi.'; }
        if ($formData['parent_phone'] === '')  { $errors[] = 'Nomor HP/WA Orang Tua wajib diisi.'; }
        if (!$formData['major_choice_1'])      { $errors[] = 'Pilihan Jurusan 1 wajib dipilih.'; }

        // Cek apakah NISN sudah pernah terdaftar
        if ($formData['nisn'] !== '') {
            $checkNisn = $db->prepare("SELECT id, registration_no FROM ppdb_registrations WHERE nisn = ?");
            $checkNisn->execute([$formData['nisn']]);
            if ($existing = $checkNisn->fetch()) {
                $errors[] = 'NISN ' . e($formData['nisn']) . ' sudah terdaftar sebelumnya dengan Nomor Registrasi: ' . e($existing['registration_no']) . '. Silakan cek status pendaftaran Anda.';
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
            if (!$docRapor) { $errors[] = 'Rapor/SKL: ' . $err; }
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

            setFlash('success', 'Selamat! Pendaftaran Anda berhasil dikirim.');
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

<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/">Beranda</a></li>
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/ppdb">PPDB</a></li>
    <li class="breadcrumb-item active" aria-current="page">Formulir Pendaftaran</li>
  </ol>
</nav>

<section class="section pt-3 pb-5">
  <div class="container" style="max-width: 920px;">
    <div class="text-center mb-4">
      <h1 class="section-title mb-2">Pendaftaran Peserta Didik Baru</h1>
      <p class="text-muted">Tahun Ajaran <?= e($settings['academic_year']) ?></p>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-danger shadow-sm mb-4">
        <div class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Harap periksa kesalahan berikut:</div>
        <ul class="mb-0 ps-3">
          <?php foreach ($errors as $err): ?>
            <li><?= e($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="card-header bg-primary text-white py-3 px-4">
        <h2 class="h6 mb-0 text-white"><i class="bi bi-file-earmark-person me-2"></i>Isi Data Calon Peserta Didik</h2>
      </div>

      <div class="card-body p-4 p-md-5">
        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

          <!-- Bagian 1: Pilihan Jurusan -->
          <div class="mb-4">
            <h3 class="h6 fw-bold text-primary border-bottom pb-2 mb-3">
              <i class="bi bi-mortarboard me-2"></i>1. Pilihan Program Keahlian (Jurusan)
            </h3>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Pilihan Jurusan 1 <span class="text-danger">*</span></label>
                <select name="major_choice_1" class="form-select" required>
                  <option value="">— Pilih Jurusan Utama —</option>
                  <?php foreach ($majors as $m): ?>
                    <option value="<?= (int) $m['id'] ?>" <?= (int) $formData['major_choice_1'] === (int) $m['id'] ? 'selected' : '' ?>>
                      <?= e($m['name']) ?> (<?= e($m['code']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold">Pilihan Jurusan 2 <span class="text-muted fw-normal">(Cadangan / Opsional)</span></label>
                <select name="major_choice_2" class="form-select">
                  <option value="">— Tanpa Pilihan Kedua —</option>
                  <?php foreach ($majors as $m): ?>
                    <option value="<?= (int) $m['id'] ?>" <?= (int) $formData['major_choice_2'] === (int) $m['id'] ? 'selected' : '' ?>>
                      <?= e($m['name']) ?> (<?= e($m['code']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <!-- Bagian 2: Data Diri Siswa -->
          <div class="mb-4">
            <h3 class="h6 fw-bold text-primary border-bottom pb-2 mb-3">
              <i class="bi bi-person-vcard me-2"></i>2. Data Pribadi Calon Siswa
            </h3>
            <div class="row g-3">
              <div class="col-md-12">
                <label class="form-label small fw-semibold">Nama Lengkap (Sesuai Ijazah SMP/Akta) <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control" value="<?= e($formData['full_name']) ?>" required placeholder="Contoh: Muhammad Rizky Pratama">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold">NISN (10 Digit) <span class="text-danger">*</span></label>
                <input type="text" name="nisn" class="form-control" value="<?= e($formData['nisn']) ?>" required maxlength="20" placeholder="Contoh: 0081234567">
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold">NIK / No. KTP Siswa (16 Digit) <span class="text-danger">*</span></label>
                <input type="text" name="nik" class="form-control" value="<?= e($formData['nik']) ?>" required maxlength="25" placeholder="Contoh: 1471012304080001">
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
                <input type="text" name="birth_place" class="form-control" value="<?= e($formData['birth_place']) ?>" required placeholder="Contoh: Pekanbaru">
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
                <input type="tel" name="phone" class="form-control" value="<?= e($formData['phone']) ?>" required placeholder="Contoh: 081234567890">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold">Email <span class="text-muted fw-normal">(Opsional)</span></label>
                <input type="email" name="email" class="form-control" value="<?= e($formData['email']) ?>" placeholder="siswa@gmail.com">
              </div>

              <div class="col-12">
                <label class="form-label small fw-semibold">Alamat Tempat Tinggal Lengkap <span class="text-danger">*</span></label>
                <textarea name="address" rows="2" class="form-control" required placeholder="Jl. Nama Jalan No. RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten"><?= e($formData['address']) ?></textarea>
              </div>
            </div>
          </div>

          <!-- Bagian 3: Data Asal Sekolah -->
          <div class="mb-4">
            <h3 class="h6 fw-bold text-primary border-bottom pb-2 mb-3">
              <i class="bi bi-building me-2"></i>3. Asal Sekolah & Nilai
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
                <label class="form-label small fw-semibold">Rata-rata Nilai <span class="text-muted fw-normal">(Skala 100)</span></label>
                <input type="number" step="0.01" name="average_score" class="form-control" value="<?= e($formData['average_score']) ?>" placeholder="mis. 85.50">
              </div>
            </div>
          </div>

          <!-- Bagian 4: Data Orang Tua / Wali -->
          <div class="mb-4">
            <h3 class="h6 fw-bold text-primary border-bottom pb-2 mb-3">
              <i class="bi bi-people me-2"></i>4. Data Orang Tua / Wali
            </h3>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Nama Orang Tua / Wali <span class="text-danger">*</span></label>
                <input type="text" name="parent_name" class="form-control" value="<?= e($formData['parent_name']) ?>" required placeholder="Nama Ayah / Ibu / Wali">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold">Nomor WhatsApp / HP Orang Tua <span class="text-danger">*</span></label>
                <input type="tel" name="parent_phone" class="form-control" value="<?= e($formData['parent_phone']) ?>" required placeholder="08xxxxxxxxxx">
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold">Pekerjaan Orang Tua</label>
                <input type="text" name="parent_job" class="form-control" value="<?= e($formData['parent_job']) ?>" placeholder="PNS / Wiraswasta / Karyawan / dll.">
              </div>
            </div>
          </div>

          <!-- Bagian 5: Upload Dokumen Persyaratan -->
          <div class="mb-4">
            <h3 class="h6 fw-bold text-primary border-bottom pb-2 mb-3">
              <i class="bi bi-cloud-arrow-up me-2"></i>5. Unggah Berkas Persyaratan <span class="text-muted fw-normal fs-7">(Maks. 4MB / file, format JPG, PNG, WEBP, atau PDF)</span>
            </h3>
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label small fw-semibold">Pas Foto 3x4 Terbaru</label>
                <input type="file" name="document_photo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                <div class="form-text small">Foto berseragam SMP/MTs latar merah/biru.</div>
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold">Foto / Scan Kartu Keluarga (KK)</label>
                <input type="file" name="document_kk" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf">
                <div class="form-text small">Pastikan NIK dan nama terbaca jelas.</div>
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-semibold">Scan Rapor / SKL / Ijazah</label>
                <input type="file" name="document_rapor" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf">
                <div class="form-text small">Surat Keterangan Lulus atau Ijazah SMP.</div>
              </div>
            </div>
          </div>

          <!-- Tombol Kirim -->
          <div class="pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-3">
            <a href="<?= e(BASE_URL) ?>/ppdb" class="btn btn-outline-secondary px-4">
              <i class="bi bi-arrow-left me-1"></i>Batal
            </a>
            <button type="submit" class="btn btn-accent btn-lg px-5 shadow-sm">
              <i class="bi bi-send-check me-2"></i>Kirim Pendaftaran
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
