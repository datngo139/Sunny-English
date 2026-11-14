<?php
/** Sunny World — sửa một chủ đề từ vựng và các thẻ từ của nó. */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require SUNNY_APP . '/admin_layout.php';

$admin = require_admin();
$pdo = db();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$st = $pdo->prepare('SELECT * FROM sw_topics WHERE id = ?');
$st->execute([$id]);
$topic = $st->fetch();
if (!$topic) {
    flash_set('err', 'Không tìm thấy chủ đề.');
    redirect('/admin/sw-tu-vung.php');
}
$self = '/admin/sw-chu-de.php?id=' . $id;
$txt = fn(string $k, int $max) => mb_substr(trim(preg_replace('/\s+/u', ' ', (string) ($_POST[$k] ?? ''))), 0, $max);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $act = (string) ($_POST['action'] ?? '');

    if ($act === 'save_topic') {
        $en = $txt('title_en', 60);
        if ($en === '') {
            flash_set('err', 'Tên chủ đề (tiếng Anh) không được để trống.');
        } else {
            $pdo->prepare('UPDATE sw_topics SET title_en=?, title_vi=?, color=?, art=?, image=?, active=? WHERE id=?')
                ->execute([$en, $txt('title_vi', 60), sw_clean_color((string) ($_POST['color'] ?? ''), '#FDBE10'),
                    admin_clean_art((string) ($_POST['art'] ?? '')), sw_clean_image((string) ($_POST['image'] ?? '')), empty($_POST['active']) ? 0 : 1, $id]);
            flash_set('ok', 'Đã lưu chủ đề.');
        }
        redirect($self);
    }

    if ($act === 'save_card') {
        $cid = (int) ($_POST['card_id'] ?? 0);
        $word = $txt('word', 40);
        if ($word === '' || !preg_match('/^[A-Za-z][A-Za-z\'’\- ]*$/', $word)) {
            flash_set('err', 'Từ tiếng Anh chỉ gồm chữ cái, dấu cách, dấu nháy hoặc gạch nối.');
            redirect($self . ($cid ? '&card=' . $cid : '') . '#the-tu');
        }
        $vals = [$word, $txt('meaning', 80), $txt('example', 140), $txt('example_vi', 160),
            admin_clean_art((string) ($_POST['art'] ?? '')), sw_clean_image((string) ($_POST['image'] ?? '')),
            max(0, min(9999, (int) ($_POST['sort'] ?? 0))), empty($_POST['active']) ? 0 : 1];
        if ($cid) {
            $pdo->prepare('UPDATE sw_cards SET word=?, meaning=?, example=?, example_vi=?, art=?, image=?, sort=?, active=? WHERE id=? AND topic_id=?')
                ->execute([...$vals, $cid, $id]);
            flash_set('ok', 'Đã lưu thẻ "' . $word . '".');
        } else {
            if ($vals[6] === 0) {
                $q = $pdo->prepare('SELECT COALESCE(MAX(sort), 0) FROM sw_cards WHERE topic_id = ?');
                $q->execute([$id]);
                $vals[6] = (int) $q->fetchColumn() + 10;
            }
            $pdo->prepare('INSERT INTO sw_cards (word, meaning, example, example_vi, art, image, sort, active, topic_id) VALUES (?,?,?,?,?,?,?,?,?)')
                ->execute([...$vals, $id]);
            flash_set('ok', 'Đã thêm thẻ "' . $word . '".');
        }
        redirect($self . '#the-tu');
    }

    $cid = (int) ($_POST['card_id'] ?? 0);
    if ($act === 'card_hide' || $act === 'card_show') {
        $pdo->prepare('UPDATE sw_cards SET active = ? WHERE id = ? AND topic_id = ?')->execute([$act === 'card_show' ? 1 : 0, $cid, $id]);
    } elseif ($act === 'card_delete') {
        $pdo->prepare('DELETE FROM sw_cards WHERE id = ? AND topic_id = ?')->execute([$cid, $id]);
        flash_set('ok', 'Đã xoá thẻ.');
    }
    redirect($self . '#the-tu');
}

