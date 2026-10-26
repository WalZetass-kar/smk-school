<?php
$profile = getSchoolProfile();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

/** Small helper so the current page's nav-link gets the "active" class. */
if (!function_exists('navActive')) {
    function navActive(string $path, string $current): string
    {
        return $current === $path ? ' active' : '';
    }
}
$hasTicker = !empty($profile['is_running_text_active']) && !empty($profile['running_text']);
?>
<?php if ($hasTicker): ?>
<div class="top-announcement-bar fixed-top">
  <div class="container-fluid px-3 px-md-4 px-xl-5 d-flex align-items-center">
    <div class="announcement-badge me-2 flex-shrink-0">
      <i class="bi bi-megaphone-fill me-1"></i>INFO
    </div>
    <div class="ticker-wrapper flex-grow-1 overflow-hidden">
      <div class="ticker-content"><?= e($profile['running_text']) ?></div>
    </div>
  </div>
</div>
<?php endif; ?>

<nav class="navbar navbar-expand-xl navbar-smk fixed-top <?= $hasTicker ? 'has-ticker' : '' ?>">
  <div class="container-fluid px-3 px-md-4 px-xl-5">
    <a class="navbar-brand me-2 me-xl-4" href="<?= e(BASE_URL) ?>/" title="<?= e($profile['school_name'] ?? '') ?>">
      <img src="<?= e(BASE_URL . '/' . ($profile['logo'] ?? 'assets/images/logo.png')) ?>" alt="Logo <?= e($profile['school_name'] ?? '') ?>">
      <span class="navbar-brand-text"><?= e($profile['school_name'] ?? 'SMK Negeri Contoh Indonesia') ?></span>
    </a>
    <div class="d-flex align-items-center gap-1 d-xl-none ms-auto me-2">
      <button type="button" class="btn btn-sm btn-light rounded-circle shadow-none p-1" data-bs-toggle="modal" data-bs-target="#globalSearchModal" title="Pencarian" style="width:36px;height:36px;">
        <i class="bi bi-search"></i>
      </button>
    </div>
    <button class="navbar-toggler border-0 shadow-none p-1" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Buka menu navigasi">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto align-items-xl-center">
        <li class="nav-item"><a class="nav-link<?= navActive('/', $currentPath) ?>" href="<?= e(BASE_URL) ?>/">Beranda</a></li>
        
        <!-- Profil Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle<?= str_starts_with($currentPath, '/profil') ? ' active' : '' ?>" href="<?= e(BASE_URL) ?>/profil" id="profilDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Profil</a>
          <ul class="dropdown-menu shadow-sm" aria-labelledby="profilDropdown">
            <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>/profil/sambutan"><i class="bi bi-person-badge me-2 text-primary"></i>Sambutan Kepala Sekolah</a></li>
            <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>/profil/sejarah"><i class="bi bi-clock-history me-2 text-primary"></i>Sejarah Sekolah</a></li>
            <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>/profil/visi-misi"><i class="bi bi-bullseye me-2 text-primary"></i>Visi &amp; Misi</a></li>
            <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>/profil/struktur-organisasi"><i class="bi bi-diagram-3 me-2 text-primary"></i>Struktur Organisasi</a></li>
            <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>/profil/guru-tendik"><i class="bi bi-people me-2 text-primary"></i>Guru &amp; Tenaga Pendidik</a></li>
          </ul>
        </li>

        <!-- Akademik Dropdown (Only if at least one submodule is active) -->
        <?php if (isModuleActive('majors') || isModuleActive('agendas') || isModuleActive('downloads')): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle<?= $currentPath === '/jurusan' || $currentPath === '/agenda' || $currentPath === '/unduhan' ? ' active' : '' ?>" href="#" id="akademikDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Akademik</a>
          <ul class="dropdown-menu shadow-sm" aria-labelledby="akademikDropdown">
            <?php if (isModuleActive('majors')): ?>
              <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>/jurusan"><i class="bi bi-mortarboard me-2 text-primary"></i>Program Keahlian (Jurusan)</a></li>
            <?php endif; ?>
            <?php if (isModuleActive('agendas')): ?>
              <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>/agenda"><i class="bi bi-calendar-event me-2 text-primary"></i>Agenda &amp; Kalender Kegiatan</a></li>
            <?php endif; ?>
            <?php if (isModuleActive('downloads')): ?>
              <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>/unduhan"><i class="bi bi-download me-2 text-primary"></i>Pusat Unduhan Dokumen</a></li>
            <?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Kesiswaan Dropdown (Only if at least one submodule is active) -->
        <?php if (isModuleActive('extracurriculars') || isModuleActive('prestasi') || isModuleActive('kelulusan')): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle<?= $currentPath === '/ekstrakurikuler' || $currentPath === '/prestasi' || $currentPath === '/kelulusan' ? ' active' : '' ?>" href="#" id="kesiswaanDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Kesiswaan</a>
          <ul class="dropdown-menu shadow-sm" aria-labelledby="kesiswaanDropdown">
            <?php if (isModuleActive('extracurriculars')): ?>
              <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>/ekstrakurikuler"><i class="bi bi-activity me-2 text-primary"></i>Ekstrakurikuler</a></li>
            <?php endif; ?>
            <?php if (isModuleActive('prestasi')): ?>
              <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>/prestasi"><i class="bi bi-trophy me-2 text-primary"></i>Prestasi Siswa (Hall of Fame)</a></li>
            <?php endif; ?>
            <?php if (isModuleActive('kelulusan')): ?>
              <?php if (isModuleActive('extracurriculars') || isModuleActive('prestasi')): ?><li><hr class="dropdown-divider"></li><?php endif; ?>
              <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>/kelulusan"><i class="bi bi-mortarboard-fill me-2 text-primary"></i>Pengumuman Kelulusan (SKL)</a></li>
            <?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Informasi Dropdown (Only if at least one submodule is active) -->
        <?php if (isModuleActive('news') || isModuleActive('galleries') || isModuleActive('bkk')): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle<?= $currentPath === '/berita' || $currentPath === '/galeri' || str_starts_with($currentPath, '/bkk') ? ' active' : '' ?>" href="#" id="informasiDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Informasi</a>
          <ul class="dropdown-menu shadow-sm" aria-labelledby="informasiDropdown">
            <?php if (isModuleActive('news')): ?>
              <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>/berita"><i class="bi bi-newspaper me-2 text-primary"></i>Berita &amp; Artikel</a></li>
            <?php endif; ?>
            <?php if (isModuleActive('galleries')): ?>
              <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>/galeri"><i class="bi bi-images me-2 text-primary"></i>Galeri Foto &amp; Kegiatan</a></li>
            <?php endif; ?>
            <?php if (isModuleActive('bkk')): ?>
              <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>/bkk"><i class="bi bi-briefcase me-2 text-primary"></i>Bursa Kerja Khusus (BKK)</a></li>
            <?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>

        <!-- PPDB Dropdown (Only if PPDB module is active) -->
        <?php if (isModuleActive('ppdb')): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle<?= str_starts_with($currentPath, '/ppdb') ? ' active' : '' ?>" href="<?= e(BASE_URL) ?>/ppdb" id="ppdbDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">PPDB</a>
          <ul class="dropdown-menu shadow-sm" aria-labelledby="ppdbDropdown">
            <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>/ppdb"><i class="bi bi-info-circle me-2 text-primary"></i>Informasi &amp; Alur PPDB</a></li>
            <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>/ppdb/daftar"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Formulir Pendaftaran Online</a></li>
            <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>/ppdb/cek-status"><i class="bi bi-search me-2 text-primary"></i>Cek Status Pendaftaran</a></li>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Kontak Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle<?= $currentPath === '/kontak' || $currentPath === '/buku-tamu' ? ' active' : '' ?>" href="#" id="kontakDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Kontak</a>
          <ul class="dropdown-menu shadow-sm" aria-labelledby="kontakDropdown">
            <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>/kontak"><i class="bi bi-telephone me-2 text-primary"></i>Kontak &amp; Lokasi Sekolah</a></li>
            <?php if (isModuleActive('guestbook')): ?>
              <li><a class="dropdown-item" href="<?= e(BASE_URL) ?>/buku-tamu"><i class="bi bi-book-half me-2 text-primary"></i>Buku Tamu Digital</a></li>
            <?php endif; ?>
          </ul>
        </li>
        
        <!-- Search Button for Desktop -->
        <li class="nav-item d-none d-xl-flex align-items-center ms-2">
          <button type="button" class="btn btn-sm btn-light rounded-circle shadow-none p-1 border" data-bs-toggle="modal" data-bs-target="#globalSearchModal" title="Pencarian Global (Ctrl + K)" style="width:36px;height:36px;">
            <i class="bi bi-search"></i>
          </button>
        </li>

        <?php if (isModuleActive('ppdb')): ?>
        <li class="nav-item ms-xl-3 mt-2 mt-xl-0">
          <a class="btn btn-sm btn-accent text-nowrap px-3 py-2 fw-bold d-inline-flex align-items-center gap-1" href="<?= e(BASE_URL) ?>/ppdb/daftar">
            <i class="bi bi-mortarboard-fill"></i> Daftar PPDB
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="navbar-spacer <?= $hasTicker ? 'has-ticker' : '' ?>" aria-hidden="true"></div>
