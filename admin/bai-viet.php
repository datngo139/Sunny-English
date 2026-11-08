<?php
/** Soạn / sửa bài viết. */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require SUNNY_APP . '/admin_layout.php';

$admin = require_admin();
$pdo = db();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$post = null;
if ($id) {
    $st = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
    $st->execute([$id]);
    $post = $st->fetch() ?: null;
    if (!$post) {
        flash_set('err', 'Không tìm thấy bài viết.');
        redirect('/admin/');
    }
}

$form = [
    'title' => $post['title'] ?? '',
    'slug' => $post['slug'] ?? '',
    'excerpt' => $post['excerpt'] ?? '',
    'cover' => $post['cover'] ?? '',
    'body' => $post['body'] ?? '',
    'status' => $post['status'] ?? 'draft',
    'published_at' => $post['published_at'] ?? '',
];
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $form['title'] = mb_substr(trim((string) ($_POST['title'] ?? '')), 0, 150);
    $form['slug'] = trim((string) ($_POST['slug'] ?? ''));
    $form['excerpt'] = mb_substr(trim((string) ($_POST['excerpt'] ?? '')), 0, 300);
    $form['cover'] = trim((string) ($_POST['cover'] ?? ''));
    $form['body'] = sanitize_html((string) ($_POST['body'] ?? ''));
    $form['status'] = (($_POST['status'] ?? '') === 'published') ? 'published' : 'draft';
    $dtRaw = trim((string) ($_POST['published_at'] ?? ''));

    // Ảnh bìa chỉ được là ảnh đã tải lên website.
    if ($form['cover'] !== '' && !preg_match('#^/uploads/\d{4}/\d{2}/[a-f0-9]{16}\.(?:jpe?g|png|webp|gif)$#', $form['cover'])) {
        $form['cover'] = '';
    }

    if ($form['title'] === '') {
        $error = 'Vui lòng nhập tiêu đề bài viết.';
    } elseif (trim(strip_tags($form['body'])) === '' && !str_contains($form['body'], '<img')) {
        $error = 'Bài viết chưa có nội dung.';
    }

    // Ngày đăng: để trống = ngay bây giờ. Nhập ngày tương lai = hẹn giờ đăng.
    $publishedAt = $post['published_at'] ?? null;
    if ($dtRaw !== '') {
        $t = strtotime(str_replace('T', ' ', $dtRaw));
        $publishedAt = $t ? date('Y-m-d H:i:s', $t) : $publishedAt;
    }
    if ($form['status'] === 'published' && !$publishedAt) {
        $publishedAt = date('Y-m-d H:i:s');
    }
    $form['published_at'] = (string) $publishedAt;

    if (!$error) {
        // Slug duy nhất.
        $base = slugify($form['slug'] !== '' ? $form['slug'] : $form['title']);
        $slug = $base;
        $n = 2;
        $chk = $pdo->prepare('SELECT COUNT(*) FROM posts WHERE slug = ? AND id <> ?');
        while (true) {
            $chk->execute([$slug, $id]);
            if ((int) $chk->fetchColumn() === 0) {
                break;
            }
            $slug = mb_substr($base, 0, 74) . '-' . $n++;
        }
        $now = date('Y-m-d H:i:s');
        if ($post) {
            $pdo->prepare('UPDATE posts SET slug=?, title=?, excerpt=?, cover=?, body=?, status=?, published_at=?, updated_at=? WHERE id=?')
                ->execute([$slug, $form['title'], $form['excerpt'], $form['cover'], $form['body'], $form['status'], $publishedAt, $now, $id]);
        } else {
            $pdo->prepare('INSERT INTO posts (slug, title, excerpt, cover, body, status, published_at, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?)')
                ->execute([$slug, $form['title'], $form['excerpt'], $form['cover'], $form['body'], $form['status'], $publishedAt, $now, $now]);
            $id = (int) $pdo->lastInsertId();
        }
        flash_set('ok', $form['status'] === 'published' ? 'Đã lưu và đăng bài.' : 'Đã lưu bản nháp.');
        redirect('/admin/bai-viet.php?id=' . $id);
    }
    $form['slug'] = $form['slug'];
}

$dtValue = $form['published_at'] ? date('Y-m-d\TH:i', strtotime($form['published_at'])) : '';
admin_head($post ? 'Sửa bài viết' : 'Viết bài mới', 'posts', $admin);
?>
<div class="adm-title">
  <div>
    <a class="back-link" href="<?= e(url('/admin/')) ?>"><?= icon('chevron-left') ?>Tất cả bài viết</a>
    <h1><?= $post ? 'Sửa bài viết' : 'Viết bài mới' ?></h1>
  </div>
  <?php if ($post): ?><a class="btn btn-outline btn-sm" href="<?= e(url('/bai-viet/' . $post['slug'])) ?>" target="_blank" rel="noopener"><?= icon('eye') ?>Xem bài</a><?php endif; ?>
</div>

<?php if ($error): ?><div class="adm-flash adm-flash-err" role="alert"><?= e($error) ?></div><?php endif; ?>

