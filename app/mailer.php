<?php
/**
 * Gửi email thông báo. Ưu tiên SMTP (Gmail) nếu đã cấu hình trong config.php → 'smtp'.
 * Nếu chưa cấu hình SMTP thì dùng hàm mail() mặc định của PHP.
 * Lỗi gửi thư không bao giờ làm hỏng đăng ký: chỉ ghi vào data/mail-error.log.
 */
declare(strict_types=1);

/** Danh sách email nhận thông báo: ưu tiên danh sách trong admin, nếu trống thì lấy notify_email trong config. */
function notify_recipients(): array
{
    $raw = (string) setting_get('notify_emails', '');
    if (trim($raw) === '') {
        $raw = (string) config('notify_email', '');
    }
    $list = [];
    foreach (preg_split('/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) as $e) {
        $e = strtolower(trim($e));
        if (filter_var($e, FILTER_VALIDATE_EMAIL) && !in_array($e, $list, true)) {
            $list[] = $e;
        }
    }
    return array_slice($list, 0, 10);
}

function smtp_configured(): bool
{
    $s = config('smtp', []);
    return is_array($s) && !empty($s['host']) && !empty($s['user']) && !empty($s['pass']);
}

function mail_log(string $msg): void
{
    @file_put_contents(SUNNY_ROOT . '/data/mail-error.log', '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n", FILE_APPEND | LOCK_EX);
}

function mail_encode_header(string $s): string
{
    return preg_match('/[^\x20-\x7E]/', $s) ? '=?UTF-8?B?' . base64_encode($s) . '?=' : $s;
}

/**
 * Gửi thư văn bản. Trả true nếu máy chủ thư đã nhận; ngược lại false và điền $error.
 * @param string[] $to
 */
function send_mail(array $to, string $subject, string $body, ?string $replyTo = null, ?string &$error = null): bool
{
    $error = null;
    $to = array_values(array_filter($to, fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL)));
    if (!$to) {
        $error = 'Chưa có địa chỉ email nhận thông báo.';
        return false;
    }
    try {
        if (smtp_configured()) {
            $ok = smtp_send($to, $subject, $body, $replyTo, $error);
        } else {
            $headers = ['MIME-Version: 1.0', 'Content-Type: text/plain; charset=UTF-8'];
            $from = (string) config('from_email', '');
            if ($from !== '' && filter_var($from, FILTER_VALIDATE_EMAIL)) {
                $headers[] = 'From: Sunny Website <' . $from . '>';
            }
            if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                $headers[] = 'Reply-To: ' . $replyTo;
            }
            $ok = @mail(implode(',', $to), mail_encode_header($subject), $body, implode("\r\n", $headers));
            if (!$ok) {
                $error = 'Hàm mail() của hosting không gửi được. Hãy cấu hình Gmail SMTP trong app/config.php.';
            }
        }
    } catch (Throwable $e) {
        $ok = false;
        $error = $e->getMessage();
    }
    if (!$ok) {
        mail_log('Gửi tới ' . implode(',', $to) . ' thất bại: ' . $error);
    }
    return $ok;
}

/** Đọc một phản hồi SMTP (có thể nhiều dòng). Trả [mã, nội dung]. */
function smtp_read($fp): array
{
    $text = '';
    $code = 0;
    while (($line = fgets($fp, 1024)) !== false) {
        $text .= $line;
        $code = (int) substr($line, 0, 3);
        if (strlen($line) < 4 || $line[3] !== '-') {
            break;
        }
    }
    if ($text === '') {
        throw new RuntimeException('Máy chủ thư không phản hồi (hết thời gian chờ).');
    }
    return [$code, trim($text)];
}

function smtp_cmd($fp, string $cmd, array $okCodes): string
{
    fwrite($fp, $cmd . "\r\n");
    [$code, $text] = smtp_read($fp);
    if (!in_array($code, $okCodes, true)) {
        // Không đưa lệnh (có thể chứa mật khẩu mã hoá) vào thông báo lỗi.
        throw new RuntimeException('Máy chủ thư từ chối (' . $code . '): ' . $text);
    }
    return $text;
}

