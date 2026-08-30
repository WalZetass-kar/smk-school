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
<link href="<?= e(ASSETS_URL . '/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
<link href="<?= e(ASSETS_URL . '/vendor/bootstrap-icons/bootstrap-icons.css') ?>" rel="stylesheet">

<!-- Site styles -->
<link href="<?= e(ASSETS_URL . '/css/style.css?v=' . filemtime(BASE_PATH . '/assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body>
