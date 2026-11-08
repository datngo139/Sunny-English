<?php
/** Lịch học thử: ngày giờ nhận học thử, độ dài mỗi suất, số bé mỗi suất, ngày nghỉ. */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
require SUNNY_APP . '/admin_layout.php';

$admin = require_admin();
$cfg = schedule_get();
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $new = $cfg;
    foreach (SCHEDULE_WEEKDAY_FULL as $d => $_) {
        $on = !empty($_POST['day_on'][$d]);
        $open = schedule_clean_time($_POST['day_open'][$d] ?? '', $cfg['days'][$d]['open']);
        $close = schedule_clean_time($_POST['day_close'][$d] ?? '', $cfg['days'][$d]['close']);
        if ($on && schedule_to_minutes($close) <= schedule_to_minutes($open)) {
            $error = SCHEDULE_WEEKDAY_FULL[$d] . ': giờ đóng cửa phải sau giờ mở cửa.';
        }
        $new['days'][$d] = ['on' => $on, 'open' => $open, 'close' => $close];
    }
    $slot = (int) ($_POST['slot_minutes'] ?? 30);
    $new['slot_minutes'] = in_array($slot, [20, 30, 45, 60], true) ? $slot : 30;
    $new['capacity'] = max(1, min(10, (int) ($_POST['capacity'] ?? 1)));
    $new['advance_days'] = max(1, min(60, (int) ($_POST['advance_days'] ?? 14)));
    $new['min_notice_hours'] = max(0, min(72, (int) ($_POST['min_notice_hours'] ?? 2)));
    $blocked = [];
    foreach (preg_split('/[\s,;]+/', (string) ($_POST['blocked'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) as $d) {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false) {
            $blocked[$d] = true;
        }
    }
    $blocked = array_keys($blocked);
    sort($blocked);
    $new['blocked'] = array_slice($blocked, 0, 120);
    $cfg = $new;
    if (!$error) {
        schedule_save($cfg);
        flash_set('ok', 'Đã lưu lịch học thử.');
        redirect('/admin/lich.php');
    }
}

$today = date('Y-m-d');
$cfg['blocked'] = array_values(array_filter($cfg['blocked'], fn($d) => $d >= $today)); // chỉ hiện ngày nghỉ chưa qua
$preview = array_slice(schedule_available($cfg), 0, 7);

admin_head('Lịch học thử', 'schedule', $admin);
?>
<div class="adm-title">
  <div>
    <h1>Lịch học thử</h1>
    <p class="muted">Đặt ngày giờ trung tâm nhận học thử. Phụ huynh chỉ thấy các suất còn trống trong form đăng ký.</p>
  </div>
</div>
<?php if ($error): ?><div class="adm-flash adm-flash-err" role="alert"><?= e($error) ?></div><?php endif; ?>

<form method="post" class="sched-admin">
  <?= csrf_field() ?>
  <div class="card side-card" style="padding:24px">
    <h2>Các ngày trong tuần nhận học thử</h2>
    <div class="week">
      <?php foreach ([1, 2, 3, 4, 5, 6, 0] as $d): $day = $cfg['days'][$d]; ?>
        <div class="week-row">
          <label class="choice"><input type="checkbox" name="day_on[<?= $d ?>]" value="1"<?= $day['on'] ? ' checked' : '' ?>><span class="box"></span><span class="text"><?= e(SCHEDULE_WEEKDAY_FULL[$d]) ?></span></label>
          <span class="control sm"><input type="time" name="day_open[<?= $d ?>]" value="<?= e($day['open']) ?>" aria-label="Giờ mở <?= e(SCHEDULE_WEEKDAY_FULL[$d]) ?>"></span>
          <span class="to">đến</span>
          <span class="control sm"><input type="time" name="day_close[<?= $d ?>]" value="<?= e($day['close']) ?>" aria-label="Giờ đóng <?= e(SCHEDULE_WEEKDAY_FULL[$d]) ?>"></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card side-card" style="padding:24px">
    <h2>Suất học thử</h2>
    <div class="sched-grid">
      <div class="field"><label class="label" for="slot_minutes">Mỗi suất dài</label>
        <span class="control has-select sm"><select id="slot_minutes" name="slot_minutes"><?php foreach ([20, 30, 45, 60] as $m): ?><option value="<?= $m ?>"<?= (int) $cfg['slot_minutes'] === $m ? ' selected' : '' ?>><?= $m ?> phút</option><?php endforeach; ?></select></span></div>
      <div class="field"><label class="label" for="capacity">Số bé tối đa mỗi suất</label>
        <span class="control sm"><input id="capacity" name="capacity" type="number" min="1" max="10" value="<?= (int) $cfg['capacity'] ?>"></span>
        <span class="hint">Học thử 1 kèm 1 thì để 1.</span></div>
      <div class="field"><label class="label" for="advance_days">Cho đặt trước tối đa</label>
        <span class="control sm"><input id="advance_days" name="advance_days" type="number" min="1" max="60" value="<?= (int) $cfg['advance_days'] ?>"></span>
        <span class="hint">ngày kể từ hôm nay</span></div>
      <div class="field"><label class="label" for="min_notice_hours">Phải đặt trước ít nhất</label>
        <span class="control sm"><input id="min_notice_hours" name="min_notice_hours" type="number" min="0" max="72" value="<?= (int) $cfg['min_notice_hours'] ?>"></span>
        <span class="hint">giờ (để Sunny kịp gọi xác nhận)</span></div>
    </div>
  </div>

  <div class="card side-card" style="padding:24px">
    <h2>Ngày nghỉ <span class="muted" style="font-weight:500">(không nhận học thử)</span></h2>
    <div class="field">
      <div class="blocked-add">
        <span class="control sm"><input type="date" id="blocked-date" min="<?= e($today) ?>" aria-label="Chọn ngày nghỉ"></span>
        <button class="btn btn-outline btn-sm" type="button" id="blocked-add"><?= icon('plus') ?>Thêm ngày nghỉ</button>
      </div>
      <div class="chips-removable" id="blocked-list" aria-live="polite"></div>
      <input type="hidden" name="blocked" id="blocked-input" value="<?= e(implode(' ', $cfg['blocked'])) ?>">
      <span class="hint">Ví dụ nghỉ lễ, Tết, hoặc ngày trung tâm bận.</span>
    </div>
  </div>

  <button class="btn btn-lg" type="submit" style="align-self:flex-start"><?= icon('save') ?>Lưu lịch học thử</button>
</form>

<div class="card side-card" style="padding:24px;margin-top:28px">
  <h2>Phụ huynh đang thấy những ngày nào? <span class="muted" style="font-weight:500">(7 ngày gần nhất còn suất)</span></h2>
  <?php if (!$preview): ?>
    <p class="muted">Hiện không còn suất nào. Phụ huynh sẽ thấy dòng "Sunny sẽ gọi để hẹn ngày giờ". Hãy bật ít nhất một ngày trong tuần ở trên.</p>
  <?php else: ?>
    <div class="preview-days">
      <?php foreach ($preview as $d): ?>
        <div class="pd"><b><?= e($d['full']) ?></b><span><?= e(implode(' · ', array_map(fn($s) => ltrim($s['t'], '0'), array_slice($d['slots'], 0, 6)))) ?><?= count($d['slots']) > 6 ? ' …' : '' ?></span><small><?= count($d['slots']) ?> suất</small></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php admin_foot(); ?>
