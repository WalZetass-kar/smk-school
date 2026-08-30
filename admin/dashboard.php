<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

$db = getDB();
$profile = getSchoolProfile();
$ppdbSettings = getPpdbSettings();

// 1. Statistics Counts
$stats = [
    'Total Berita'     => ['count' => (int) $db->query("SELECT COUNT(*) FROM news")->fetchColumn(), 'icon' => 'bi-newspaper', 'url' => BASE_URL . '/admin/news/index.php', 'color' => '#2563EB'],
    'Pendaftar PPDB'   => ['count' => (int) $db->query("SELECT COUNT(*) FROM ppdb_registrations")->fetchColumn(), 'icon' => 'bi-person-lines-fill', 'url' => BASE_URL . '/admin/ppdb/index.php', 'color' => '#10B981'],
    'Lowongan BKK'     => ['count' => (int) $db->query("SELECT COUNT(*) FROM bkk_jobs WHERE status = 'open'")->fetchColumn(), 'icon' => 'bi-briefcase', 'url' => BASE_URL . '/admin/bkk/index.php', 'color' => '#F59E0B'],
    'Prestasi Siswa'   => ['count' => (int) $db->query("SELECT COUNT(*) FROM achievements")->fetchColumn(), 'icon' => 'bi-trophy', 'url' => BASE_URL . '/admin/achievements/index.php', 'color' => '#8B5CF6'],
    'Ekstrakurikuler'  => ['count' => (int) $db->query("SELECT COUNT(*) FROM extracurriculars WHERE status = 1")->fetchColumn(), 'icon' => 'bi-activity', 'url' => BASE_URL . '/admin/extracurriculars/index.php', 'color' => '#EC4899'],
    'Total Unduhan'    => ['count' => (int) $db->query("SELECT COUNT(*) FROM downloads")->fetchColumn(), 'icon' => 'bi-download', 'url' => BASE_URL . '/admin/downloads/index.php', 'color' => '#06B6D4'],
    'Total Guru'       => ['count' => (int) $db->query("SELECT COUNT(*) FROM teachers WHERE category = 'guru'")->fetchColumn(), 'icon' => 'bi-person-workspace', 'url' => BASE_URL . '/admin/teachers/index.php', 'color' => '#3B82F6'],
    'Pesan Masuk'      => ['count' => (int) $db->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn(), 'icon' => 'bi-envelope', 'url' => BASE_URL . '/admin/contacts/index.php', 'color' => '#EF4444'],
];

// 2. Monthly News for Chart (Last 6 Months)
$monthly = $db->query("
    SELECT DATE_FORMAT(published_at, '%Y-%m') AS ym, COUNT(*) AS total
    FROM news WHERE published_at IS NOT NULL
    GROUP BY ym ORDER BY ym DESC LIMIT 6
")->fetchAll();
$monthly = array_reverse($monthly);
$chartLabels = array_map(fn($m) => formatBulanSingkat($m['ym']), $monthly);
$chartData   = array_map(fn($m) => (int) $m['total'], $monthly);

function formatBulanSingkat(string $ym): string
{
    $bulan = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'Mei','06'=>'Jun','07'=>'Jul','08'=>'Agu','09'=>'Sep','10'=>'Okt','11'=>'Nov','12'=>'Des'];
    [$y, $m] = explode('-', $ym);
    return ($bulan[$m] ?? $m) . ' ' . substr($y, 2);
}

