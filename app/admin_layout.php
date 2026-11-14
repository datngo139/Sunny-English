<?php
declare(strict_types=1);

function admin_head(string $title, string $active = '', ?array $admin = null): void
{
    // Phải mở session (và tạo mã CSRF) TRƯỚC khi in HTML, nếu không cookie phiên không gửi được.
    admin_session_start();
    csrf_token();
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store');
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
      <a href="<?= e(url('/admin/khoa-hoc.php')) ?>"<?= $active === 'courses' ? ' aria-current="page"' : '' ?>><?= icon('graduation-cap') ?>Khoá học</a>
      <a href="<?= e(url('/admin/sw-tu-vung.php')) ?>"<?= $active === 'vocab' ? ' aria-current="page"' : '' ?>><?= icon('shapes') ?>Từ vựng</a>
      <a href="<?= e(url('/admin/sw-truyen.php')) ?>"<?= $active === 'stories' ? ' aria-current="page"' : '' ?>><?= icon('book-open') ?>Truyện</a>
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
    if (!empty($_SESSION['admin_id'])) {
        echo '<p class="adm-legacy">Form đăng ký học thử đã gỡ khỏi website. Dữ liệu cũ vẫn còn: <a href="' . e(url('/admin/dang-ky.php')) . '">xem danh sách đăng ký cũ</a>.</p>';
    }
    echo "</main>\n" . $extraScripts . '<script src="' . e(asset('/assets/js/admin.js')) . '" defer></script>' . "\n</body>\n</html>\n";
}

/** Ô chọn ảnh tải lên (dùng với assets/js/admin.js, form phải có data-upload và data-csrf). */
function admin_image_picker(string $name, string $value): string
{
    $has = $value !== '';
    return '<div class="picker-row" data-picker>'
        . '<input type="hidden" name="' . e($name) . '" value="' . e($value) . '">'
        . '<div class="cover-preview"' . ($has ? '' : ' data-empty="1"') . '>' . ($has ? '<img src="' . e(url($value)) . '" alt="">' : '<span>' . icon('image') . 'Chưa có ảnh</span>') . '</div>'
        . '<div class="cover-actions"><button class="btn btn-outline btn-sm" type="button" data-pick>' . icon('upload') . 'Chọn ảnh</button>'
        . '<button class="btn btn-ghost btn-sm danger" type="button" data-clear' . ($has ? '' : ' hidden') . '>Bỏ ảnh</button></div>'
        . '<input type="file" accept="image/jpeg,image/png,image/webp,image/gif" hidden></div>';
}

/** Ô chọn hình vẽ sẵn của Sunny World. */
function admin_art_select(string $name, string $value, string $id): string
{
    $h = '<span class="control has-select"><select id="' . e($id) . '" name="' . e($name) . '"><option value="">Không dùng hình vẽ sẵn</option>';
    foreach (sw_art_keys() as $k) {
        $h .= '<option value="' . e($k) . '"' . ($value === $k ? ' selected' : '') . '>' . e($k) . '</option>';
    }
    // Hình màu / số là hình sinh tự động: giữ lại nếu đang được dùng.
    if ($value !== '' && !in_array($value, sw_art_keys(), true) && sw_art($value) !== '') {
        $h .= '<option value="' . e($value) . '" selected>' . e($value) . '</option>';
    }
    return $h . '</select></span>';
}

/** Giá trị hình vẽ hợp lệ gửi từ form (khoá lạ → rỗng). */
function admin_clean_art(string $key): string
{
    return $key !== '' && sw_art($key) !== '' ? $key : '';
}
