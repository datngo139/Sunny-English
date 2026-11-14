<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';

$slug = (string) ($_GET['slug'] ?? '');
$post = null;
$preview = false;

if (preg_match('/^[a-z0-9-]{1,100}$/', $slug)) {
    $st = db()->prepare('SELECT * FROM posts WHERE slug = ?');
    $st->execute([$slug]);
    $post = $st->fetch() ?: null;
}

if ($post) {
    $live = $post['status'] === 'published' && $post['published_at'] <= date('Y-m-d H:i:s');
    if (!$live) {
        // Bản nháp / hẹn giờ: chỉ admin đã đăng nhập mới xem trước được.
        if (is_admin_viewer()) {
            $preview = true;
        } else {
            $post = null;
        }
    }
}

if (!$post) {
    http_response_code(404);
    site_head(['title' => 'Không tìm thấy bài viết — Sunny', 'path' => '/bai-viet', 'noindex' => true]);
    site_header('bai-viet');
    echo '<main id="noi-dung" class="container"><div class="empty" style="padding:96px 0"><h1 style="font-size:36px;margin-bottom:12px">Không tìm thấy bài viết</h1>'
       . '<p style="margin-bottom:24px">Bài viết này không tồn tại hoặc đã được gỡ.</p>'
       . '<a class="btn" href="' . e(url('/bai-viet')) . '">Xem các bài viết khác</a></div></main>';
    site_footer();
    site_end();
    exit;
}

$desc = $post['excerpt'] !== '' ? $post['excerpt'] : excerpt_from_html($post['body'], 155);
$others = array_values(array_filter(published_posts(4), fn($p) => (int) $p['id'] !== (int) $post['id']));
$others = array_slice($others, 0, 3);

site_head([
    'title' => $post['title'] . ' — Sunny',
    'description' => $desc,
    'path' => '/bai-viet/' . $post['slug'],
    'image' => $post['cover'] !== '' ? $post['cover'] : '/assets/img/og-default.png',
    'type' => 'article',
    'noindex' => $preview,
]);
site_header('bai-viet');
?>
<main id="noi-dung">
  <?php if ($preview): ?>
    <div style="background:var(--sun-100);color:var(--brown-700);text-align:center;padding:10px 20px;font-weight:700;font-size:14px">
      Đây là bản xem trước — bài chưa được đăng công khai. <a href="<?= e(url('/admin/bai-viet.php?id=' . (int) $post['id'])) ?>">Quay lại chỉnh sửa</a>
    </div>
  <?php endif; ?>
  <article class="article">
    <a class="back" href="<?= e(url('/bai-viet')) ?>"><?= icon('chevron-left') ?>Tất cả bài viết</a>
    <h1><?= e($post['title']) ?></h1>
    <div class="byline"><?= e(vn_date($post['published_at'] ?: $post['created_at'])) ?> · Sunny Education</div>
    <?php if ($post['cover'] !== ''): ?>
      <div class="cover"><img src="<?= e(url($post['cover'])) ?>" alt="<?= e($post['title']) ?>"></div>
    <?php endif; ?>
    <div class="prose" style="margin-top:28px"><?= $post['body'] /* đã lọc bằng sanitize_html() khi lưu */ ?></div>

    <aside class="article-cta">
      <h2>Cho con học thử một buổi, ba mẹ xem con nói được gì</h2>
      <?= contact_cta('Đặt lịch học thử cho con', 'btn-lg') ?>
      <?= risk_note('30 phút, 1 kèm 1 với giáo viên') ?>
    </aside>
  </article>

  <?php if ($others): ?>
    <section class="container" style="padding-top:32px;padding-bottom:88px">
      <h2 class="h-section" style="margin-bottom:24px">Bài viết khác</h2>
      <div class="post-grid"><?php foreach ($others as $p) { echo post_card($p); } ?></div>
    </section>
  <?php endif; ?>
</main>
<?php site_footer(); site_end(); ?>
