<?php
/**
 * sitemap.php — Generates dynamic sitemap.xml compliant with Sitemaps Protocol 0.9
 * and Google Image Sitemap extensions. Automatically reflects active modules.
 */
require_once __DIR__ . '/config/config.php';

header('Content-Type: application/xml; charset=UTF-8');

$db = getDB();
$urls = [];

// Base Homepage
$urls[] = ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily'];

// School Profile Pages
$urls[] = ['loc' => '/profil', 'priority' => '0.8', 'changefreq' => 'monthly'];
$urls[] = ['loc' => '/profil/sambutan', 'priority' => '0.7', 'changefreq' => 'monthly'];
$urls[] = ['loc' => '/profil/sejarah', 'priority' => '0.6', 'changefreq' => 'monthly'];
$urls[] = ['loc' => '/profil/visi-misi', 'priority' => '0.7', 'changefreq' => 'monthly'];
$urls[] = ['loc' => '/profil/struktur-organisasi', 'priority' => '0.6', 'changefreq' => 'monthly'];
$urls[] = ['loc' => '/profil/guru-tendik', 'priority' => '0.7', 'changefreq' => 'monthly'];
$urls[] = ['loc' => '/kontak', 'priority' => '0.6', 'changefreq' => 'monthly'];

// Module-Filtered Pages
if (isModuleActive('ppdb')) {
    $urls[] = ['loc' => '/ppdb', 'priority' => '0.9', 'changefreq' => 'daily'];
    $urls[] = ['loc' => '/ppdb/daftar', 'priority' => '0.9', 'changefreq' => 'daily'];
    $urls[] = ['loc' => '/ppdb/cek-status', 'priority' => '0.8', 'changefreq' => 'daily'];
}

if (isModuleActive('majors')) {
    $urls[] = ['loc' => '/jurusan', 'priority' => '0.9', 'changefreq' => 'weekly'];
    $majors = $db->query("SELECT slug, name, image, updated_at FROM majors WHERE status = 1")->fetchAll();
    foreach ($majors as $m) {
        $img = !empty($m['image']) ? (BASE_URL . '/' . ltrim($m['image'], '/')) : null;
        $urls[] = [
            'loc' => '/jurusan/detail/' . $m['slug'],
            'priority' => '0.8',
            'changefreq' => 'weekly',
            'lastmod' => $m['updated_at'],
            'image' => $img,
            'image_title' => 'Jurusan ' . $m['name']
        ];
    }
}

if (isModuleActive('news')) {
    $urls[] = ['loc' => '/berita', 'priority' => '0.8', 'changefreq' => 'daily'];
    $news = $db->query("SELECT slug, title, featured_image, published_at, updated_at FROM news WHERE status = 'publish' ORDER BY published_at DESC LIMIT 200")->fetchAll();
    foreach ($news as $n) {
        $img = !empty($n['featured_image']) ? (BASE_URL . '/' . ltrim($n['featured_image'], '/')) : null;
        $urls[] = [
            'loc' => '/berita/' . $n['slug'],
            'priority' => '0.7',
            'changefreq' => 'weekly',
            'lastmod' => $n['updated_at'] ?: $n['published_at'],
            'image' => $img,
            'image_title' => $n['title']
        ];
    }
}

if (isModuleActive('agendas')) {
    $urls[] = ['loc' => '/agenda', 'priority' => '0.7', 'changefreq' => 'weekly'];
    $agendas = $db->query("SELECT slug, title, updated_at FROM agendas WHERE status = 1 ORDER BY start_date DESC LIMIT 100")->fetchAll();
    foreach ($agendas as $a) {
        $urls[] = [
            'loc' => '/agenda/' . $a['slug'],
            'priority' => '0.6',
            'changefreq' => 'monthly',
            'lastmod' => $a['updated_at']
        ];
    }
}

if (isModuleActive('bkk')) {
    $urls[] = ['loc' => '/bkk', 'priority' => '0.8', 'changefreq' => 'weekly'];
    $jobs = $db->query("SELECT slug, updated_at FROM bkk_jobs WHERE status = 'open' LIMIT 50")->fetchAll();
    foreach ($jobs as $b) {
        $urls[] = [
            'loc' => '/bkk/' . $b['slug'],
            'priority' => '0.7',
            'changefreq' => 'weekly',
            'lastmod' => $b['updated_at']
        ];
    }
}

if (isModuleActive('galleries')) {
    $urls[] = ['loc' => '/galeri', 'priority' => '0.6', 'changefreq' => 'weekly'];
}
if (isModuleActive('extracurriculars')) {
    $urls[] = ['loc' => '/ekstrakurikuler', 'priority' => '0.6', 'changefreq' => 'monthly'];
}
if (isModuleActive('achievements')) {
    $urls[] = ['loc' => '/prestasi', 'priority' => '0.6', 'changefreq' => 'monthly'];
}
if (isModuleActive('downloads')) {
    $urls[] = ['loc' => '/unduhan', 'priority' => '0.5', 'changefreq' => 'monthly'];
}
if (isModuleActive('guestbook')) {
    $urls[] = ['loc' => '/buku-tamu', 'priority' => '0.5', 'changefreq' => 'monthly'];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars(BASE_URL . $u['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
    if (!empty($u['lastmod'])) {
        echo '    <lastmod>' . date('Y-m-d', strtotime($u['lastmod'])) . "</lastmod>\n";
    }
    if (!empty($u['changefreq'])) {
        echo '    <changefreq>' . $u['changefreq'] . "</changefreq>\n";
    }
    echo '    <priority>' . $u['priority'] . "</priority>\n";
    if (!empty($u['image'])) {
        echo "    <image:image>\n";
        echo '      <image:loc>' . htmlspecialchars($u['image'], ENT_XML1, 'UTF-8') . "</image:loc>\n";
        if (!empty($u['image_title'])) {
            echo '      <image:title>' . htmlspecialchars($u['image_title'], ENT_XML1, 'UTF-8') . "</image:title>\n";
        }
        echo "    </image:image>\n";
    }
    echo "  </url>\n";
}
echo '</urlset>';

