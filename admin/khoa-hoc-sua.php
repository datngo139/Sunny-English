<?php
/** Thêm / sửa một khoá học (tên, độ tuổi, mô tả, màu, biểu tượng, ảnh, nội dung chi tiết). */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require SUNNY_APP . '/admin_layout.php';

$admin = require_admin();
$pdo = db();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$row = null;
if ($id) {
    $st = $pdo->prepare('SELECT * FROM courses WHERE id = ?');
    $st->execute([$id]);
    $row = $st->fetch() ?: null;
    if (!$row) {
        flash_set('err', 'Không tìm thấy khoá học.');
        redirect('/admin/khoa-hoc.php');
    }
}

$levels = sunny_levels();
$icons = sunny_icons();
$f = [
    'title' => $row['title'] ?? '', 'ages' => $row['ages'] ?? '', 'description' => $row['description'] ?? '',
    'level' => $row['level'] ?? 'tieuhoc', 'icon' => $row['icon'] ?? 'book-open', 'image' => $row['image'] ?? '',
    'headline' => $row['headline'] ?? '', 'outcome' => $row['outcome'] ?? '', 'points' => $row['points'] ?? '',
    'active' => (int) ($row['active'] ?? 1),
];
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $clean = fn(string $k, int $max) => mb_substr(trim(preg_replace('/[ \t]+/u', ' ', (string) ($_POST[$k] ?? ''))), 0, $max);
    $f['title'] = $clean('title', 80);
    $f['ages'] = $clean('ages', 40);
    $f['description'] = $clean('description', 220);
    $f['headline'] = $clean('headline', 120);
    $f['outcome'] = $clean('outcome', 220);
    $f['level'] = array_key_exists($_POST['level'] ?? '', $levels) ? $_POST['level'] : 'tieuhoc';
    $f['icon'] = array_key_exists($_POST['icon'] ?? '', $icons) ? $_POST['icon'] : 'book-open';
    $f['active'] = empty($_POST['active']) ? 0 : 1;
    $f['image'] = trim((string) ($_POST['image'] ?? ''));
    if ($f['image'] !== '' && !preg_match('#^/uploads/\d{4}/\d{2}/[a-f0-9]{16}\.(?:jpe?g|png|webp|gif)$#', $f['image'])) {
        $f['image'] = '';
    }
    $lines = array_filter(array_map(fn($l) => mb_substr(trim($l), 0, 140), preg_split('/\R/u', (string) ($_POST['points'] ?? ''))));
    $f['points'] = implode("\n", array_slice(array_values($lines), 0, 8));

    if ($f['title'] === '') {
        $error = 'Vui lòng nhập tên khoá học.';
    } else {
        if ($row) {
            $pdo->prepare('UPDATE courses SET title=?, ages=?, description=?, level=?, icon=?, image=?, headline=?, outcome=?, points=?, active=? WHERE id=?')
                ->execute([$f['title'], $f['ages'], $f['description'], $f['level'], $f['icon'], $f['image'], $f['headline'], $f['outcome'], $f['points'], $f['active'], $id]);
            flash_set('ok', 'Đã lưu khoá học.');
        } else {
            $base = slugify($f['title']);
            $code = $base;
            $n = 2;
            $chk = $pdo->prepare('SELECT COUNT(*) FROM courses WHERE code = ?');
            while (true) {
                $chk->execute([$code]);
                if ((int) $chk->fetchColumn() === 0) {
                    break;
                }
                $code = mb_substr($base, 0, 70) . '-' . $n++;
            }
            $sort = (int) $pdo->query('SELECT COALESCE(MAX(sort), 0) FROM courses')->fetchColumn() + 10;
            $pdo->prepare('INSERT INTO courses (code, level, icon, title, ages, description, headline, outcome, points, image, sort, active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')
                ->execute([$code, $f['level'], $f['icon'], $f['title'], $f['ages'], $f['description'], $f['headline'], $f['outcome'], $f['points'], $f['image'], $sort, $f['active']]);
            flash_set('ok', 'Đã thêm khoá học mới.');
        }
        redirect('/admin/khoa-hoc.php');
    }
}

admin_head($row ? 'Sửa khoá học' : 'Thêm khoá học', 'courses', $admin);
?>
<div class="adm-title">
  <div>
    <a class="back-link" href="<?= e(url('/admin/khoa-hoc.php')) ?>"><?= icon('chevron-left') ?>Tất cả khoá học</a>
    <h1><?= $row ? 'Sửa khoá học' : 'Thêm khoá học' ?></h1>
  </div>
</div>
<?php if ($error): ?><div class="adm-flash adm-flash-err" role="alert"><?= e($error) ?></div><?php endif; ?>

