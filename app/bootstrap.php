<?php
/**
 * Khởi tạo chung: cấu hình, lỗi, múi giờ, CSDL, hàm tiện ích.
 * Mọi trang đều `require` file này đầu tiên.
 */
declare(strict_types=1);

define('SUNNY_ROOT', dirname(__DIR__));
define('SUNNY_APP', __DIR__);

mb_internal_encoding('UTF-8');
date_default_timezone_set('Asia/Ho_Chi_Minh');

/* ---------- Cấu hình ---------- */

function config(?string $key = null, $default = null)
{
    static $cfg = null;
    if ($cfg === null) {
        $file = SUNNY_APP . '/config.php';
        if (!is_file($file)) {
            http_response_code(503);
            header('Content-Type: text/html; charset=utf-8');
            echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
               . '<title>Chưa cấu hình</title><body style="font-family:system-ui;max-width:560px;margin:15vh auto;padding:0 20px;line-height:1.6">'
               . '<h1>Website chưa được cấu hình</h1><p>Hãy sao chép <code>app/config.sample.php</code> thành '
               . '<code>app/config.php</code> rồi điền thông tin (xem README.md).</p></body>';
            exit;
        }
        $cfg = require $file;
    }
    return $key === null ? $cfg : ($cfg[$key] ?? $default);
}

$__debug = (bool) config('debug', false);
error_reporting(E_ALL);
ini_set('display_errors', $__debug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', SUNNY_ROOT . '/data/php-error.log');

set_exception_handler(function (Throwable $e) use ($__debug) {
    error_log((string) $e);
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
       . '<title>Có lỗi xảy ra</title><body style="font-family:system-ui;max-width:560px;margin:15vh auto;padding:0 20px;line-height:1.6">'
       . '<h1>Có lỗi xảy ra</h1><p>Xin ba mẹ thử lại sau ít phút hoặc gọi trực tiếp cho Sunny.</p>';
    if ($__debug) {
        echo '<pre style="white-space:pre-wrap">' . htmlspecialchars((string) $e) . '</pre>';
    }
    echo '</body>';
    exit;
});

require_once SUNNY_APP . '/helpers.php';
require_once SUNNY_APP . '/db.php';
require_once SUNNY_APP . '/content.php';
require_once SUNNY_APP . '/sanitize.php';
require_once SUNNY_APP . '/auth.php';
require_once SUNNY_APP . '/layout.php';
