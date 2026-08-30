<?php
/**
 * pages/search-api.php — /api/search?q=...
 * Live Global Search API across News, Majors, BKK, Ekskul, Downloads
 */
header('Content-Type: application/json; charset=utf-8');

$query = trim($_GET['q'] ?? '');
if (mb_strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

$db = getDB();
$results = [];
$term = "%$query%";

// 1. News / Berita
$stmt = $db->prepare("SELECT title, slug, published_at FROM news WHERE status = 'publish' AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?) ORDER BY published_at DESC LIMIT 4");
$stmt->execute([$term, $term, $term]);
while ($r = $stmt->fetch()) {
    $results[] = [
        'category' => 'Berita & Informasi',
        'title'    => $r['title'],
        'subtitle' => date('d/m/Y', strtotime($r['published_at'])),
        'url'      => BASE_URL . '/berita/' . $r['slug'],
        'icon'     => 'bi-newspaper',
        'badge'    => 'bg-primary'
    ];
}

// 2. Majors / Jurusan
$stmt = $db->prepare("SELECT name, slug, code, short_description FROM majors WHERE status = 1 AND (name LIKE ? OR short_description LIKE ? OR code LIKE ?) LIMIT 3");
$stmt->execute([$term, $term, $term]);
while ($r = $stmt->fetch()) {
    $results[] = [
        'category' => 'Program Keahlian (Jurusan)',
        'title'    => $r['name'] . ' (' . $r['code'] . ')',
        'subtitle' => mb_strimwidth($r['short_description'], 0, 75, '...'),
        'url'      => BASE_URL . '/jurusan/detail/' . $r['slug'],
        'icon'     => 'bi-mortarboard',
        'badge'    => 'bg-success'
    ];
}

// 3. BKK Jobs / Lowongan
$stmt = $db->prepare("SELECT title, slug, company_name, job_type FROM bkk_jobs WHERE status = 'open' AND (title LIKE ? OR company_name LIKE ? OR location LIKE ?) LIMIT 3");
$stmt->execute([$term, $term, $term]);
while ($r = $stmt->fetch()) {
    $results[] = [
        'category' => 'Bursa Kerja (BKK)',
        'title'    => $r['title'],
        'subtitle' => $r['company_name'] . ' &middot; ' . ucfirst($r['job_type']),
        'url'      => BASE_URL . '/bkk/' . $r['slug'],
        'icon'     => 'bi-briefcase',
        'badge'    => 'bg-warning text-dark'
    ];
}

// 4. Extracurriculars
$stmt = $db->prepare("SELECT name, slug, category, schedule FROM extracurriculars WHERE status = 1 AND (name LIKE ? OR description LIKE ? OR category LIKE ?) LIMIT 3");
$stmt->execute([$term, $term, $term]);
while ($r = $stmt->fetch()) {
    $results[] = [
        'category' => 'Ekstrakurikuler',
        'title'    => $r['name'],
        'subtitle' => $r['category'] . ($r['schedule'] ? ' (' . $r['schedule'] . ')' : ''),
        'url'      => BASE_URL . '/ekstrakurikuler',
        'icon'     => 'bi-activity',
        'badge'    => 'bg-info text-dark'
    ];
}

// 5. Downloads / Unduhan
$stmt = $db->prepare("SELECT id, title, category, file_size FROM downloads WHERE title LIKE ? OR description LIKE ? OR category LIKE ? LIMIT 3");
$stmt->execute([$term, $term, $term]);
while ($r = $stmt->fetch()) {
    $results[] = [
        'category' => 'Pusat Unduhan',
        'title'    => $r['title'],
        'subtitle' => $r['category'] . ' (' . ($r['file_size'] ?: 'File') . ')',
        'url'      => BASE_URL . '/unduhan/download/' . $r['id'],
        'icon'     => 'bi-download',
        'badge'    => 'bg-secondary'
    ];
}

echo json_encode(['results' => $results]);
exit;
