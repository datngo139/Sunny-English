<?php
/**
 * Sunny World — khu dành cho bé: bản đồ, Vườn Từ vựng, Thư viện Truyện, Góc ba mẹ.
 * Đường dẫn đẹp (xem .htaccess): /sunny-world, /sunny-world/tu-vung[/chu-de], /sunny-world/truyen[/ten-truyen], /sunny-world/ba-me
 */
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';

$view = (string) ($_GET['view'] ?? '');
$slug = (string) ($_GET['slug'] ?? '');
if ($slug !== '' && !preg_match('/^[a-z0-9-]{1,100}$/', $slug)) {
    $slug = '-';
}

function sw_not_found(): never
{
    http_response_code(404);
    sw_head(['title' => 'Không tìm thấy', 'noindex' => true, 'path' => '/sunny-world']);
    sw_topbar(sw_url(), 'Bản đồ');
    echo '<main class="sw-main"><div class="sw-empty"><h1 class="sw-h1">Ôi, con đi lạc rồi!</h1><p>Trang này không có trong Sunny World.</p><a class="btn btn-lg" href="' . e(sw_url()) . '">Về bản đồ</a></div></main>';
    sw_foot();
    exit;
}

/* ====================== BẢN ĐỒ ====================== */
if ($view === '') {
    sw_head(['title' => 'Thế giới tiếng Anh của bé', 'path' => '/sunny-world']);
    sw_topbar(url('/'), 'Trang chủ');
    ?>
<main class="sw-main">
  <div class="sw-hello">
    <h1 class="sw-h1">Chào mừng con đến Sunny World!</h1>
    <p class="sw-sub">Bấm vào một ngôi nhà để bắt đầu khám phá.</p>
  </div>
  <div class="sw-mapbox"><?= sw_map() ?></div>
  <nav class="sw-tiles" aria-label="Các khu trong Sunny World">
    <?php foreach (sw_places() as $p): ?>
      <?php if ($p['open']): ?>
        <a class="sw-tile" href="<?= e(sw_url($p['key'])) ?>" style="--c:<?= e($p['color']) ?>"><b><?= e($p['name']) ?></b><span><?= e($p['desc']) ?></span></a>
      <?php else: ?>
        <span class="sw-tile locked" style="--c:<?= e($p['color']) ?>"><b><?= e($p['name']) ?></b><span>Sắp mở</span></span>
      <?php endif; ?>
    <?php endforeach; ?>
    <a class="sw-tile" href="<?= e(sw_url('ba-me')) ?>" style="--c:#B5652B"><b>Góc ba mẹ</b><span>Xem con đã học được gì</span></a>
  </nav>
</main>
<?php
    sw_foot();
    exit;
}

/* ====================== VƯỜN TỪ VỰNG ====================== */
if ($view === 'tu-vung' && $slug === '') {
    $topics = sw_topics();
    $ids = [];
    foreach (db()->query('SELECT id, topic_id FROM sw_cards WHERE active = 1') as $r) {
        $ids[(int) $r['topic_id']][] = (int) $r['id'];
    }
    sw_head(['title' => 'Vườn Từ vựng', 'path' => '/sunny-world/tu-vung']);
    sw_topbar(sw_url(), 'Bản đồ');
    ?>
<main class="sw-main">
  <div class="sw-hello">
    <h1 class="sw-h1">Vườn Từ vựng</h1>
    <p class="sw-sub">Chọn một chủ đề, lật thẻ để học từ mới nhé!</p>
  </div>
  <?php if (!$topics): ?>
    <div class="sw-empty"><p>Vườn chưa có chủ đề nào. Con quay lại sau nhé!</p></div>
  <?php else: ?>
  <div class="tp-grid">
    <?php foreach ($topics as $t): if ((int) $t['n'] === 0) { continue; } ?>
      <a class="tp" href="<?= e(sw_url('tu-vung', $t['slug'])) ?>" style="--c:<?= e(sw_clean_color($t['color'])) ?>" data-cards="<?= e(implode(',', $ids[(int) $t['id']] ?? [])) ?>">
        <span class="tp-pic"><?= sw_picture($t, $t['title_en']) ?></span>
        <span class="tp-en"><?= e($t['title_en']) ?></span>
        <span class="tp-vi"><?= e($t['title_vi']) ?></span>
        <span class="tp-bar"><i></i></span>
        <span class="tp-n"><b class="tp-known">0</b>/<?= (int) $t['n'] ?> từ đã thuộc</span>
      </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</main>
<?php
    sw_foot();
    exit;
}

