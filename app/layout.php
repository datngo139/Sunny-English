<?php
declare(strict_types=1);

/** URL tài nguyên tĩnh kèm số phiên bản (đổi file là trình duyệt tải lại). */
function asset(string $path): string
{
    $file = SUNNY_ROOT . '/' . ltrim($path, '/');
    $v = is_file($file) ? filemtime($file) : 1;
    return url($path) . '?v=' . $v;
}

function icon(string $name, string $extra = ''): string
{
    return '<i class="icon icon-' . e($name) . ($extra ? ' ' . e($extra) : '') . '" aria-hidden="true"></i>';
}

/**
 * <head> chung cho trang công khai.
 * $o: title, description, path (đường dẫn chuẩn), image, type ('website'|'article'), noindex
 */
function site_head(array $o = []): void
{
    $c = sunny_contact();
    $title = $o['title'] ?? ($c['center'] . ' — Tiếng Anh cho bé 3–14 tuổi');
    $desc = $o['description'] ?? 'Cho con học thử miễn phí một buổi tại Sunny: lớp nhỏ, cô giáo theo sát, con tự tin nói tiếng Anh và đạt từng mốc Cambridge.';
    $canon = abs_url($o['path'] ?? '/');
    $image = $o['image'] ?? '/assets/img/og-default.png';
    $imageAbs = preg_match('#^https?://#', $image) ? $image : abs_url($image);
    ?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e($canon) ?>">
<?php if (!empty($o['noindex'])): ?><meta name="robots" content="noindex,nofollow">
<?php endif; ?>
<meta name="theme-color" content="#FDBE10">
<meta property="og:type" content="<?= e($o['type'] ?? 'website') ?>">
<meta property="og:locale" content="vi_VN">
<meta property="og:site_name" content="<?= e($c['center']) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e($canon) ?>">
<meta property="og:image" content="<?= e($imageAbs) ?>">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" type="image/png" href="<?= e(url('/assets/img/favicon.png')) ?>">
<link rel="apple-touch-icon" href="<?= e(url('/assets/img/favicon.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&display=swap">
<link rel="stylesheet" href="https://unpkg.com/lucide-static@0.460.0/font/lucide.css">
<link rel="stylesheet" href="<?= e(asset('/assets/css/tokens.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('/assets/css/sunny.css')) ?>">
<script>document.documentElement.className+=' js'</script>
<?= $o['head_extra'] ?? '' ?>
</head>
<body>
<a class="skip-link" href="#noi-dung">Bỏ qua, tới nội dung chính</a>
<?php
}

function site_header(string $active = ''): void
{
    $links = [
        ['/#khoa-hoc', 'Khoá học', 'khoa-hoc'],
        ['/#ve-sunny', 'Về Sunny', 've-sunny'],
        ['/#co-so', 'Cơ sở', 'co-so'],
        ['/bai-viet', 'Bài viết', 'bai-viet'],
        ['/#lien-he', 'Liên hệ', 'lien-he'],
    ];
    ?>
<header class="site-header" id="site-header">
  <div class="bar">
    <a class="brand" href="<?= e(url('/')) ?>" aria-label="Sunny Education — trang chủ">
      <img src="<?= e(asset('/assets/img/sunny-logo-mark.png')) ?>" alt="" width="51" height="46">
      <img src="<?= e(asset('/assets/img/sunny-logo-wordmark.png')) ?>" alt="Sunny Education" style="height:38px;margin-left:8px" width="96" height="38">
    </a>
    <button class="nav-toggle" id="nav-toggle" type="button" aria-label="Mở menu" aria-expanded="false" aria-controls="site-nav"><?= icon('menu') ?></button>
    <nav class="nav" id="site-nav" aria-label="Menu chính">
      <?php foreach ($links as [$href, $label, $key]): ?>
        <a class="link" href="<?= e(url($href)) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
      <a class="btn btn-sm" href="<?= e(url('/#dang-ky')) ?>">Đặt lịch học thử cho con</a>
    </nav>
  </div>
</header>
<?php
}

function site_footer(): void
{
    $c = sunny_contact();
    ?>
<footer class="site-footer" id="lien-he">
  <div class="in">
    <div class="col">
      <span class="logo-chip"><img src="<?= e(asset('/assets/img/sunny-logo-mark.png')) ?>" alt="Sunny Education" width="62" height="56"></span>
      <span><?= e($c['center']) ?></span>
    </div>
    <div class="col">
      <h4>Khoá học</h4>
      <?php foreach (sunny_courses() as $course): ?>
        <a href="<?= e(url('/#khoa-hoc')) ?>" style="font-weight:500"><?= e($course['title']) ?></a>
      <?php endforeach; ?>
      <a href="<?= e(url('/bai-viet')) ?>" style="font-weight:500">Bài viết từ Sunny</a>
    </div>
    <div class="col">
      <h4>Liên hệ</h4>
      <?php if ($c['tel']): ?><a class="item" href="<?= e($c['tel']) ?>"><?= icon('phone') ?><span><?= e($c['phone']) ?></span></a><?php endif; ?>
      <?php if ($c['zaloUrl']): ?><a class="item" href="<?= e($c['zaloUrl']) ?>" target="_blank" rel="noopener"><?= icon('message-circle') ?><span>Zalo: <?= e($c['zalo']) ?></span></a><?php endif; ?>
      <?php if ($c['facebook']): ?><a class="item" href="<?= e($c['facebook']) ?>" target="_blank" rel="noopener"><?= icon('facebook') ?><span>Facebook Sunny</span></a><?php endif; ?>
      <?php if ($c['address']): ?><a class="item" href="<?= e($c['maps']) ?>" target="_blank" rel="noopener" style="font-weight:500"><?= icon('map-pin') ?><span><?= e($c['address']) ?></span></a><?php endif; ?>
    </div>
  </div>
  <div class="copy">© <?= date('Y') ?> <?= e($c['center']) ?> · <a href="<?= e(url('/admin/')) ?>" style="color:var(--blue-300);font-weight:500">Quản trị</a></div>
</footer>
<?php
}