<form method="post" id="course-form" class="editor-layout" data-upload="<?= e(url('/admin/upload.php')) ?>" data-csrf="<?= e(csrf_token()) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int) $id ?>">
  <input type="hidden" name="image" id="image-input" value="<?= e($f['image']) ?>">

  <div class="editor-main">
    <div class="card side-card" style="padding:24px">
      <h2>Thông tin trên thẻ khoá học</h2>
      <div class="field">
        <label class="label" for="title">Tên khoá học</label>
        <span class="control"><input id="title" name="title" type="text" maxlength="80" value="<?= e($f['title']) ?>" placeholder="Tiếng Anh Tiểu Học" required></span>
      </div>
      <div class="field">
        <label class="label" for="ages">Độ tuổi</label>
        <span class="control"><input id="ages" name="ages" type="text" maxlength="40" value="<?= e($f['ages']) ?>" placeholder="6–11 tuổi"></span>
        <span class="hint">Dùng dấu gạch dài "–" cho khoảng tuổi, ví dụ 3–6 tuổi.</span>
      </div>
      <div class="field">
        <label class="label" for="description">Mô tả ngắn</label>
        <span class="control is-textarea"><textarea id="description" name="description" maxlength="220" rows="3" style="min-height:84px" placeholder="1–2 câu hiện ngay trên thẻ"><?= e($f['description']) ?></textarea></span>
      </div>
      <div class="row-2" style="grid-template-columns:1fr 1fr">
        <div class="field">
          <label class="label" for="level">Màu thẻ</label>
          <span class="control has-select"><select id="level" name="level"><?php foreach ($levels as $k => $label): ?><option value="<?= e($k) ?>"<?= $f['level'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></span>
        </div>
        <div class="field">
          <label class="label" for="icon">Biểu tượng <span class="opt">(khi chưa có ảnh)</span></label>
          <span class="control has-select"><select id="icon" name="icon"><?php foreach ($icons as $k => $label): ?><option value="<?= e($k) ?>"<?= $f['icon'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></span>
        </div>
      </div>
    </div>

    <div class="card side-card" style="padding:24px">
      <h2>Nội dung khi bấm "Xem lộ trình cho con" <span class="muted" style="font-weight:500">(tuỳ chọn)</span></h2>
      <p class="hint">Để trống tiêu đề thì thẻ sẽ không có cửa sổ chi tiết, nút bấm sẽ đưa ba mẹ thẳng tới form đăng ký.</p>
      <div class="field">
        <label class="label" for="headline">Tiêu đề lớn</label>
        <span class="control"><input id="headline" name="headline" type="text" maxlength="120" value="<?= e($f['headline']) ?>" placeholder="Con tự tin nói tiếng Anh, sẵn sàng thi Movers – Flyers"></span>
      </div>
      <div class="field">
        <label class="label" for="outcome">Kết quả con đạt được</label>
        <span class="control is-textarea"><textarea id="outcome" name="outcome" maxlength="220" rows="2" style="min-height:70px"><?= e($f['outcome']) ?></textarea></span>
      </div>
      <div class="field">
        <label class="label" for="points">Các điểm nổi bật <span class="opt">(mỗi dòng một ý, tối đa 8)</span></label>
        <span class="control is-textarea"><textarea id="points" name="points" rows="5" style="min-height:120px"><?= e($f['points']) ?></textarea></span>
      </div>
    </div>
  </div>

  <aside class="editor-side">
    <div class="card side-card">
      <h2>Hiển thị</h2>
      <label class="choice"><input type="checkbox" name="active" value="1"<?= $f['active'] ? ' checked' : '' ?>><span class="box"></span><span class="text">Hiện trên website<br><span class="hint" style="font-weight:400">Bỏ chọn để ẩn tạm thời</span></span></label>
      <button class="btn btn-lg btn-block" type="submit"><?= icon('save') ?>Lưu khoá học</button>
    </div>
    <div class="card side-card">
      <h2>Ảnh khoá học</h2>
      <div class="cover-preview" id="image-preview"<?= $f['image'] === '' ? ' data-empty="1"' : '' ?>>
        <?php if ($f['image'] !== ''): ?><img src="<?= e(url($f['image'])) ?>" alt=""><?php else: ?><span><?= icon('image') ?>Chưa có ảnh</span><?php endif; ?>
      </div>
      <div class="cover-actions">
        <button class="btn btn-outline btn-sm" type="button" id="image-pick"><?= icon('upload') ?>Chọn ảnh</button>
        <button class="btn btn-ghost btn-sm danger" type="button" id="image-clear"<?= $f['image'] === '' ? ' hidden' : '' ?>>Bỏ ảnh</button>
      </div>
      <input type="file" id="image-file" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
      <span class="hint">Ảnh ngang đẹp nhất. Tối đa 6MB. Khi chưa có ảnh, thẻ hiện biểu tượng màu.</span>
    </div>
  </aside>
</form>
<?php admin_foot(); ?>
