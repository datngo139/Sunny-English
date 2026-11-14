<?php
/** Sunny World — thêm / sửa một truyện: nội dung từng trang, nghĩa cả trang, từ mới, nghĩa các từ khác. */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require SUNNY_APP . '/admin_layout.php';

$admin = require_admin();
$pdo = db();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$row = null;
if ($id) {
    $st = $pdo->prepare('SELECT * FROM sw_stories WHERE id = ?');
    $st->execute([$id]);
    $row = $st->fetch() ?: null;
    if (!$row) {
        flash_set('err', 'Không tìm thấy truyện.');
        redirect('/admin/sw-truyen.php');
    }
}

/** Tách ô văn bản thành các trang: mỗi trang cách nhau một dòng trống. */
$split = function (string $text): array {
    $out = [];
    foreach (preg_split('/\R\s*\R/u', trim(str_replace("\r\n", "\n", $text))) as $p) {
        $p = trim(preg_replace('/\s+/u', ' ', $p));
        if ($p !== '') {
            $out[] = mb_substr($p, 0, 600);
        }
    }
    return array_slice($out, 0, 40);
};

$pages = $row ? sw_story_pages($row) : [];
$f = [
    'title' => $row['title'] ?? '', 'title_vi' => $row['title_vi'] ?? '', 'kind' => $row['kind'] ?? 'story',
    'color' => $row['color'] ?? '#FFE9A8', 'art' => $row['art'] ?? '', 'image' => $row['image'] ?? '',
    'en' => implode("\n\n", array_column($pages, 'en')), 'vi' => implode("\n\n", array_column($pages, 'vi')),
    'glossary' => $row['glossary'] ?? '', 'dict_add' => '', 'active' => (int) ($row['active'] ?? 1),
];
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $f['title'] = mb_substr(trim((string) ($_POST['title'] ?? '')), 0, 100);
    $f['title_vi'] = mb_substr(trim((string) ($_POST['title_vi'] ?? '')), 0, 100);
    $f['kind'] = isset(SW_KINDS[$_POST['kind'] ?? '']) ? $_POST['kind'] : 'story';
    $f['color'] = sw_clean_color((string) ($_POST['color'] ?? ''));
    $f['art'] = admin_clean_art((string) ($_POST['art'] ?? ''));
    $f['image'] = sw_clean_image((string) ($_POST['image'] ?? ''));
    $f['en'] = mb_substr((string) ($_POST['en'] ?? ''), 0, 20000);
    $f['vi'] = mb_substr((string) ($_POST['vi'] ?? ''), 0, 20000);
    $f['glossary'] = mb_substr(trim((string) ($_POST['glossary'] ?? '')), 0, 4000);
    $f['dict_add'] = mb_substr(trim((string) ($_POST['dict_add'] ?? '')), 0, 8000);
    $f['active'] = empty($_POST['active']) ? 0 : 1;

    $en = $split($f['en']);
    $vi = $split($f['vi']);
    if ($f['title'] === '') {
        $error = 'Vui lòng nhập tên truyện.';
    } elseif (!$en) {
        $error = 'Truyện chưa có nội dung tiếng Anh.';
    } elseif ($vi && count($vi) !== count($en)) {
        $error = 'Phần tiếng Anh có ' . count($en) . ' trang nhưng phần nghĩa tiếng Việt có ' . count($vi) . ' trang. Hai phần cần cùng số trang (mỗi trang cách nhau một dòng trống).';
    }

    if (!$error) {
        $json = [];
        foreach ($en as $i => $p) {
            $json[] = ['en' => $p, 'vi' => $vi[$i] ?? ''];
        }
        $pagesJson = json_encode($json, JSON_UNESCAPED_UNICODE);
        if ($row) {
            $pdo->prepare('UPDATE sw_stories SET title=?, title_vi=?, kind=?, color=?, art=?, image=?, pages=?, glossary=?, active=? WHERE id=?')
                ->execute([$f['title'], $f['title_vi'], $f['kind'], $f['color'], $f['art'], $f['image'], $pagesJson, $f['glossary'], $f['active'], $id]);
        } else {
            $base = slugify($f['title']);
            $slug = $base;
            $n = 2;
            $chk = $pdo->prepare('SELECT COUNT(*) FROM sw_stories WHERE slug = ?');
            while (true) {
                $chk->execute([$slug]);
                if ((int) $chk->fetchColumn() === 0) {
                    break;
                }
                $slug = mb_substr($base, 0, 70) . '-' . $n++;
            }
            $sort = (int) $pdo->query('SELECT COALESCE(MAX(sort), 0) FROM sw_stories')->fetchColumn() + 10;
            $pdo->prepare('INSERT INTO sw_stories (slug, kind, title, title_vi, art, image, color, pages, glossary, sort, active, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')
                ->execute([$slug, $f['kind'], $f['title'], $f['title_vi'], $f['art'], $f['image'], $f['color'], $pagesJson, $f['glossary'], $sort, $f['active'], date('Y-m-d H:i:s')]);
            $id = (int) $pdo->lastInsertId();
        }
        // Từ mới của truyện: thêm vào từ điển chung nếu từ đó chưa có. "Nghĩa các từ khác": ghi đè vào từ điển chung.
        $add = $pdo->prepare('INSERT OR IGNORE INTO sw_dict (word, meaning) VALUES (?, ?)');
        foreach (sw_parse_glossary($f['glossary']) as $w => $m) {
            $add->execute([$w, $m]);
        }
        $put = $pdo->prepare('INSERT OR REPLACE INTO sw_dict (word, meaning) VALUES (?, ?)');
        $n = 0;
        foreach (sw_parse_glossary($f['dict_add']) as $w => $m) {
            $put->execute([$w, $m]);
            $n++;
        }
        flash_set('ok', 'Đã lưu truyện.' . ($n ? ' Đã cập nhật ' . $n . ' từ vào từ điển chung.' : ''));
        redirect('/admin/sw-truyen-sua.php?id=' . $id);
    }
}

