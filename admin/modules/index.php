<?php
/**
 * admin/modules/index.php
 * Halaman Pengaturan Fitur & Modul Halaman (Multi-Level School Template Switcher)
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';

$db = getDB();
$modules = $db->query("SELECT * FROM site_modules ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

$totalModules = count($modules);
$activeCount  = count(array_filter($modules, fn($m) => (int)$m['is_active'] === 1));
$inactiveCount = $totalModules - $activeCount;

// Group by category
$categories = [];
foreach ($modules as $m) {
    $cat = $m['category'] ?: 'Lainnya';
    $categories[$cat][] = $m;
}

$pageTitle = 'Fitur & Modul Halaman';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
require __DIR__ . '/../includes/topbar.php';
?>

<?= flashMessage() ?>

<!-- Top Intro Banner & Status Summary -->
<div class="row g-3 mb-4">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
      <div class="d-flex align-items-start gap-3">
        <div class="p-3 rounded-3 fs-3 flex-shrink-0" style="background-color: var(--color-primary-subtle, #e2e8f0); color: var(--color-primary, #0F2C59);">
          <i class="bi bi-sliders2-vertical"></i>
        </div>
        <div>
          <h2 class="h5 fw-bold text-primary mb-1">Pengaturan Fitur &amp; Modul Halaman</h2>
          <p class="text-muted small mb-0" style="line-height: 1.6;">
            Sesuaikan website ini untuk berbagai jenjang sekolah (<strong>SMK, SMA, SMP, atau SD</strong>). Anda dapat mengaktifkan atau menonaktifkan fitur seperti <em>Mitra Kerjasama DU-DI, BKK, Jurusan, SKL Online, PPDB, dll.</em> dengan 1-klik. Modul yang dinonaktifkan akan otomatis disembunyikan dari menu navigasi dan beranda publik.
          </p>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 text-center d-flex flex-column justify-content-center">
      <div class="d-flex justify-content-around align-items-center">
        <div>
          <div class="h3 fw-bold text-success mb-0"><?= $activeCount ?></div>
          <span class="text-muted small">Modul Aktif (ON)</span>
        </div>
        <div class="border-end" style="height: 40px;"></div>
        <div>
          <div class="h3 fw-bold text-secondary mb-0"><?= $inactiveCount ?></div>
          <span class="text-muted small">Nonaktif (OFF)</span>
        </div>
      </div>
      <div class="mt-3 pt-2 border-top">
        <a href="<?= e(BASE_URL) ?>/" target="_blank" class="btn btn-sm btn-outline-primary-custom w-100 fw-semibold">
          <i class="bi bi-box-arrow-up-right me-1"></i> Preview Tampilan Publik
        </a>
      </div>
    </div>
  </div>
</div>

<!-- 1-Click Level Preset Selector -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
  <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
      <h3 class="h6 fw-bold text-primary mb-0 d-flex align-items-center gap-2">
        <i class="bi bi-magic text-warning"></i> Preset Cepat Jenjang Sekolah
      </h3>
      <span class="text-muted small">Terapkan konfigurasi modul siap pakai dalam 1 kali klik sesuai jenjang institusi Anda:</span>
    </div>
  </div>

  <div class="row g-3">
    <!-- Preset SMK -->
    <div class="col-md-3 col-6">
      <form method="post" action="preset.php" data-confirm="Terapkan Preset SMK? Seluruh fitur vokasi, jurusan, mitra industri DU/DI, dan BKK akan diaktifkan.">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="preset" value="SMK">
        <button type="submit" class="btn btn-outline-primary w-100 p-3 rounded-3 text-start h-100 d-flex flex-column justify-content-between hover-lift">
          <div>
            <div class="fw-bold mb-1"><i class="bi bi-building me-1.5"></i> Preset SMK</div>
            <small class="text-muted d-block" style="font-size: 11.5px; line-height: 1.4;">Semua fitur aktif (Jurusan, Mitra DU/DI, BKK, SKL, PPDB, dll).</small>
          </div>
          <span class="badge bg-primary text-white mt-2 align-self-start">Vokasi Penuh</span>
        </button>
      </form>
    </div>

    <!-- Preset SMA -->
    <div class="col-md-3 col-6">
      <form method="post" action="preset.php" data-confirm="Terapkan Preset SMA / MA? Modul Mitra DU/DI dan BKK akan dinonaktifkan.">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="preset" value="SMA">
        <button type="submit" class="btn btn-outline-success w-100 p-3 rounded-3 text-start h-100 d-flex flex-column justify-content-between hover-lift">
          <div>
            <div class="fw-bold mb-1"><i class="bi bi-mortarboard me-1.5"></i> Preset SMA / MA</div>
            <small class="text-muted d-block" style="font-size: 11.5px; line-height: 1.4;">Akademik, Peminatan (Jurusan), Ekskul, Prestasi. Tanpa BKK & Mitra.</small>
          </div>
          <span class="badge bg-success text-white mt-2 align-self-start">Akademik</span>
        </button>
      </form>
    </div>

    <!-- Preset SMP -->
    <div class="col-md-3 col-6">
      <form method="post" action="preset.php" data-confirm="Terapkan Preset SMP / MTs? Modul Jurusan, Mitra DU/DI, dan BKK akan dinonaktifkan.">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="preset" value="SMP">
        <button type="submit" class="btn btn-outline-warning w-100 p-3 rounded-3 text-start h-100 d-flex flex-column justify-content-between hover-lift">
          <div>
            <div class="fw-bold mb-1 text-dark"><i class="bi bi-backpack me-1.5 text-warning"></i> Preset SMP / MTs</div>
            <small class="text-muted d-block" style="font-size: 11.5px; line-height: 1.4;">Berita, Ekskul, Prestasi, Galeri, PPDB. Tanpa Jurusan, BKK, Mitra.</small>
          </div>
          <span class="badge bg-warning text-dark mt-2 align-self-start">Menengah</span>
        </button>
      </form>
    </div>

    <!-- Preset SD -->
    <div class="col-md-3 col-6">
      <form method="post" action="preset.php" data-confirm="Terapkan Preset SD / MI? Tampilan ramah anak tanpa Jurusan, BKK, dan Mitra.">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="preset" value="SD">
        <button type="submit" class="btn btn-outline-info w-100 p-3 rounded-3 text-start h-100 d-flex flex-column justify-content-between hover-lift">
          <div>
            <div class="fw-bold mb-1 text-dark"><i class="bi bi-book me-1.5 text-info"></i> Preset SD / MI</div>
            <small class="text-muted d-block" style="font-size: 11.5px; line-height: 1.4;">Sederhana & ramah anak: Berita, Galeri, Prestasi, PPDB.</small>
          </div>
          <span class="badge bg-info text-dark mt-2 align-self-start">Dasar</span>
        </button>
      </form>
    </div>
  </div>
</div>

<?php
$moduleIcons = [
    'majors'           => 'bi-mortarboard',
    'partners'         => 'bi-building-check',
    'bkk'              => 'bi-briefcase',
    'ppdb'             => 'bi-person-plus',
    'graduations'      => 'bi-award',
    'news'             => 'bi-newspaper',
    'agendas'          => 'bi-calendar-event',
    'galleries'        => 'bi-images',
    'extracurriculars' => 'bi-trophy',
    'achievements'     => 'bi-star',
    'teachers'         => 'bi-people',
    'organization'     => 'bi-diagram-3',
    'history'          => 'bi-hourglass-split',
    'downloads'        => 'bi-download',
    'guestbook'        => 'bi-book-half',
];
?>

<!-- Modules Grouped by Category -->
<?php foreach ($categories as $catName => $catItems): ?>
  <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
      <h3 class="h6 fw-bold text-primary mb-0 d-flex align-items-center gap-2">
        <i class="bi bi-folder2-open text-primary"></i> Kategori: <?= e($catName) ?>
      </h3>
      <span class="badge bg-light text-dark border small"><?= count($catItems) ?> Modul</span>
    </div>

    <div class="row g-3">
      <?php foreach ($catItems as $m): ?>
        <?php 
          $modKey  = $m['module_key'] ?? '';
          $modIcon = $moduleIcons[$modKey] ?? ($m['icon'] ?? 'bi-gear');
          $modName = $m['name'] ?? ($m['module_name'] ?? ucfirst($modKey));
        ?>
        <div class="col-lg-6">
          <div class="p-3 rounded-3 border h-100 d-flex align-items-center justify-content-between gap-3 <?= $m['is_active'] ? 'bg-white shadow-xs' : 'bg-light bg-opacity-75' ?>" style="transition: all 0.2s ease;">
            <div class="d-flex align-items-start gap-3">
              <div class="rounded-3 fs-5 d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px; height:44px; background-color: <?= $m['is_active'] ? 'var(--color-primary-subtle, #e2e8f0)' : '#f1f5f9' ?>; color: <?= $m['is_active'] ? 'var(--color-primary, #0F2C59)' : '#94a3b8' ?>;">
                <i class="bi <?= e($modIcon) ?>"></i>
              </div>
              <div>
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                  <h4 class="h6 fw-bold mb-0 text-dark"><?= e($modName) ?></h4>
                  <?php if (!empty($m['target_levels'])): ?>
                    <span class="badge bg-light text-muted border" style="font-size: 10px;"><?= e($m['target_levels']) ?></span>
                  <?php endif; ?>
                </div>
                <p class="small text-muted mb-0" style="font-size: 12.5px; line-height: 1.45;"><?= e($m['description'] ?? '') ?></p>
              </div>
            </div>

            <!-- 1-Click Toggle Form -->
            <form method="post" action="toggle.php" class="flex-shrink-0">
              <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
              <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
              <?php if ($m['is_active']): ?>
                <button type="submit" class="btn btn-sm btn-success px-3 py-1.5 rounded-pill fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5" title="Klik untuk menonaktifkan">
                  <i class="bi bi-toggle-on fs-5"></i>
                  <span>ON</span>
                </button>
              <?php else: ?>
                <button type="submit" class="btn btn-sm btn-outline-secondary px-3 py-1.5 rounded-pill fw-semibold shadow-xs d-inline-flex align-items-center gap-1.5" title="Klik untuk mengaktifkan">
                  <i class="bi bi-toggle-off fs-5"></i>
                  <span>OFF</span>
                </button>
              <?php endif; ?>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endforeach; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
