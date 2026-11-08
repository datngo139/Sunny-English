<?php
declare(strict_types=1);

/** Escape HTML. */
function e($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** URL nội bộ (có tính base_path). */
function url(string $path = ''): string
{
    $base = rtrim((string) config('base_path', ''), '/');
    return $base . '/' . ltrim($path, '/');
}

/** URL tuyệt đối dùng cho thẻ OG / canonical. */
function abs_url(string $path = ''): string
{
    $site = rtrim((string) config('site_url', ''), '/');
    if ($site === '') {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $site = ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }
    return $site . url($path);
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function redirect(string $path, int $code = 302): never
{
    header('Location: ' . (preg_match('#^https?://#', $path) ? $path : url($path)), true, $code);
    exit;
}

/** Địa chỉ IP người dùng (băm lại, không lưu IP thô). */
function client_ip_hash(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return substr(hash('sha256', $ip . '|' . app_secret()), 0, 32);
}

/** Chuyển tiếng Việt có dấu thành slug không dấu: "Lớp học thử" => "lop-hoc-thu". */
function slugify(string $text): string
{
    $text = mb_strtolower(trim($text), 'UTF-8');
    $map = [
        'a' => 'àáạảãâầấậẩẫăằắặẳẵ', 'e' => 'èéẹẻẽêềếệểễ', 'i' => 'ìíịỉĩ',
        'o' => 'òóọỏõôồốộổỗơờớợởỡ', 'u' => 'ùúụủũưừứựửữ', 'y' => 'ỳýỵỷỹ', 'd' => 'đ',
    ];
    foreach ($map as $to => $chars) {
        $text = preg_replace('/[' . $chars . ']/u', $to, $text);
    }
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim((string) $text, '-');
    return mb_substr($text, 0, 80) ?: 'bai-viet';
}

/** Ngày giờ kiểu Việt Nam. */
function vn_date(?string $ts, bool $withTime = false): string
{
    if (!$ts) {
        return '';
    }
    $t = strtotime($ts);
    return $t ? date($withTime ? 'd/m/Y H:i' : 'd/m/Y', $t) : '';
}

/** Cắt chuỗi gọn gàng theo số ký tự, không cắt giữa từ. */
function excerpt_from_html(string $html, int $max = 160): string
{
    $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '</li>', '</h2>', '</h3>'], ' ', $html)), ENT_QUOTES, 'UTF-8')));
    if (mb_strlen($text) <= $max) {
        return $text;
    }
    $cut = mb_substr($text, 0, $max);
    $sp = mb_strrpos($cut, ' ');
    return rtrim($sp !== false && $sp > $max * 0.6 ? mb_substr($cut, 0, $sp) : $cut, " ,.;:-") . '…';
}

function json_response(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Chuẩn hoá số điện thoại Việt Nam; trả null nếu không hợp lệ. */
function normalize_vn_phone(string $raw): ?string
{
    $p = preg_replace('/[\s.\-()]+/', '', $raw);
    if (str_starts_with($p, '+84')) {
        $p = '0' . substr($p, 3);
    } elseif (str_starts_with($p, '84') && strlen($p) === 11) {
        $p = '0' . substr($p, 2);
    }
    return preg_match('/^0\d{9}$/', $p) ? $p : null;
}
