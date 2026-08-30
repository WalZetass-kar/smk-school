<?php
/**
 * admin/includes/header.php
 * Expects $pageTitle to be set by the including page.
 */
$adminPageTitle = $pageTitle ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= e($adminPageTitle) ?> — Admin SMK</title>

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= e(ASSETS_URL . '/css/style.css') ?>" rel="stylesheet">
<link href="<?= e(ASSETS_URL . '/css/admin.css') ?>" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
</head>
<body class="admin-body">
