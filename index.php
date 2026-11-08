<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';

$courses = sunny_courses();
$filters = sunny_age_filters();
$slots = sunny_slots();
$posts = published_posts(3);
$c = sunny_contact();

site_head([
    'title' => 'Sunny — Con tự tin nói tiếng Anh | Học thử miễn phí cho bé 3–14 tuổi',
    'path' => '/',
]);
site_header();
?>
<main id="noi-dung">

  <!-- HERO -->
  <section class="hero" id="trang-chu">
    <div class="hero-main">
      <div class="hero-art">
        <img src="<?= e(asset('/assets/img/sunny-logo-full.png')) ?>" alt="Sunny Education" width="400" height="434">
      </div>
      <div class="hero-copy">
        <h1>Con nhút nhát, ngại nói tiếng Anh?</h1>
        <p class="lead">Ở Sunny, con <b>tự tin nói tiếng Anh</b> và đạt từng mốc Cambridge Starters, Movers, Flyers. Lớp cho con từ 3 đến 14 tuổi.</p>
        <div class="cta-stack">
          <div>
            <a class="btn btn-lg btn-block" href="#dang-ky" data-open-register>Đăng ký test trình độ miễn phí cho con</a>
            <?= risk_note('Chưa cần quyết định học – vẫn nhận báo cáo trình độ của con') ?>
          </div>
          <?= nearby_actions() ?>
        </div>
      </div>
    </div>
    <nav class="course-strip" aria-label="Các khoá học">
      <div class="in">
        <?php foreach ($courses as $co): ?>
          <a href="#khoa-hoc" class="lv-<?= e($co['level']) ?>" data-strip-course="<?= e($co['id']) ?>">
            <span class="strip-ico"><?= icon($co['icon']) ?></span><?= e($co['title']) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </nav>
  </section>

  <!-- VỀ SUNNY -->
  <section id="ve-sunny" class="container" style="padding-top:40px;padding-bottom:40px">
    <?php foreach (sunny_why() as $i => $w): ?>
      <div class="feature<?= $i % 2 === 0 ? ' flip' : '' ?>">
        <div class="feature-art tint-<?= e($w['tint']) ?>"><span class="ico"><?= icon($w['icon']) ?></span></div>
        <div>
          <h2><?= e($w['title']) ?></h2>
          <p><?= e($w['body']) ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </section>

  <!-- KHOÁ HỌC -->
  <section id="khoa-hoc" class="section section-soft">
    <div class="container">
      <div class="section-head">
        <h2>Con đang ở độ tuổi nào?</h2>
        <div class="tabs" role="tablist" aria-label="Lọc theo độ tuổi" id="age-tabs">
          <?php $first = true; foreach ($filters as $label => $key): ?>
            <button class="tab" type="button" role="tab" aria-selected="<?= $first ? 'true' : 'false' ?>" data-filter="<?= e((string) $key) ?>"><?= e($label) ?></button>
          <?php $first = false; endforeach; ?>
        </div>
      </div>
      <div class="course-grid" id="course-grid">
        <?php foreach ($courses as $co): ?>
          <button type="button" class="course-card lv-<?= e($co['level']) ?>" data-course="<?= e($co['id']) ?>" aria-haspopup="dialog">
            <span class="cc-top"><?= icon($co['icon']) ?><span class="cc-tag"><?= e(preg_replace('/^Tiếng Anh /u', '', $co['title'])) ?></span></span>
            <span class="cc-body">
              <span class="cc-ages"><?= e($co['ages']) ?></span>
              <span class="cc-title"><?= e($co['title']) ?></span>
              <span class="cc-desc"><?= e($co['desc']) ?></span>
              <span class="cc-cta">Xem lộ trình cho con →</span>
            </span>
          </button>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- QUY TRÌNH HỌC THỬ -->
  <section class="section section-sunny" aria-labelledby="h-steps">
    <div class="container">
      <div class="section-head" style="margin-bottom:28px">
        <h2 id="h-steps">Học thử cho con thế nào?</h2>
      </div>
      <ol class="steps" style="list-style:none;margin:0;padding:0">
        <?php foreach (sunny_steps() as $i => $s): ?>
          <li class="step"><span class="n"><?= $i + 1 ?></span><h3><?= e($s['title']) ?></h3><p><?= e($s['body']) ?></p></li>
        <?php endforeach; ?>
      </ol>
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

  <!-- ĐĂNG KÝ HỌC THỬ -->
  <section id="dang-ky" class="section section-soft">
    <div class="reg-wrap" style="padding:0 20px">
      <div class="reg-head">
        <h2>Đăng ký test trình độ miễn phí cho con</h2>
        <p>Sunny gọi lại trong 24 giờ để hẹn giờ test cho con.</p>
      </div>
      <div class="progress" aria-hidden="true"><i id="reg-progress"></i></div>
      <form class="card" id="reg-form" action="<?= e(url('/api/register.php')) ?>" method="post" novalidate style="padding:28px">
        <div class="form-grid">
          <?php $flag = $_GET['dang-ky'] ?? ''; ?>
          <div class="form-msg" id="reg-error" role="alert"><?= $flag === 'loi' ? 'Chưa gửi được đăng ký. Ba mẹ kiểm tra lại thông tin hoặc gọi trực tiếp cho Sunny nhé.' : '' ?></div>
          <?php if ($flag === 'ok'): ?><div class="form-msg" style="background:var(--green-100);color:var(--green-700)" role="status">Đăng ký thành công! Sunny sẽ gọi cho ba mẹ trong 24 giờ.</div><?php endif; ?>

          <div class="field" data-field="parent_name">
            <label class="label" for="f-parent">Họ tên phụ huynh</label>
            <span class="control"><input id="f-parent" name="parent_name" type="text" autocomplete="name" placeholder="Nguyễn Thị Lan" maxlength="80" required></span>
            <span class="error-text"></span>
          </div>

          <div class="field" data-field="phone">
            <label class="label" for="f-phone">Số điện thoại (Zalo)</label>
            <span class="control"><?= icon('phone') ?><input id="f-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="09xx xxx xxx" maxlength="20" required></span>
            <span class="error-text"></span>
          </div>

          <div class="row-2">
            <div class="field" data-field="child_name">
              <label class="label" for="f-child">Tên của con <span class="opt">(không bắt buộc)</span></label>
              <span class="control"><input id="f-child" name="child_name" type="text" placeholder="An" maxlength="60"></span>
              <span class="error-text"></span>
            </div>
            <div class="field" data-field="child_age">
              <label class="label" for="f-age">Tuổi</label>
              <span class="control"><input id="f-age" name="child_age" type="number" inputmode="numeric" min="2" max="18" placeholder="7"></span>
              <span class="error-text"></span>
            </div>
          </div>

          <div class="field" data-field="course">
            <label class="label" for="f-course">Khoá học quan tâm</label>
            <span class="control has-select">
              <select id="f-course" name="course">
                <option value="">Chưa biết, nhờ Sunny tư vấn</option>
                <?php foreach ($courses as $co): ?>
                  <option value="<?= e($co['id']) ?>"><?= e($co['title']) ?> (<?= e($co['ages']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </span>
            <span class="error-text"></span>
          </div>

          <fieldset style="border:0;padding:0;margin:0;min-width:0">
            <legend class="label" style="padding:0">Thời gian học thử</legend>
            <div class="row-slots">
              <?php $i = 0; foreach ($slots as $val => $label): ?>
                <label class="choice radio"><input type="radio" name="slot" value="<?= e($val) ?>"<?= $i === 0 ? ' checked' : '' ?>><span class="box"></span><span class="text"><?= e($label) ?></span></label>
              <?php $i++; endforeach; ?>
            </div>
          </fieldset>

          <!-- Bẫy chống bot: người thật không thấy ô này -->
          <div class="hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
          <input type="hidden" name="ts" value="<?= time() ?>">

          <div class="field" data-field="consent">
            <label class="choice"><input type="checkbox" name="consent" value="1" checked><span class="box"></span><span class="text">Tôi đồng ý để Sunny liên hệ tư vấn</span></label>
            <span class="error-text"></span>
          </div>

          <div>
            <button class="btn btn-lg btn-block" type="submit" id="reg-submit">Đặt lịch test miễn phí cho con</button>
            <?= risk_note('Chưa cần quyết định học – vẫn nhận báo cáo trình độ của con') ?>
          </div>
        </div>
      </form>
    </div>
  </section>

  <!-- CTA CUỐI -->
  <section class="container final-cta">
    <h2>Cho con học thử một buổi, ba mẹ xem con nói được gì</h2>
    <div style="width:100%;max-width:420px">
      <a class="btn btn-lg btn-block" href="#dang-ky" data-open-register>Đặt lịch học thử cho con</a>
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
      <div><button class="btn btn-lg btn-block" type="button" id="cd-register">Đặt lịch học thử cho con</button><?= risk_note('30 phút, 1 kèm 1 với giáo viên') ?></div>
    </div>
  </div>
</dialog>

<!-- Hộp thoại đăng ký thành công -->
<dialog class="dlg" id="success-dialog" aria-labelledby="sd-title">
  <div class="dlg-body">
    <button class="dlg-close" type="button" aria-label="Đóng" data-close>×</button>
    <h3 class="dlg-title" id="sd-title">Đăng ký thành công!</h3>
    <p id="sd-text"></p>
    <div class="dlg-actions"><button class="btn btn-block" type="button" data-close>Đóng</button></div>
  </div>
</dialog>

<?php site_footer(); ?>
<script>
window.SUNNY = <?= json_encode([
    'courses' => $courses,
    'slots' => $slots,
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<?php site_end(); ?>
