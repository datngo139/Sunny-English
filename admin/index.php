<?php
/** Danh sách bài viết + đăng/ẩn/xoá. */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require SUNNY_APP . '/admin_layout.php';

$admin = require_admin();
$pdo = db();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    $act = (string) ($_POST['action'] ?? '');
    $st = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
    $st->execute([$id]);
    $post = $st->fetch();
    if ($post) {
        if ($act === 'delete') {
            $pdo->prepare('DELETE FROM posts WHERE id = ?')->execute([$id]);
            flash_set('ok', 'Đã xoá bài "' . $post['title'] . '".');
        } elseif ($act === 'publish') {
            $pdo->prepare("UPDATE posts SET status = 'published', published_at = COALESCE(published_at, ?), updated_at = ? WHERE id = ?")
                ->execute([date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $id]);
            flash_set('ok', 'Đã đăng bài "' . $post['title'] . '".');
        } elseif ($act === 'unpublish') {
            $pdo->prepare("UPDATE posts SET status = 'draft', updated_at = ? WHERE id = ?")->execute([date('Y-m-d H:i:s'), $id]);
            flash_set('ok', 'Đã chuyển bài "' . $post['title'] . '" về nháp (ẩn khỏi website).');
        }
    }
    redirect('/admin/');
}

$posts = $pdo->query('SELECT id, slug, title, status, published_at, updated_at FROM posts ORDER BY COALESCE(published_at, updated_at) DESC, id DESC')->fetchAll();
$now = date('Y-m-d H:i:s');

admin_head('Bài viết', 'posts', $admin);
?>
<div class="adm-title">
  <div>
    <h1>Bài viết</h1>
    <p class="muted">Viết tin, thông báo lớp mới, chia sẻ cho ba mẹ. Bài đã đăng sẽ hiện ở trang chủ và mục Bài viết.</p>
  </div>
  <a class="btn" href="<?= e(url('/admin/bai-viet.php')) ?>"><?= icon('plus') ?>Viết bài mới</a>
</div>

<?php if (!$posts): ?>
  <div class="card adm-empty">
    <?= icon('pen-line') ?>
    <h2>Chưa có bài viết nào</h2>
    <p>Bấm "Viết bài mới" để đăng bài đầu tiên của trung tâm.</p>
    <a class="btn" href="<?= e(url('/admin/bai-viet.php')) ?>">Viết bài mới</a>
  </div>
<?php else: ?>
  <div class="card adm-table-card">
    <div class="table-scroll">
    <table class="adm-table">
      <thead><tr><th>Tiêu đề</th><th>Trạng thái</th><th>Ngày đăng</th><th class="r">Thao tác</th></tr></thead>
      <tbody>
      <?php foreach ($posts as $p):
          $live = $p['status'] === 'published' && $p['published_at'] <= $now;
          $sched = $p['status'] === 'published' && $p['published_at'] > $now;
          ?>
        <tr>
          <td data-label="Tiêu đề"><div class="cell"><a class="t" href="<?= e(url('/admin/bai-viet.php?id=' . $p['id'])) ?>"><?= e($p['title']) ?></a><div class="muted small">/bai-viet/<?= e($p['slug']) ?></div></div></td>
          <td data-label="Trạng thái">
            <?php if ($live): ?><span class="badge badge-green">Đã đăng</span>
            <?php elseif ($sched): ?><span class="badge badge-blue">Hẹn giờ</span>
            <?php else: ?><span class="badge badge-neutral">Nháp</span><?php endif; ?>
          </td>
          <td data-label="Ngày đăng"><?= $p['published_at'] ? e(vn_date($p['published_at'], true)) : '—' ?></td>
          <td class="r actions">
            <a class="btn btn-outline btn-sm" href="<?= e(url('/admin/bai-viet.php?id=' . $p['id'])) ?>">Sửa</a>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/bai-viet/' . $p['slug'])) ?>" target="_blank" rel="noopener"><?= $live ? 'Xem' : 'Xem thử' ?></a>
            <form method="post" class="inline">
              <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
              <?php if ($p['status'] === 'published'): ?>
                <button class="btn btn-ghost btn-sm" name="action" value="unpublish" type="submit">Ẩn</button>
              <?php else: ?>
                <button class="btn btn-sm" name="action" value="publish" type="submit">Đăng</button>
              <?php endif; ?>
              <button class="btn btn-ghost btn-sm danger" name="action" value="delete" type="submit" data-confirm="Xoá bài &quot;<?= e($p['title']) ?>&quot;? Không thể khôi phục."><?= icon('trash-2') ?><span class="sr-only">Xoá</span></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
<?php endif; ?>
<?php admin_foot(); ?>
