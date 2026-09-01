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
<aside class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-brand">
    <i class="bi bi-mortarboard-fill" style="color: var(--color-accent);"></i>
    <span>Admin SMK</span>
  </div>
  <nav class="nav flex-column pb-4">
    <a class="nav-link <?= str_ends_with($currentScript, '/admin/dashboard.php') ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/dashboard.php">
      <i class="bi bi-grid-1x2"></i> Dashboard
    </a>

    <div class="nav-section-label">Website</div>
    <a class="nav-link <?= navIsActive('profile', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/profile/index.php"><i class="bi bi-gear"></i> Pengaturan Website</a>
    <a class="nav-link <?= navIsActive('banners', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/banners/index.php"><i class="bi bi-images"></i> Banner</a>
    <a class="nav-link <?= navIsActive('statistics', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/statistics/index.php"><i class="bi bi-bar-chart"></i> Statistik</a>
    <a class="nav-link <?= navIsActive('social-media', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/social-media/index.php"><i class="bi bi-share"></i> Social Media</a>

    <div class="nav-section-label">Profil</div>
    <a class="nav-link <?= navIsActive('principal', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/principal/index.php"><i class="bi bi-person-badge"></i> Sambutan Kepsek</a>
    <a class="nav-link <?= navIsActive('history', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/history/index.php"><i class="bi bi-clock-history"></i> Sejarah</a>
    <a class="nav-link <?= navIsActive('vision-mission', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/vision-mission/index.php"><i class="bi bi-bullseye"></i> Visi &amp; Misi</a>
    <a class="nav-link <?= navIsActive('organization', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/organization/index.php"><i class="bi bi-diagram-3"></i> Struktur Organisasi</a>
    <a class="nav-link <?= navIsActive('teachers', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/teachers/index.php"><i class="bi bi-people"></i> Guru &amp; Tendik</a>

    <div class="nav-section-label">Jurusan</div>
    <a class="nav-link <?= navIsActive('majors', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/majors/index.php"><i class="bi bi-mortarboard"></i> Daftar Jurusan</a>

    <div class="nav-section-label">Berita</div>
    <a class="nav-link <?= navIsActive('news', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/news/index.php"><i class="bi bi-newspaper"></i> Semua Berita</a>
    <a class="nav-link <?= navIsActive('news/categories', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/news/categories.php"><i class="bi bi-tags"></i> Kategori Berita</a>

    <div class="nav-section-label">Galeri</div>
    <a class="nav-link <?= navIsActive('galleries', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/galleries/index.php"><i class="bi bi-image"></i> Semua Galeri</a>
    <a class="nav-link <?= navIsActive('galleries/categories', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/galleries/categories.php"><i class="bi bi-tags"></i> Kategori Galeri</a>

    <div class="nav-section-label">Alumni &amp; Partner</div>
    <a class="nav-link <?= navIsActive('alumni', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/alumni/index.php"><i class="bi bi-chat-quote"></i> Testimoni Alumni</a>
    <a class="nav-link <?= navIsActive('partners', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/partners/index.php"><i class="bi bi-building"></i> Data Partner</a>

    <div class="nav-section-label">Kesiswaan</div>
    <a class="nav-link <?= navIsActive('extracurriculars', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/extracurriculars/index.php"><i class="bi bi-activity"></i> Ekstrakurikuler</a>
    <a class="nav-link <?= navIsActive('achievements', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/achievements/index.php"><i class="bi bi-trophy"></i> Prestasi Siswa</a>

    <div class="nav-section-label">BKK &amp; Karir</div>
    <a class="nav-link <?= navIsActive('bkk', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/bkk/index.php"><i class="bi bi-briefcase"></i> Lowongan Kerja (BKK)</a>

    <div class="nav-section-label">Unduhan</div>
    <a class="nav-link <?= navIsActive('downloads', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/downloads/index.php"><i class="bi bi-download"></i> Pusat Unduhan</a>

    <div class="nav-section-label">PPDB Online</div>
    <a class="nav-link <?= navIsActive('ppdb/index', $currentScript) || (navIsActive('ppdb', $currentScript) && !navIsActive('ppdb/settings', $currentScript)) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/ppdb/index.php"><i class="bi bi-person-lines-fill"></i> Data Pendaftar</a>
    <a class="nav-link <?= navIsActive('ppdb/settings', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/ppdb/settings.php"><i class="bi bi-sliders"></i> Pengaturan PPDB</a>

    <div class="nav-section-label">Kelulusan &amp; Agenda</div>
    <a class="nav-link <?= navIsActive('graduations', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/graduations/index.php"><i class="bi bi-mortarboard-fill"></i> Kelulusan (SKL Online)</a>
    <a class="nav-link <?= navIsActive('agendas', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/agendas/index.php"><i class="bi bi-calendar-event"></i> Agenda Kegiatan</a>

    <div class="nav-section-label">Buku Tamu &amp; Kontak</div>
    <a class="nav-link <?= navIsActive('guestbook', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/guestbook/index.php"><i class="bi bi-book-half"></i> Buku Tamu Digital</a>
    <a class="nav-link <?= navIsActive('contacts', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/contacts/index.php"><i class="bi bi-envelope"></i> Pesan Masuk</a>

    <div class="nav-section-label">Sistem &amp; Akun</div>
    <?php if (isSuperAdmin()): ?>
    <a class="nav-link <?= navIsActive('users', $currentScript) ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/users/index.php"><i class="bi bi-shield-lock"></i> Manajemen Admin</a>
    <a class="nav-link text-warning" href="<?= e(BASE_URL) ?>/admin/backup.php"><i class="bi bi-database-down"></i> Backup Database (1-Klik)</a>
    <?php endif; ?>
    <a class="nav-link <?= str_ends_with($currentScript, '/admin/profile-admin.php') ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/profile-admin.php"><i class="bi bi-person-circle"></i> Profil Admin</a>
    <a class="nav-link <?= str_ends_with($currentScript, '/admin/change-password.php') ? 'active' : '' ?>" href="<?= e(BASE_URL) ?>/admin/change-password.php"><i class="bi bi-key"></i> Ganti Password</a>
    <a class="nav-link" href="<?= e(BASE_URL) ?>/admin/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </nav>
</aside>
