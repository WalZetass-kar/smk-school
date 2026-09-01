<?php
$profile = getSchoolProfile();
$socials = getSocialMedia();
$whatsapp = null;
foreach ($socials as $s) {
    if ($s['platform'] === 'WhatsApp') { $whatsapp = $s['url']; break; }
}
?>
  <footer class="footer-smk pt-5 pb-4">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-4 col-md-6">
          <div class="d-flex align-items-center gap-2 mb-3">
            <img src="<?= e(BASE_URL . '/' . ($profile['logo'] ?? 'assets/images/logo.png')) ?>" alt="Logo" style="height:38px;">
            <span class="fw-bold text-white" style="font-family: var(--font-display);"><?= e($profile['school_name'] ?? '') ?></span>
          </div>
          <p class="small"><?= e($profile['description'] ?? '') ?></p>
        </div>
        <div class="col-lg-2 col-md-6">
          <h6>Menu</h6>
          <ul class="list-unstyled small">
            <li class="mb-2"><a href="<?= e(BASE_URL) ?>/">Beranda</a></li>
            <li class="mb-2"><a href="<?= e(BASE_URL) ?>/profil">Profil</a></li>
            <li class="mb-2"><a href="<?= e(BASE_URL) ?>/jurusan">Jurusan</a></li>
            <li class="mb-2"><a href="<?= e(BASE_URL) ?>/berita">Berita</a></li>
            <li class="mb-2"><a href="<?= e(BASE_URL) ?>/galeri">Galeri</a></li>
            <li class="mb-2"><a href="<?= e(BASE_URL) ?>/kontak">Kontak</a></li>
          </ul>
        </div>
        <div class="col-lg-3 col-md-6">
          <h6>Kontak</h6>
          <ul class="list-unstyled small">
            <li class="mb-2"><i class="bi bi-geo-alt me-2"></i><?= e($profile['address'] ?? '') ?></li>
            <li class="mb-2"><i class="bi bi-telephone me-2"></i><?= e($profile['phone'] ?? '') ?></li>
            <li class="mb-2"><i class="bi bi-envelope me-2"></i><?= e($profile['email'] ?? '') ?></li>
            <li class="mb-2"><i class="bi bi-globe me-2"></i><?= e($profile['website'] ?? '') ?></li>
          </ul>
        </div>
        <div class="col-lg-3 col-md-6">
          <h6>Ikuti Kami</h6>
          <div class="d-flex gap-2">
            <?php foreach ($socials as $s): ?>
              <a class="social-icon-circle" href="<?= e($s['url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($s['platform']) ?>">
                <i class="bi <?= e($s['icon']) ?>"></i>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <span>&copy; <?= date('Y') ?> <?= e($profile['school_name'] ?? '') ?>. Seluruh hak cipta dilindungi.</span>
        <span>Developer by <a href="https://github.com/WalZetass-kar" target="_blank" rel="noopener noreferrer" class="text-white fw-bold text-decoration-none border-bottom border-warning pb-0.5">WalZetass-Kar</a></span>
      </div>
    </div>
  </footer>

  <!-- ================= GLOBAL LIVE SEARCH MODAL ================= -->
  <div class="modal fade" id="globalSearchModal" tabindex="-1" aria-labelledby="globalSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius);">
        <div class="modal-header border-bottom px-4 py-3">
          <div class="input-group input-group-lg border-0 align-items-center">
            <i class="bi bi-search text-muted fs-4 me-2"></i>
            <input type="text" id="globalSearchInput" class="form-control border-0 shadow-none fs-5" placeholder="Ketik kata kunci (contoh: PPDB, Jurusan, Lowongan, Beasiswa)..." autocomplete="off">
            <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Tutup"></button>
          </div>
        </div>
        <div class="modal-body p-4" style="max-height: 480px; overflow-y: auto;">
          <div id="searchQuickLinks" class="mb-3">
            <p class="text-muted small fw-semibold mb-2">Pencarian Cepat Populer:</p>
            <div class="d-flex flex-wrap gap-2">
              <button class="btn btn-sm btn-light quick-search-tag" data-query="PPDB"><i class="bi bi-mortarboard me-1 text-primary"></i> PPDB Online</button>
              <button class="btn btn-sm btn-light quick-search-tag" data-query="Jurusan"><i class="bi bi-laptop me-1 text-success"></i> Program Jurusan</button>
              <button class="btn btn-sm btn-light quick-search-tag" data-query="Lowongan"><i class="bi bi-briefcase me-1 text-warning"></i> Lowongan BKK</button>
              <button class="btn btn-sm btn-light quick-search-tag" data-query="Ekstrakurikuler"><i class="bi bi-trophy me-1 text-info"></i> Ekstrakurikuler</button>
              <button class="btn btn-sm btn-light quick-search-tag" data-query="Unduhan"><i class="bi bi-download me-1 text-secondary"></i> Unduhan Dokumen</button>
            </div>
          </div>
          
          <div id="searchResultsSpinner" class="text-center py-4 d-none">
            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Mencari...</span></div>
            <p class="text-muted small mt-2">Mencari informasi...</p>
          </div>

          <div id="searchResultsList" class="d-flex flex-column gap-2"></div>

          <div id="searchEmptyMessage" class="text-center py-4 d-none">
            <i class="bi bi-search text-muted fs-1 mb-2 d-block opacity-50"></i>
            <p class="text-muted mb-0">Tidak ditemukan hasil yang cocok dengan kata kunci Anda.</p>
          </div>
        </div>
        <div class="modal-footer bg-light border-top px-4 py-2 d-flex justify-content-between">
          <small class="text-muted"><kbd class="bg-white text-dark border">ESC</kbd> untuk menutup</small>
          <small class="text-muted"><kbd class="bg-white text-dark border">Ctrl</kbd> + <kbd class="bg-white text-dark border">K</kbd> buka pencarian</small>
        </div>
      </div>
    </div>
  </div>

  <!-- ================= MULTI-CS WHATSAPP FLOATING WIDGET ================= -->
  <?php 
    $waPPDB = !empty($profile['whatsapp_ppdb']) ? preg_replace('/[^0-9]/', '', $profile['whatsapp_ppdb']) : '6281234567890';
    $waBKK  = !empty($profile['whatsapp_bkk']) ? preg_replace('/[^0-9]/', '', $profile['whatsapp_bkk']) : '6281234567891';
    $waTU   = !empty($profile['whatsapp_tu']) ? preg_replace('/[^0-9]/', '', $profile['whatsapp_tu']) : '6281234567892';
  ?>
  <div class="wa-widget-wrapper">
    <div id="waPopupCard" class="wa-popup-card shadow-lg d-none">
      <div class="wa-popup-header d-flex justify-content-between align-items-center">
        <div>
          <h6 class="fw-bold mb-0 text-white"><i class="bi bi-whatsapp me-2"></i>Layanan WhatsApp</h6>
          <small class="text-white-50">Silakan pilih bagian yang ingin dihubungi:</small>
        </div>
        <button id="waCloseBtn" class="btn btn-sm btn-link text-white text-decoration-none p-0 fs-5"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="wa-popup-body p-3 d-flex flex-column gap-2">
        <a href="https://wa.me/<?= e($waPPDB) ?>?text=Halo%20Panitia%20PPDB%2C%20saya%20ingin%20bertanya%20informasi%20pendaftaran%20sekolah." target="_blank" rel="noopener" class="wa-contact-item">
          <div class="wa-contact-icon bg-primary text-white"><i class="bi bi-mortarboard-fill"></i></div>
          <div class="wa-contact-info">
            <div class="fw-bold text-dark small">Panitia PPDB Online</div>
            <div class="text-muted" style="font-size: 0.75rem;">Informasi &amp; Pendaftaran Siswa Baru</div>
          </div>
          <i class="bi bi-chevron-right text-muted ms-auto"></i>
        </a>
        <a href="https://wa.me/<?= e($waBKK) ?>?text=Halo%20Bursa%20Kerja%20Khusus%20(BKK)%2C%20saya%20ingin%20informasi%20lowongan%2Fmagang." target="_blank" rel="noopener" class="wa-contact-item">
          <div class="wa-contact-icon bg-warning text-dark"><i class="bi bi-briefcase-fill"></i></div>
          <div class="wa-contact-info">
            <div class="fw-bold text-dark small">Bursa Kerja Khusus (BKK)</div>
            <div class="text-muted" style="font-size: 0.75rem;">Lowongan Kerja, PKL, &amp; Karir Alumni</div>
          </div>
          <i class="bi bi-chevron-right text-muted ms-auto"></i>
        </a>
        <a href="https://wa.me/<?= e($waTU) ?>?text=Halo%20Tata%20Usaha%2C%20saya%20ingin%20bertanya%20perihal%20administrasi%20sekolah." target="_blank" rel="noopener" class="wa-contact-item">
          <div class="wa-contact-icon bg-success text-white"><i class="bi bi-building"></i></div>
          <div class="wa-contact-info">
            <div class="fw-bold text-dark small">Tata Usaha (TU) Sekolah</div>
            <div class="text-muted" style="font-size: 0.75rem;">Surat Menyurat &amp; Administrasi</div>
          </div>
          <i class="bi bi-chevron-right text-muted ms-auto"></i>
        </a>
      </div>
      <div class="wa-popup-footer px-3 py-2 bg-light text-center border-top">
        <small class="text-muted" style="font-size: 0.75rem;">Respon cepat pada jam kerja (07.30 - 15.00 WIB)</small>
      </div>
    </div>

    <button id="waToggleBtn" class="btn-wa-float shadow-soft" aria-label="Chat WhatsApp">
      <i class="bi bi-whatsapp"></i>
    </button>
  </div>

  <button class="btn-back-to-top" aria-label="Kembali ke atas">
    <i class="bi bi-arrow-up"></i>
  </button>

  <script src="<?= e(ASSETS_URL . '/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
  <script src="<?= e(ASSETS_URL . '/js/main.js') ?>"></script>
</body>
</html>
