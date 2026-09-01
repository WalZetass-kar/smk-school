<?php
/**
 * admin/guestbook/index.php
 * Kelola Data Buku Tamu Digital
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAuth();

$db = getDB();
$pageTitle = 'Buku Tamu Digital';
$currentAdminNav = 'guestbook';

$q = trim($_GET['q'] ?? '');
$purposeFilter = trim($_GET['purpose'] ?? 'all');

$where = ["1=1"];
$params = [];

if ($q !== '') {
    $where[] = "(name LIKE ? OR institution LIKE ? OR phone LIKE ? OR person_to_meet LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

if ($purposeFilter !== 'all') {
    $where[] = "purpose = ?";
    $params[] = $purposeFilter;
}

$whereSql = implode(' AND ', $where);
$stmt = $db->prepare("SELECT * FROM guestbooks WHERE $whereSql ORDER BY created_at DESC");
$stmt->execute($params);
$guests = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-bold text-primary mb-1"><i class="bi bi-book-half me-2"></i>Buku Tamu Digital</h1>
    <p class="text-muted small mb-0">Daftar rekaman kehadiran kunjungan tamu ke sekolah.</p>
  </div>
  <a href="<?= e(BASE_URL) ?>/buku-tamu" target="_blank" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-box-arrow-up-right me-1"></i>Buka Formulir Tamu
  </a>
</div>

<?= flashMessage() ?>

<!-- FILTER & SEARCH -->
<div class="admin-card mb-4 p-3">
  <form method="get" action="<?= e(BASE_URL) ?>/admin/guestbook/index.php" class="row g-2 align-items-center">
    <div class="col-md-7">
      <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari nama tamu, instansi, no HP, pihak dituju..." value="<?= e($q) ?>">
    </div>
    <div class="col-md-3">
      <select name="purpose" class="form-select form-select-sm">
        <option value="all" <?= $purposeFilter === 'all' ? 'selected' : '' ?>>Semua Keperluan</option>
        <option value="Dinas / Instansi" <?= $purposeFilter === 'Dinas / Instansi' ? 'selected' : '' ?>>Dinas / Instansi</option>
        <option value="Kemitraan Industri" <?= $purposeFilter === 'Kemitraan Industri' ? 'selected' : '' ?>>Kemitraan Industri</option>
        <option value="Orang Tua / Wali Siswa" <?= $purposeFilter === 'Orang Tua / Wali Siswa' ? 'selected' : '' ?>>Orang Tua / Wali Siswa</option>
        <option value="Alumni" <?= $purposeFilter === 'Alumni' ? 'selected' : '' ?>>Alumni</option>
        <option value="Umum" <?= $purposeFilter === 'Umum' ? 'selected' : '' ?>>Umum</option>
      </select>
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
    </div>
  </form>
</div>

<!-- TABLE -->
<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Waktu Kunjungan</th>
          <th>Nama Tamu / Instansi</th>
          <th>Kontak</th>
          <th>Kategori &amp; Dituju</th>
          <th>Maksud &amp; Catatan</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($guests)): ?>
          <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada catatan buku tamu.</td></tr>
        <?php else: ?>
          <?php foreach ($guests as $g): ?>
            <tr>
              <td>
                <div class="fw-bold text-dark"><?= date('d/m/Y', strtotime($g['created_at'])) ?></div>
                <div class="small text-muted"><?= date('H:i', strtotime($g['created_at'])) ?> WIB</div>
              </td>
              <td>
                <div class="fw-bold text-dark"><?= e($g['name']) ?></div>
                <div class="small text-muted"><i class="bi bi-building me-1"></i><?= e($g['institution'] ?? '-') ?></div>
              </td>
              <td>
                <div>
                  <a href="https://wa.me/<?= e(preg_replace('/[^0-9]/', '', $g['phone'])) ?>" target="_blank" class="text-decoration-none fw-semibold text-success small">
                    <i class="bi bi-whatsapp me-1"></i><?= e($g['phone']) ?>
                  </a>
                </div>
                <?php if (!empty($g['email'])): ?>
                  <div class="small text-muted"><?= e($g['email']) ?></div>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-1 d-inline-block"><?= e($g['purpose']) ?></span>
                <div class="small text-muted"><i class="bi bi-person me-1"></i><?= e($g['person_to_meet']) ?></div>
              </td>
              <td>
                <div class="small text-dark" style="max-width:300px;"><?= nl2br(e($g['notes'] ?? '-')) ?></div>
              </td>
              <td class="text-end">
                <form action="<?= e(BASE_URL) ?>/admin/guestbook/delete.php" method="post" class="d-inline" data-confirm-delete="Hapus catatan tamu <?= e($g['name']) ?>?">
                  <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                  <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger p-1 px-2" title="Hapus">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
