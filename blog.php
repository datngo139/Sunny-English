<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';

$perPage = 9;
$total = published_post_count();
$pages = max(1, (int) ceil($total / $perPage));
$page = min($pages, max(1, (int) ($_GET['trang'] ?? 1)));
$posts = published_posts($perPage, ($page - 1) * $perPage);

site_head([
    'title' => 'Bài viết từ Sunny — Chia sẻ cho ba mẹ có con học tiếng Anh',
    'description' => 'Tin tức, thông báo lớp mới và chia sẻ kinh nghiệm giúp con học tiếng Anh tự tin từ trung tâm Sunny.',
    'path' => '/bai-viet' . ($page > 1 ? '?trang=' . $page : ''),
]);
site_header('bai-viet');
?>
<main id="noi-dung">
  <section class="container page-head">
    <span class="overline">Blog Sunny</span>
    <h1>Bài viết từ Sunny</h1>
    <p>Thông báo lớp mới, chia sẻ kinh nghiệm và hoạt động của các bé tại trung tâm.</p>
  </section>

  <section class="container" style="padding-top:36px;padding-bottom:88px">
    <?php if (!$posts): ?>
      <div class="empty"><?= icon('newspaper') ?> Sunny chưa có bài viết nào. Ba mẹ quay lại sau nhé!</div>
    <?php else: ?>
      <div class="post-grid"><?php foreach ($posts as $p) { echo post_card($p); } ?></div>
      <?php if ($pages > 1): ?>
        <nav class="pager" aria-label="Phân trang">
          <?php if ($page > 1): ?><a class="btn btn-outline btn-sm" href="<?= e(url('/bai-viet?trang=' . ($page - 1))) ?>">← Mới hơn</a><?php endif; ?>
          <span class="badge badge-neutral" style="align-self:center">Trang <?= $page ?>/<?= $pages ?></span>
          <?php if ($page < $pages): ?><a class="btn btn-outline btn-sm" href="<?= e(url('/bai-viet?trang=' . ($page + 1))) ?>">Cũ hơn →</a><?php endif; ?>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </section>
</main>
<?php site_footer(); site_end(); ?>
