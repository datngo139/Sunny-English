<?php
declare(strict_types=1);

const ADMIN_COOKIE = 'sunny_admin';
const ADMIN_IDLE_SECONDS = 8 * 3600;

function admin_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name(ADMIN_COOKIE);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    // Lưu session riêng trong data/ (shared hosting đôi khi dùng chung thư mục tmp).
    $dir = SUNNY_ROOT . '/data/sessions';
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }
    if (is_dir($dir) && is_writable($dir)) {
        session_save_path($dir);
    }
    session_start();
}

/** Người dùng có cookie admin không (để trang công khai khỏi phải mở session cho mọi khách). */
function has_admin_cookie(): bool
{
    return isset($_COOKIE[ADMIN_COOKIE]);
}

function current_admin(): ?array
{
    admin_session_start();
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    if (time() - ($_SESSION['last_seen'] ?? 0) > ADMIN_IDLE_SECONDS) {
        admin_logout();
        return null;
    }
    $_SESSION['last_seen'] = time();
    return ['id' => (int) $_SESSION['admin_id'], 'username' => (string) $_SESSION['admin_user']];
}

/** Admin đang đăng nhập (dùng cho trang công khai: chỉ kiểm tra khi có cookie). */
function is_admin_viewer(): bool
{
    return has_admin_cookie() && current_admin() !== null;
}

function require_admin(): array
{
    $admin = current_admin();
    if (!$admin) {
        if (admin_count() === 0) {
            redirect('/admin/setup.php');
        }
        redirect('/admin/login.php');
    }
    header('Cache-Control: no-store');
    return $admin;
}

function admin_count(): int
{
    return (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
}

function admin_login(int $id, string $username): void
{
    admin_session_start();
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $id;
    $_SESSION['admin_user'] = $username;
    $_SESSION['last_seen'] = time();
}

function admin_logout(): void
{
    admin_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'] ?? '', (bool) $p['secure'], (bool) $p['httponly']);
    }
    session_destroy();
}

/* ---------- CSRF ---------- */

function csrf_token(): string
{
    admin_session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(?string $token = null): void
{
    admin_session_start();
    $token = $token ?? ($_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
    if (empty($_SESSION['csrf']) || !is_string($token) || !hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(419);
        header('Content-Type: text/plain; charset=utf-8');
        exit('Phiên làm việc đã hết hạn hoặc yêu cầu không hợp lệ. Hãy tải lại trang và thử lại.');
    }
}

/* ---------- Giới hạn đăng nhập sai ---------- */

const LOGIN_MAX_FAILS = 6;
const LOGIN_WINDOW_SECONDS = 900;

function login_is_locked(): bool
{
    $pdo = db();
    $pdo->prepare('DELETE FROM login_attempts WHERE created_at < ?')->execute([time() - 86400]);
    $st = $pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip_hash = ? AND created_at > ?');
    $st->execute([client_ip_hash(), time() - LOGIN_WINDOW_SECONDS]);
    return (int) $st->fetchColumn() >= LOGIN_MAX_FAILS;
}

function login_record_fail(): void
{
    db()->prepare('INSERT INTO login_attempts (ip_hash, created_at) VALUES (?, ?)')->execute([client_ip_hash(), time()]);
}

function login_clear_fails(): void
{
    db()->prepare('DELETE FROM login_attempts WHERE ip_hash = ?')->execute([client_ip_hash()]);
}

/* ---------- Flash message ---------- */

function flash_set(string $type, string $msg): void
{
    admin_session_start();
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function flash_get(): ?array
{
    admin_session_start();
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function password_problem(string $pw): ?string
{
    if (mb_strlen($pw) < 10) {
        return 'Mật khẩu cần ít nhất 10 ký tự.';
    }
    if (!preg_match('/[A-Za-z]/', $pw) || !preg_match('/\d/', $pw)) {
        return 'Mật khẩu cần có cả chữ và số.';
    }
    return null;
}
