<?php
/**
 * Router cho máy chủ PHP tích hợp (chỉ dùng khi chạy thử trên máy, KHÔNG dùng trên hosting).
 * Mô phỏng các quy tắc trong .htaccess. Chạy:  php -S localhost:8000 dev/router.php
 */
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = $_SERVER['DOCUMENT_ROOT'];
if (preg_match('#^/(app|data|dev|\.git)(/|$)#', $uri) || preg_match('#/\.#', $uri) && !str_contains($uri,'.well-known')) { http_response_code(404); echo "blocked"; return true; }
if (preg_match('#\.(sqlite|sqlite-wal|sqlite-shm|log|md)$#', $uri)) { http_response_code(404); echo "blocked"; return true; }
if ($uri === '/bai-viet' || $uri === '/bai-viet/') { require $root.'/blog.php'; return true; }
if (preg_match('#^/bai-viet/([a-z0-9-]+)/?$#', $uri, $m)) { $_GET['slug']=$m[1]; require $root.'/post.php'; return true; }
if ($uri === '/sitemap.xml') { require $root.'/sitemap.php'; return true; }
if ($uri === '/robots.txt') { require $root.'/robots.php'; return true; }
$f = $root.$uri;
if ($uri !== '/' && is_file($f)) {
  if (preg_match('#^/uploads/.*\.php$#', $uri)) { http_response_code(403); echo "denied"; return true; }
  return false;
}
if (is_dir($f) && is_file($f.'/index.php')) { chdir($f); require $f.'/index.php'; return true; }
if ($uri === '/') { require $root.'/index.php'; return true; }
require $root.'/404.php'; return true;