if ($view === 'tu-vung') {
    $topic = sw_topic_by_slug($slug);
    $cards = $topic ? sw_cards((int) $topic['id']) : [];
    if (!$topic || !$cards) {
        sw_not_found();
    }
    $data = array_map(fn($c) => ['id' => (int) $c['id'], 'word' => $c['word'], 'meaning' => $c['meaning'], 'ex' => $c['example'], 'exVi' => $c['example_vi']], $cards);
    sw_head(['title' => $topic['title_en'] . ' (' . $topic['title_vi'] . ')', 'path' => '/sunny-world/tu-vung/' . $topic['slug']]);
    sw_topbar(sw_url('tu-vung'), 'Chủ đề');
    ?>
<main class="sw-main" style="--c:<?= e(sw_clean_color($topic['color'])) ?>">
  <div class="sw-hello">
    <h1 class="sw-h1"><?= e($topic['title_en']) ?> <small><?= e($topic['title_vi']) ?></small></h1>
  </div>
  <div class="fcs" id="fc">
    <button class="fc-nav" type="button" id="fc-prev" aria-label="Thẻ trước"><?= icon('chevron-left') ?></button>
    <div class="fc-card" id="fc-card" role="button" tabindex="0" aria-label="Bấm để lật thẻ xem nghĩa">
      <div class="fc-face fc-front">
        <div class="fc-pic" id="fc-pic"></div>
        <div class="fc-word" id="fc-word"></div>
        <span class="fc-hint"><?= icon('rotate-cw') ?>Bấm để xem nghĩa</span>
      </div>
      <div class="fc-face fc-back">
        <div class="fc-mean" id="fc-mean"></div>
        <p class="fc-ex" id="fc-ex"></p>
        <p class="fc-exvi" id="fc-exvi"></p>
      </div>
    </div>
    <button class="fc-nav" type="button" id="fc-next" aria-label="Thẻ sau"><?= icon('chevron-right') ?></button>
  </div>
  <div class="fc-bar">
    <button class="btn btn-outline tts" type="button" id="fc-say"><?= icon('volume-2') ?>Nghe</button>
    <span class="fc-count" id="fc-count" aria-live="polite"></span>
    <button class="btn" type="button" id="fc-known"><?= icon('star') ?><span>Con thuộc rồi!</span></button>
  </div>
  <p class="fc-done" id="fc-done" hidden><?= icon('party-popper') ?> Giỏi quá! Con đã thuộc hết các từ của chủ đề này.</p>
  <div class="fc-grid" id="fc-grid" aria-label="Tất cả các thẻ">
    <?php foreach ($cards as $i => $c): ?>
      <button class="fc-thumb" type="button" data-i="<?= $i ?>" data-id="<?= (int) $c['id'] ?>"><span class="pic"><?= sw_picture($c, $c['word']) ?></span><span class="t"><?= e($c['word']) ?></span></button>
    <?php endforeach; ?>
  </div>
</main>
<?php
    sw_foot(sw_json('SW_CARDS', $data));
    exit;
}

