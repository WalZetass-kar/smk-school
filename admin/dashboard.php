<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

$db = getDB();

$stats = [
    'Total Berita'     => (int) $db->query("SELECT COUNT(*) FROM news")->fetchColumn(),
    'Pendaftar PPDB'   => (int) $db->query("SELECT COUNT(*) FROM ppdb_registrations")->fetchColumn(),
    'Lowongan BKK'     => (int) $db->query("SELECT COUNT(*) FROM bkk_jobs WHERE status = 'open'")->fetchColumn(),
    'Prestasi Siswa'   => (int) $db->query("SELECT COUNT(*) FROM achievements")->fetchColumn(),
    'Ekstrakurikuler'  => (int) $db->query("SELECT COUNT(*) FROM extracurriculars WHERE status = 1")->fetchColumn(),
    'Total Unduhan'    => (int) $db->query("SELECT COUNT(*) FROM downloads")->fetchColumn(),
    'Total Guru'       => (int) $db->query("SELECT COUNT(*) FROM teachers WHERE category = 'guru'")->fetchColumn(),
    'Pesan Masuk'      => (int) $db->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn(),
];
$icons = ['bi-newspaper', 'bi-person-lines-fill', 'bi-briefcase', 'bi-trophy', 'bi-activity', 'bi-download', 'bi-person-workspace', 'bi-envelope'];

// Berita per bulan (6 bulan terakhir)
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

$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
require __DIR__ . '/includes/topbar.php';
?>

<?= flashMessage() ?>

<?php if (isSuperAdmin()): ?>
<div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 border-0 shadow-sm rounded-3" style="background:#e8f4fd;">
  <div class="d-flex align-items-center gap-3">
    <i class="bi bi-shield-check fs-3 text-primary"></i>
    <div>
      <div class="fw-bold text-primary">Pemeliharaan &amp; Keamanan Database</div>
      <div class="small text-muted">Amankan seluruh data berita, siswa PPDB, dan konfigurasi dengan unduh cadangan SQL berkala.</div>
    </div>
  </div>
  <a href="<?= e(BASE_URL) ?>/admin/backup.php" class="btn btn-sm btn-primary fw-semibold">
    <i class="bi bi-database-down me-1"></i>Unduh Backup Database (.sql)
  </a>
</div>
<?php endif; ?>

<div class="row g-3 mb-4">
  <?php $i = 0; foreach ($stats as $label => $value): ?>
    <div class="col-xl-3 col-md-6">
      <div class="stat-card">
        <div class="stat-icon"><i class="bi <?= $icons[$i] ?>"></i></div>
        <div>
          <div class="stat-value"><?= number_format($value, 0, ',', '.') ?></div>
          <div class="stat-label"><?= e($label) ?></div>
        </div>
      </div>
    </div>
  <?php $i++; endforeach; ?>
</div>

<div class="admin-card">
  <h2 class="h6 text-primary mb-3">Berita per Bulan</h2>
  <canvas id="newsChart" height="90"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('newsChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($chartLabels) ?>,
    datasets: [{
      label: 'Jumlah Berita',
      data: <?= json_encode($chartData) ?>,
      backgroundColor: '#5B8DBE',
      borderRadius: 6,
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
  }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