function smtp_send(array $to, string $subject, string $body, ?string $replyTo, ?string &$error): bool
{
    $c = config('smtp', []);
    $host = (string) $c['host'];
    $port = (int) ($c['port'] ?? 587);
    $secure = strtolower((string) ($c['secure'] ?? ($port === 465 ? 'ssl' : 'tls'))); // ssl | tls | none
    $user = (string) $c['user'];
    $pass = (string) $c['pass'];
    $fromName = (string) ($c['from_name'] ?? 'Sunny Website');

    $ctx = stream_context_create(['ssl' => [
        'verify_peer' => ($c['verify_peer'] ?? true) !== false,
        'verify_peer_name' => ($c['verify_peer'] ?? true) !== false,
        'allow_self_signed' => ($c['verify_peer'] ?? true) === false,
    ]]);
    $errno = 0;
    $errstr = '';
    $fp = @stream_socket_client(($secure === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
    if (!$fp) {
        $error = "Không kết nối được tới máy chủ thư $host:$port ($errstr). Hosting có thể đang chặn cổng gửi thư.";
        return false;
    }
    stream_set_timeout($fp, 20);

    try {
        [$code, $text] = smtp_read($fp);
        if ($code !== 220) {
            throw new RuntimeException("Máy chủ thư chào lạ ($code): $text");
        }
        $ehlo = preg_replace('/[^A-Za-z0-9.\-]/', '', (string) ($_SERVER['SERVER_NAME'] ?? 'localhost')) ?: 'localhost';
        smtp_cmd($fp, "EHLO $ehlo", [250]);
        if ($secure === 'tls') {
            smtp_cmd($fp, 'STARTTLS', [220]);
            if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('Không thiết lập được kết nối mã hoá (TLS) với máy chủ thư.');
            }
            smtp_cmd($fp, "EHLO $ehlo", [250]);
        }
        // Đăng nhập. Lỗi 534/535 từ Gmail thường do dùng mật khẩu thường thay vì "mật khẩu ứng dụng".
        fwrite($fp, "AUTH LOGIN\r\n");
        [$code] = smtp_read($fp);
        if ($code !== 334) {
            throw new RuntimeException("Máy chủ thư không hỗ trợ đăng nhập AUTH LOGIN ($code).");
        }
        fwrite($fp, base64_encode($user) . "\r\n");
        [$code] = smtp_read($fp);
        if ($code !== 334) {
            throw new RuntimeException("Tên đăng nhập bị từ chối ($code).");
        }
        fwrite($fp, base64_encode($pass) . "\r\n");
        [$code, $text] = smtp_read($fp);
        if ($code !== 235) {
            throw new RuntimeException('Đăng nhập Gmail thất bại. Hãy kiểm tra địa chỉ Gmail và dùng "mật khẩu ứng dụng" 16 ký tự (không phải mật khẩu Gmail thường). Chi tiết: ' . $code);
        }

        smtp_cmd($fp, 'MAIL FROM:<' . $user . '>', [250]);
        foreach ($to as $rcpt) {
            smtp_cmd($fp, 'RCPT TO:<' . $rcpt . '>', [250, 251]);
        }
        smtp_cmd($fp, 'DATA', [354]);

        $domain = substr(strrchr($user, '@') ?: '@localhost', 1);
        $headers = [
            'Date: ' . date('r'),
            'From: ' . mail_encode_header($fromName) . ' <' . $user . '>',
            'To: ' . implode(', ', $to),
            'Subject: ' . mail_encode_header($subject),
            'Message-ID: <' . bin2hex(random_bytes(10)) . '@' . $domain . '>',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
        ];
        if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: ' . $replyTo;
        }
        $msg = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($body), 76, "\r\n");
        // Base64 chỉ gồm A-Z a-z 0-9 + / = nên không có dòng nào bắt đầu bằng "." cần nhân đôi.
        fwrite($fp, $msg . "\r\n.\r\n");
        [$code, $text] = smtp_read($fp);
        if ($code !== 250) {
            throw new RuntimeException("Máy chủ thư không nhận thư ($code): $text");
        }
        @fwrite($fp, "QUIT\r\n");
        return true;
    } catch (Throwable $e) {
        $error = $e->getMessage();
        return false;
    } finally {
        @fclose($fp);
    }
}

/** Nội dung email báo đăng ký mới. */
function registration_mail_body(array $r): string
{
    $courseTitle = '';
    foreach (sunny_courses(true) as $co) {
        if ($co['id'] === $r['course']) {
            $courseTitle = $co['title'];
        }
    }
    $when = $r['visit_date'] !== '' ? schedule_label($r['visit_date'], $r['visit_time']) . ' (phụ huynh đề xuất, cần gọi xác nhận)' : '(chưa chọn, cần gọi hẹn giờ)';
    return "Có phụ huynh vừa đăng ký học thử trên website:\n\n"
        . "Phụ huynh : {$r['parent_name']}\n"
        . "Điện thoại: {$r['phone']}\n"
        . 'Tên con   : ' . ($r['child_name'] !== '' ? $r['child_name'] : '(chưa nhập)') . "\n"
        . 'Tuổi      : ' . ($r['child_age'] ?: '(chưa nhập)') . "\n"
        . 'Khoá học  : ' . ($courseTitle !== '' ? $courseTitle : '(chưa chọn)') . "\n"
        . "Lịch học thử: $when\n\n"
        . 'Gọi lại trong 24 giờ như đã hứa trên website.' . "\n"
        . 'Xem tất cả đăng ký: ' . abs_url('/admin/dang-ky.php') . "\n";
}
