<?php
/**
 * pages/sambutan.php — /profil/sambutan
 * Halaman Sambutan Resmi Kepala Sekolah
 */
$db        = getDB();
$profile   = getSchoolProfile();
$principal = $db->query("SELECT * FROM principal_message ORDER BY id LIMIT 1")->fetch();

$pageTitle       = 'Sambutan Kepala Sekolah';
$pageDescription = $principal ? excerpt($principal['content'], 155) : 'Sambutan resmi Kepala Sekolah ' . e($profile['school_name'] ?? 'kami');
$canonicalPath   = '/profil/sambutan';

require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';

// Calculate reading time estimate
$plainText = strip_tags($principal['content'] ?? '');
$wordCount = str_word_count($plainText);
$readTime  = max(1, (int) ceil($wordCount / 180));
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="container pt-4">
  <ol class="breadcrumb small mb-0">
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
    <li class="breadcrumb-item"><a href="<?= e(BASE_URL) ?>/profil">Profil</a></li>
    <li class="breadcrumb-item active" aria-current="page">Sambutan Kepala Sekolah</li>
  </ol>
</nav>

<section class="section pt-3 pb-5">
  <div class="container">
    <!-- Header -->
    <div class="text-center mx-auto mb-5 reveal-up" style="max-width:740px;">
      <h1 class="section-title mb-2">Sambutan Kepala Sekolah</h1>
      <p class="section-subtitle mx-auto">Komitmen kepemimpinan, arah pengembangan mutu, dan dedikasi dalam mencetak generasi vokasi yang unggul, berakhlak mulia, dan siap bersaing di kancah global.</p>
      
      <div class="d-inline-flex align-items-center gap-3 text-muted small mt-2 bg-light px-3 py-1.5 rounded-pill border">
        <span><i class="bi bi-person-check-fill text-primary me-1"></i><?= e($principal['principal_name'] ?? 'Kepala Sekolah') ?></span>
        <span>&bull;</span>
        <span><i class="bi bi-clock me-1"></i><?= $readTime ?> Menit Baca</span>
        <span>&bull;</span>
        <span><i class="bi bi-calendar-check me-1"></i>Tahun Ajaran Aktif</span>
      </div>
    </div>

    <!-- Main Two-Column Layout -->
    <div class="row g-4 g-lg-5">
      <!-- Left Column: Main Message & Leadership Pillars -->
      <div class="col-lg-8 reveal-left">
        <?php if ($principal): ?>
          <!-- Executive Lead Quote Banner -->
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 text-white" style="background: linear-gradient(135deg, #0F2C59 0%, #183E7A 100%);">
            <div class="card-body p-4 p-md-5 position-relative">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold" style="font-size:12px;">
                  <i class="bi bi-quote me-1"></i> PESAN UTAMA
                </span>
                <i class="bi bi-chat-square-quote fs-1 text-white-50 opacity-25"></i>
              </div>
              
              <p class="fs-5 fw-semibold mb-0 text-white" style="line-height: 1.65; font-style: italic;">
                "Pendidikan vokasi bukan sekadar transfer ilmu pengetahuan, melainkan proses membentuk karakter, mengasah keahlian terapan nyata, dan menumbuhkan daya juang agar siap menjadi motor kemajuan bangsa."
              </p>
            </div>
          </div>

          <!-- Principal Message Content -->
          <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-5">
            <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom">
              <i class="bi bi-file-earmark-text-fill text-accent fs-4"></i>
              <h2 class="h5 fw-bold text-primary mb-0">Penyampaian Sambutan Resmi</h2>
            </div>

            <div class="article-body" style="font-size: 1.02rem; line-height: 2; color: #334155;">
              <?= cleanHtml($principal['content']) ?>
            </div>

            <!-- Formal Signature & Seal Box -->
            <div class="mt-5 pt-4 border-top">
              <div class="row align-items-center">
                <div class="col-sm-6 mb-3 mb-sm-0">
                  <div class="d-flex align-items-center gap-2 text-success small fw-semibold">
                    <i class="bi bi-shield-check fs-5"></i>
                    <span>Tervalidasi Resmi oleh Pimpinan Sekolah</span>
                  </div>
                  <div class="text-muted small mt-1">
                    <?= e($profile['school_name'] ?? 'SMK') ?> &middot; <?= e($profile['city'] ?? 'Pekanbaru') ?>
                  </div>
                </div>
                <div class="col-sm-6 text-sm-end">
                  <p class="small text-muted mb-1"><?= e($profile['city'] ?? 'Pekanbaru') ?>, <?= date('d F Y') ?></p>
                  <p class="small text-muted fw-semibold mb-3">Kepala <?= e($profile['school_name'] ?? 'SMK') ?></p>
                  <div class="d-inline-block text-start border-bottom border-dark pb-1" style="min-width: 180px;">
                    <span class="fw-bold text-primary fs-6 d-block"><?= e($principal['principal_name']) ?></span>
                  </div>
                  <div class="small text-muted mt-1"><?= e($principal['position']) ?></div>
                </div>
              </div>
            </div>

            <!-- Share & Action Toolbar -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4 pt-3 border-top bg-light p-3 rounded-3">
              <div class="d-flex align-items-center gap-2">
                <span class="small fw-bold text-dark"><i class="bi bi-share me-1 text-primary"></i>Bagikan:</span>
                <a href="https://api.whatsapp.com/send?text=<?= urlencode('Sambutan Kepala ' . ($profile['school_name'] ?? 'Sekolah') . ': ' . BASE_URL . '/profil/sambutan') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success" title="Bagikan ke WhatsApp">
                  <i class="bi bi-whatsapp"></i>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . '/profil/sambutan') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary" title="Bagikan ke Facebook">
                  <i class="bi bi-facebook"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="navigator.clipboard.writeText(window.location.href); alert('Tautan berhasil disalin!');" title="Salin Tautan">
                  <i class="bi bi-link-45deg"></i>
                </button>
              </div>

              <button type="button" class="btn btn-sm btn-outline-dark" onclick="window.print();">
                <i class="bi bi-printer me-1"></i>Cetak Sambutan
              </button>
            </div>
          </div>

          <!-- 4 Pilar Kepemimpinan & Komitmen Pendidikan -->
          <div class="mb-4">
            <div class="d-flex align-items-center gap-2 mb-3">
              <i class="bi bi-compass-fill text-accent fs-4"></i>
              <h2 class="h5 fw-bold text-primary mb-0">4 Pilar Komitmen Mutu Pendidikan</h2>
            </div>
            <p class="text-muted small mb-4">Prinsip fundamental kepemimpinan dalam mengarahkan seluruh ekosistem sekolah menuju standar keunggulan.</p>

            <div class="row g-3">
              <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white hover-lift border-top border-3 border-primary">
                  <div class="d-flex align-items-start gap-3">
                    <div class="p-2.5 rounded-3 bg-primary bg-opacity-10 text-primary fs-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px; height:48px;">
                      <i class="bi bi-laptop-fill"></i>
                    </div>
                    <div>
                      <h3 class="h6 fw-bold text-primary mb-1">1. Kurikulum Industri 4.0</h3>
                      <p class="small text-muted mb-0">Penyelarasan kurikulum terpadu bersama mitra industri terkemuka dengan fasilitas laboratorium dan bengkel praktik berstandar kerja.</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white hover-lift border-top border-3 border-warning">
                  <div class="d-flex align-items-start gap-3">
                    <div class="p-2.5 rounded-3 bg-warning bg-opacity-10 text-warning-emphasis fs-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px; height:48px;">
                      <i class="bi bi-award-fill"></i>
                    </div>
                    <div>
                      <h3 class="h6 fw-bold text-primary mb-1">2. Karakter &amp; Disiplin 5R</h3>
                      <p class="small text-muted mb-0">Pembiasaan etos kerja profesional, integritas, kepemimpinan, dan nilai religius yang kokoh pada setiap peserta didik.</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white hover-lift border-top border-3 border-success">
                  <div class="d-flex align-items-start gap-3">
                    <div class="p-2.5 rounded-3 bg-success bg-opacity-10 text-success fs-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px; height:48px;">
                      <i class="bi bi-briefcase-fill"></i>
                    </div>
                    <div>
                      <h3 class="h6 fw-bold text-primary mb-1">3. Penyaluran Kerja (BKK)</h3>
                      <p class="small text-muted mb-0">Kerja sama rekrutmen langsung, program magang industri bersertifikat BNSP, dan bursa kerja khusus bagi para alumni.</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white hover-lift border-top border-3 border-info">
                  <div class="d-flex align-items-start gap-3">
                    <div class="p-2.5 rounded-3 bg-info bg-opacity-10 text-info-emphasis fs-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px; height:48px;">
                      <i class="bi bi-lightbulb-fill"></i>
                    </div>
                    <div>
                      <h3 class="h6 fw-bold text-primary mb-1">4. Technopreneurship</h3>
                      <p class="small text-muted mb-0">Mendorong inovasi *teaching factory*, kemandirian wirausaha, serta penguasaan teknologi digital mutakhir.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <i class="bi bi-chat-square-dots fs-1 text-muted mb-3 d-block"></i>
            <h2 class="h5 fw-bold text-dark">Sambutan Belum Diterbitkan</h2>
            <p class="text-muted small mb-0">Konten sambutan kepala sekolah saat ini sedang dipersiapkan oleh pihak pengelola.</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Right Column: Executive Profile Card, Profile Navigation & CTA -->
      <div class="col-lg-4">
        <?php if ($principal): ?>
          <!-- Principal Photo & Profile Card -->
          <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white text-center reveal-right">
            <div class="sambutan-photo-frame mx-auto mb-3" style="max-width: 240px;">
              <img src="<?= e(BASE_URL . '/' . $principal['photo']) ?>" alt="<?= e($principal['principal_name']) ?>" class="img-fluid w-100">
            </div>

            <h2 class="h5 fw-bold text-primary mb-1"><?= e($principal['principal_name']) ?></h2>
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1.5 rounded-pill fw-semibold small mb-3">
              <i class="bi bi-person-badge-fill me-1"></i><?= e($principal['position']) ?>
            </span>

            <div class="border-top pt-3 text-start small">
              <div class="d-flex justify-content-between py-1.5 border-bottom">
                <span class="text-muted">Lembaga:</span>
                <span class="fw-bold text-dark text-end"><?= e($profile['school_name'] ?? 'SMK') ?></span>
              </div>
              <div class="d-flex justify-content-between py-1.5 border-bottom">
                <span class="text-muted">Status Kepemimpinan:</span>
                <span class="badge bg-success bg-opacity-15 text-success fw-bold">Aktif &amp; Terakreditasi</span>
              </div>
              <div class="d-flex justify-content-between py-1.5">
                <span class="text-muted">Fokus Pengembangan:</span>
                <span class="fw-semibold text-primary">Vokasi &amp; Industri 4.0</span>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <!-- Quick Profile Navigation -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white reveal-right">
          <h2 class="h6 fw-bold text-primary mb-3"><i class="bi bi-list-nested me-2"></i>Navigasi Profil</h2>
          <div class="list-group list-group-flush">
            <a href="<?= e(BASE_URL) ?>/profil/sambutan" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small fw-bold text-primary" style="background:#f8fafc; padding-left:10px !important; border-radius:6px;">
              <span><i class="bi bi-person-badge me-2 text-primary"></i>Sambutan Kepala Sekolah</span>
              <i class="bi bi-chevron-right text-primary"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/sejarah" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small text-dark">
              <span><i class="bi bi-clock-history me-2 text-primary"></i>Sejarah Sekolah</span>
              <i class="bi bi-chevron-right text-muted"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/visi-misi" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small text-dark">
              <span><i class="bi bi-bullseye me-2 text-primary"></i>Visi &amp; Misi</span>
              <i class="bi bi-chevron-right text-muted"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/struktur-organisasi" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-bottom small text-dark">
              <span><i class="bi bi-diagram-3 me-2 text-primary"></i>Struktur Organisasi</span>
              <i class="bi bi-chevron-right text-muted"></i>
            </a>
            <a href="<?= e(BASE_URL) ?>/profil/guru-tendik" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between px-0 py-2.5 border-0 small text-dark">
              <span><i class="bi bi-people me-2 text-primary"></i>Guru &amp; Tendik</span>
              <i class="bi bi-chevron-right text-muted"></i>
            </a>
          </div>
        </div>

        <!-- School Quality Commitment Widget -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white reveal-right">
          <h2 class="h6 fw-bold text-primary mb-3"><i class="bi bi-award me-2"></i>Komitmen Mutu &amp; Layanan</h2>
          <div class="d-flex flex-column gap-2.5 small">
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-success"></i>
              <span>Kurikulum Merdeka Berbasis Industri</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-success"></i>
              <span>Sertifikasi Kompetensi BNSP &amp; LSP-P1</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-success"></i>
              <span>Program Magang Kerja Industri 6 Bulan</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-check-circle-fill text-success"></i>
              <span>Penyaluran Kerja melalui BKK Resmi</span>
            </div>
          </div>
        </div>

        <!-- PPDB Callout -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-primary text-white text-center reveal-right" style="background: linear-gradient(135deg, #0F2C59 0%, #183E7A 100%);">
          <div class="p-3 bg-white bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:55px;height:55px;">
            <i class="bi bi-mortarboard fs-2 text-warning"></i>
          </div>
          <h2 class="h5 fw-bold text-white mb-2">Raih Masa Depan Vokasi Bersama Kami</h2>
          <p class="small text-white-50 mb-3">Pendaftaran Peserta Didik Baru (PPDB Online) telah dibuka. Pilih jurusan impian Anda sekarang.</p>
          <a href="<?= e(BASE_URL) ?>/ppdb/daftar" class="btn btn-accent btn-sm w-100 fw-bold py-2">
            <i class="bi bi-arrow-right-circle me-1"></i> Daftar PPDB Online
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require BASE_PATH . '/includes/footer.php'; ?>
