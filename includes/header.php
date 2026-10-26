<?php
/**
 * header.php
 * Expects the including page to optionally set these BEFORE requiring this file:
 *   $pageTitle       (string)  e.g. "Berita Terbaru"
 *   $pageDescription (string)  meta description, ~155 chars
 *   $pageImage       (string)  absolute/relative URL for Open Graph image
 *   $canonicalPath   (string)  e.g. "/berita" — defaults to current request URI
 */
$profile = getSchoolProfile();
$schoolName = $profile['school_name'] ?? 'SMK Negeri Contoh Indonesia';

$pageTitle       = $pageTitle ?? $schoolName;
$fullTitle       = $pageTitle === $schoolName ? $schoolName : $pageTitle . ' — ' . $schoolName;

// Clean plain text description (strips HTML, collapses whitespace, max 160 chars)
$rawDesc         = !empty($pageDescription) ? $pageDescription : ($profile['description'] ?? 'Portal Resmi ' . $schoolName);
$cleanDesc       = trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($rawDesc, ENT_QUOTES, 'UTF-8'))));
$pageDescription = mb_substr($cleanDesc, 0, 160, 'UTF-8');

// Absolute URL for Open Graph Image (WhatsApp & Facebook require full absolute URL with protocol)
$rawImage = $pageImage ?? (!empty($profile['logo']) ? $profile['logo'] : 'assets/images/og-default.jpg');
if (!str_starts_with($rawImage, 'http://') && !str_starts_with($rawImage, 'https://')) {
    $ogImage = rtrim(BASE_URL, '/') . '/' . ltrim($rawImage, '/');
} else {
    $ogImage = $rawImage;
}

$canonicalPath   = $canonicalPath ?? ($_SERVER['REQUEST_URI'] ?? '/');
$canonicalUrl    = str_starts_with($canonicalPath, 'http') ? $canonicalPath : (rtrim(BASE_URL, '/') . $canonicalPath);
$pageType        = $pageType ?? 'website';

// Emit standard Security Headers if headers haven't been sent yet
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net data:; img-src 'self' data: https:; frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://api.qrserver.com;");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($fullTitle) ?></title>

<!-- Standard SEO -->
<meta name="description" content="<?= e($pageDescription) ?>">
<link rel="canonical" href="<?= e($canonicalUrl) ?>">
<meta name="robots" content="index, follow">
<meta name="author" content="<?= e($schoolName) ?>">

<!-- Open Graph / WhatsApp / Facebook Rich Preview -->
<meta property="og:site_name" content="<?= e($schoolName) ?>">
<meta property="og:locale" content="id_ID">
<meta property="og:type" content="<?= e($pageType) ?>">
<meta property="og:title" content="<?= e($fullTitle) ?>">
<meta property="og:description" content="<?= e($pageDescription) ?>">
<meta property="og:url" content="<?= e($canonicalUrl) ?>">
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta property="og:image:secure_url" content="<?= e($ogImage) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?= e($fullTitle) ?>">
<?php if (!empty($publishedTime)): ?>
<meta property="article:published_time" content="<?= e(date('c', strtotime($publishedTime))) ?>">
<meta property="article:author" content="<?= e($authorName ?? $schoolName) ?>">
<?php endif; ?>

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($fullTitle) ?>">
<meta name="twitter:description" content="<?= e($pageDescription) ?>">
<meta name="twitter:image" content="<?= e($ogImage) ?>">
<meta name="twitter:image:alt" content="<?= e($fullTitle) ?>">

<!-- Favicon -->
<link rel="icon" href="<?= e(BASE_URL . '/' . ($profile['favicon'] ?? 'assets/images/favicon.png')) ?>">

<!-- Schema.org: EducationalOrganization -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "EducationalOrganization",
  "name": <?= json_encode($schoolName, JSON_UNESCAPED_UNICODE) ?>,
  "url": <?= json_encode(BASE_URL, JSON_UNESCAPED_UNICODE) ?>,
  "address": <?= json_encode($profile['address'] ?? '', JSON_UNESCAPED_UNICODE) ?>
}
</script>

<!-- Google Fonts with local fallback -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<!-- Local Bootstrap 5.3 + Bootstrap Icons (100% Reliable Offline/Online) -->
<link href="<?= e(ASSETS_URL . '/vendor/bootstrap/bootstrap.min.css?v=5.3.3') ?>" rel="stylesheet">
<link href="<?= e(ASSETS_URL . '/vendor/bootstrap-icons/bootstrap-icons.css?v=' . filemtime(BASE_PATH . '/assets/vendor/bootstrap-icons/bootstrap-icons.css')) ?>" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" onerror="this.onerror=null;">

<!-- Site styles -->
<link href="<?= e(ASSETS_URL . '/css/style.css?v=' . filemtime(BASE_PATH . '/assets/css/style.css')) ?>" rel="stylesheet">

<!-- Dynamic Theme Colors Overrides -->
<?= renderDynamicThemeStyles($profile) ?>
</head>
<body>