<form method="post" id="post-form" class="editor-layout" data-upload="<?= e(url('/admin/upload.php')) ?>" data-csrf="<?= e(csrf_token()) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int) $id ?>">
  <input type="hidden" name="cover" id="cover-input" value="<?= e($form['cover']) ?>">
  <textarea name="body" id="body-input" hidden><?= e($form['body']) ?></textarea>

  <div class="editor-main">
    <div class="field">
      <label class="label" for="title">Tiêu đề</label>
      <span class="control"><input id="title" name="title" type="text" maxlength="150" value="<?= e($form['title']) ?>" placeholder="Ví dụ: Lớp Starters mới khai giảng tháng 12" required style="font-weight:700"></span>
    </div>

    <div class="field">
      <span class="label" id="body-label">Nội dung</span>
      <div class="editor-box">
        <div class="toolbar" role="toolbar" aria-label="Định dạng văn bản">
          <button type="button" data-cmd="formatBlock" data-val="h2" title="Tiêu đề lớn"><b>H2</b></button>
          <button type="button" data-cmd="formatBlock" data-val="h3" title="Tiêu đề nhỏ"><b>H3</b></button>
          <button type="button" data-cmd="formatBlock" data-val="p" title="Đoạn văn thường">¶</button>
          <span class="sep"></span>
          <button type="button" data-cmd="bold" title="In đậm (Ctrl+B)"><?= icon('bold') ?></button>
          <button type="button" data-cmd="italic" title="In nghiêng (Ctrl+I)"><?= icon('italic') ?></button>
          <button type="button" data-cmd="underline" title="Gạch chân (Ctrl+U)"><?= icon('underline') ?></button>
          <span class="sep"></span>
          <button type="button" data-cmd="insertUnorderedList" title="Danh sách chấm"><?= icon('list') ?></button>
          <button type="button" data-cmd="insertOrderedList" title="Danh sách số"><?= icon('list-ordered') ?></button>
          <button type="button" data-cmd="formatBlock" data-val="blockquote" title="Trích dẫn"><?= icon('quote') ?></button>
          <span class="sep"></span>
          <button type="button" data-act="link" title="Chèn liên kết"><?= icon('link') ?></button>
          <button type="button" data-act="image" title="Chèn ảnh"><?= icon('image-plus') ?></button>
          <button type="button" data-cmd="insertHorizontalRule" title="Đường kẻ ngang"><?= icon('minus') ?></button>
          <button type="button" data-cmd="removeFormat" title="Xoá định dạng"><?= icon('remove-formatting') ?></button>
        </div>
        <div class="editable prose" id="editor" contenteditable="true" role="textbox" aria-multiline="true" aria-labelledby="body-label" spellcheck="true"><?= $form['body'] /* đã lọc bằng sanitize_html() */ ?></div>
      </div>
      <input type="file" id="img-input" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
      <span class="hint" id="editor-hint">Dán nội dung sẽ được giữ ở dạng chữ thường. Dùng các nút phía trên để định dạng, chèn ảnh và liên kết.</span>
    </div>
  </div>

  <aside class="editor-side">
    <div class="card side-card">
      <h2>Đăng bài</h2>
      <div class="status-choices">
        <label class="choice radio"><input type="radio" name="status" value="draft"<?= $form['status'] === 'draft' ? ' checked' : '' ?>><span class="box"></span><span class="text">Nháp<br><span class="hint" style="font-weight:400">Chỉ mình admin thấy</span></span></label>
        <label class="choice radio"><input type="radio" name="status" value="published"<?= $form['status'] === 'published' ? ' checked' : '' ?>><span class="box"></span><span class="text">Đăng công khai<br><span class="hint" style="font-weight:400">Hiện trên website</span></span></label>
      </div>
      <div class="field">
        <label class="label" for="published_at">Ngày đăng <span class="opt">(tuỳ chọn)</span></label>
        <span class="control"><input id="published_at" name="published_at" type="datetime-local" value="<?= e($dtValue) ?>"></span>
        <span class="hint">Để trống = đăng ngay. Chọn ngày giờ sau này để hẹn giờ.</span>
      </div>
      <button class="btn btn-lg btn-block" type="submit" id="save-btn"><?= icon('save') ?>Lưu bài viết</button>
    </div>

    <div class="card side-card">
      <h2>Ảnh bìa</h2>
      <div class="cover-preview" id="cover-preview"<?= $form['cover'] === '' ? ' data-empty="1"' : '' ?>>
        <?php if ($form['cover'] !== ''): ?><img src="<?= e(url($form['cover'])) ?>" alt=""><?php else: ?><span><?= icon('image') ?>Chưa có ảnh bìa</span><?php endif; ?>
      </div>
      <div class="cover-actions">
        <button class="btn btn-outline btn-sm" type="button" id="cover-pick"><?= icon('upload') ?>Chọn ảnh</button>
        <button class="btn btn-ghost btn-sm danger" type="button" id="cover-clear"<?= $form['cover'] === '' ? ' hidden' : '' ?>>Bỏ ảnh</button>
      </div>
      <input type="file" id="cover-file" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
      <span class="hint">Ảnh ngang (tỉ lệ 16:9) sẽ đẹp nhất. Tối đa 6MB.</span>
    </div>

    <div class="card side-card">
      <h2>Đường dẫn &amp; tóm tắt</h2>
      <div class="field">
        <label class="label" for="slug">Đường dẫn bài</label>
        <span class="control"><input id="slug" name="slug" type="text" maxlength="80" value="<?= e($form['slug']) ?>" placeholder="tự động từ tiêu đề"></span>
        <span class="hint">/bai-viet/<span id="slug-preview"><?= e($form['slug'] ?: '…') ?></span></span>
      </div>
      <div class="field">
        <label class="label" for="excerpt">Tóm tắt <span class="opt">(tuỳ chọn)</span></label>
        <span class="control is-textarea"><textarea id="excerpt" name="excerpt" maxlength="300" rows="3" style="min-height:84px" placeholder="1–2 câu hiện dưới tiêu đề và khi chia sẻ Facebook/Zalo. Để trống sẽ tự lấy từ nội dung."><?= e($form['excerpt']) ?></textarea></span>
      </div>
    </div>
  </aside>
</form>
<?php admin_foot(); ?>
