<?php
/**
 * pages/agenda-detail.php
 * Detail Agenda Kegiatan Sekolah
 */
require_once __DIR__ . '/../config/config.php';
$db = getDB();

$slug = trim($_GET['slug'] ?? '');
$stmt = $db->prepare("SELECT * FROM agendas WHERE slug = ? AND status = 1 LIMIT 1");
$stmt->execute([$slug]);
$agenda = $stmt->fetch();

if (!$agenda) {
    http_response_code(404);
    $pageTitle = 'Agenda Tidak Ditemukan';
    require BASE_PATH . '/includes/header.php';
    require BASE_PATH . '/includes/navbar.php';
    echo '<section class="section text-center"><div class="container"><h1>Agenda Tidak Ditemukan</h1><a href="' . e(BASE_URL) . '/agenda" class="btn btn-accent mt-3">Kembali ke Daftar Agenda</a></div></section>';
    require BASE_PATH . '/includes/footer.php';
    exit;
}

$pageTitle = $agenda['title'] . ' — Agenda Sekolah';
require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>

<section class="section py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/" class="text-decoration-none">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/agenda" class="text-decoration-none">Agenda</a></li>
            <li class="breadcrumb-item active text-truncate" style="max-width:300px;"><?= e($agenda['title']) ?></li>
          </ol>
        </nav>

        <article class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
          <h1 class="h2 fw-bold text-dark mb-3"><?= e($agenda['title']) ?></h1>

          <div class="d-flex flex-wrap gap-3 py-3 border-top border-bottom mb-4 text-muted small">
            <div><i class="bi bi-calendar3 me-1 text-primary"></i><?= date('d F Y', strtotime($agenda['start_date'])) ?><?= !empty($agenda['end_date']) && $agenda['end_date'] !== $agenda['start_date'] ? ' s/d ' . date('d F Y', strtotime($agenda['end_date'])) : '' ?></div>
            <div><i class="bi bi-clock me-1 text-primary"></i><?= e($agenda['time_info']) ?></div>
            <div><i class="bi bi-geo-alt me-1 text-danger"></i><?= e($agenda['location']) ?></div>
            <div><i class="bi bi-person-badge me-1 text-success"></i><?= e($agenda['organizer']) ?></div>
          </div>

          <div class="content-body lh-lg text-dark mb-5">
            <?= nl2br(e($agenda['description'] ?? '')) ?>
          </div>

          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3 border-top">
            <a href="<?= e(BASE_URL) ?>/agenda" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-arrow-left me-1"></i>Kembali ke Semua Agenda
            </a>
            <div class="d-flex gap-2 align-items-center">
              <span class="small text-muted">Bagikan:</span>
              <a href="https://wa.me/?text=<?= urlencode($agenda['title'] . ' ' . BASE_URL . '/agenda/' . $agenda['slug']) ?>" target="_blank" class="btn btn-sm btn-success rounded-circle" title="Share via WhatsApp" style="width:32px;height:32px;padding:0;line-height:32px;text-align:center;">
                <i class="bi bi-whatsapp"></i>
              </a>
            </div>
          </div>
        </article>
      </div>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
