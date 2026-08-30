<?php
/**
 * sitemap.php — generates sitemap.xml dynamically so new berita/jurusan
 * are picked up automatically. Rewritten from /sitemap.xml via .htaccess.
 */
require_once __DIR__ . '/config/config.php';

header('Content-Type: application/xml; charset=UTF-8');

$db = getDB();
$urls = [
    ['loc' => '/', 'priority' => '1.0'],
    ['loc' => '/profil', 'priority' => '0.8'],
    ['loc' => '/profil/sambutan', 'priority' => '0.6'],
    ['loc' => '/profil/sejarah', 'priority' => '0.6'],
    ['loc' => '/profil/visi-misi', 'priority' => '0.6'],
    ['loc' => '/profil/struktur-organisasi', 'priority' => '0.6'],
    ['loc' => '/profil/guru-tendik', 'priority' => '0.6'],
    ['loc' => '/jurusan', 'priority' => '0.9'],
    ['loc' => '/berita', 'priority' => '0.8'],
    ['loc' => '/galeri', 'priority' => '0.6'],
    ['loc' => '/kontak', 'priority' => '0.5'],
    ['loc' => '/ppdb', 'priority' => '0.9'],
    ['loc' => '/ppdb/daftar', 'priority' => '0.9'],
    ['loc' => '/ppdb/cek-status', 'priority' => '0.7'],
    ['loc' => '/bkk', 'priority' => '0.8'],
    ['loc' => '/ekstrakurikuler', 'priority' => '0.7'],
    ['loc' => '/prestasi', 'priority' => '0.7'],
    ['loc' => '/unduhan', 'priority' => '0.6'],
];

foreach ($db->query("SELECT slug, updated_at FROM majors WHERE status = 1") as $m) {
    $urls[] = ['loc' => '/jurusan/detail/' . $m['slug'], 'priority' => '0.7', 'lastmod' => $m['updated_at']];
}
foreach ($db->query("SELECT slug, updated_at FROM news WHERE status = 'publish'") as $n) {
    $urls[] = ['loc' => '/berita/' . $n['slug'], 'priority' => '0.6', 'lastmod' => $n['updated_at']];
}
foreach ($db->query("SELECT slug, updated_at FROM bkk_jobs WHERE status = 'open'") as $b) {
    $urls[] = ['loc' => '/bkk/' . $b['slug'], 'priority' => '0.7', 'lastmod' => $b['updated_at']];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars(BASE_URL . $u['loc'], ENT_XML1) . "</loc>\n";
    if (!empty($u['lastmod'])) {
        echo '    <lastmod>' . date('Y-m-d', strtotime($u['lastmod'])) . "</lastmod>\n";
    }
    echo '    <priority>' . $u['priority'] . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>';
