<?php
/** Thông báo đăng ký mới qua email: địa chỉ nhận, trạng thái Gmail gửi, gửi thử. */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require SUNNY_APP . '/admin_layout.php';

$admin = require_admin();
$result = null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $act = (string) ($_POST['action'] ?? 'save');
    if ($act === 'save') {
        $valid = [];
        $bad = [];
        foreach (preg_split('/[\s,;]+/', (string) ($_POST['emails'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) as $e) {
            $e = strtolower(trim($e));
            if (filter_var($e, FILTER_VALIDATE_EMAIL)) {
                $valid[$e] = true;
            } else {
                $bad[] = $e;
            }
        }
        if (count($valid) > 10) {
            flash_set('err', 'Tối đa 10 địa chỉ email nhận thông báo.');
        } elseif ($bad) {
            flash_set('err', 'Địa chỉ không hợp lệ: ' . implode(', ', array_slice($bad, 0, 3)) . '. Chưa lưu.');
        } else {
            setting_set('notify_emails', implode("\n", array_keys($valid)));
            flash_set('ok', $valid ? 'Đã lưu ' . count($valid) . ' địa chỉ nhận thông báo.' : 'Đã xoá danh sách (sẽ không gửi email).');
        }
        redirect('/admin/thong-bao.php');
    }
    if ($act === 'test') {
        $to = notify_recipients();
        $err = null;
        $ok = send_mail($to, 'Sunny Website: email thử',
            "Đây là email thử từ trang quản trị website Sunny.\n\nNếu bạn đọc được thư này, thông báo đăng ký học thử đã hoạt động.\nGửi lúc: " . date('d/m/Y H:i') . "\n",
            null, $err);
        $result = ['ok' => $ok, 'to' => $to, 'error' => $err];
    }
}

$recipients = notify_recipients();
$smtp = config('smtp', []);
$smtpOn = smtp_configured();
admin_head('Thông báo', 'notify', $admin);
?>
<div class="adm-title">
  <div>
    <h1>Thông báo đăng ký mới</h1>
    <p class="muted">Mỗi khi có phụ huynh đăng ký học thử, website gửi email báo cho các địa chỉ dưới đây.</p>
  </div>
</div>

<?php if ($result): ?>
  <?php if ($result['ok']): ?>
    <div class="adm-flash" role="status">Đã gửi email thử tới <?= e(implode(', ', $result['to'])) ?>. Hãy kiểm tra hộp thư (cả mục Spam). Nếu thấy thư, mọi thứ đã sẵn sàng.</div>
  <?php else: ?>
    <div class="adm-flash adm-flash-err" role="alert"><b>Gửi thử không thành công.</b><br><?= e((string) $result['error']) ?></div>
  <?php endif; ?>
<?php endif; ?>

<div class="notify-grid">
  <form method="post" class="card side-card" style="padding:24px">
    <?= csrf_field() ?><input type="hidden" name="action" value="save">
    <h2>Gmail nhận thông báo</h2>
    <div class="field">
      <label class="label" for="emails">Địa chỉ email nhận <span class="opt">(mỗi dòng một địa chỉ, tối đa 10)</span></label>
      <span class="control is-textarea"><textarea id="emails" name="emails" rows="4" style="min-height:110px" placeholder="ban@gmail.com&#10;nhanvien@gmail.com"><?= e(implode("\n", $recipients)) ?></textarea></span>
      <span class="hint">Thư báo gồm tên phụ huynh, số điện thoại, tên con, khoá học và ngày giờ chọn.</span>
    </div>
    <button class="btn" type="submit"><?= icon('save') ?>Lưu danh sách</button>
  </form>

  <div class="card side-card" style="padding:24px">
    <h2>Gmail dùng để gửi</h2>
    <?php if ($smtpOn): ?>
      <p><span class="badge badge-green">Đã cấu hình</span></p>
      <p class="muted small">Gửi từ <b><?= e((string) $smtp['user']) ?></b> qua <?= e((string) $smtp['host']) ?>:<?= (int) ($smtp['port'] ?? 587) ?>. Mật khẩu ứng dụng được lưu trong <code>app/config.php</code> trên hosting và không hiển thị ở đây.</p>
    <?php else: ?>
      <p><span class="badge badge-orange">Chưa cấu hình</span></p>
      <p class="muted small">Website sẽ thử gửi bằng hàm mail() mặc định của hosting (hay vào Spam, một số hosting không hỗ trợ). Để gửi chắc chắn, hãy điền mục <code>smtp</code> trong <code>app/config.php</code> theo hướng dẫn trong <code>HUONG-DAN-DEPLOY.md</code>.</p>
    <?php endif; ?>
    <form method="post" style="margin-top:14px">
      <?= csrf_field() ?><input type="hidden" name="action" value="test">
      <button class="btn btn-outline" type="submit"<?= $recipients ? '' : ' disabled' ?>><?= icon('send') ?>Gửi email thử</button>
      <?php if (!$recipients): ?><p class="hint" style="margin-top:8px">Hãy lưu ít nhất một địa chỉ nhận trước.</p><?php endif; ?>
    </form>
  </div>
</div>
<?php admin_foot(); ?>
