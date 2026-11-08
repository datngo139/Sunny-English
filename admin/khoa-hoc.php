<?php
/** Danh sách khoá học: ẩn/hiện, sắp xếp, xoá. */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require SUNNY_APP . '/admin_layout.php';

$admin = require_admin();
$pdo = db();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    $act = (string) ($_POST['action'] ?? '');
    $st = $pdo->prepare('SELECT * FROM courses WHERE id = ?');
    $st->execute([$id]);
    $course = $st->fetch();
    if ($course) {
        if ($act === 'hide' || $act === 'show') {
            $pdo->prepare('UPDATE courses SET active = ? WHERE id = ?')->execute([$act === 'show' ? 1 : 0, $id]);
            flash_set('ok', $act === 'show' ? 'Đã hiện khoá "' . $course['title'] . '" trên website.' : 'Đã ẩn khoá "' . $course['title'] . '" khỏi website.');
        } elseif ($act === 'up' || $act === 'down') {
            $ids = array_map('intval', array_column($pdo->query('SELECT id FROM courses ORDER BY sort, id')->fetchAll(), 'id'));
            $i = array_search($id, $ids, true);
            $j = $act === 'up' ? $i - 1 : $i + 1;
            if ($i !== false && isset($ids[$j])) {
                [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
                $upd = $pdo->prepare('UPDATE courses SET sort = ? WHERE id = ?');
                foreach ($ids as $n => $cid) {
                    $upd->execute([($n + 1) * 10, $cid]);
                }
            }
        } elseif ($act === 'delete') {
            $pdo->prepare('DELETE FROM courses WHERE id = ?')->execute([$id]);
            flash_set('ok', 'Đã xoá khoá "' . $course['title'] . '".');
        }
    }
    redirect('/admin/khoa-hoc.php');
}

$courses = sunny_courses(true);
admin_head('Khoá học', 'courses', $admin);
?>
<div class="adm-title">
  <div>
    <h1>Khoá học</h1>
    <p class="muted">Thẻ khoá học hiện ở trang chủ và trong form đăng ký. Khoá bị ẩn sẽ không hiện cho khách.</p>
  </div>
  <a class="btn" href="<?= e(url('/admin/khoa-hoc-sua.php')) ?>"><?= icon('plus') ?>Thêm khoá học</a>
</div>

<?php if (!$courses): ?>
  <div class="card adm-empty"><?= icon('graduation-cap') ?><h2>Chưa có khoá học nào</h2><p>Bấm "Thêm khoá học" để tạo thẻ khoá học đầu tiên.</p></div>
<?php else: ?>
  <div class="card adm-table-card">
    <div class="table-scroll">
    <table class="adm-table">
      <thead><tr><th>Thứ tự</th><th>Khoá học</th><th>Trạng thái</th><th class="r">Thao tác</th></tr></thead>
      <tbody>
      <?php foreach ($courses as $i => $c): ?>
        <tr>
          <td data-label="Thứ tự" class="order">
            <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $c['dbid'] ?>">
              <button class="btn btn-ghost btn-sm" name="action" value="up" type="submit" aria-label="Lên"<?= $i === 0 ? ' disabled' : '' ?>><?= icon('arrow-up') ?></button>
              <button class="btn btn-ghost btn-sm" name="action" value="down" type="submit" aria-label="Xuống"<?= $i === count($courses) - 1 ? ' disabled' : '' ?>><?= icon('arrow-down') ?></button>
            </form>
          </td>
          <td data-label="Khoá học"><div class="cell course-cell">
            <span class="thumb lv-<?= e($c['level']) ?>"><?php if ($c['image'] !== ''): ?><img src="<?= e(url($c['image'])) ?>" alt=""><?php else: ?><?= icon($c['icon']) ?><?php endif; ?></span>
            <span><a class="t" href="<?= e(url('/admin/khoa-hoc-sua.php?id=' . $c['dbid'])) ?>"><?= e($c['title']) ?></a>
            <div class="muted small"><?= e($c['ages'] ?: 'Chưa nhập độ tuổi') ?></div></span>
          </div></td>
          <td data-label="Trạng thái"><?= $c['active'] ? '<span class="badge badge-green">Đang hiện</span>' : '<span class="badge badge-neutral">Đã ẩn</span>' ?></td>
          <td class="r actions">
            <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/khoa-hoc-sua.php?id=' . $c['dbid'])) ?>">Sửa</a>
            <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $c['dbid'] ?>">
              <?php if ($c['active']): ?><button class="btn btn-ghost btn-sm" name="action" value="hide" type="submit">Ẩn</button>
              <?php else: ?><button class="btn btn-sm" name="action" value="show" type="submit">Hiện</button><?php endif; ?>
              <button class="btn btn-ghost btn-sm danger" name="action" value="delete" type="submit" data-confirm="Xoá khoá &quot;<?= e($c['title']) ?>&quot;? Không thể khôi phục. Nếu chỉ muốn tạm dừng, hãy bấm Ẩn thay vì Xoá."><?= icon('trash-2') ?><span class="sr-only">Xoá</span></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
  <p class="muted small" style="margin-top:14px">Giao diện trang chủ đẹp nhất với 4–6 thẻ khoá học.</p>
<?php endif; ?>
<?php admin_foot(); ?>
