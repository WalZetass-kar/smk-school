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
$pageDescription = $pageDescription ?? ($profile['description'] ?? '');
$pageImage       = $pageImage ?? (ASSETS_URL . '/images/og-default.jpg');
$canonicalPath   = $canonicalPath ?? ($_SERVER['REQUEST_URI'] ?? '/');
$canonicalUrl    = BASE_URL . $canonicalPath;

// Emit standard Security Headers if headers haven't been sent yet
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($fullTitle) ?></title>

<!-- SEO -->
<meta name="description" content="<?= e($pageDescription) ?>">
<link rel="canonical" href="<?= e($canonicalUrl) ?>">
<meta name="robots" content="index, follow">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($fullTitle) ?>">
<meta property="og:description" content="<?= e($pageDescription) ?>">
<meta property="og:image" content="<?= e($pageImage) ?>">
<meta property="og:url" content="<?= e($canonicalUrl) ?>">

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

<!-- Site styles -->
<link href="<?= e(ASSETS_URL . '/css/style.css?v=' . filemtime(BASE_PATH . '/assets/css/style.css')) ?>" rel="stylesheet">

<!-- Dynamic Theme Colors -->
<?php
  $themePrimary   = !empty($profile['theme_color_primary']) ? $profile['theme_color_primary'] : '#0F2C59';
  $themeSecondary = !empty($profile['theme_color_secondary']) ? $profile['theme_color_secondary'] : '#2563EB';
  $themeAccent    = !empty($profile['theme_color_accent']) ? $profile['theme_color_accent'] : '#F59E0B';
?>
<style>
  :root {
    --color-primary: <?= htmlspecialchars($themePrimary, ENT_QUOTES, 'UTF-8') ?>;
    --color-secondary: <?= htmlspecialchars($themeSecondary, ENT_QUOTES, 'UTF-8') ?>;
    --color-accent: <?= htmlspecialchars($themeAccent, ENT_QUOTES, 'UTF-8') ?>;
  }
</style>
</head>
<body>
