<?php
/**
 * admin/includes/header.php
 * Expects $pageTitle to be set by the including page.
 */
$adminPageTitle = $pageTitle ?? 'Dashboard';

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
<meta name="robots" content="noindex, nofollow">
<title><?= e($adminPageTitle) ?> — Admin SMK</title>

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<!-- Local Bootstrap 5.3 + Icons -->
<link href="<?= e(ASSETS_URL . '/vendor/bootstrap/bootstrap.min.css?v=5.3.3') ?>" rel="stylesheet">
<link href="<?= e(ASSETS_URL . '/vendor/bootstrap-icons/bootstrap-icons.css?v=' . filemtime(BASE_PATH . '/assets/vendor/bootstrap-icons/bootstrap-icons.css')) ?>" rel="stylesheet">

<!-- Site styles -->
<link href="<?= e(ASSETS_URL . '/css/style.css?v=' . filemtime(BASE_PATH . '/assets/css/style.css')) ?>" rel="stylesheet">
<link href="<?= e(ASSETS_URL . '/css/admin.css?v=' . filemtime(BASE_PATH . '/assets/css/admin.css')) ?>" rel="stylesheet">

<!-- Local Summernote -->
<link href="<?= e(ASSETS_URL . '/vendor/summernote/summernote-lite.min.css?v=' . filemtime(BASE_PATH . '/assets/vendor/summernote/summernote-lite.min.css')) ?>" rel="stylesheet">
</head>
<body class="admin-body">
