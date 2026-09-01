<?php
/**
 * admin/ppdb/detail.php?id=...
 * Detail Pendaftar & Form Verifikasi Status
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $db->prepare("
    SELECT r.*, m1.name AS major1_name, m1.code AS major1_code, m2.name AS major2_name, m2.code AS major2_code
    FROM ppdb_registrations r
    LEFT JOIN majors m1 ON m1.id = r.major_choice_1
    LEFT JOIN majors m2 ON m2.id = r.major_choice_2
    WHERE r.id = ?
");
$stmt->execute([$id]);
$reg = $stmt->fetch();

if (!$reg) {
    setFlash('error', 'Data pendaftar tidak ditemukan.');
    redirect(BASE_URL . '/admin/ppdb/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sesi form tidak valid, silakan coba lagi.';
    } else {
        $status = $_POST['status'] ?? 'pending';
        $notes  = trim($_POST['notes'] ?? '');
        $sendWa = isset($_POST['send_wa']) ? 1 : 0;

        if (!in_array($status, ['pending','verified','accepted','rejected','reserve'], true)) {
            $status = 'pending';
        }

        $update = $db->prepare("UPDATE ppdb_registrations SET status = ?, notes = ? WHERE id = ?");
        $update->execute([$status, $notes, $id]);

        logActivity('UPDATE_PPDB_STATUS', 'ppdb', "Verifikasi status PPDB {$reg['full_name']} ({$reg['registration_no']}) diubah menjadi: $status");

        $waFeedback = '';
        // Send WhatsApp notification if requested
        if ($sendWa && !empty($reg['phone'])) {
            $waSettings = getWAGatewaySettings();
            if (!empty($waSettings['is_active'])) {
                $statusLabels = [
                    'pending'  => 'Pending (Menunggu Verifikasi)',
                    'verified' => 'Terverifikasi (Lolos Berkas)',
                    'accepted' => 'Diterima',
                    'reserve'  => 'Cadangan',
                    'rejected' => 'Ditolak'
                ];
                $statusIndo = $statusLabels[$status] ?? ucfirst($status);
                $schoolProfile = getSchoolProfile();
                $schoolTitle = $schoolProfile['school_name'] ?? 'Sekolah';
                $checkUrl = BASE_URL . '/ppdb/cek-status?reg=' . urlencode($reg['registration_no']);

                $template = !empty($waSettings['ppdb_status_template']) ? $waSettings['ppdb_status_template'] : "Halo *{nama_siswa}* (No: *{no_registrasi}*),\nStatus pendaftaran PPDB Anda di *{nama_sekolah}* saat ini: *{status_baru}*.\nCatatan: {catatan}\nCek: {link_cek}";

                $waMsg = str_replace(
                    ['{nama_siswa}', '{no_registrasi}', '{nama_sekolah}', '{status_baru}', '{catatan}', '{link_cek}'],
                    [$reg['full_name'], $reg['registration_no'], $schoolTitle, $statusIndo, $notes ?: '-', $checkUrl],
                    $template
                );

                $waRes = sendWhatsAppNotification($reg['phone'], $waMsg);
                if ($waRes['status']) {
                    $waFeedback = ' dan notifikasi WhatsApp berhasil dikirim ke nomor siswa (' . e($reg['phone']) . ')';
                } else {
                    $waFeedback = ', namun pengiriman WA gagal: ' . e($waRes['message'] ?? 'Periksa API token');
                }
            }
        }

        setFlash('success', 'Status pendaftaran berhasil diperbarui' . $waFeedback . '.');
        redirect(BASE_URL . '/admin/ppdb/detail.php?id=' . $id);
    }
}

$pageTitle = 'Detail Pendaftar — ' . ($reg['registration_no'] ?? '');
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>

<?= flashMessage() ?>

<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <a href="index.php" class="btn btn-sm btn-outline-secondary mb-2">
      <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar
    </a>
    <h1 class="h4 fw-bold text-primary mb-0">Pendaftar: <?= e($reg['full_name']) ?></h1>
    <span class="text-muted font-monospace small">No. Registrasi: <?= e($reg['registration_no']) ?></span>
  </div>

  <div class="d-flex gap-2">
    <a href="<?= e(BASE_URL) ?>/ppdb/cetak?no=<?= urlencode($reg['registration_no']) ?>" target="_blank" class="btn btn-outline-primary-custom">
      <i class="bi bi-printer me-1"></i>Cetak Kartu Registrasi
    </a>
  </div>
</div>

<div class="row g-4">
  <!-- Kolom Kiri: Biodata Lengkap -->
  <div class="col-lg-8">
    <div class="admin-card mb-4">
      <h2 class="h6 fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-person-vcard me-2"></i>1. Biodata Calon Siswa</h2>
      <div class="table-responsive">
        <table class="table table-bordered mb-0">
          <tbody>
            <tr>
              <th class="bg-light" style="width:30%;">Nama Lengkap</th>
              <td><strong><?= e($reg['full_name']) ?></strong></td>
            </tr>
            <tr>
              <th class="bg-light">NISN / NIK</th>
              <td><?= e($reg['nisn']) ?> / <?= e($reg['nik']) ?></td>
            </tr>
            <tr>
              <th class="bg-light">Jenis Kelamin</th>
              <td><?= $reg['gender'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
            </tr>
            <tr>
              <th class="bg-light">Tempat, Tanggal Lahir</th>
              <td><?= e($reg['birth_place']) ?>, <?= formatTanggalIndo($reg['birth_date']) ?></td>
            </tr>
            <tr>
              <th class="bg-light">Agama</th>
              <td><?= e($reg['religion']) ?></td>
            </tr>
            <tr>
              <th class="bg-light">Alamat Lengkap</th>
              <td><?= nl2br(e($reg['address'])) ?></td>
            </tr>
            <tr>
              <th class="bg-light">Kontak Siswa</th>
              <td>
                <div><i class="bi bi-whatsapp text-success me-1"></i><?= e($reg['phone']) ?></div>
                <?php if ($reg['email']): ?>
                  <div class="small text-muted"><i class="bi bi-envelope me-1"></i><?= e($reg['email']) ?></div>
                <?php endif; ?>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="admin-card mb-4">
      <h2 class="h6 fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-mortarboard me-2"></i>2. Asal Sekolah & Pilihan Jurusan</h2>
      <div class="table-responsive">
        <table class="table table-bordered mb-0">
          <tbody>
            <tr>
              <th class="bg-light" style="width:30%;">Asal SMP / MTs</th>
              <td><?= e($reg['school_origin']) ?> (Tahun Lulus: <?= e($reg['graduation_year']) ?>)</td>
            </tr>
            <tr>
              <th class="bg-light">Nilai Rata-rata</th>
              <td><strong><?= $reg['average_score'] !== null ? number_format($reg['average_score'], 2) : '-' ?></strong></td>
            </tr>
            <tr>
              <th class="bg-light">Pilihan Jurusan 1</th>
              <td><span class="badge bg-primary fs-7"><?= e($reg['major1_code']) ?></span> <?= e($reg['major1_name']) ?></td>
            </tr>
            <?php if ($reg['major2_name']): ?>
            <tr>
              <th class="bg-light">Pilihan Jurusan 2</th>
              <td><span class="badge bg-secondary fs-7"><?= e($reg['major2_code']) ?></span> <?= e($reg['major2_name']) ?></td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="admin-card mb-4">
      <h2 class="h6 fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-people me-2"></i>3. Data Orang Tua / Wali</h2>
      <div class="table-responsive">
        <table class="table table-bordered mb-0">
          <tbody>
            <tr>
              <th class="bg-light" style="width:30%;">Nama Orang Tua/Wali</th>
              <td><?= e($reg['parent_name']) ?></td>
            </tr>
            <tr>
              <th class="bg-light">No. HP/WA Orang Tua</th>
              <td><i class="bi bi-whatsapp text-success me-1"></i><?= e($reg['parent_phone']) ?></td>
            </tr>
            <tr>
              <th class="bg-light">Pekerjaan</th>
              <td><?= e($reg['parent_job'] ?: '-') ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Berkas Dokumen -->
    <div class="admin-card">
      <h2 class="h6 fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-files me-2"></i>4. Berkas Persyaratan</h2>
      <div class="row g-3">
        <div class="col-md-4 text-center">
          <div class="border rounded p-3 bg-light h-100 d-flex flex-column justify-content-between">
            <div>
              <div class="fw-bold small mb-2">Pas Foto 3x4</div>
              <?php if (!empty($reg['document_photo'])): ?>
                <img src="<?= e(BASE_URL . '/' . $reg['document_photo']) ?>" class="img-fluid rounded mb-2" style="max-height: 140px; object-fit: cover;" alt="Pas Foto">
              <?php else: ?>
                <div class="text-muted small py-4">Belum diunggah</div>
              <?php endif; ?>
            </div>
            <?php if (!empty($reg['document_photo'])): ?>
              <a href="<?= e(BASE_URL . '/' . $reg['document_photo']) ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">Lihat Berkas Asli</a>
            <?php endif; ?>
          </div>
        </div>

        <div class="col-md-4 text-center">
          <div class="border rounded p-3 bg-light h-100 d-flex flex-column justify-content-between">
            <div>
              <div class="fw-bold small mb-2">Kartu Keluarga (KK)</div>
              <?php if (!empty($reg['document_kk'])): ?>
                <?php if (str_ends_with(strtolower($reg['document_kk']), '.pdf')): ?>
                  <i class="bi bi-file-earmark-pdf fs-1 text-danger d-block mb-2"></i>
                  <div class="small text-muted">Dokumen PDF</div>
                <?php else: ?>
                  <img src="<?= e(BASE_URL . '/' . $reg['document_kk']) ?>" class="img-fluid rounded mb-2" style="max-height: 140px; object-fit: contain;" alt="Kartu Keluarga">
                <?php endif; ?>
              <?php else: ?>
                <div class="text-muted small py-4">Belum diunggah</div>
              <?php endif; ?>
            </div>
            <?php if (!empty($reg['document_kk'])): ?>
              <a href="<?= e(BASE_URL . '/' . $reg['document_kk']) ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">Lihat Berkas Asli</a>
            <?php endif; ?>
          </div>
        </div>

        <div class="col-md-4 text-center">
          <div class="border rounded p-3 bg-light h-100 d-flex flex-column justify-content-between">
            <div>
              <div class="fw-bold small mb-2">Rapor / SKL / Ijazah</div>
              <?php if (!empty($reg['document_rapor'])): ?>
                <?php if (str_ends_with(strtolower($reg['document_rapor']), '.pdf')): ?>
                  <i class="bi bi-file-earmark-pdf fs-1 text-danger d-block mb-2"></i>
                  <div class="small text-muted">Dokumen PDF</div>
                <?php else: ?>
                  <img src="<?= e(BASE_URL . '/' . $reg['document_rapor']) ?>" class="img-fluid rounded mb-2" style="max-height: 140px; object-fit: contain;" alt="Rapor/SKL">
                <?php endif; ?>
              <?php else: ?>
                <div class="text-muted small py-4">Belum diunggah</div>
              <?php endif; ?>
            </div>
            <?php if (!empty($reg['document_rapor'])): ?>
              <a href="<?= e(BASE_URL . '/' . $reg['document_rapor']) ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">Lihat Berkas Asli</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Kolom Kanan: Verifikasi Status -->
  <div class="col-lg-4">
    <div class="admin-card sticky-top" style="top: 20px;">
      <h2 class="h6 fw-bold text-primary border-bottom pb-2 mb-3">
        <i class="bi bi-check2-circle me-2"></i>Verifikasi & Status
      </h2>

      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

        <div class="mb-3">
          <label class="form-label small fw-semibold">Status Pendaftaran</label>
          <select name="status" class="form-select" required>
            <option value="pending" <?= $reg['status'] === 'pending' ? 'selected' : '' ?>>Pending (Menunggu Verifikasi)</option>
            <option value="verified" <?= $reg['status'] === 'verified' ? 'selected' : '' ?>>Terverifikasi (Lolos Berkas)</option>
            <option value="accepted" <?= $reg['status'] === 'accepted' ? 'selected' : '' ?>>Diterima</option>
            <option value="reserve" <?= $reg['status'] === 'reserve' ? 'selected' : '' ?>>Cadangan</option>
            <option value="rejected" <?= $reg['status'] === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold">Catatan Panitia / Alasan</label>
          <textarea name="notes" rows="4" class="form-control" placeholder="Contoh: Berkas lengkap, menunggu jadwal tes wawancara pada tanggal..."><?= e($reg['notes'] ?? '') ?></textarea>
          <div class="form-text small">Catatan ini akan dapat dilihat oleh calon siswa di menu Cek Status.</div>
        </div>

        <!-- WhatsApp Notification Switch -->
        <div class="mb-4 p-3 rounded-3 bg-light border">
          <div class="form-check form-switch mb-1">
            <input class="form-check-input" type="checkbox" role="switch" id="sendWaSwitch" name="send_wa" value="1" checked>
            <label class="form-check-label small fw-semibold ms-1 text-dark" for="sendWaSwitch">
              <i class="bi bi-whatsapp text-success me-1"></i> Kirim Notifikasi WA Otomatis
            </label>
          </div>
          <div class="small text-muted" style="font-size: 11.5px; line-height: 1.4;">
            Pesan status terbaru akan otomatis dikirimkan ke nomor WhatsApp siswa (<strong><?= e($reg['phone']) ?></strong>).
          </div>
        </div>

        <button type="submit" class="btn btn-accent w-100 py-2.5 fw-semibold rounded-3 mb-2 shadow-xs">
          <i class="bi bi-save me-1"></i>Simpan Perubahan Status
        </button>

        <?php if (!empty($reg['phone'])): ?>
          <a href="https://wa.me/<?= formatWhatsAppNumber($reg['phone']) ?>?text=Halo%20<?= urlencode($reg['full_name']) ?>%2C%20kami%20dari%20Panitia%20PPDB%20<?= urlencode($profile['school_name'] ?? 'Sekolah') ?>%20ingin%20mengonfirmasi%20perihal%20pendaftaran%20Anda." target="_blank" rel="noopener" class="btn btn-outline-success w-100 py-2 fw-semibold rounded-3 btn-sm">
            <i class="bi bi-chat-dots me-1"></i> Chat WhatsApp Siswa
          </a>
        <?php endif; ?>
      </form>

      <hr class="my-4">

      <div class="small text-muted">
        <div class="mb-1"><i class="bi bi-calendar3 me-1"></i>Didaftarkan: <?= date('d/m/Y H:i:s', strtotime($reg['created_at'])) ?></div>
        <div><i class="bi bi-clock me-1"></i>Terakhir Update: <?= date('d/m/Y H:i:s', strtotime($reg['updated_at'])) ?></div>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
