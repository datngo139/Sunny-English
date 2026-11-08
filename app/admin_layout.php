<?php
declare(strict_types=1);

function admin_head(string $title, string $active = '', ?array $admin = null): void
{
    // Phải mở session (và tạo mã CSRF) TRƯỚC khi in HTML, nếu không cookie phiên không gửi được.
    admin_session_start();
    csrf_token();
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store');
    $newCount = $admin ? (int) db()->query("SELECT COUNT(*) FROM registrations WHERE status = 'new'")->fetchColumn() : 0;
    $flash = $admin ? flash_get() : null;
    ?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= e($title) ?> — Quản trị Sunny</title>
<link rel="icon" type="image/png" href="<?= e(url('/assets/img/favicon.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&display=swap">
<link rel="stylesheet" href="https://unpkg.com/lucide-static@0.460.0/font/lucide.css">
<link rel="stylesheet" href="<?= e(asset('/assets/css/tokens.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('/assets/css/sunny.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('/assets/css/admin.css')) ?>">
</head>
<body class="admin">
<?php if ($admin): ?>
<header class="adm-bar">
  <div class="in">
    <a class="adm-brand" href="<?= e(url('/admin/')) ?>">
      <img src="<?= e(asset('/assets/img/sunny-logo-mark.png')) ?>" alt="" width="40" height="36">
      <span>Quản trị Sunny</span>
    </a>
    <nav class="adm-nav" aria-label="Menu quản trị">
      <a href="<?= e(url('/admin/')) ?>"<?= $active === 'posts' ? ' aria-current="page"' : '' ?>><?= icon('newspaper') ?>Bài viết</a>
      <a href="<?= e(url('/admin/dang-ky.php')) ?>"<?= $active === 'regs' ? ' aria-current="page"' : '' ?>><?= icon('users') ?>Đăng ký học thử<?php if ($newCount): ?><span class="dot"><?= $newCount ?></span><?php endif; ?></a>
      <a href="<?= e(url('/admin/mat-khau.php')) ?>"<?= $active === 'pw' ? ' aria-current="page"' : '' ?>><?= icon('key-round') ?>Mật khẩu</a>
      <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener"><?= icon('external-link') ?>Xem website</a>
    </nav>
    <form method="post" action="<?= e(url('/admin/logout.php')) ?>" class="adm-logout">
      <?= csrf_field() ?>
      <span class="who"><?= e($admin['username']) ?></span>
      <button class="btn btn-outline btn-sm" type="submit"><?= icon('log-out') ?>Đăng xuất</button>
    </form>
  </div>
</header>
<?php endif; ?>
<main class="adm-main" id="noi-dung">
<?php if ($flash): ?>
  <div class="adm-flash adm-flash-<?= e($flash['type']) ?>" role="status"><?= e($flash['msg']) ?></div>
<?php endif; ?>
<?php
}

function admin_foot(string $extraScripts = ''): void
{
    echo "</main>\n" . $extraScripts . '<script src="' . e(asset('/assets/js/admin.js')) . '" defer></script>' . "\n</body>\n</html>\n";
}