$missing = $row && !$error ? sw_missing_words($pages, sw_parse_glossary((string) $row['glossary'])) : [];
admin_head($row ? 'Sửa truyện' : 'Thêm truyện', 'stories', $admin);
?>
<div class="adm-title">
  <div>
    <a class="back-link" href="<?= e(url('/admin/sw-truyen.php')) ?>"><?= icon('chevron-left') ?>Tất cả truyện</a>
    <h1><?= $row ? 'Sửa truyện' : 'Thêm truyện' ?></h1>
  </div>
  <?php if ($row && $row['active']): ?><a class="btn btn-outline btn-sm" href="<?= e(sw_url('truyen', $row['slug'])) ?>" target="_blank" rel="noopener"><?= icon('eye') ?>Xem truyện</a><?php endif; ?>
</div>
<?php if ($error): ?><div class="adm-flash adm-flash-err" role="alert"><?= e($error) ?></div><?php endif; ?>

<?php if ($missing): ?>
  <div class="card" style="margin-bottom:20px;display:grid;gap:10px;background:var(--sun-50)">
    <b><?= count($missing) ?> từ trong truyện chưa có nghĩa</b>
    <div class="miss"><?php foreach ($missing as $w): ?><code><?= e($w) ?></code><?php endforeach; ?></div>
    <p class="hint">Bé rê chuột vào các từ này sẽ không thấy nghĩa. Hãy thêm vào ô "Nghĩa các từ khác" bên dưới, mỗi dòng theo mẫu <code>từ = nghĩa</code>, rồi lưu.</p>
  </div>
<?php endif; ?>

