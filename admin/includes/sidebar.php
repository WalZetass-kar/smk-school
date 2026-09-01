<?php
/**
 * admin/includes/sidebar.php
 * Highlights the active link by matching the current script's folder/name.
 */
$currentScript = $_SERVER['SCRIPT_NAME'] ?? '';
$sidebarProfile = getSchoolProfile();
$adminBrandTitle = !empty($sidebarProfile['school_name']) ? $sidebarProfile['school_name'] : 'Admin Panel';

/** True when the current request is inside admin/$folder/ or is admin/$file itself. */
function navIsActive(string $needle, string $currentScript): bool
{
    return str_contains($currentScript, '/admin/' . $needle);
}
?>
<!-- Mobile Backdrop Overlay -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<aside class="admin-sidebar" id="adminSidebar">
  <!-- Sidebar Header with Brand and Internal Burger Menu -->
  <div class="sidebar-brand d-flex align-items-center justify-content-between">
    <div class="sidebar-brand-left d-flex align-items-center gap-2 overflow-hidden" title="<?= e($adminBrandTitle) ?>">
      <i class="bi bi-mortarboard-fill flex-shrink-0" style="color: var(--color-accent); font-size:1.35rem;"></i>
      <span class="sidebar-brand-title text-nowrap fw-bold"><?= e($adminBrandTitle) ?></span>
    </div>
    <button type="button" class="btn btn-sm btn-link text-white-50 p-1 sidebar-toggle-btn shadow-none flex-shrink-0" id="sidebarToggleInsideBtn" title="Buka / Tutup Sidebar" aria-label="Toggle Sidebar">
      <i class="bi bi-list fs-4 text-white"></i>
    </button>
  </div>

  <!-- Scrollable Nav Items -->
  <div class="sidebar-nav-scroll flex-grow-1 overflow-y-auto overflow-x-hidden">
    <nav class="nav flex-column py-2">
      <a class="nav-link <?= str_ends_with($currentScript, '/admin/dashboard.php') ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/dashboard.php" title="Dashboard">
        <i class="bi bi-grid-1x2"></i> <span class="nav-text">Dashboard</span>
      </a>

      <div class="nav-section-label">Website &amp; Fitur</div>
      <a class="nav-link <?= navIsActive('modules', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/modules/index.php" title="Fitur & Modul Halaman"><i class="bi bi-sliders2-vertical text-warning"></i> <span class="nav-text">Fitur &amp; Modul (ON/OFF)</span></a>
      <a class="nav-link <?= navIsActive('profile', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/profile/index.php" title="Identitas & Profil Website"><i class="bi bi-gear"></i> <span class="nav-text">Identitas Sekolah</span></a>
      <a class="nav-link <?= navIsActive('banners', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/banners/index.php" title="Banner Slider"><i class="bi bi-images"></i> <span class="nav-text">Banner Slider</span></a>
      <a class="nav-link <?= navIsActive('statistics', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/statistics/index.php" title="Statistik Sekolah (Jumlah Siswa, Guru, dll)"><i class="bi bi-bar-chart-line"></i> <span class="nav-text">Statistik Sekolah</span></a>
      <a class="nav-link <?= navIsActive('social-media', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/social-media/index.php" title="Social Media"><i class="bi bi-share"></i> <span class="nav-text">Social Media</span></a>

      <div class="nav-section-label">Profil</div>
      <a class="nav-link <?= navIsActive('principal', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/principal/index.php" title="Sambutan Kepsek"><i class="bi bi-person-badge"></i> <span class="nav-text">Sambutan Kepsek</span></a>
      <a class="nav-link <?= navIsActive('history', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/history/index.php" title="Sejarah"><i class="bi bi-clock-history"></i> <span class="nav-text">Sejarah</span></a>
      <a class="nav-link <?= navIsActive('vision-mission', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/vision-mission/index.php" title="Visi &amp; Misi"><i class="bi bi-bullseye"></i> <span class="nav-text">Visi &amp; Misi</span></a>
      <a class="nav-link <?= navIsActive('organization', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/organization/index.php" title="Struktur Organisasi"><i class="bi bi-diagram-3"></i> <span class="nav-text">Struktur Organisasi</span></a>
      <a class="nav-link <?= navIsActive('teachers', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/teachers/index.php" title="Guru &amp; Tendik"><i class="bi bi-people"></i> <span class="nav-text">Guru &amp; Tendik</span></a>

      <div class="nav-section-label">Jurusan</div>
      <a class="nav-link <?= navIsActive('majors', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/majors/index.php" title="Daftar Jurusan"><i class="bi bi-mortarboard"></i> <span class="nav-text">Daftar Jurusan</span></a>

      <div class="nav-section-label">Berita</div>
      <a class="nav-link <?= navIsActive('news', $currentScript) && !navIsActive('news/categories', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/news/index.php" title="Semua Berita"><i class="bi bi-newspaper"></i> <span class="nav-text">Semua Berita</span></a>
      <a class="nav-link <?= navIsActive('news/categories', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/news/categories.php" title="Kategori Berita"><i class="bi bi-tags"></i> <span class="nav-text">Kategori Berita</span></a>

      <div class="nav-section-label">Galeri</div>
      <a class="nav-link <?= navIsActive('galleries', $currentScript) && !navIsActive('galleries/categories', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/galleries/index.php" title="Semua Galeri"><i class="bi bi-image"></i> <span class="nav-text">Semua Galeri</span></a>
      <a class="nav-link <?= navIsActive('galleries/categories', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/galleries/categories.php" title="Kategori Galeri"><i class="bi bi-tags"></i> <span class="nav-text">Kategori Galeri</span></a>

      <div class="nav-section-label">Alumni &amp; Mitra</div>
      <a class="nav-link <?= navIsActive('alumni', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/alumni/index.php" title="Testimoni Alumni"><i class="bi bi-chat-quote"></i> <span class="nav-text">Testimoni Alumni</span></a>
      <a class="nav-link <?= navIsActive('partners', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/partners/index.php" title="Mitra Kerja Sama &amp; Industri (DU/DI)"><i class="bi bi-buildings"></i> <span class="nav-text">Mitra Kerja Sama</span></a>

      <div class="nav-section-label">Kesiswaan</div>
      <a class="nav-link <?= navIsActive('extracurriculars', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/extracurriculars/index.php" title="Ekstrakurikuler"><i class="bi bi-activity"></i> <span class="nav-text">Ekstrakurikuler</span></a>
      <a class="nav-link <?= navIsActive('achievements', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/achievements/index.php" title="Prestasi Siswa"><i class="bi bi-trophy"></i> <span class="nav-text">Prestasi Siswa</span></a>

      <div class="nav-section-label">BKK &amp; Karir</div>
      <a class="nav-link <?= navIsActive('bkk', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/bkk/index.php" title="Lowongan BKK"><i class="bi bi-briefcase"></i> <span class="nav-text">Lowongan BKK</span></a>

      <div class="nav-section-label">Unduhan</div>
      <a class="nav-link <?= navIsActive('downloads', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/downloads/index.php" title="Pusat Unduhan"><i class="bi bi-download"></i> <span class="nav-text">Pusat Unduhan</span></a>

      <div class="nav-section-label">PPDB &amp; Notifikasi</div>
      <a class="nav-link <?= navIsActive('ppdb/index', $currentScript) || (navIsActive('ppdb', $currentScript) && !navIsActive('ppdb/settings', $currentScript) && !navIsActive('ppdb/detail', $currentScript)) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/ppdb/index.php" title="Data Pendaftar PPDB"><i class="bi bi-person-lines-fill"></i> <span class="nav-text">Data Pendaftar</span></a>
      <a class="nav-link <?= navIsActive('ppdb/settings', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/ppdb/settings.php" title="Pengaturan PPDB"><i class="bi bi-sliders"></i> <span class="nav-text">Pengaturan PPDB</span></a>
      <a class="nav-link <?= navIsActive('whatsapp', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/whatsapp/index.php" title="WhatsApp Gateway"><i class="bi bi-whatsapp text-success"></i> <span class="nav-text">WhatsApp Gateway</span></a>

      <div class="nav-section-label">Kelulusan &amp; Agenda</div>
      <a class="nav-link <?= navIsActive('graduations', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/graduations/index.php" title="Kelulusan (SKL Online)"><i class="bi bi-mortarboard-fill"></i> <span class="nav-text">Kelulusan (SKL)</span></a>
      <a class="nav-link <?= navIsActive('agendas', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/agendas/index.php" title="Agenda Kegiatan"><i class="bi bi-calendar-event"></i> <span class="nav-text">Agenda Kegiatan</span></a>

      <div class="nav-section-label">Buku Tamu &amp; Kontak</div>
      <a class="nav-link <?= navIsActive('guestbook', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/guestbook/index.php" title="Buku Tamu Digital"><i class="bi bi-book-half"></i> <span class="nav-text">Buku Tamu Digital</span></a>
      <a class="nav-link <?= navIsActive('contacts', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/contacts/index.php" title="Pesan Masuk"><i class="bi bi-envelope"></i> <span class="nav-text">Pesan Masuk</span></a>

      <div class="nav-section-label">Sistem &amp; Akun</div>
      <a class="nav-link <?= navIsActive('activity-logs', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/activity-logs/index.php" title="Audit Log Aktivitas"><i class="bi bi-shield-lock-fill"></i> <span class="nav-text">Audit Log Aktivitas</span></a>
      <?php if (isSuperAdmin()): ?>
      <a class="nav-link <?= navIsActive('users', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/users/index.php" title="Manajemen Admin"><i class="bi bi-people-fill"></i> <span class="nav-text">Manajemen Admin</span></a>
      <a class="nav-link" href="<?= e(BASE_URL) ?>/admin/backup.php" title="Backup Database"><i class="bi bi-database-down"></i> <span class="nav-text">Backup Database</span></a>
      <?php endif; ?>
      <a class="nav-link <?= str_ends_with($currentScript, '/admin/profile-admin.php') ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/profile-admin.php" title="Profil &amp; Keamanan"><i class="bi bi-person-circle"></i> <span class="nav-text">Profil &amp; Keamanan</span></a>
    </nav>
  </div>

  <!-- Bottom Pinned Logout Button (Never scrolls away!) -->
  <div class="sidebar-footer flex-shrink-0">
    <a class="nav-link sidebar-logout-btn" href="#" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal" title="Keluar dari Panel Admin">
      <i class="bi bi-box-arrow-right"></i>
      <span class="nav-text">Keluar (Logout)</span>
    </a>
  </div>
</aside>