/* ====================== THƯ VIỆN TRUYỆN ====================== */
if ($view === 'truyen' && $slug === '') {
    $kind = (string) ($_GET['loai'] ?? '');
    if (!isset(SW_KINDS[$kind])) {
        $kind = '';
    }
    $stories = sw_stories($kind !== '' ? $kind : null);
    sw_head(['title' => 'Thư viện Truyện', 'path' => '/sunny-world/truyen']);
    sw_topbar(sw_url(), 'Bản đồ');
    ?>
<main class="sw-main">
  <div class="sw-hello">
    <h1 class="sw-h1">Thư viện Truyện</h1>
    <p class="sw-sub">Chạm vào từng từ tiếng Anh để xem nghĩa tiếng Việt.</p>
  </div>
  <div class="shelf-tabs" role="tablist" aria-label="Thể loại truyện">
    <a role="tab" aria-selected="<?= $kind === '' ? 'true' : 'false' ?>" href="<?= e(sw_url('truyen')) ?>">Tất cả</a>
    <?php foreach (SW_KINDS as $k => $label): ?>
      <a role="tab" aria-selected="<?= $kind === $k ? 'true' : 'false' ?>" href="<?= e(sw_url('truyen') . '?loai=' . $k) ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </div>
  <?php if (!$stories): ?>
    <div class="sw-empty"><p>Kệ này chưa có truyện. Con chọn kệ khác nhé!</p></div>
  <?php else: ?>
  <div class="shelf">
    <?php foreach ($stories as $s): $n = count(sw_story_pages($s)); ?>
      <a class="bk" href="<?= e(sw_url('truyen', $s['slug'])) ?>" data-story="<?= (int) $s['id'] ?>">
        <span class="bk-cover" style="background:<?= e(sw_clean_color($s['color'])) ?>"><?= sw_picture($s, $s['title']) ?><span class="bk-kind k-<?= e($s['kind']) ?>"><?= e(SW_KINDS[$s['kind']] ?? 'Truyện') ?></span><span class="bk-read" hidden><?= icon('check') ?>Đã đọc</span></span>
        <span class="bk-t"><?= e($s['title']) ?></span>
        <span class="bk-vi"><?= e($s['title_vi']) ?></span>
        <span class="bk-meta"><?= $n ?> trang</span>
      </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</main>
<?php
    sw_foot();
    exit;
}

if ($view === 'truyen') {
    $story = sw_story_by_slug($slug);
    $pages = $story ? sw_story_pages($story) : [];
    if (!$story || !$pages) {
        sw_not_found();
    }
    $gloss = sw_parse_glossary((string) $story['glossary']);
    sw_head(['title' => $story['title'] . ' (' . $story['title_vi'] . ')', 'path' => '/sunny-world/truyen/' . $story['slug']]);
    sw_topbar(sw_url('truyen'), 'Kệ truyện');
    $total = count($pages);
    ?>
<main class="sw-main">
  <div class="book" id="reader" data-story="<?= (int) $story['id'] ?>" data-pages="<?= $total ?>">
    <section class="pg pg-cover" data-pg="0" style="--bg:<?= e(sw_clean_color($story['color'])) ?>">
      <div class="cover-art"><?= sw_picture($story, $story['title']) ?></div>
      <h1 class="cover-t"><?= e($story['title']) ?></h1>
      <p class="cover-vi"><?= e($story['title_vi']) ?></p>
      <button class="btn btn-lg" type="button" data-go="1"><?= icon('play') ?>Bắt đầu đọc</button>
    </section>
    <?php foreach ($pages as $i => $p): ?>
      <section class="pg" data-pg="<?= $i + 1 ?>" hidden>
        <p class="en" lang="en"><?= sw_words_html($p['en'], $gloss) ?></p>
        <?php if ($p['vi'] !== ''): ?><p class="vi" hidden><?= e($p['vi']) ?></p><?php endif; ?>
      </section>
    <?php endforeach; ?>
    <section class="pg pg-end" data-pg="<?= $total + 1 ?>" hidden>
      <h2 class="end-t"><?= icon('party-popper') ?> Con đọc xong rồi!</h2>
      <p class="end-star" id="end-star"></p>
      <?php if ($gloss): ?>
        <h3 class="end-h">Từ mới trong truyện</h3>
        <ul class="newwords">
          <?php foreach ($gloss as $w => $m): ?><li><button type="button" class="nw tts" data-say="<?= e($w) ?>"><?= icon('volume-2') ?><b><?= e($w) ?></b></button><span><?= e($m) ?></span></li><?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <div class="end-actions">
        <button class="btn btn-outline" type="button" data-go="0"><?= icon('rotate-ccw') ?>Đọc lại</button>
        <a class="btn" href="<?= e(sw_url('truyen')) ?>">Chọn truyện khác</a>
      </div>
    </section>
  </div>
  <div class="rd-bar" id="rd-bar">
    <button class="btn btn-outline" type="button" id="rd-prev" aria-label="Trang trước"><?= icon('chevron-left') ?></button>
    <button class="btn btn-outline tts" type="button" id="rd-say"><?= icon('volume-2') ?><span>Nghe đọc</span></button>
    <span class="rd-count" id="rd-count" aria-live="polite"></span>
    <button class="btn btn-outline" type="button" id="rd-vi" aria-pressed="false"><?= icon('languages') ?><span>Xem nghĩa</span></button>
    <button class="btn" type="button" id="rd-next" aria-label="Trang sau"><?= icon('chevron-right') ?></button>
  </div>
  <p class="rd-hint">Rê chuột hoặc chạm vào một từ để xem nghĩa và nghe đọc.</p>
</main>
<?php
    sw_foot();
    exit;
}

