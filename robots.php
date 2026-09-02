<?php
/**
 * robots.php — Generates dynamic robots.txt pointing to the correct absolute sitemap.xml URL.
 */
require_once __DIR__ . '/config/config.php';

header('Content-Type: text/plain; charset=UTF-8');

$sitemapUrl = rtrim(BASE_URL, '/') . '/sitemap.xml';

echo "User-agent: *\n";
echo "Allow: /\n\n";
echo "# Disallow private and administrative areas\n";
echo "Disallow: /admin/\n";
echo "Disallow: /config/\n";
echo "Disallow: /includes/\n";
echo "Disallow: /assets/uploads/documents/\n\n";
echo "# Dynamic Sitemap Index\n";
echo "Sitemap: " . $sitemapUrl . "\n";