/** Nút Zalo / Gọi nổi ở góc phải màn hình. */
function floating_contact(): string
{
    $c = sunny_contact();
    $h = '<div class="float-contact">';
    if ($c['tel']) {
        $h .= '<a class="fc fc-call" href="' . e($c['tel']) . '" aria-label="Gọi ' . e($c['phone']) . '">' . icon('phone') . '</a>';
    }
    if ($c['zaloUrl']) {
        $h .= '<a class="fc fc-zalo" href="' . e($c['zaloUrl']) . '" target="_blank" rel="noopener" aria-label="Nhắn Zalo ' . e($c['zalo']) . '">' . icon('message-circle') . '<span>Chat Zalo</span></a>';
    }
    return $h . '</div>';
}

function site_end(array $o = []): void
{
    echo floating_contact();
    echo '<script src="' . e(asset('/assets/js/site.js')) . '" defer></script>' . "\n";
    echo ($o['scripts'] ?? '');
    echo "</body>\n</html>\n";
}

/** Dòng gỡ rủi ro dưới nút chính (luật 4). */
function risk_note(string $text, string $align = 'center'): string
{
    return '<p class="risk-note" style="text-align:' . e($align) . '">' . e($text) . '</p>';
}

/** Khối liên hệ nhanh (luật 5): Zalo + số điện thoại nổi nhất, "Xem đường đến lớp" là nút phụ. */
function nearby_actions(): string
{
    $c = sunny_contact();
    $h = '<div class="contact-stack">';
    if ($c['zaloUrl']) {
        $h .= '<a class="btn btn-secondary btn-block btn-zalo" href="' . e($c['zaloUrl']) . '" target="_blank" rel="noopener">'
            . icon('message-circle') . '<span class="zl-text"><span class="zl-main">Nhắn Zalo tư vấn</span><span class="zl-num">' . e($c['zalo']) . '</span></span></a>';
    }
    $row = '';
    if ($c['tel']) {
        $row .= '<a class="btn btn-outline btn-block only-mobile" href="' . e($c['tel']) . '">' . icon('phone') . 'Gọi ngay</a>';
    }
    if ($c['maps']) {
        $row .= '<a class="btn btn-outline btn-block btn-map" href="' . e($c['maps']) . '" target="_blank" rel="noopener">' . icon('map-pin') . 'Xem đường đến lớp</a>';
    }
    if ($row !== '') {
        $h .= '<div class="contact-row">' . $row . '</div>';
    }
    return $h . '</div>';
}

/** Lấy các bài đã đăng, mới nhất trước. */
function published_posts(int $limit, int $offset = 0): array
{
    $st = db()->prepare("SELECT id, slug, title, excerpt, cover, body, published_at FROM posts
        WHERE status = 'published' AND published_at <= ? ORDER BY published_at DESC, id DESC LIMIT ? OFFSET ?");
    $st->bindValue(1, date('Y-m-d H:i:s'));
    $st->bindValue(2, $limit, PDO::PARAM_INT);
    $st->bindValue(3, $offset, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll();
}

function published_post_count(): int
{
    $st = db()->prepare("SELECT COUNT(*) FROM posts WHERE status = 'published' AND published_at <= ?");
    $st->execute([date('Y-m-d H:i:s')]);
    return (int) $st->fetchColumn();
}

/** Thẻ bài viết dùng ở trang chủ và trang danh sách. */
function post_card(array $p): string
{
    $ex = $p['excerpt'] !== '' ? $p['excerpt'] : excerpt_from_html($p['body'], 130);
    $thumb = $p['cover'] !== ''
        ? '<img src="' . e(url($p['cover'])) . '" alt="" loading="lazy">'
        : icon('newspaper');
    return '<a class="post-card" href="' . e(url('/bai-viet/' . $p['slug'])) . '">'
        . '<div class="post-thumb">' . $thumb . '</div>'
        . '<div class="body"><span class="meta">' . e(vn_date($p['published_at'])) . '</span>'
        . '<h3>' . e($p['title']) . '</h3><p>' . e($ex) . '</p><span class="more">Đọc bài →</span></div></a>';
}
