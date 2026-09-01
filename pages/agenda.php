<?php
/**
 * pages/agenda.php
 * Kalender & Agenda Kegiatan Sekolah
 */
require_once __DIR__ . '/../config/config.php';
$db = getDB();

$q = trim($_GET['q'] ?? '');
$filter = trim($_GET['filter'] ?? 'all'); // all, upcoming, ongoing, past

$today = date('Y-m-d');
$where = ["status = 1"];
$params = [];

if ($q !== '') {
    $where[] = "(title LIKE ? OR location LIKE ? OR organizer LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

if ($filter === 'upcoming') {
    $where[] = "start_date > ?";
    $params[] = $today;
} elseif ($filter === 'ongoing') {
    $where[] = "start_date <= ? AND (end_date >= ? OR (end_date IS NULL AND start_date = ?))";
    $params[] = $today;
    $params[] = $today;
    $params[] = $today;
} elseif ($filter === 'past') {
    $where[] = "(end_date < ? OR (end_date IS NULL AND start_date < ?))";
    $params[] = $today;
    $params[] = $today;
}

$whereSql = implode(' AND ', $where);
$stmt = $db->prepare("SELECT * FROM agendas WHERE $whereSql ORDER BY start_date ASC");
$stmt->execute($params);
$agendas = $stmt->fetchAll();

$pageTitle = 'Agenda & Kalender Kegiatan Sekolah';
require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<div class="navbar-spacer <?= (!empty($profile['is_running_text_active']) && !empty($profile['running_text'])) ? 'has-ticker' : '' ?>"></div>

<section class="section py-5">
  <div class="container">
    <div class="text-center mx-auto mb-5" style="max-width:760px;">
      <h1 class="section-title mb-2">Agenda &amp; Kegiatan Sekolah</h1>
      <p class="text-muted">Jadwal kegiatan akademik, ujian, perlombaan, dan agenda penting <?= e($profile['school_name'] ?? 'SMK Negeri Contoh') ?>.</p>
    </div>

    <!-- SEARCH & FILTER BAR -->
    <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-5 bg-white">
      <form method="get" action="<?= e(BASE_URL) ?>/agenda" class="row g-3 align-items-center">
        <div class="col-lg-6">
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Cari nama kegiatan, lokasi, penyelenggara..." value="<?= e($q) ?>">
          </div>
        </div>
        <div class="col-md-4 col-lg-4">
          <select name="filter" class="form-select">
            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>Semua Agenda Kegiatan</option>
            <option value="upcoming" <?= $filter === 'upcoming' ? 'selected' : '' ?>>Akan Datang (Mendatang)</option>
            <option value="ongoing" <?= $filter === 'ongoing' ? 'selected' : '' ?>>Sedang Berlangsung</option>
            <option value="past" <?= $filter === 'past' ? 'selected' : '' ?>>Telah Selesai</option>
          </select>
        </div>
        <div class="col-md-2 col-lg-2">
          <button type="submit" class="btn btn-accent w-100 fw-semibold">Filter</button>
        </div>
      </form>
    </div>

    <!-- AGENDAS LIST -->
    <?php if (empty($agendas)): ?>
      <div class="text-center py-5">
        <i class="bi bi-calendar-x display-3 text-muted"></i>
        <h2 class="h5 text-dark mt-3">Tidak Ada Agenda Ditemukan</h2>
        <p class="text-muted small">Silakan ubah filter pencarian atau cek kembali nanti.</p>
        <a href="<?= e(BASE_URL) ?>/agenda" class="btn btn-outline-primary btn-sm mt-2">Reset Filter</a>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($agendas as $item): ?>
          <?php
            $isPast = (!empty($item['end_date']) && $item['end_date'] < $today) || (empty($item['end_date']) && $item['start_date'] < $today);
            $isOngoing = ($item['start_date'] <= $today && (!empty($item['end_date']) && $item['end_date'] >= $today));
            $day = date('d', strtotime($item['start_date']));
            $month = date('M', strtotime($item['start_date']));
            $year = date('Y', strtotime($item['start_date']));
          ?>
          <div class="col-md-6 col-lg-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white hover-lift position-relative">
              <div class="d-flex gap-3 align-items-start">
                <!-- Date Box -->
                <div class="p-3 rounded-3 text-center text-white flex-shrink-0 <?= $isOngoing ? 'bg-success' : ($isPast ? 'bg-secondary' : 'bg-primary') ?>" style="width:75px;">
                  <span class="d-block fs-3 fw-bold lh-1"><?= e($day) ?></span>
                  <span class="small text-uppercase fw-semibold"><?= e($month) ?></span>
                  <span class="d-block" style="font-size:0.7rem;"><?= e($year) ?></span>
                </div>

                <!-- Info Box -->
                <div class="flex-grow-1">
                  <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1">
                    <?php if ($isOngoing): ?>
                      <span class="badge bg-success-subtle text-success border border-success-subtle small"><i class="bi bi-play-circle me-1"></i>Berlangsung</span>
                    <?php elseif ($isPast): ?>
                      <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small"><i class="bi bi-check-circle me-1"></i>Selesai</span>
                    <?php else: ?>
                      <span class="badge bg-primary-subtle text-primary border border-primary-subtle small"><i class="bi bi-calendar-event me-1"></i>Akan Datang</span>
                    <?php endif; ?>
                    <span class="small text-muted"><i class="bi bi-clock me-1"></i><?= e($item['time_info']) ?></span>
                  </div>

                  <h2 class="h5 fw-bold text-dark mb-2">
                    <a href="<?= e(BASE_URL) ?>/agenda/<?= e($item['slug']) ?>" class="text-dark text-decoration-none hover-primary">
                      <?= e($item['title']) ?>
                    </a>
                  </h2>

                  <p class="text-muted small mb-3 line-clamp-2">
                    <?= e(strip_tags($item['description'] ?? '')) ?>
                  </p>

                  <div class="border-top pt-2 d-flex flex-wrap justify-content-between align-items-center text-muted small">
                    <div><i class="bi bi-geo-alt me-1 text-danger"></i><?= e($item['location']) ?></div>
                    <div><i class="bi bi-person-badge me-1 text-primary"></i><?= e($item['organizer']) ?></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
