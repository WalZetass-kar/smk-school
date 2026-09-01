<?php
/**
 * index.php — front controller.
 * Every request (after .htaccess rewriting) lands here and gets routed
 * to the right file under /pages based on the URL path.
 *
 * NOTE: This is a Phase-1 skeleton. Only "/" is wired up to a real page
 * (home.php). The other public routes described in the brief (/profil,
 * /jurusan, /berita, /galeri, /kontak and their detail pages) are listed
 * in $routes so the folder structure and navigation links are already
 * correct — build each pages/*.php file the same way home.php was built,
 * then point its route at it, phase by phase.
 */
require_once __DIR__ . '/config/config.php';

$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri  = rtrim($uri, '/');
$uri  = $uri === '' ? '/' : $uri;

// Static route table. Add an entry here each time a new pages/*.php is built.
$routes = [
    '/'                             => 'home.php',
    '/profil'                       => 'profil.php',
    '/profil/sambutan'              => 'sambutan.php',
    '/profil/sejarah'               => 'sejarah.php',
    '/profil/visi-misi'             => 'visi-misi.php',
    '/profil/struktur-organisasi'   => 'organisasi.php',
    '/profil/guru-tendik'           => 'guru-tendik.php',
    '/jurusan'                      => 'jurusan.php',
    '/berita'                       => 'berita.php',
    '/galeri'                       => 'galeri.php',
    '/kontak'                       => 'kontak.php',
    '/ppdb'                         => 'ppdb.php',
    '/ppdb/daftar'                  => 'ppdb-daftar.php',
    '/ppdb/sukses'                  => 'ppdb-sukses.php',
    '/ppdb/cek-status'              => 'ppdb-cek.php',
    '/ppdb/cetak'                   => 'ppdb-cetak.php',
    '/bkk'                          => 'bkk.php',
    '/ekstrakurikuler'              => 'ekstrakurikuler.php',
    '/prestasi'                     => 'prestasi.php',
    '/unduhan'                      => 'unduhan.php',
    '/kelulusan'                    => 'kelulusan.php',
    '/kelulusan/cetak'              => 'kelulusan-cetak.php',
    '/agenda'                       => 'agenda.php',
    '/buku-tamu'                    => 'buku-tamu.php',
    '/api/search'                   => 'search-api.php',
];

if (isset($routes[$uri])) {
    require BASE_PATH . '/pages/' . $routes[$uri];
    exit;
}

// Detail routes with a slug or ID
if (preg_match('#^/jurusan/detail/([a-z0-9\-]+)$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    require BASE_PATH . '/pages/jurusan-detail.php';
    exit;
}
if (preg_match('#^/berita/([a-z0-9\-]+)$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    require BASE_PATH . '/pages/berita-detail.php';
    exit;
}
if (preg_match('#^/bkk/([a-z0-9\-]+)$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    require BASE_PATH . '/pages/bkk-detail.php';
    exit;
}
if (preg_match('#^/agenda/([a-z0-9\-]+)$#', $uri, $m)) {
    $_GET['slug'] = $m[1];
    require BASE_PATH . '/pages/agenda-detail.php';
    exit;
}
if (preg_match('#^/unduhan/download/([0-9]+)$#', $uri, $m)) {
    $_GET['id'] = (int) $m[1];
    require BASE_PATH . '/pages/unduhan-download.php';
    exit;
}

// No route matched — 404.
http_response_code(404);
$pageTitle = 'Halaman Tidak Ditemukan';
require BASE_PATH . '/includes/header.php';
require BASE_PATH . '/includes/navbar.php';
?>
<section class="section text-center">
  <div class="container">
    <h1 class="display-1 fw-bold text-primary">404</h1>
    <p class="section-subtitle mx-auto mb-4">Halaman yang Anda cari tidak ditemukan atau belum tersedia.</p>
    <a href="<?= e(BASE_URL) ?>/" class="btn btn-accent">Kembali ke Beranda</a>
  </div>
</section>
<?php require BASE_PATH . '/includes/footer.php'; ?>
