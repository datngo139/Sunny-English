<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require SUNNY_APP . '/admin_layout.php';

if (admin_count() === 0) {
    redirect('/admin/setup.php');
}
if (current_admin()) {
    redirect('/admin/');
}

$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    if (login_is_locked()) {
        $error = 'Đăng nhập sai quá nhiều lần. Vui lòng thử lại sau 15 phút.';
    } else {
        $user = trim((string) ($_POST['username'] ?? ''));
        $pass = (string) ($_POST['password'] ?? '');
        $st = db()->prepare('SELECT id, username, password_hash FROM admins WHERE username = ?');
        $st->execute([$user]);
        $row = $st->fetch();
        // Luôn gọi password_verify (kể cả khi không có user) để thời gian phản hồi không lộ tên đăng nhập.
        $hash = $row['password_hash'] ?? '$2y$10$bmPibuqH6FI0gxtE8X0fW.4dBcIqF12Qb1Ix0sat1KrVe94Rj9INu';
        if (password_verify($pass, $hash) && $row) {
            login_clear_fails();
            if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
                db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')->execute([password_hash($pass, PASSWORD_DEFAULT), $row['id']]);
            }
            admin_login((int) $row['id'], (string) $row['username']);
            redirect('/admin/');
        }
        login_record_fail();
        $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
    }
}

admin_head('Đăng nhập');
?>
<div class="auth-wrap">
  <div class="auth-logo"><img src="<?= e(asset('/assets/img/sunny-logo-full.png')) ?>" alt="Sunny Education" width="140" height="152"></div>
  <form class="card auth-card" method="post" autocomplete="on">
    <h1>Đăng nhập quản trị</h1>
    <?php if ($error): ?><div class="form-msg" role="alert"><?= e($error) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <div class="field">
      <label class="label" for="u">Tên đăng nhập</label>
      <span class="control"><input id="u" name="username" type="text" autocomplete="username" required autofocus></span>
    </div>
    <div class="field">
      <label class="label" for="p">Mật khẩu</label>
      <span class="control"><input id="p" name="password" type="password" autocomplete="current-password" required></span>
    </div>
    <button class="btn btn-lg btn-block" type="submit">Đăng nhập</button>
  </form>
  <p class="auth-back"><a href="<?= e(url('/')) ?>">← Về trang chủ</a></p>
</div>
<?php admin_foot(); ?>
