<?php
/** Sunny World — danh sách truyện: ẩn/hiện, sắp xếp, xoá. */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require SUNNY_APP . '/admin_layout.php';

$admin = require_admin();
$pdo = db();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    $act = (string) ($_POST['action'] ?? '');
    $st = $pdo->prepare('SELECT * FROM sw_stories WHERE id = ?');
    $st->execute([$id]);
    $story = $st->fetch();
    if ($story) {
        if ($act === 'hide' || $act === 'show') {
            $pdo->prepare('UPDATE sw_stories SET active = ? WHERE id = ?')->execute([$act === 'show' ? 1 : 0, $id]);
            flash_set('ok', $act === 'show' ? 'Đã hiện truyện "' . $story['title'] . '".' : 'Đã ẩn truyện "' . $story['title'] . '".');
        } elseif ($act === 'up' || $act === 'down') {
            $ids = array_map('intval', array_column($pdo->query('SELECT id FROM sw_stories ORDER BY sort, id')->fetchAll(), 'id'));
            $i = array_search($id, $ids, true);
            $j = $act === 'up' ? $i - 1 : $i + 1;
            if ($i !== false && isset($ids[$j])) {
                [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
                $upd = $pdo->prepare('UPDATE sw_stories SET sort = ? WHERE id = ?');
                foreach ($ids as $k => $sid) {
                    $upd->execute([($k + 1) * 10, $sid]);
                }
            }
        } elseif ($act === 'delete') {
            $pdo->prepare('DELETE FROM sw_stories WHERE id = ?')->execute([$id]);
            flash_set('ok', 'Đã xoá truyện "' . $story['title'] . '".');
        }
    }
    redirect('/admin/sw-truyen.php');
}

$stories = sw_stories(null, true);
admin_head('Truyện', 'stories', $admin);
?>
<div class="adm-title">
  <div>
    <h1>Truyện (Sunny World)</h1>
    <p class="muted">Truyện tiếng Anh trong Thư viện Truyện. Bé rê chuột hoặc chạm vào từng từ để xem nghĩa.</p>
  </div>
  <a class="btn" href="<?= e(url('/admin/sw-truyen-sua.php')) ?>"><?= icon('plus') ?>Thêm truyện</a>
</div>

<?php if (!$stories): ?>
  <div class="card adm-empty"><?= icon('book-open') ?><h2>Chưa có truyện nào</h2><p>Bấm "Thêm truyện" để viết truyện đầu tiên.</p></div>
<?php else: ?>
  <div class="card adm-table-card"><div class="table-scroll">
    <table class="adm-table">
      <thead><tr><th>Thứ tự</th><th>Truyện</th><th>Loại</th><th>Từ thiếu nghĩa</th><th>Trạng thái</th><th class="r">Thao tác</th></tr></thead>
      <tbody>
      <?php foreach ($stories as $i => $s):
          $pages = sw_story_pages($s);
          $miss = count(sw_missing_words($pages, sw_parse_glossary((string) $s['glossary'])));
          ?>
        <tr>
          <td data-label="Thứ tự" class="order"><form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
            <button class="btn btn-ghost btn-sm" name="action" value="up" type="submit" aria-label="Lên"<?= $i === 0 ? ' disabled' : '' ?>><?= icon('arrow-up') ?></button>
            <button class="btn btn-ghost btn-sm" name="action" value="down" type="submit" aria-label="Xuống"<?= $i === count($stories) - 1 ? ' disabled' : '' ?>><?= icon('arrow-down') ?></button></form></td>
          <td data-label="Truyện"><div class="cell course-cell"><span class="sw-art-sm" style="background:<?= e(sw_clean_color($s['color'])) ?>"><?= sw_picture($s, $s['title']) ?></span>
            <span><a class="t" href="<?= e(url('/admin/sw-truyen-sua.php?id=' . (int) $s['id'])) ?>"><?= e($s['title']) ?></a><div class="muted small"><?= e($s['title_vi']) ?> · <?= count($pages) ?> trang</div></span></div></td>
          <td data-label="Loại"><span class="badge <?= $s['kind'] === 'science' ? 'badge-blue' : 'badge-neutral' ?>"><?= e(SW_KINDS[$s['kind']] ?? 'Truyện') ?></span></td>
          <td data-label="Từ thiếu nghĩa"><?= $miss ? '<span class="badge badge-orange">' . $miss . ' từ</span>' : '<span class="muted">Đủ</span>' ?></td>
          <td data-label="Trạng thái"><?= $s['active'] ? '<span class="badge badge-green">Đang hiện</span>' : '<span class="badge badge-neutral">Đã ẩn</span>' ?></td>
          <td class="r actions">
            <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/sw-truyen-sua.php?id=' . (int) $s['id'])) ?>">Sửa</a>
            <?php if ($s['active']): ?><a class="btn btn-ghost btn-sm" href="<?= e(sw_url('truyen', $s['slug'])) ?>" target="_blank" rel="noopener">Xem</a><?php endif; ?>
            <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
              <?php if ($s['active']): ?><button class="btn btn-ghost btn-sm" name="action" value="hide" type="submit">Ẩn</button>
              <?php else: ?><button class="btn btn-sm" name="action" value="show" type="submit">Hiện</button><?php endif; ?>
              <button class="btn btn-ghost btn-sm danger" name="action" value="delete" type="submit" data-confirm="Xoá truyện &quot;<?= e($s['title']) ?>&quot;? Không thể khôi phục."><?= icon('trash-2') ?><span class="sr-only">Xoá</span></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div></div>
<?php endif; ?>
<?php admin_foot(); ?>
