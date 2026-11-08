<?php
/** Danh sách phụ huynh đăng ký học thử. */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require SUNNY_APP . '/admin_layout.php';

$admin = require_admin();
$pdo = db();

const REG_STATUS = ['new' => 'Mới', 'contacted' => 'Đã gọi', 'enrolled' => 'Đã xếp lớp', 'closed' => 'Không học'];
$statusBadge = ['new' => 'badge', 'contacted' => 'badge-blue', 'enrolled' => 'badge-green', 'closed' => 'badge-neutral'];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $id = (int) ($_POST['id'] ?? 0);
    $act = (string) ($_POST['action'] ?? '');
    if ($act === 'status' && isset(REG_STATUS[$_POST['status'] ?? ''])) {
        $pdo->prepare('UPDATE registrations SET status = ? WHERE id = ?')->execute([$_POST['status'], $id]);
        flash_set('ok', 'Đã cập nhật trạng thái.');
    } elseif ($act === 'delete') {
        $pdo->prepare('DELETE FROM registrations WHERE id = ?')->execute([$id]);
        flash_set('ok', 'Đã xoá đăng ký.');
    }
    redirect('/admin/dang-ky.php' . (isset($_GET['status']) ? '?status=' . urlencode((string) $_GET['status']) : ''));
}

$filter = (string) ($_GET['status'] ?? '');
if (!isset(REG_STATUS[$filter])) {
    $filter = '';
}

$courseNames = array_column(sunny_courses(true), 'title', 'id');
$legacySlots = ['sang-t7' => 'Sáng thứ 7', 'chieu-cn' => 'Chiều chủ nhật']; // đăng ký cũ trước khi có chọn ngày giờ
$whenOf = function (array $r) use ($legacySlots): string {
    if (($r['visit_date'] ?? '') !== '') {
        return schedule_label($r['visit_date'], $r['visit_time'] ?? '');
    }
    return $legacySlots[$r['slot'] ?? ''] ?? '';
};

$sql = 'SELECT * FROM registrations' . ($filter ? ' WHERE status = ?' : '') . ' ORDER BY created_at DESC, id DESC LIMIT 1000';
$st = $pdo->prepare($sql);
$st->execute($filter ? [$filter] : []);
$rows = $st->fetchAll();

// Xuất CSV (mở được bằng Excel, có BOM để hiển thị đúng tiếng Việt).
if (($_GET['export'] ?? '') === 'csv') {
    $cell = function ($v): string {
        $v = (string) $v;
        if ($v !== '' && strpbrk($v[0], "=+-@\t\r") !== false) {
            $v = "'" . $v; // chống chèn công thức (CSV injection)
        }
        return '"' . str_replace('"', '""', $v) . '"';
    };
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="dang-ky-hoc-thu-' . date('Y-m-d') . '.csv"');
    echo "\xEF\xBB\xBF";
    echo implode(',', array_map($cell, ['Thời gian', 'Phụ huynh', 'Điện thoại', 'Tên con', 'Tuổi', 'Khoá học', 'Ngày giờ học thử', 'Trạng thái'])) . "\r\n";
    foreach ($rows as $r) {
        echo implode(',', array_map($cell, [
            vn_date($r['created_at'], true), $r['parent_name'], $r['phone'], $r['child_name'], $r['child_age'] ?? '',
            $courseNames[$r['course']] ?? '', $whenOf($r), REG_STATUS[$r['status']] ?? $r['status'],
        ])) . "\r\n";
    }
    exit;
}

$counts = ['' => 0] + array_fill_keys(array_keys(REG_STATUS), 0);
foreach ($pdo->query('SELECT status, COUNT(*) c FROM registrations GROUP BY status') as $r) {
    $counts[$r['status']] = (int) $r['c'];
    $counts[''] += (int) $r['c'];
}

admin_head('Đăng ký học thử', 'regs', $admin);
?>
<div class="adm-title">
  <div>
    <h1>Đăng ký học thử</h1>
    <p class="muted">Gọi lại cho ba mẹ trong 24 giờ như đã hứa trên website.</p>
  </div>
  <a class="btn btn-outline" href="<?= e(url('/admin/dang-ky.php?export=csv' . ($filter ? '&status=' . $filter : ''))) ?>"><?= icon('download') ?>Tải file Excel (CSV)</a>
</div>

<div class="tabs" role="tablist" style="margin-bottom:20px">
  <a class="tab" role="tab" aria-selected="<?= $filter === '' ? 'true' : 'false' ?>" href="<?= e(url('/admin/dang-ky.php')) ?>">Tất cả (<?= $counts[''] ?>)</a>
  <?php foreach (REG_STATUS as $k => $label): ?>
    <a class="tab" role="tab" aria-selected="<?= $filter === $k ? 'true' : 'false' ?>" href="<?= e(url('/admin/dang-ky.php?status=' . $k)) ?>"><?= e($label) ?> (<?= $counts[$k] ?>)</a>
  <?php endforeach; ?>
</div>

<?php if (!$rows): ?>
  <div class="card adm-empty"><?= icon('inbox') ?><h2>Chưa có đăng ký nào</h2><p>Khi phụ huynh điền form trên website, thông tin sẽ hiện ở đây.</p></div>
<?php else: ?>
  <div class="card adm-table-card">
    <div class="table-scroll">
    <table class="adm-table">
      <thead><tr><th>Thời gian</th><th>Phụ huynh</th><th>Con</th><th>Khoá học · Ngày giờ học thử</th><th>Trạng thái</th><th class="r"></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td data-label="Thời gian"><?= e(vn_date($r['created_at'], true)) ?></td>
          <td data-label="Phụ huynh"><div class="cell"><b><?= e($r['parent_name']) ?></b><br>
            <a href="tel:<?= e($r['phone']) ?>"><?= icon('phone') ?> <?= e($r['phone']) ?></a>
            · <a href="https://zalo.me/<?= e($r['phone']) ?>" target="_blank" rel="noopener">Zalo</a></div></td>
          <td data-label="Con"><?= e($r['child_name'] ?: '—') ?><?= $r['child_age'] ? ' · ' . (int) $r['child_age'] . ' tuổi' : '' ?></td>
          <td data-label="Khoá học"><div class="cell"><?= e($courseNames[$r['course']] ?? 'Chưa chọn') ?><div class="muted small"><?= e($whenOf($r) ?: 'Chưa chọn giờ, cần gọi hẹn') ?></div></div></td>
          <td data-label="Trạng thái">
            <form method="post" class="inline">
              <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="action" value="status">
              <span class="control has-select sm"><select name="status" data-autosubmit aria-label="Trạng thái của <?= e($r['parent_name']) ?>">
                <?php foreach (REG_STATUS as $k => $label): ?><option value="<?= e($k) ?>"<?= $r['status'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
              </select></span>
            </form>
          </td>
          <td class="r">
            <form method="post" class="inline">
              <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <button class="btn btn-ghost btn-sm danger" name="action" value="delete" type="submit" data-confirm="Xoá đăng ký của <?= e($r['parent_name']) ?>?"><?= icon('trash-2') ?><span class="sr-only">Xoá</span></button>
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
