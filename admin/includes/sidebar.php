<?php
/**
 * admin/includes/sidebar.php
 * Highlights the active link by matching the current script's folder/name.
 */
$currentScript = $_SERVER['SCRIPT_NAME'] ?? '';

/** True when the current request is inside admin/$folder/ or is admin/$file itself. */
function navIsActive(string $needle, string $currentScript): bool
{
    return str_contains($currentScript, '/admin/' . $needle);
}
?>
<!-- Mobile Backdrop Overlay -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<aside class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-brand d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-mortarboard-fill" style="color: var(--color-accent); font-size:1.3rem;"></i>
      <span class="sidebar-brand-title">Admin SMK</span>
    </div>
    <button type="button" class="btn btn-sm btn-link text-white-50 p-0 d-lg-none" id="sidebarCloseBtn" aria-label="Tutup Menu">
      <i class="bi bi-x-lg fs-5 text-white"></i>
    </button>
  </div>
  <nav class="nav flex-column pb-4">
    <a class="nav-link <?= str_ends_with($currentScript, '/admin/dashboard.php') ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/dashboard.php">
      <i class="bi bi-grid-1x2"></i> <span>Dashboard</span>
    </a>

    <div class="nav-section-label">Website</div>
    <a class="nav-link <?= navIsActive('profile', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/profile/index.php"><i class="bi bi-gear"></i> <span>Pengaturan Website</span></a>
    <a class="nav-link <?= navIsActive('banners', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/banners/index.php"><i class="bi bi-images"></i> <span>Banner</span></a>
    <a class="nav-link <?= navIsActive('statistics', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/statistics/index.php"><i class="bi bi-bar-chart"></i> <span>Statistik</span></a>
    <a class="nav-link <?= navIsActive('social-media', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/social-media/index.php"><i class="bi bi-share"></i> <span>Social Media</span></a>

    <div class="nav-section-label">Profil</div>
    <a class="nav-link <?= navIsActive('principal', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/principal/index.php"><i class="bi bi-person-badge"></i> <span>Sambutan Kepsek</span></a>
    <a class="nav-link <?= navIsActive('history', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/history/index.php"><i class="bi bi-clock-history"></i> <span>Sejarah</span></a>
    <a class="nav-link <?= navIsActive('vision-mission', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/vision-mission/index.php"><i class="bi bi-bullseye"></i> <span>Visi &amp; Misi</span></a>
    <a class="nav-link <?= navIsActive('organization', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/organization/index.php"><i class="bi bi-diagram-3"></i> <span>Struktur Organisasi</span></a>
    <a class="nav-link <?= navIsActive('teachers', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/teachers/index.php"><i class="bi bi-people"></i> <span>Guru &amp; Tendik</span></a>

    <div class="nav-section-label">Jurusan</div>
    <a class="nav-link <?= navIsActive('majors', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/majors/index.php"><i class="bi bi-mortarboard"></i> <span>Daftar Jurusan</span></a>

    <div class="nav-section-label">Berita</div>
    <a class="nav-link <?= navIsActive('news', $currentScript) && !navIsActive('news/categories', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/news/index.php"><i class="bi bi-newspaper"></i> <span>Semua Berita</span></a>
    <a class="nav-link <?= navIsActive('news/categories', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/news/categories.php"><i class="bi bi-tags"></i> <span>Kategori Berita</span></a>

    <div class="nav-section-label">Galeri</div>
    <a class="nav-link <?= navIsActive('galleries', $currentScript) && !navIsActive('galleries/categories', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/galleries/index.php"><i class="bi bi-image"></i> <span>Semua Galeri</span></a>
    <a class="nav-link <?= navIsActive('galleries/categories', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/galleries/categories.php"><i class="bi bi-tags"></i> <span>Kategori Galeri</span></a>

    <div class="nav-section-label">Alumni &amp; Partner</div>
    <a class="nav-link <?= navIsActive('alumni', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/alumni/index.php"><i class="bi bi-chat-quote"></i> <span>Testimoni Alumni</span></a>
    <a class="nav-link <?= navIsActive('partners', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/partners/index.php"><i class="bi bi-building"></i> <span>Data Partner</span></a>

    <div class="nav-section-label">Kesiswaan</div>
    <a class="nav-link <?= navIsActive('extracurriculars', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/extracurriculars/index.php"><i class="bi bi-activity"></i> <span>Ekstrakurikuler</span></a>
    <a class="nav-link <?= navIsActive('achievements', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/achievements/index.php"><i class="bi bi-trophy"></i> <span>Prestasi Siswa</span></a>

    <div class="nav-section-label">BKK &amp; Karir</div>
    <a class="nav-link <?= navIsActive('bkk', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/bkk/index.php"><i class="bi bi-briefcase"></i> <span>Lowongan BKK</span></a>

    <div class="nav-section-label">Unduhan</div>
    <a class="nav-link <?= navIsActive('downloads', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/downloads/index.php"><i class="bi bi-download"></i> <span>Pusat Unduhan</span></a>

    <div class="nav-section-label">PPDB Online</div>
    <a class="nav-link <?= navIsActive('ppdb/index', $currentScript) || (navIsActive('ppdb', $currentScript) && !navIsActive('ppdb/settings', $currentScript) && !navIsActive('ppdb/detail', $currentScript)) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/ppdb/index.php"><i class="bi bi-person-lines-fill"></i> <span>Data Pendaftar</span></a>
    <a class="nav-link <?= navIsActive('ppdb/settings', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/ppdb/settings.php"><i class="bi bi-sliders"></i> <span>Pengaturan PPDB</span></a>

    <div class="nav-section-label">Kontak</div>
    <a class="nav-link <?= navIsActive('contacts', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/contacts/index.php"><i class="bi bi-envelope"></i> <span>Pesan Masuk</span></a>

    <div class="nav-section-label">Sistem &amp; Akun</div>
    <?php if (isSuperAdmin()): ?>
    <a class="nav-link <?= navIsActive('users', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/users/index.php"><i class="bi bi-people-fill text-warning"></i> <span>Manajemen Admin</span></a>
    <a class="nav-link text-warning" href="<?= e(BASE_URL) ?>/admin/backup.php"><i class="bi bi-database-down"></i> <span>Backup Database</span></a>
    <?php endif; ?>
    <a class="nav-link <?= str_ends_with($currentScript, '/admin/profile-admin.php') ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/profile-admin.php"><i class="bi bi-person-circle"></i> <span>Profil &amp; Keamanan</span></a>
    <a class="nav-link <?= str_ends_with($currentScript, '/admin/change-password.php') ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/change-password.php"><i class="bi bi-key"></i> <span>Ganti Password</span></a>
    <a class="nav-link text-danger" href="<?= e(BASE_URL) ?>/admin/logout.php"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a>
  </nav>
</aside>
