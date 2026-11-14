<?php
/** Sunny World — danh sách chủ đề từ vựng: thêm, ẩn/hiện, sắp xếp, xoá. */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require SUNNY_APP . '/admin_layout.php';

$admin = require_admin();
$pdo = db();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $act = (string) ($_POST['action'] ?? '');
    if ($act === 'add') {
        $en = mb_substr(trim((string) ($_POST['title_en'] ?? '')), 0, 60);
        $vi = mb_substr(trim((string) ($_POST['title_vi'] ?? '')), 0, 60);
        if ($en === '') {
            flash_set('err', 'Vui lòng nhập tên chủ đề bằng tiếng Anh.');
            redirect('/admin/sw-tu-vung.php');
        }
        $base = slugify($en);
        $slug = $base;
        $n = 2;
        $chk = $pdo->prepare('SELECT COUNT(*) FROM sw_topics WHERE slug = ?');
        while (true) {
            $chk->execute([$slug]);
            if ((int) $chk->fetchColumn() === 0) {
                break;
            }
            $slug = mb_substr($base, 0, 70) . '-' . $n++;
        }
        $sort = (int) $pdo->query('SELECT COALESCE(MAX(sort), 0) FROM sw_topics')->fetchColumn() + 10;
        $pdo->prepare('INSERT INTO sw_topics (slug, title_en, title_vi, sort, active) VALUES (?,?,?,?,1)')->execute([$slug, $en, $vi, $sort]);
        flash_set('ok', 'Đã tạo chủ đề "' . $en . '". Giờ hãy thêm thẻ từ cho chủ đề này.');
        redirect('/admin/sw-chu-de.php?id=' . (int) $pdo->lastInsertId());
    }
    $id = (int) ($_POST['id'] ?? 0);
    $st = $pdo->prepare('SELECT * FROM sw_topics WHERE id = ?');
    $st->execute([$id]);
    $topic = $st->fetch();
    if ($topic) {
        if ($act === 'hide' || $act === 'show') {
            $pdo->prepare('UPDATE sw_topics SET active = ? WHERE id = ?')->execute([$act === 'show' ? 1 : 0, $id]);
            flash_set('ok', $act === 'show' ? 'Đã hiện chủ đề "' . $topic['title_en'] . '".' : 'Đã ẩn chủ đề "' . $topic['title_en'] . '" khỏi Sunny World.');
        } elseif ($act === 'up' || $act === 'down') {
            $ids = array_map('intval', array_column($pdo->query('SELECT id FROM sw_topics ORDER BY sort, id')->fetchAll(), 'id'));
            $i = array_search($id, $ids, true);
            $j = $act === 'up' ? $i - 1 : $i + 1;
            if ($i !== false && isset($ids[$j])) {
                [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
                $upd = $pdo->prepare('UPDATE sw_topics SET sort = ? WHERE id = ?');
                foreach ($ids as $k => $tid) {
                    $upd->execute([($k + 1) * 10, $tid]);
                }
            }
        } elseif ($act === 'delete') {
            $pdo->prepare('DELETE FROM sw_cards WHERE topic_id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM sw_topics WHERE id = ?')->execute([$id]);
            flash_set('ok', 'Đã xoá chủ đề "' . $topic['title_en'] . '" và các thẻ từ của nó.');
        }
    }
    redirect('/admin/sw-tu-vung.php');
}

$topics = sw_topics(true);
admin_head('Từ vựng', 'vocab', $admin);
?>
<div class="adm-title">
  <div>
    <h1>Từ vựng (Sunny World)</h1>
    <p class="muted">Mỗi chủ đề là một bộ thẻ từ trong Vườn Từ vựng. Chủ đề chưa có thẻ nào sẽ không hiện cho bé.</p>
  </div>
  <a class="btn btn-outline" href="<?= e(sw_url('tu-vung')) ?>" target="_blank" rel="noopener"><?= icon('external-link') ?>Xem Vườn Từ vựng</a>
</div>

<form method="post" class="card" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;align-items:end;margin-bottom:22px">
  <?= csrf_field() ?><input type="hidden" name="action" value="add">
  <div class="field"><label class="label" for="t-en">Tên chủ đề (tiếng Anh)</label><span class="control"><input id="t-en" name="title_en" type="text" maxlength="60" placeholder="Family" required></span></div>
  <div class="field"><label class="label" for="t-vi">Tên tiếng Việt</label><span class="control"><input id="t-vi" name="title_vi" type="text" maxlength="60" placeholder="Gia đình"></span></div>
  <button class="btn" type="submit"><?= icon('plus') ?>Thêm chủ đề</button>
</form>

<?php if (!$topics): ?>
  <div class="card adm-empty"><?= icon('shapes') ?><h2>Chưa có chủ đề nào</h2><p>Nhập tên ở trên rồi bấm "Thêm chủ đề".</p></div>
<?php else: ?>
  <div class="card adm-table-card"><div class="table-scroll">
    <table class="adm-table">
      <thead><tr><th>Thứ tự</th><th>Chủ đề</th><th>Số thẻ</th><th>Trạng thái</th><th class="r">Thao tác</th></tr></thead>
      <tbody>
      <?php foreach ($topics as $i => $t): ?>
        <tr>
          <td data-label="Thứ tự" class="order"><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
            <button class="btn btn-ghost btn-sm" name="action" value="up" type="submit" aria-label="Lên"<?= $i === 0 ? ' disabled' : '' ?>><?= icon('arrow-up') ?></button>
            <button class="btn btn-ghost btn-sm" name="action" value="down" type="submit" aria-label="Xuống"<?= $i === count($topics) - 1 ? ' disabled' : '' ?>><?= icon('arrow-down') ?></button></form></td>
          <td data-label="Chủ đề"><div class="cell course-cell"><span class="sw-art-sm"><?= sw_picture($t, $t['title_en']) ?></span>
            <span><a class="t" href="<?= e(url('/admin/sw-chu-de.php?id=' . (int) $t['id'])) ?>"><?= e($t['title_en']) ?></a><div class="muted small"><?= e($t['title_vi']) ?></div></span></div></td>
          <td data-label="Số thẻ"><?= (int) $t['n'] ?></td>
          <td data-label="Trạng thái"><?= $t['active'] ? '<span class="badge badge-green">Đang hiện</span>' : '<span class="badge badge-neutral">Đã ẩn</span>' ?></td>
          <td class="r actions">
            <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/sw-chu-de.php?id=' . (int) $t['id'])) ?>">Sửa và thêm thẻ</a>
            <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
              <?php if ($t['active']): ?><button class="btn btn-ghost btn-sm" name="action" value="hide" type="submit">Ẩn</button>
              <?php else: ?><button class="btn btn-sm" name="action" value="show" type="submit">Hiện</button><?php endif; ?>
              <button class="btn btn-ghost btn-sm danger" name="action" value="delete" type="submit" data-confirm="Xoá chủ đề &quot;<?= e($t['title_en']) ?>&quot; cùng <?= (int) $t['n'] ?> thẻ từ? Không thể khôi phục."><?= icon('trash-2') ?><span class="sr-only">Xoá</span></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div></div>
<?php endif; ?>
<?php admin_foot(); ?>
