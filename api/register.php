<?php
/**
 * Nhận đăng ký học thử. Trả JSON cho trình duyệt hiện đại (fetch), hoặc chuyển hướng nếu form được gửi không qua JS.
 */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

$wantsJson = stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

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
$slot = $str('slot', 30);

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
if (!array_key_exists($slot, sunny_slots())) {
    $slot = '';
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
    $pdo->prepare('INSERT INTO registrations (parent_name, phone, child_name, child_age, course, slot, ip_hash, created_at)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([$parent, $phone, $child, $age, $course, $slot, $ip, date('Y-m-d H:i:s')]);

    // Gửi email báo cho trung tâm (nếu đã cấu hình). Lỗi gửi mail không làm hỏng đăng ký.
    $to = (string) config('notify_email', '');
    if ($to !== '' && filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $courseTitle = '';
        foreach (sunny_courses() as $co) {
            if ($co['id'] === $course) {
                $courseTitle = $co['title'];
            }
        }
        $body = "Có phụ huynh vừa đăng ký học thử trên website:\n\n"
            . "Phụ huynh: $parent\nĐiện thoại: $phone\n"
            . 'Tên con: ' . ($child ?: '(chưa nhập)') . "\n"
            . 'Tuổi: ' . ($age ?? '(chưa nhập)') . "\n"
            . 'Khoá học: ' . ($courseTitle ?: '(chưa chọn)') . "\n"
            . 'Khung giờ: ' . (sunny_slots()[$slot] ?? '(chưa chọn)') . "\n\n"
            . 'Xem tất cả: ' . abs_url('/admin/dang-ky.php') . "\n";
        $from = (string) config('from_email', '');
        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        if ($from !== '' && filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'From: Sunny Website <' . $from . '>';
        }
        @mail($to, '=?UTF-8?B?' . base64_encode('Đăng ký học thử mới: ' . $parent) . '?=', $body, implode("\r\n", $headers));
    }
}

if ($wantsJson) {
    json_response(['ok' => true]);
}
redirect('/?dang-ky=ok#dang-ky');
