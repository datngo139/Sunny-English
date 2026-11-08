<?php
/**
 * Tạo tài khoản admin lần đầu. Chỉ dùng được khi CHƯA có admin nào, và phải nhập "mã cài đặt"
 * (config.php → setup_code) để người lạ không chiếm quyền trước chủ website.
 */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require SUNNY_APP . '/admin_layout.php';

// Đặt lại admin khi quên mật khẩu: tạo file data/reset-admin.txt trên hosting (chỉ người có quyền File Manager làm được).
$resetFlag = SUNNY_ROOT . '/data/reset-admin.txt';
$reset = is_file($resetFlag);
if (admin_count() > 0 && !$reset) {
    redirect('/admin/login.php');
}

$configured = (string) config('setup_code', '');
$placeholder = $configured === '' || str_starts_with($configured, 'DOI-CHUOI-NAY');
$error = '';
$username = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !$placeholder) {
    csrf_check();
    $username = trim((string) ($_POST['username'] ?? ''));
    $pw = (string) ($_POST['password'] ?? '');
    $pw2 = (string) ($_POST['password2'] ?? '');
    $code = (string) ($_POST['setup_code'] ?? '');

    if (login_is_locked()) {
        $error = 'Nhập sai quá nhiều lần. Vui lòng thử lại sau 15 phút.';
    } elseif (!hash_equals($configured, $code)) {
        login_record_fail();
        $error = 'Mã cài đặt không đúng.';
    } elseif (!preg_match('/^[A-Za-z0-9_.\-]{3,32}$/', $username)) {
        $error = 'Tên đăng nhập gồm 3–32 ký tự: chữ không dấu, số, dấu gạch.';
    } elseif ($pw !== $pw2) {
        $error = 'Hai lần nhập mật khẩu chưa giống nhau.';
    } elseif ($problem = password_problem($pw)) {
        $error = $problem;
    } else {
        $pdo = db();
        if ($reset) {
            $pdo->exec('DELETE FROM admins');
            @unlink($resetFlag);
        }
        $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)')
            ->execute([$username, password_hash($pw, PASSWORD_DEFAULT)]);
        login_clear_fails();
        admin_login((int) $pdo->lastInsertId(), $username);
        flash_set('ok', 'Đã tạo tài khoản admin. Chào mừng bạn!');
        redirect('/admin/');
    }
}

admin_head('Cài đặt lần đầu');
?>
<div class="auth-wrap">
  <div class="auth-logo"><img src="<?= e(asset('/assets/img/sunny-logo-full.png')) ?>" alt="Sunny Education" width="140" height="152"></div>
  <?php if ($placeholder): ?>
    <div class="card auth-card">
      <h1>Cần đặt mã cài đặt</h1>
      <p>Mở file <code>app/config.php</code> trên hosting, tìm dòng <code>'setup_code'</code> và đổi thành một chuỗi bí mật của riêng bạn (dài, khó đoán), lưu lại rồi tải lại trang này.</p>
    </div>
  <?php else: ?>
    <form class="card auth-card" method="post" autocomplete="off">
      <h1><?= $reset ? 'Đặt lại tài khoản admin' : 'Tạo tài khoản admin' ?></h1>
      <p><?= $reset ? 'Tài khoản admin cũ sẽ bị thay bằng tài khoản mới này. Bài viết và đăng ký được giữ nguyên.' : 'Đây là lần đầu chạy website. Hãy tạo tài khoản quản trị.' ?></p>
      <?php if ($error): ?><div class="form-msg" role="alert"><?= e($error) ?></div><?php endif; ?>
      <?= csrf_field() ?>
      <div class="field">
        <label class="label" for="sc">Mã cài đặt</label>
        <span class="control"><input id="sc" name="setup_code" type="password" required autocomplete="off"></span>
        <span class="hint">Chuỗi bạn đã đặt ở <code>setup_code</code> trong <code>app/config.php</code>.</span>
      </div>
      <div class="field">
        <label class="label" for="u">Tên đăng nhập</label>
        <span class="control"><input id="u" name="username" type="text" value="<?= e($username) ?>" required autocomplete="username"></span>
      </div>
      <div class="field">
        <label class="label" for="p">Mật khẩu</label>
        <span class="control"><input id="p" name="password" type="password" required autocomplete="new-password" minlength="10"></span>
        <span class="hint">Ít nhất 10 ký tự, có cả chữ và số.</span>
      </div>
      <div class="field">
        <label class="label" for="p2">Nhập lại mật khẩu</label>
        <span class="control"><input id="p2" name="password2" type="password" required autocomplete="new-password"></span>
      </div>
      <button class="btn btn-lg btn-block" type="submit">Tạo tài khoản</button>
    </form>
  <?php endif; ?>
</div>
<?php admin_foot(); ?>
