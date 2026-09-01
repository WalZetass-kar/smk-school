<?php
/**
 * admin/ppdb/export.php
 * Export Data Pendaftar PPDB ke Format Microsoft Excel (.xls) Berdesain Resmi & Rapi
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$profile = getSchoolProfile();
$settings = getPpdbSettings();

$statusFilter = $_GET['status'] ?? '';
$majorFilter  = (int) ($_GET['major_id'] ?? 0);
$search       = trim($_GET['q'] ?? '');

$where = [];
$params = [];

if ($statusFilter !== '' && in_array($statusFilter, ['pending','verified','accepted','rejected','reserve'], true)) {
    $where[] = "r.status = ?";
    $params[] = $statusFilter;
}
if ($majorFilter > 0) {
    $where[] = "(r.major_choice_1 = ? OR r.major_choice_2 = ?)";
    $params[] = $majorFilter;
    $params[] = $majorFilter;
}
if ($search !== '') {
    $where[] = "(r.full_name LIKE ? OR r.registration_no LIKE ? OR r.nisn LIKE ? OR r.school_origin LIKE ?)";
    $s = "%$search%";
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "
    SELECT 
        r.registration_no,
        r.full_name,
        r.nisn,
        r.nik,
        CASE WHEN r.gender = 'L' THEN 'Laki-laki' ELSE 'Perempuan' END AS jenis_kelamin,
        r.birth_place,
        r.birth_date,
        r.religion,
        r.address,
        r.phone,
        r.email,
        r.school_origin,
        r.graduation_year,
        r.average_score,
        r.parent_name,
        r.parent_phone,
        r.parent_job,
        m1.name AS pilihan_jurusan_1,
        m1.code AS kode_jurusan_1,
        m2.name AS pilihan_jurusan_2,
        m2.code AS kode_jurusan_2,
        r.status,
        r.notes,
        r.created_at
    FROM ppdb_registrations r
    LEFT JOIN majors m1 ON m1.id = r.major_choice_1
    LEFT JOIN majors m2 ON m2.id = r.major_choice_2
    $whereSql
    ORDER BY r.id DESC
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statusLabels = [
    'pending'  => 'Pending (Menunggu Verifikasi)',
    'verified' => 'Terverifikasi (Lolos Berkas)',
    'accepted' => 'Diterima',
    'reserve'  => 'Cadangan',
    'rejected' => 'Ditolak'
];

$statusColors = [
    'pending'  => '#6c757d',
    'verified' => '#0d6efd',
    'accepted' => '#198754',
    'reserve'  => '#d97706',
    'rejected' => '#dc3545'
];

$statusBadgeBg = [
    'pending'  => '#f1f5f9',
    'verified' => '#e0f2fe',
    'accepted' => '#dcfce7',
    'reserve'  => '#fef3c7',
    'rejected' => '#fee2e2'
];

$filterLabel = $statusFilter !== '' ? ($statusLabels[$statusFilter] ?? ucfirst($statusFilter)) : 'Semua Status Pendaftar';
$schoolName = $profile['school_name'] ?? 'SMK Negeri Contoh Indonesia';
$academicYear = $settings['academic_year'] ?? '2026/2027';

$filename = 'Rekap_PPDB_' . ($statusFilter ? strtoupper($statusFilter) . '_' : '') . date('Ymd_His') . '.xls';

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Pragma: public');
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 11pt;
      color: #1e293b;
    }
    .header-title {
      font-size: 16pt;
      font-weight: bold;
      color: #0f2c59;
      text-align: center;
    }
    .header-sub {
      font-size: 12pt;
      font-weight: bold;
      color: #334155;
      text-align: center;
    }
    .meta-info {
      font-size: 10pt;
      color: #64748b;
    }
    .table-excel {
      border-collapse: collapse;
      width: 100%;
    }
    .table-excel th {
      background-color: #0F2C59;
      color: #ffffff;
      font-weight: bold;
      text-align: center;
      vertical-align: middle;
      border: 1px solid #000000;
      padding: 8px 10px;
      font-size: 11pt;
    }
    .table-excel td {
      border: 1px solid #cbd5e1;
      padding: 6px 8px;
      vertical-align: middle;
      font-size: 10pt;
    }
    .row-even {
      background-color: #ffffff;
    }
    .row-odd {
      background-color: #f8fafc;
    }
    .text-center {
      text-align: center;
    }
    .text-right {
      text-align: right;
    }
    .text-left {
      text-align: left;
    }
    .str-format {
      mso-number-format: "\@";
    }
    .num-format {
      mso-number-format: "0\.00";
    }
    .date-format {
      mso-number-format: "yyyy\-mm\-dd";
    }
  </style>
</head>
<body>
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td colspan="19" class="header-title"><?= strtoupper(htmlspecialchars($schoolName)) ?></td>
    </tr>
    <tr>
      <td colspan="19" class="header-sub">REKAPITULASI DATA PENDAFTARAN PPDB ONLINE TAHUN AJARAN <?= htmlspecialchars($academicYear) ?></td>
    </tr>
    <tr>
      <td colspan="19" class="text-center meta-info">Filter: <strong><?= htmlspecialchars($filterLabel) ?></strong> | Waktu Cetak: <?= date('d F Y, H:i:s') ?> WIB | Total Pendaftar: <strong><?= count($rows) ?> Siswa</strong></td>
    </tr>
    <tr><td colspan="19"></td></tr>
  </table>

  <table class="table-excel" border="1" cellpadding="6" cellspacing="0">
    <thead>
      <tr>
        <th style="width: 40px;">No.</th>
        <th style="width: 140px;">No. Registrasi</th>
        <th style="width: 220px;">Nama Lengkap Siswa</th>
        <th style="width: 120px;">NISN</th>
        <th style="width: 150px;">NIK</th>
        <th style="width: 90px;">L/P</th>
        <th style="width: 140px;">Tempat Lahir</th>
        <th style="width: 110px;">Tanggal Lahir</th>
        <th style="width: 100px;">Agama</th>
        <th style="width: 250px;">Alamat Lengkap</th>
        <th style="width: 130px;">No. WhatsApp</th>
        <th style="width: 180px;">Email</th>
        <th style="width: 200px;">Asal Sekolah (SMP/MTs)</th>
        <th style="width: 90px;">Tahun Lulus</th>
        <th style="width: 90px;">Rata-rata</th>
        <th style="width: 180px;">Pilihan Jurusan 1</th>
        <th style="width: 180px;">Pilihan Jurusan 2</th>
        <th style="width: 180px;">Nama Orang Tua / Wali</th>
        <th style="width: 130px;">No. HP Orang Tua</th>
        <th style="width: 140px;">Status Pendaftaran</th>
        <th style="width: 220px;">Catatan Panitia</th>
        <th style="width: 140px;">Waktu Daftar</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr>
          <td colspan="22" class="text-center" style="padding: 20px; color: #64748b;">Tidak ada data pendaftar yang sesuai dengan filter.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($rows as $i => $r): ?>
          <?php 
            $statusKey = $r['status'] ?? 'pending';
            $statusTxt = $statusLabels[$statusKey] ?? ucfirst($statusKey);
            $stColor   = $statusColors[$statusKey] ?? '#334155';
            $stBg      = $statusBadgeBg[$statusKey] ?? '#ffffff';
            $rowClass  = ($i % 2 === 0) ? 'row-even' : 'row-odd';
          ?>
          <tr class="<?= $rowClass ?>">
            <td class="text-center"><?= $i + 1 ?></td>
            <td class="text-center str-format" style="font-weight: bold; color: #0F2C59;"><?= htmlspecialchars($r['registration_no']) ?></td>
            <td class="text-left" style="font-weight: bold;"><?= htmlspecialchars($r['full_name']) ?></td>
            <td class="text-center str-format"><?= htmlspecialchars($r['nisn']) ?></td>
            <td class="text-center str-format"><?= htmlspecialchars($r['nik']) ?></td>
            <td class="text-center"><?= htmlspecialchars($r['jenis_kelamin']) ?></td>
            <td class="text-left"><?= htmlspecialchars($r['birth_place']) ?></td>
            <td class="text-center date-format"><?= htmlspecialchars($r['birth_date']) ?></td>
            <td class="text-center"><?= htmlspecialchars($r['religion']) ?></td>
            <td class="text-left"><?= htmlspecialchars($r['address']) ?></td>
            <td class="text-left str-format"><?= htmlspecialchars($r['phone']) ?></td>
            <td class="text-left"><?= htmlspecialchars($r['email'] ?? '-') ?></td>
            <td class="text-left"><?= htmlspecialchars($r['school_origin']) ?></td>
            <td class="text-center str-format"><?= htmlspecialchars($r['graduation_year']) ?></td>
            <td class="text-center num-format"><?= $r['average_score'] !== null ? number_format((float)$r['average_score'], 2) : '-' ?></td>
            <td class="text-left"><?= htmlspecialchars($r['pilihan_jurusan_1'] ?? '-') ?></td>
            <td class="text-left"><?= htmlspecialchars($r['pilihan_jurusan_2'] ?? '-') ?></td>
            <td class="text-left"><?= htmlspecialchars($r['parent_name']) ?></td>
            <td class="text-left str-format"><?= htmlspecialchars($r['parent_phone']) ?></td>
            <td class="text-center" style="background-color: <?= $stBg ?>; color: <?= $stColor ?>; font-weight: bold; border: 1px solid <?= $stColor ?>;">
              <?= htmlspecialchars($statusTxt) ?>
            </td>
            <td class="text-left"><?= htmlspecialchars($r['notes'] ?? '-') ?></td>
            <td class="text-center"><?= !empty($r['created_at']) ? date('d/m/Y H:i', strtotime($r['created_at'])) : '-' ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
<?php exit; ?>
