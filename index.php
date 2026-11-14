<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';

$courses = sunny_courses();
$facility = sunny_facility();
$posts = published_posts(3);

/** "Tiếng Anh Mầm Non" → "Mầm Non" cho thanh khoá học gọn trên một hàng. */
$short = fn(string $t) => trim(preg_replace('/^Tiếng Anh\s+/u', '', $t));

site_head([
    'title' => 'Sunny — Con tự tin nói tiếng Anh | Học thử miễn phí cho bé 3–14 tuổi',
    'path' => '/',
]);
site_header();
echo sun_defs();
?>
<main id="noi-dung">

  <!-- HERO -->
  <section class="hero" id="trang-chu">
    <div class="hero-main">
      <div class="hero-art">
        <?= mascot_hero() ?>
      </div>
      <div class="hero-copy">
        <h1>Con nhút nhát, ngại nói tiếng Anh?</h1>
        <p class="lead">Ở Sunny, con <b>tự tin nói tiếng Anh</b> và đạt từng mốc Cambridge Starters, Movers, Flyers. Lớp cho con từ 3 đến 14 tuổi.</p>
        <div class="cta-stack">
          <div>
            <?= contact_cta('Đặt lịch học thử miễn phí cho con') ?>
            <?= risk_note('Chưa cần quyết định học – vẫn nhận báo cáo trình độ của con', 'inherit') ?>
          </div>
          <?= nearby_actions() ?>
        </div>
      </div>
    </div>
    <?php if ($courses): ?>
    <nav class="course-strip" aria-label="Các khoá học">
      <div class="in">
        <?php foreach ($courses as $co): ?>
          <a href="#khoa-hoc" class="lv-<?= e($co['level']) ?>" data-strip-course="<?= e($co['id']) ?>">
            <span class="strip-ico"><?= icon($co['icon']) ?></span><?= e($short($co['title'])) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </nav>
    <?php endif; ?>
  </section>

  <!-- VỀ SUNNY -->
  <section id="ve-sunny" class="container" style="padding-top:40px;padding-bottom:40px">
    <?php foreach (sunny_why() as $i => $w): ?>
      <div class="feature tint-<?= e($w['tint']) ?><?= $i % 2 === 0 ? ' flip' : '' ?>">
        <div class="feature-art" data-reveal><?= scene_svg($w['scene']) ?></div>
        <div class="feature-copy" data-reveal>
          <h2><?= e($w['title']) ?></h2>
          <p><?= e($w['body']) ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </section>

  <!-- KHOÁ HỌC -->
  <?php if ($courses): ?>
  <section id="khoa-hoc" class="section section-soft">
    <div class="container">
      <div class="section-head">
        <h2>Con đang ở độ tuổi nào?</h2>
      </div>
      <div class="course-grid" id="course-grid">
        <?php foreach ($courses as $co): $hasDetail = $co['headline'] !== ''; ?>
          <button type="button" class="course-card lv-<?= e($co['level']) ?>" data-course="<?= e($co['id']) ?>"<?= $hasDetail ? ' aria-haspopup="dialog"' : '' ?>>
            <span class="cc-top<?= $co['image'] !== '' ? ' has-img' : '' ?>">
              <?php if ($co['image'] !== ''): ?>
                <img src="<?= e(url($co['image'])) ?>" alt="" loading="lazy">
              <?php else: ?>
                <?= icon($co['icon']) ?>
              <?php endif; ?>
              <span class="cc-tag"><?= e($short($co['title'])) ?></span>
            </span>
            <span class="cc-body">
              <?php if ($co['ages'] !== ''): ?><span class="cc-ages"><?= e($co['ages']) ?></span><?php endif; ?>
              <span class="cc-title"><?= e($co['title']) ?></span>
              <span class="cc-desc"><?= e($co['desc']) ?></span>
              <span class="cc-cta"><?= $hasDetail ? 'Xem lộ trình cho con' : 'Đặt lịch học thử cho con' ?> →</span>
            </span>
          </button>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- SUNNY WORLD -->
  <section id="sunny-world" class="section sw-teaser" aria-labelledby="h-sw">
    <div class="container sw-teaser-in">
      <div class="sw-teaser-copy">
        <span class="overline">Mới · Miễn phí</span>
        <h2 id="h-sw">Sunny World: con vừa chơi vừa học tiếng Anh</h2>
        <p class="section-lead">Con lật thẻ học từ vựng theo chủ đề và đọc truyện tiếng Anh, chạm vào từng từ là thấy nghĩa tiếng Việt. Không cần tài khoản.</p>
        <a class="btn btn-lg btn-secondary" href="<?= e(sw_url()) ?>">Vào Sunny World cùng con</a>
      </div>
      <a class="sw-teaser-art" href="<?= e(sw_url()) ?>" aria-label="Vào Sunny World">
        <?php foreach (['cat', 'apple', 'book', 'rainbow', 'star', 'num-3'] as $k => $art): ?><span class="tz tz<?= $k ?>"><?= sw_art($art) ?></span><?php endforeach; ?>
        <svg class="tz-sun" viewBox="0 0 200 200" aria-hidden="true"><use href="#sunFace"/></svg>
      </a>
    </div>
  </section>

  <!-- CƠ SỞ VẬT CHẤT -->
  <section id="co-so" class="section facility" aria-labelledby="h-coso">
    <?= facility_icons() ?>
    <div class="container">
      <div class="section-head" style="margin-bottom:12px">
        <div>
          <span class="overline">Cơ sở vật chất</span>
          <h2 id="h-coso">Nơi con muốn đến lớp mỗi ngày</h2>
        </div>
      </div>
      <p class="section-lead">Không gian sáng, sạch, ngập sắc cam của Sunny: có khu chờ cho ba mẹ, phòng học cho bé nhỏ với thảm chữ cái và phòng học lớp lớn rộng rãi.</p>
      <div class="carousel" id="facility" role="region" aria-roledescription="băng chuyền ảnh" aria-label="Ảnh cơ sở vật chất của Sunny">
        <div class="car-track" id="facility-track" tabindex="0">
          <?php foreach ($facility as $i => $f): ?>
            <figure class="car-slide" aria-roledescription="ảnh" aria-label="<?= $i + 1 ?> trên <?= count($facility) ?>">
              <img src="<?= e(asset('/assets/img/facility/' . $f['file'])) ?>" alt="<?= e($f['caption']) ?>" style="object-position:<?= e($f['pos']) ?>"<?= $i > 0 ? ' loading="lazy"' : '' ?> width="1100" height="733">
              <figcaption><?= e($f['caption']) ?></figcaption>
            </figure>
          <?php endforeach; ?>
        </div>
        <button class="car-btn prev" type="button" aria-label="Ảnh trước"><?= icon('chevron-left') ?></button>
        <button class="car-btn next" type="button" aria-label="Ảnh sau"><?= icon('chevron-right') ?></button>
        <div class="car-dots" role="tablist" aria-label="Chọn ảnh">
          <?php foreach ($facility as $i => $f): ?>
            <button type="button" role="tab" aria-label="Ảnh <?= $i + 1 ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"></button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- BÀI VIẾT MỚI -->
  <?php if ($posts): ?>
  <section class="section" id="bai-viet-moi" aria-labelledby="h-posts">
    <div class="container">
      <div class="section-head">
        <h2 id="h-posts">Tin mới từ Sunny</h2>
        <a class="btn btn-outline btn-sm" href="<?= e(url('/bai-viet')) ?>">Xem tất cả bài viết</a>
      </div>
      <div class="post-grid"><?php foreach ($posts as $p) { echo post_card($p); } ?></div>
    </div>
  </section>
  <?php endif; ?>

  <!-- CTA CUỐI -->
  <section class="container final-cta">
    <h2>Cho con học thử một buổi, ba mẹ xem con nói được gì</h2>
    <div style="width:100%;max-width:420px">
      <?= contact_cta('Đặt lịch học thử cho con') ?>
      <?= risk_note('30 phút, 1 kèm 1 với giáo viên') ?>
    </div>
  </section>
</main>

<!-- Hộp thoại chi tiết khoá học -->
<dialog class="dlg" id="course-dialog" aria-labelledby="cd-title">
  <div class="dlg-body">
    <button class="dlg-close" type="button" aria-label="Đóng" data-close>×</button>
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px"><span class="badge" id="cd-badge"></span><span class="badge badge-neutral" id="cd-ages"></span></div>
    <h3 class="dlg-title" id="cd-title" style="color:var(--text-heading);font-size:28px;line-height:1.2"></h3>
    <p id="cd-outcome" style="font-size:17px;margin-bottom:16px"></p>
    <ul id="cd-points" style="list-style:none;margin:0 0 4px;padding:0;display:grid;gap:10px"></ul>
    <div class="dlg-actions" style="flex-direction:column">
      <div><?= contact_cta('Đặt lịch học thử cho con') ?><?= risk_note('30 phút, 1 kèm 1 với giáo viên') ?></div>
    </div>
  </div>
</dialog>

<?php site_footer(); ?>
<script>
window.SUNNY = <?= json_encode([
    'courses' => $courses,
    'book' => booking_link()[0],
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<?php site_end(); ?>