// 3. PPDB by Major for Doughnut Chart
$ppdbByMajor = $db->query("
    SELECT m.name AS major_name, COUNT(r.id) AS total
    FROM majors m
    LEFT JOIN ppdb_registrations r ON r.major_choice_1 = m.id
    GROUP BY m.id, m.name
    ORDER BY total DESC, m.id ASC
")->fetchAll(PDO::FETCH_ASSOC);

$donutLabels = array_column($ppdbByMajor, 'major_name');
$donutData   = array_map('intval', array_column($ppdbByMajor, 'total'));

// 4. Recent PPDB Registrations (Top 5)
$recentPPDB = $db->query("
    SELECT r.id, r.registration_no, r.full_name, r.school_origin, r.status, r.created_at, m.name AS major_name
    FROM ppdb_registrations r
    LEFT JOIN majors m ON r.major_choice_1 = m.id
    ORDER BY r.id DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// 5. Recent Contact Messages (Top 5)
$recentContacts = $db->query("
    SELECT id, name, email, subject, message, is_read, created_at
    FROM contacts
    ORDER BY is_read ASC, id DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Helper for Greeting
$hour = (int) date('H');
if ($hour >= 4 && $hour < 11) {
    $greeting = 'Selamat Pagi';
} elseif ($hour >= 11 && $hour < 15) {
    $greeting = 'Selamat Siang';
} elseif ($hour >= 15 && $hour < 18) {
    $greeting = 'Selamat Sore';
} else {
    $greeting = 'Selamat Malam';
}

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
require __DIR__ . '/includes/topbar.php';
?>

<?= flashMessage() ?>

<!-- 1. Hero Greeting & Quick Actions Banner -->
<div class="admin-card p-4 mb-4" style="background: linear-gradient(135deg, #0F2C59 0%, #183E7A 100%); color: #ffffff; border: none; border-radius: var(--radius);">
  <div class="row align-items-center g-3">
    <div class="col-lg-7">
      <div class="d-flex align-items-center gap-2 mb-2">
        <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill fw-semibold" style="font-size:0.75rem;">
          <i class="bi bi-clock-history me-1"></i> <?= date('d F Y') ?>
        </span>
        <?php if (!empty($ppdbSettings['is_open'])): ?>
          <span class="badge bg-success text-white px-2.5 py-1 rounded-pill fw-semibold" style="font-size:0.75rem;">
            <i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i> PPDB <?= e($ppdbSettings['academic_year'] ?? '') ?>: DIBUKA
          </span>
        <?php else: ?>
          <span class="badge bg-secondary text-white px-2.5 py-1 rounded-pill fw-semibold" style="font-size:0.75rem;">
            PPDB: Ditutup
          </span>
        <?php endif; ?>
      </div>

      <h1 class="h3 fw-bold mb-1 text-white"><?= $greeting ?>, <?= e($_SESSION['admin_name'] ?? 'Admin') ?>! <i class="bi bi-person-check-fill ms-1 text-warning"></i></h1>
      <p class="text-white-50 small mb-0">
        Selamat datang di pusat kendali website <strong><?= e($profile['school_name'] ?? 'SMK') ?></strong>. Semua sistem berjalan normal.
      </p>
    </div>

    <!-- Quick Action Shortcut Buttons -->
    <div class="col-lg-5 text-lg-end">
      <div class="d-inline-flex flex-wrap gap-2 justify-content-lg-end">
        <a href="<?= e(BASE_URL) ?>/admin/news/form.php" class="btn btn-sm btn-accent fw-semibold shadow-sm">
          <i class="bi bi-pencil-square me-1"></i> Tulis Berita
        </a>
        <a href="<?= e(BASE_URL) ?>/admin/ppdb/index.php" class="btn btn-sm btn-light fw-semibold shadow-sm text-dark">
          <i class="bi bi-person-lines-fill me-1 text-primary"></i> Data PPDB
        </a>
        <a href="<?= e(BASE_URL) ?>/admin/bkk/form.php" class="btn btn-sm btn-outline-light fw-semibold">
          <i class="bi bi-plus-circle me-1"></i> Lowongan BKK
        </a>
      </div>
    </div>
  </div>
</div>

<!-- 2. Database Backup Reminder (Super Admin) -->
<?php if (isSuperAdmin()): ?>
<div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 border-0 shadow-sm rounded-3" style="background:#e8f4fd;">
  <div class="d-flex align-items-center gap-3">
    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
      <i class="bi bi-shield-check fs-4"></i>
    </div>
    <div>
      <div class="fw-bold text-primary">Pemeliharaan &amp; Cadangan Database</div>
      <div class="small text-muted">Amankan seluruh data pendaftar PPDB, berita, dan konfigurasi sekolah dengan backup rutin.</div>
    </div>
  </div>
  <a href="<?= e(BASE_URL) ?>/admin/backup.php" class="btn btn-sm btn-primary fw-semibold d-inline-flex align-items-center gap-1">
    <i class="bi bi-database-down"></i> Unduh Cadangan SQL
  </a>
</div>
<?php endif; ?>

<!-- 3. Interactive Stat Cards Grid -->
<div class="row g-3 mb-4">
  <?php foreach ($stats as $label => $item): ?>
    <div class="col-xl-3 col-md-6">
      <a href="<?= e($item['url']) ?>" class="text-decoration-none">
        <div class="stat-card">
          <div class="stat-icon" style="background-color: <?= $item['color'] ?>15; color: <?= $item['color'] ?>;">
            <i class="bi <?= $item['icon'] ?>"></i>
          </div>
          <div>
            <div class="stat-value"><?= number_format($item['count'], 0, ',', '.') ?></div>
            <div class="stat-label"><?= e($label) ?></div>
          </div>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<!-- 4. Dual Charts: Monthly News & PPDB Distribution -->
<div class="row g-4 mb-4">
  <!-- Chart 1: Berita per Bulan -->
  <div class="col-lg-7">
    <div class="admin-card h-100 mb-0">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h2 class="h6 fw-bold text-primary mb-1">
            <i class="bi bi-bar-chart-line text-accent me-1"></i> Tren Publikasi Berita
          </h2>
          <span class="text-muted small">Jumlah artikel &amp; informasi yang diterbitkan per bulan</span>
        </div>
        <a href="<?= e(BASE_URL) ?>/admin/news/index.php" class="btn btn-sm btn-link text-decoration-none p-0 text-primary small">Lihat Semua →</a>
      </div>
      <div style="height: 250px;">
        <canvas id="newsChart"></canvas>
      </div>
    </div>
  </div>

  <!-- Chart 2: Pendaftar PPDB per Jurusan -->
  <div class="col-lg-5">
    <div class="admin-card h-100 mb-0">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h2 class="h6 fw-bold text-primary mb-1">
            <i class="bi bi-pie-chart text-accent me-1"></i> Peminat Jurusan PPDB
          </h2>
          <span class="text-muted small">Pilihan program keahlian pendaftar baru</span>
        </div>
        <a href="<?= e(BASE_URL) ?>/admin/ppdb/index.php" class="btn btn-sm btn-link text-decoration-none p-0 text-primary small">Detail →</a>
      </div>
      <div style="height: 250px; position: relative;">
        <canvas id="majorChart"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- 5. Recent Activity Feeds (PPDB & Contact Messages) -->
<div class="row g-4">
  <!-- 5.1 Recent PPDB Registrations -->
  <div class="col-lg-7">
    <div class="admin-card h-100 mb-0">
      <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
        <div>
          <h2 class="h6 fw-bold text-primary mb-0">
            <i class="bi bi-person-lines-fill text-accent me-1"></i> Pendaftar PPDB Terkini
          </h2>
        </div>
        <a href="<?= e(BASE_URL) ?>/admin/ppdb/index.php" class="btn btn-sm btn-outline-primary-custom" style="font-size:0.78rem;">
          Semua Pendaftar (<?= $stats['Pendaftar PPDB']['count'] ?>)
        </a>
      </div>

      <?php if (!empty($recentPPDB)): ?>
        <div class="table-responsive">
          <table class="table table-admin table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>No. Reg &amp; Nama</th>
                <th>Pilihan Jurusan</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentPPDB as $p): ?>
                <tr>
                  <td>
                    <div class="fw-bold text-dark"><?= e($p['full_name']) ?></div>
                    <div class="small text-muted font-monospace" style="font-size:0.75rem;"><?= e($p['registration_no']) ?> &middot; <?= e($p['school_origin']) ?></div>
                  </td>
                  <td>
                    <span class="badge bg-light text-dark border"><?= e($p['major_name'] ?? '-') ?></span>
                  </td>
                  <td>
                    <?php if ($p['status'] === 'accepted'): ?>
                      <span class="badge badge-status-accepted">Diterima</span>
                    <?php elseif ($p['status'] === 'verified'): ?>
                      <span class="badge bg-info-subtle text-info border border-info-subtle">Terverifikasi</span>
                    <?php elseif ($p['status'] === 'rejected'): ?>
                      <span class="badge badge-status-rejected">Ditolak</span>
                    <?php elseif ($p['status'] === 'reserve'): ?>
                      <span class="badge badge-status-reserve">Cadangan</span>
                    <?php else: ?>
                      <span class="badge badge-status-pending">Menunggu</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-end">
                    <a href="<?= e(BASE_URL) ?>/admin/ppdb/detail.php?id=<?= (int) $p['id'] ?>" class="btn btn-sm btn-outline-primary-custom py-0 px-2" title="Detail Siswa" style="font-size:0.75rem;">
                      Detail
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="text-center py-4 text-muted">
          <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
          Belum ada data pendaftar PPDB baru.
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- 5.2 Recent Contact Messages -->
  <div class="col-lg-5">
    <div class="admin-card h-100 mb-0">
      <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
        <div>
          <h2 class="h6 fw-bold text-primary mb-0">
            <i class="bi bi-envelope-paper text-accent me-1"></i> Pesan Masuk Terbaru
          </h2>
        </div>
        <a href="<?= e(BASE_URL) ?>/admin/contacts/index.php" class="btn btn-sm btn-outline-primary-custom" style="font-size:0.78rem;">
          Buka Kotak Masuk
        </a>
      </div>

      <?php if (!empty($recentContacts)): ?>
        <div class="list-group list-group-flush">
          <?php foreach ($recentContacts as $c): ?>
            <a href="<?= e(BASE_URL) ?>/admin/contacts/index.php" class="list-group-item list-group-item-action px-0 py-2.5 border-bottom text-decoration-none">
              <div class="d-flex justify-content-between align-items-start mb-1">
                <div class="fw-semibold text-dark small d-flex align-items-center gap-1.5">
                  <?php if (empty($c['is_read'])): ?>
                    <span class="badge bg-danger rounded-circle p-1" title="Belum dibaca"></span>
                  <?php endif; ?>
                  <?= e($c['name']) ?>
                </div>
                <span class="text-muted" style="font-size:0.72rem;"><?= date('d/m/y H:i', strtotime($c['created_at'])) ?></span>
              </div>
              <div class="text-primary small fw-semibold text-truncate"><?= e($c['subject']) ?></div>
              <div class="text-muted small text-truncate" style="font-size:0.78rem;"><?= e($c['message'] ?? '') ?></div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-center py-4 text-muted">
          <i class="bi bi-chat-square-dots fs-2 d-block mb-2 text-muted"></i>
          Tidak ada pesan masuk baru.
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Chart.js Engine -->
<script src="<?= e(ASSETS_URL . '/vendor/chartjs/chart.umd.min.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Chart 1: Monthly News
  const ctxNews = document.getElementById('newsChart');
  if (ctxNews) {
    new Chart(ctxNews, {
      type: 'bar',
      data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
          label: 'Jumlah Berita',
          data: <?= json_encode($chartData) ?>,
          backgroundColor: '#2563EB',
          hoverBackgroundColor: '#1D4ED8',
          borderRadius: 6,
          maxBarThickness: 45
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            padding: 10,
            cornerRadius: 8
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { precision: 0, stepSize: 1 },
            grid: { color: 'rgba(226, 232, 240, 0.6)' }
          },
          x: {
            grid: { display: false }
          }
        }
      }
    });
  }

  // Chart 2: PPDB Major Distribution (Doughnut)
  const ctxMajor = document.getElementById('majorChart');
  if (ctxMajor) {
    new Chart(ctxMajor, {
      type: 'doughnut',
      data: {
        labels: <?= json_encode($donutLabels) ?>,
        datasets: [{
          data: <?= json_encode($donutData) ?>,
          backgroundColor: [
            '#2563EB',
            '#F59E0B',
            '#10B981',
            '#8B5CF6',
            '#EC4899',
            '#06B6D4'
          ],
          borderWidth: 2,
          borderColor: '#ffffff',
          hoverOffset: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              boxWidth: 12,
              padding: 12,
              font: { size: 11 }
            }
          },
          tooltip: {
            padding: 10,
            cornerRadius: 8
          }
        },
        cutout: '65%'
      }
    });
  }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