<form method="post" class="editor-layout" data-upload="<?= e(url('/admin/upload.php')) ?>" data-csrf="<?= e(csrf_token()) ?>">
  <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $id ?>">
  <div class="editor-main">
    <div class="card card-form">
      <div class="grid-2">
        <div class="field"><label class="label" for="title">Tên truyện (tiếng Anh)</label><span class="control"><input id="title" name="title" type="text" maxlength="100" value="<?= e($f['title']) ?>" placeholder="The Little Sun" required></span></div>
        <div class="field"><label class="label" for="title_vi">Tên tiếng Việt</label><span class="control"><input id="title_vi" name="title_vi" type="text" maxlength="100" value="<?= e($f['title_vi']) ?>" placeholder="Mặt trời nhỏ"></span></div>
      </div>
      <div class="field">
        <label class="label" for="en">Nội dung tiếng Anh</label>
        <span class="control is-textarea"><textarea id="en" name="en" rows="10" style="min-height:240px" placeholder="Trang 1: viết 1–3 câu ngắn.&#10;&#10;Trang 2: cách trang trước một dòng trống." required><?= e($f['en']) ?></textarea></span>
        <span class="hint">Mỗi trang cách nhau <b>một dòng trống</b>. Mỗi trang nên có 1–3 câu ngắn để chữ to, dễ đọc.</span>
      </div>
      <div class="field">
        <label class="label" for="vi">Nghĩa tiếng Việt của từng trang <span class="opt">(tuỳ chọn)</span></label>
        <span class="control is-textarea"><textarea id="vi" name="vi" rows="8" style="min-height:200px" placeholder="Nghĩa của trang 1.&#10;&#10;Nghĩa của trang 2."><?= e($f['vi']) ?></textarea></span>
        <span class="hint">Cùng số trang và cùng thứ tự với phần tiếng Anh. Hiện khi bé bấm "Xem nghĩa".</span>
      </div>
    </div>
    <div class="card card-form">
      <div class="field">
        <label class="label" for="glossary">Từ mới của truyện</label>
        <span class="control is-textarea"><textarea id="glossary" name="glossary" rows="6" style="min-height:150px" placeholder="shy = nhút nhát&#10;cloud = đám mây"><?= e($f['glossary']) ?></textarea></span>
        <span class="hint">Mỗi dòng một từ theo mẫu <code>từ = nghĩa</code>. Các từ này hiện trong danh sách "Từ mới" ở cuối truyện, và nghĩa ở đây được ưu tiên riêng cho truyện này.</span>
      </div>
      <div class="field">
        <label class="label" for="dict_add">Nghĩa các từ khác <span class="opt">(lưu vào từ điển chung, dùng lại cho mọi truyện)</span></label>
        <span class="control is-textarea"><textarea id="dict_add" name="dict_add" rows="5" style="min-height:120px" placeholder="the = (chỉ vật đã nói tới)&#10;runs = chạy"><?= e($f['dict_add']) ?></textarea></span>
        <span class="hint">Dùng cho những từ thông thường không cần đưa vào danh sách "Từ mới". Nhập lại một từ đã có sẽ sửa nghĩa của từ đó trong từ điển chung.</span>
      </div>
    </div>
  </div>
  <aside class="editor-side">
    <div class="card side-card">
      <h2>Hiển thị</h2>
      <div class="field"><label class="label" for="kind">Kệ truyện</label><span class="control has-select"><select id="kind" name="kind"><?php foreach (SW_KINDS as $k => $label): ?><option value="<?= e($k) ?>"<?= $f['kind'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></span></div>
      <label class="choice"><input type="checkbox" name="active" value="1"<?= $f['active'] ? ' checked' : '' ?>><span class="box"></span><span class="text">Hiện trong Thư viện Truyện</span></label>
      <button class="btn btn-lg btn-block" type="submit"><?= icon('save') ?>Lưu truyện</button>
    </div>
    <div class="card side-card">
      <h2>Bìa truyện</h2>
      <div class="field"><label class="label" for="color">Màu nền bìa</label><span class="control"><input id="color" name="color" type="color" value="<?= e(sw_clean_color($f['color'])) ?>" style="padding:0;height:36px"></span></div>
      <div class="field"><label class="label" for="s-art">Hình vẽ sẵn</label><?= admin_art_select('art', (string) $f['art'], 's-art') ?></div>
      <div class="field"><span class="label">Hoặc ảnh bìa tải lên</span><?= admin_image_picker('image', (string) $f['image']) ?></div>
    </div>
  </aside>
</form>
<?php admin_foot(); ?>
