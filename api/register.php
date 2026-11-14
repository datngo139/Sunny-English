<?php
/**
 * Nhận đăng ký học thử. Trả JSON cho trình duyệt hiện đại (fetch), hoặc chuyển hướng nếu form được gửi không qua JS.
 */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

$wantsJson = stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

// Form đăng ký học thử đã được gỡ khỏi website (phụ huynh đặt lịch qua Zalo). Cổng này bị tắt để không nhận dữ liệu cá nhân.
// Muốn bật lại: thêm 'registration' => true vào app/config.php và khôi phục form trên trang chủ.
if (!config('registration', false)) {
    http_response_code(410);
    if ($wantsJson) {
        json_response(['ok' => false, 'message' => 'Sunny không còn nhận đăng ký qua form. Ba mẹ nhắn Zalo cho Sunny nhé.'], 410);
    }
    header('Content-Type: text/plain; charset=utf-8');
    exit('Sunny không còn nhận đăng ký qua form. Ba mẹ nhắn Zalo cho Sunny nhé.');
}

function reg_fail(bool $json, int $code, string $message, array $errors = []): never
{
    if ($json) {
        json_response(['ok' => false, 'message' => $message, 'errors' => $errors], $code);
    }
    redirect('/?dang-ky=loi#dang-ky');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    reg_fail($wantsJson, 405, 'Phương thức không hợp lệ.');
}

// Chỉ nhận form gửi từ chính website này.
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$host = preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? ''));
if ($origin !== '' && parse_url($origin, PHP_URL_HOST) !== $host) {
    reg_fail($wantsJson, 403, 'Yêu cầu không hợp lệ.');
}

// Bẫy bot: ô ẩn bị điền, hoặc gửi nhanh bất thường (< 3 giây) → giả vờ thành công, không lưu.
$honeypot = trim((string) ($_POST['website'] ?? ''));
$elapsed = time() - (int) ($_POST['ts'] ?? 0);
if ($honeypot !== '' || $elapsed < 3) {
    if ($wantsJson) {
        json_response(['ok' => true]);
    }
    redirect('/?dang-ky=ok#dang-ky');
}

$str = fn(string $k, int $max): string => mb_substr(trim(preg_replace('/\s+/u', ' ', (string) ($_POST[$k] ?? ''))), 0, $max);
$parent = $str('parent_name', 80);
$phoneRaw = $str('phone', 20);
$child = $str('child_name', 60);
$ageRaw = $str('child_age', 3);
$course = $str('course', 30);
$visitDate = $str('visit_date', 10);
$visitTime = $str('visit_time', 5);

$errors = [];
if (mb_strlen($parent) < 2) {
    $errors['parent_name'] = 'Vui lòng nhập họ tên ba mẹ';
}
$phone = normalize_vn_phone($phoneRaw);
if ($phone === null) {
    $errors['phone'] = 'Số điện thoại gồm 10 chữ số, bắt đầu bằng 0';
}
$age = null;
if ($ageRaw !== '') {
    if (!ctype_digit($ageRaw) || (int) $ageRaw < 2 || (int) $ageRaw > 18) {
        $errors['child_age'] = 'Tuổi từ 2 đến 18';
    } else {
        $age = (int) $ageRaw;
    }
}
if (empty($_POST['consent'])) {
    $errors['consent'] = 'Vui lòng đồng ý để Sunny liên hệ tư vấn';
}
// Chỉ nhận giá trị nằm trong danh sách có sẵn.
$courseIds = array_column(sunny_courses(), 'id');
if ($course !== '' && !in_array($course, $courseIds, true)) {
    $course = '';
}
// Ngày giờ học thử: hoặc để trống cả hai, hoặc phải là một suất còn trống theo lịch.
if ($visitDate === '' && $visitTime === '') {
    $visitDate = $visitTime = '';
} elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $visitDate) || !preg_match('/^\d{2}:\d{2}$/', $visitTime)) {
    $errors['visit_time'] = 'Ngày giờ học thử không hợp lệ';
}
if ($errors) {
    reg_fail($wantsJson, 422, 'Ba mẹ kiểm tra lại thông tin giúp Sunny nhé.', $errors);
}

$pdo = db();
$ip = client_ip_hash();

// Giới hạn: tối đa 5 đăng ký / giờ / người, tránh spam.
$st = $pdo->prepare('SELECT COUNT(*) FROM registrations WHERE ip_hash = ? AND created_at > ?');
$st->execute([$ip, date('Y-m-d H:i:s', time() - 3600)]);
if ((int) $st->fetchColumn() >= 5) {
    reg_fail($wantsJson, 429, 'Ba mẹ gửi hơi nhiều lần rồi. Vui lòng thử lại sau hoặc gọi trực tiếp cho Sunny.');
}

// Cùng số điện thoại gửi lại trong 10 phút: coi như đã nhận, không tạo bản ghi trùng.
$st = $pdo->prepare('SELECT COUNT(*) FROM registrations WHERE phone = ? AND created_at > ?');
$st->execute([$phone, date('Y-m-d H:i:s', time() - 600)]);
$duplicate = (int) $st->fetchColumn() > 0;

if (!$duplicate) {
    // Khoá ghi (IMMEDIATE) để hai phụ huynh không cùng giành một suất ở cùng thời điểm.
    $pdo->exec('BEGIN IMMEDIATE');
    try {
        if ($visitDate !== '' && !schedule_slot_ok($visitDate, $visitTime)) {
            $pdo->exec('ROLLBACK');
            if ($wantsJson) {
                json_response(['ok' => false, 'refresh' => true, 'schedule' => schedule_available(),
                    'message' => 'Giờ này vừa có bạn khác chọn hoặc đã hết chỗ. Ba mẹ chọn giờ khác giúp Sunny nhé.',
                    'errors' => ['visit_time' => 'Hãy chọn giờ khác']], 409);
            }
            redirect('/?dang-ky=loi#dang-ky');
        }
        $pdo->prepare('INSERT INTO registrations (parent_name, phone, child_name, child_age, course, visit_date, visit_time, ip_hash, created_at)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$parent, $phone, $child, $age, $course, $visitDate, $visitTime, $ip, date('Y-m-d H:i:s')]);
        $pdo->exec('COMMIT');
    } catch (Throwable $e) {
        try {
            $pdo->exec('ROLLBACK');
        } catch (Throwable $ignored) {
        }
        throw $e;
    }

    // Báo cho trung tâm qua email (Gmail SMTP nếu đã cấu hình). Lỗi gửi thư không làm hỏng đăng ký.
    $to = notify_recipients();
    if ($to) {
        $row = ['parent_name' => $parent, 'phone' => $phone, 'child_name' => $child, 'child_age' => $age, 'course' => $course,
            'visit_date' => $visitDate, 'visit_time' => $visitTime];
        send_mail($to, 'Đăng ký học thử mới: ' . $parent . ($visitDate !== '' ? ' (' . schedule_label($visitDate, $visitTime) . ')' : ''),
            registration_mail_body($row));
    }
}

if ($wantsJson) {
    json_response(['ok' => true]);
}
redirect('/?dang-ky=ok#dang-ky');
