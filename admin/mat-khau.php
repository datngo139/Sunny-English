<?php
/** Đổi mật khẩu admin. */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require SUNNY_APP . '/admin_layout.php';

$admin = require_admin();
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $cur = (string) ($_POST['current'] ?? '');
    $new = (string) ($_POST['new'] ?? '');
    $new2 = (string) ($_POST['new2'] ?? '');
    $st = db()->prepare('SELECT password_hash FROM admins WHERE id = ?');
    $st->execute([$admin['id']]);
    $hash = (string) $st->fetchColumn();

    if (login_is_locked()) {
        $error = 'Thử sai quá nhiều lần. Vui lòng đợi 15 phút.';
    } elseif (!password_verify($cur, $hash)) {
        login_record_fail();
        $error = 'Mật khẩu hiện tại không đúng.';
    } elseif ($new !== $new2) {
        $error = 'Hai lần nhập mật khẩu mới chưa giống nhau.';
    } elseif ($problem = password_problem($new)) {
        $error = $problem;
    } else {
        db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $admin['id']]);
        session_regenerate_id(true);
        login_clear_fails();
        flash_set('ok', 'Đã đổi mật khẩu.');
        redirect('/admin/mat-khau.php');
    }
}

admin_head('Đổi mật khẩu', 'pw', $admin);
?>
<div class="adm-title"><div><h1>Đổi mật khẩu</h1></div></div>
<form class="card" method="post" autocomplete="off" style="max-width:520px;display:grid;gap:18px">
  <?php if ($error): ?><div class="form-msg" role="alert"><?= e($error) ?></div><?php endif; ?>
  <?= csrf_field() ?>
  <div class="field"><label class="label" for="c">Mật khẩu hiện tại</label><span class="control"><input id="c" name="current" type="password" required autocomplete="current-password"></span></div>
  <div class="field"><label class="label" for="n">Mật khẩu mới</label><span class="control"><input id="n" name="new" type="password" required minlength="10" autocomplete="new-password"></span><span class="hint">Ít nhất 10 ký tự, có cả chữ và số.</span></div>
  <div class="field"><label class="label" for="n2">Nhập lại mật khẩu mới</label><span class="control"><input id="n2" name="new2" type="password" required autocomplete="new-password"></span></div>
  <button class="btn btn-block" type="submit">Đổi mật khẩu</button>
</form>
<?php admin_foot(); ?>
