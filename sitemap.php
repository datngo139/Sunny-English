<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');
$urls = [['/', date('Y-m-d')], ['/bai-viet', date('Y-m-d')]];
$st = db()->prepare("SELECT slug, COALESCE(updated_at, published_at) AS m FROM posts WHERE status='published' AND published_at <= ? ORDER BY published_at DESC LIMIT 1000");
$st->execute([date('Y-m-d H:i:s')]);
foreach ($st as $r) {
    $urls[] = ['/bai-viet/' . $r['slug'], date('Y-m-d', strtotime((string) $r['m']))];
}
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as [$loc, $mod]) {
    echo '  <url><loc>' . e(abs_url($loc)) . '</loc><lastmod>' . e($mod) . '</lastmod></url>' . "\n";
}
echo '</urlset>' . "\n";