/* ====================== GÓC BA MẸ ====================== */
if ($view === 'ba-me' && $slug === '') {
    $c = sunny_contact();
    $totals = ['topics' => [], 'stories' => []];
    $ids = [];
    foreach (db()->query('SELECT id, topic_id FROM sw_cards WHERE active = 1') as $r) {
        $ids[(int) $r['topic_id']][] = (int) $r['id'];
    }
    foreach (sw_topics() as $t) {
        if (!empty($ids[(int) $t['id']])) {
            $totals['topics'][] = ['title' => $t['title_vi'] !== '' ? $t['title_vi'] : $t['title_en'], 'ids' => $ids[(int) $t['id']]];
        }
    }
    foreach (sw_stories() as $s) {
        $totals['stories'][] = ['id' => (int) $s['id'], 'title' => $s['title']];
    }
    sw_head(['title' => 'Góc ba mẹ', 'path' => '/sunny-world/ba-me']);
    sw_topbar(sw_url(), 'Bản đồ');
    ?>
<main class="sw-main sw-parents">
  <div class="sw-hello">
    <h1 class="sw-h1">Góc ba mẹ</h1>
    <p class="sw-sub">Con đã học được gì trên máy này?</p>
  </div>
  <div class="pa-stats">
    <div class="pa-stat"><b id="pa-words">0</b><span>từ con đã thuộc</span></div>
    <div class="pa-stat"><b id="pa-stories">0</b><span>truyện con đã đọc</span></div>
    <div class="pa-stat"><b id="pa-stars">0</b><span>ngôi sao</span></div>
  </div>
  <section class="pa-card">
    <h2>Từ vựng theo chủ đề</h2>
    <div id="pa-topics" class="pa-topics"></div>
  </section>
  <section class="pa-card">
    <h2>Truyện đã đọc</h2>
    <ul id="pa-read" class="pa-read"></ul>
  </section>
  <section class="pa-card pa-cta">
    <h2>Cho con học thử một buổi tại Sunny</h2>
    <p>30 phút, 1 kèm 1 với giáo viên. Chưa cần quyết định học, ba mẹ vẫn nhận báo cáo trình độ của con.</p>
    <?= contact_cta('Nhắn Zalo đặt lịch học thử cho con') ?>
  </section>
  <section class="pa-note">
    <p><?= icon('shield-check') ?> Sunny World không cần tài khoản và không thu thập thông tin của con. Tiến độ chỉ lưu trên máy này, nên đổi máy hoặc xoá dữ liệu trình duyệt thì tiến độ sẽ bắt đầu lại.</p>
    <button class="btn btn-outline btn-sm" type="button" id="pa-reset">Xoá tiến độ trên máy này</button>
  </section>
</main>
<?php
    sw_foot(sw_json('SW_TOTALS', $totals));
    exit;
}

sw_not_found();
