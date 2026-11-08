<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';

http_response_code(404);
site_head(['title' => 'Không tìm thấy trang — Sunny', 'noindex' => true]);
site_header();
?>
<main id="noi-dung" class="container">
  <div class="empty" style="padding:96px 0">
    <h1 style="font-size:clamp(30px,5vw,40px);margin-bottom:12px">Không tìm thấy trang này</h1>
    <p style="margin-bottom:24px">Đường dẫn có thể đã đổi hoặc không còn nữa.</p>
    <a class="btn btn-lg" href="<?= e(url('/')) ?>">Về trang chủ Sunny</a>
  </div>
</main>
<?php site_footer(); site_end(); ?>
