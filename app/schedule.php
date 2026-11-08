<?php
/**
 * Lịch học thử: ngày/giờ trung tâm nhận học thử, số bé tối đa mỗi suất, ngày nghỉ.
 * Cấu hình lưu trong CSDL (bảng settings, khoá "schedule"), admin chỉnh ở trang Lịch học thử.
 */
declare(strict_types=1);

const SCHEDULE_WEEKDAY_SHORT = [0 => 'CN', 1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7'];
const SCHEDULE_WEEKDAY_FULL = [0 => 'Chủ nhật', 1 => 'Thứ 2', 2 => 'Thứ 3', 3 => 'Thứ 4', 4 => 'Thứ 5', 5 => 'Thứ 6', 6 => 'Thứ 7'];

function schedule_defaults(): array
{
    $days = [];
    for ($d = 0; $d <= 6; $d++) {
        $days[$d] = ['on' => $d >= 1 && $d <= 5, 'open' => '17:00', 'close' => '20:00'];
    }
    $days[6] = ['on' => true, 'open' => '08:00', 'close' => '11:00'];
    $days[0] = ['on' => false, 'open' => '08:00', 'close' => '11:00'];
    return ['days' => $days, 'slot_minutes' => 30, 'capacity' => 1, 'advance_days' => 14, 'min_notice_hours' => 2, 'blocked' => []];
}

function schedule_get(): array
{
    $cfg = schedule_defaults();
    $raw = setting_get('schedule');
    if ($raw) {
        $saved = json_decode($raw, true);
        if (is_array($saved)) {
            foreach (['slot_minutes', 'capacity', 'advance_days', 'min_notice_hours'] as $k) {
                if (isset($saved[$k])) {
                    $cfg[$k] = (int) $saved[$k];
                }
            }
            if (isset($saved['blocked']) && is_array($saved['blocked'])) {
                $cfg['blocked'] = array_values(array_filter($saved['blocked'], fn($d) => is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)));
            }
            if (isset($saved['days']) && is_array($saved['days'])) {
                foreach ($saved['days'] as $d => $v) {
                    if (isset($cfg['days'][(int) $d]) && is_array($v)) {
                        $cfg['days'][(int) $d] = [
                            'on' => !empty($v['on']),
                            'open' => schedule_clean_time($v['open'] ?? '', $cfg['days'][(int) $d]['open']),
                            'close' => schedule_clean_time($v['close'] ?? '', $cfg['days'][(int) $d]['close']),
                        ];
                    }
                }
            }
        }
    }
    return $cfg;
}

function schedule_clean_time($v, string $fallback): string
{
    return is_string($v) && preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $v) ? $v : $fallback;
}

function schedule_save(array $cfg): void
{
    setting_set('schedule', json_encode($cfg, JSON_UNESCAPED_UNICODE));
}

function schedule_to_minutes(string $hhmm): int
{
    [$h, $m] = array_map('intval', explode(':', $hhmm));
    return $h * 60 + $m;
}

/** Các giờ bắt đầu trong một ngày, ví dụ ['17:00','17:30',...]. */
function schedule_day_times(array $day, int $slotMinutes): array
{
    $out = [];
    if (empty($day['on']) || $slotMinutes < 10) {
        return $out;
    }
    $end = schedule_to_minutes($day['close']);
    for ($t = schedule_to_minutes($day['open']); $t + $slotMinutes <= $end; $t += $slotMinutes) {
        $out[] = sprintf('%02d:%02d', intdiv($t, 60), $t % 60);
    }
    return $out;
}

/**
 * Các ngày sắp tới còn suất trống.
 * @return array<int,array{date:string,wd:string,dm:string,full:string,slots:array<int,array{t:string,left:int}>}>
 */
function schedule_available(?array $cfg = null, ?DateTimeImmutable $now = null): array
{
    $cfg = $cfg ?? schedule_get();
    $now = $now ?? new DateTimeImmutable('now');
    $earliest = $now->modify('+' . max(0, (int) $cfg['min_notice_hours']) . ' hours');
    $first = $now->setTime(0, 0);
    $last = $first->modify('+' . max(1, (int) $cfg['advance_days']) . ' days');

    // Số đăng ký đã có trong từng suất (trừ đăng ký đã chuyển "Không học" để nhả suất).
    $taken = [];
    $st = db()->prepare("SELECT visit_date, visit_time, COUNT(*) c FROM registrations
        WHERE visit_date >= ? AND visit_date <= ? AND visit_time <> '' AND status <> 'closed' GROUP BY visit_date, visit_time");
    $st->execute([$first->format('Y-m-d'), $last->format('Y-m-d')]);
    foreach ($st as $r) {
        $taken[$r['visit_date'] . ' ' . $r['visit_time']] = (int) $r['c'];
    }

    $days = [];
    for ($d = $first; $d <= $last; $d = $d->modify('+1 day')) {
        $date = $d->format('Y-m-d');
        if (in_array($date, $cfg['blocked'], true)) {
            continue;
        }
        $w = (int) $d->format('w');
        $slots = [];
        foreach (schedule_day_times($cfg['days'][$w], (int) $cfg['slot_minutes']) as $t) {
            if (new DateTimeImmutable("$date $t") < $earliest) {
                continue;
            }
            $left = (int) $cfg['capacity'] - ($taken["$date $t"] ?? 0);
            if ($left > 0) {
                $slots[] = ['t' => $t, 'left' => $left];
            }
        }
        if ($slots) {
            $days[] = ['date' => $date, 'wd' => SCHEDULE_WEEKDAY_SHORT[$w], 'dm' => $d->format('d/m'),
                'full' => SCHEDULE_WEEKDAY_FULL[$w] . ', ' . $d->format('d/m'), 'slots' => $slots];
        }
    }
    return $days;
}

/** Ngày + giờ có còn hợp lệ và còn chỗ không (kiểm tra lại ở máy chủ khi gửi form). */
function schedule_slot_ok(string $date, string $time): bool
{
    foreach (schedule_available() as $day) {
        if ($day['date'] === $date) {
            foreach ($day['slots'] as $s) {
                if ($s['t'] === $time) {
                    return true;
                }
            }
        }
    }
    return false;
}

/** "Thứ 7, 14/11 lúc 9:00" từ ngày/giờ đã lưu. */
function schedule_label(string $date, string $time): string
{
    $t = DateTimeImmutable::createFromFormat('Y-m-d', $date);
    if (!$t) {
        return '';
    }
    $w = (int) $t->format('w');
    return SCHEDULE_WEEKDAY_FULL[$w] . ', ' . $t->format('d/m/Y') . ($time !== '' ? ' lúc ' . ltrim($time, '0') : '');
}
