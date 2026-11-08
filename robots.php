<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';

header('Content-Type: text/plain; charset=utf-8');
echo "User-agent: *\n";
echo "Disallow: " . url('/admin/') . "\n";
echo "Disallow: " . url('/api/') . "\n";
echo "Allow: /\n\n";
echo 'Sitemap: ' . abs_url('/sitemap.xml') . "\n";