$cards = sw_cards($id, true);
$edit = null;
if (isset($_GET['card'])) {
    foreach ($cards as $c) {
        if ((int) $c['id'] === (int) $_GET['card']) {
            $edit = $c;
        }
    }
}
$f = $edit ?? ['id' => 0, 'word' => '', 'meaning' => '', 'example' => '', 'example_vi' => '', 'art' => '', 'image' => '', 'sort' => 0, 'active' => 1];

admin_head('Chủ đề ' . $topic['title_en'], 'vocab', $admin);
?>
<div class="adm-title">
  <div>
    <a class="back-link" href="<?= e(url('/admin/sw-tu-vung.php')) ?>"><?= icon('chevron-left') ?>Tất cả chủ đề</a>
    <h1>Chủ đề: <?= e($topic['title_en']) ?></h1>
  </div>
  <?php if ($topic['active'] && $cards): ?><a class="btn btn-outline btn-sm" href="<?= e(sw_url('tu-vung', $topic['slug'])) ?>" target="_blank" rel="noopener"><?= icon('eye') ?>Xem trên Sunny World</a><?php endif; ?>
</div>

<form method="post" class="card card-form" data-upload="<?= e(url('/admin/upload.php')) ?>" data-csrf="<?= e(csrf_token()) ?>" style="margin-bottom:26px">
  <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="action" value="save_topic">
  <h2 style="font-size:18px;color:var(--ink-900)">Thông tin chủ đề</h2>
  <div class="grid-2">
    <div class="field"><label class="label" for="title_en">Tên tiếng Anh</label><span class="control"><input id="title_en" name="title_en" type="text" maxlength="60" value="<?= e($topic['title_en']) ?>" required></span></div>
    <div class="field"><label class="label" for="title_vi">Tên tiếng Việt</label><span class="control"><input id="title_vi" name="title_vi" type="text" maxlength="60" value="<?= e($topic['title_vi']) ?>"></span></div>
  </div>
  <div class="grid-2">
    <div class="field"><label class="label" for="color">Màu chủ đề</label><span class="control"><input id="color" name="color" type="color" value="<?= e(sw_clean_color($topic['color'], '#FDBE10')) ?>" style="padding:0;height:36px"></span></div>
    <div class="field"><label class="label" for="t-art">Hình đại diện vẽ sẵn</label><?= admin_art_select('art', (string) $topic['art'], 't-art') ?></div>
  </div>
  <div class="field"><span class="label">Hoặc ảnh đại diện tải lên <span class="opt">(ưu tiên hơn hình vẽ sẵn)</span></span><?= admin_image_picker('image', (string) $topic['image']) ?></div>
  <label class="choice"><input type="checkbox" name="active" value="1"<?= $topic['active'] ? ' checked' : '' ?>><span class="box"></span><span class="text">Hiện trong Sunny World</span></label>
  <button class="btn" type="submit" style="justify-self:start"><?= icon('save') ?>Lưu chủ đề</button>
</form>

<h2 id="the-tu" style="font-size:22px;margin-bottom:12px">Thẻ từ (<?= count($cards) ?>)</h2>
<?php if ($cards): ?>
  <div class="card adm-table-card" style="margin-bottom:22px"><div class="table-scroll">
    <table class="adm-table">
      <thead><tr><th>Hình</th><th>Từ</th><th>Câu ví dụ</th><th>Trạng thái</th><th class="r">Thao tác</th></tr></thead>
      <tbody>
      <?php foreach ($cards as $c): ?>
        <tr>
          <td data-label="Hình"><span class="sw-art-sm"><?= sw_picture($c, $c['word']) ?></span></td>
          <td data-label="Từ"><div class="cell"><b><?= e($c['word']) ?></b><div class="muted small"><?= e($c['meaning']) ?></div></div></td>
          <td data-label="Ví dụ"><div class="cell"><?= e($c['example']) ?><div class="muted small"><?= e($c['example_vi']) ?></div></div></td>
          <td data-label="Trạng thái"><?= $c['active'] ? '<span class="badge badge-green">Hiện</span>' : '<span class="badge badge-neutral">Ẩn</span>' ?></td>
          <td class="r actions">
            <a class="btn btn-outline btn-sm" href="<?= e(url($self . '&card=' . (int) $c['id'])) ?>#form-the">Sửa</a>
            <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="card_id" value="<?= (int) $c['id'] ?>">
              <?php if ($c['active']): ?><button class="btn btn-ghost btn-sm" name="action" value="card_hide" type="submit">Ẩn</button>
              <?php else: ?><button class="btn btn-sm" name="action" value="card_show" type="submit">Hiện</button><?php endif; ?>
              <button class="btn btn-ghost btn-sm danger" name="action" value="card_delete" type="submit" data-confirm="Xoá thẻ &quot;<?= e($c['word']) ?>&quot;?"><?= icon('trash-2') ?><span class="sr-only">Xoá</span></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div></div>
<?php else: ?>
  <p class="muted" style="margin-bottom:18px">Chủ đề này chưa có thẻ nào nên chưa hiện cho bé. Hãy thêm thẻ đầu tiên ở dưới.</p>
<?php endif; ?>

<form method="post" id="form-the" class="card card-form" data-upload="<?= e(url('/admin/upload.php')) ?>" data-csrf="<?= e(csrf_token()) ?>">
  <?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="action" value="save_card"><input type="hidden" name="card_id" value="<?= (int) $f['id'] ?>">
  <h2 style="font-size:18px;color:var(--ink-900)"><?= $edit ? 'Sửa thẻ "' . e($edit['word']) . '"' : 'Thêm thẻ từ mới' ?></h2>
  <div class="grid-2">
    <div class="field"><label class="label" for="word">Từ tiếng Anh</label><span class="control"><input id="word" name="word" type="text" maxlength="40" value="<?= e($f['word']) ?>" placeholder="mother" required></span></div>
    <div class="field"><label class="label" for="meaning">Nghĩa tiếng Việt</label><span class="control"><input id="meaning" name="meaning" type="text" maxlength="80" value="<?= e($f['meaning']) ?>" placeholder="mẹ"></span></div>
  </div>
  <div class="grid-2">
    <div class="field"><label class="label" for="example">Câu ví dụ (tiếng Anh)</label><span class="control"><input id="example" name="example" type="text" maxlength="140" value="<?= e($f['example']) ?>" placeholder="I love my mother."></span></div>
    <div class="field"><label class="label" for="example_vi">Nghĩa của câu ví dụ</label><span class="control"><input id="example_vi" name="example_vi" type="text" maxlength="160" value="<?= e($f['example_vi']) ?>" placeholder="Con yêu mẹ."></span></div>
  </div>
  <div class="grid-2">
    <div class="field"><label class="label" for="c-art">Hình vẽ sẵn</label><?= admin_art_select('art', (string) $f['art'], 'c-art') ?></div>
    <div class="field"><label class="label" for="sort">Thứ tự <span class="opt">(để 0 = xếp cuối)</span></label><span class="control"><input id="sort" name="sort" type="number" min="0" max="9999" value="<?= (int) $f['sort'] ?>"></span></div>
  </div>
  <div class="field"><span class="label">Hoặc ảnh tải lên <span class="opt">(ảnh vuông đẹp nhất, tối đa 6MB)</span></span><?= admin_image_picker('image', (string) $f['image']) ?></div>
  <label class="choice"><input type="checkbox" name="active" value="1"<?= $f['active'] ? ' checked' : '' ?>><span class="box"></span><span class="text">Hiện thẻ này cho bé</span></label>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <button class="btn" type="submit"><?= icon('save') ?><?= $edit ? 'Lưu thẻ' : 'Thêm thẻ' ?></button>
    <?php if ($edit): ?><a class="btn btn-outline" href="<?= e(url($self)) ?>#the-tu">Huỷ sửa</a><?php endif; ?>
  </div>
  <p class="hint">Từ nhập ở đây sẽ dùng cho thẻ từ, và dùng lại cho các trò chơi ở đợt sau.</p>
</form>
<?php admin_foot(); ?>
