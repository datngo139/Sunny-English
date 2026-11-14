<?php
declare(strict_types=1);

/** Kết nối SQLite (file data/sunny.sqlite) và tự tạo bảng ở lần chạy đầu. */
function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    $dir = SUNNY_ROOT . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $pdo = new PDO('sqlite:' . $dir . '/sunny.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA busy_timeout = 5000');
    $pdo->exec('PRAGMA foreign_keys = ON');
    migrate($pdo);
    return $pdo;
}

function migrate(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS settings (k TEXT PRIMARY KEY, v TEXT NOT NULL)');

    $pdo->exec('CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )');

    $pdo->exec("CREATE TABLE IF NOT EXISTS posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT NOT NULL UNIQUE,
        title TEXT NOT NULL,
        excerpt TEXT NOT NULL DEFAULT '',
        cover TEXT NOT NULL DEFAULT '',
        body TEXT NOT NULL DEFAULT '',
        status TEXT NOT NULL DEFAULT 'draft',
        published_at TEXT,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_posts_status_pub ON posts(status, published_at)');

    $pdo->exec("CREATE TABLE IF NOT EXISTS registrations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        parent_name TEXT NOT NULL,
        phone TEXT NOT NULL,
        child_name TEXT NOT NULL DEFAULT '',
        child_age INTEGER,
        course TEXT NOT NULL DEFAULT '',
        slot TEXT NOT NULL DEFAULT '',
        note TEXT NOT NULL DEFAULT '',
        status TEXT NOT NULL DEFAULT 'new',
        ip_hash TEXT NOT NULL DEFAULT '',
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_reg_created ON registrations(created_at)');

    // Thêm cột ngày/giờ học thử cho bảng đăng ký (DB cũ trên hosting tự được nâng cấp).
    $cols = array_column($pdo->query('PRAGMA table_info(registrations)')->fetchAll(), 'name');
    foreach (['visit_date', 'visit_time'] as $col) {
        if (!in_array($col, $cols, true)) {
            try {
                $pdo->exec("ALTER TABLE registrations ADD COLUMN $col TEXT NOT NULL DEFAULT ''");
            } catch (PDOException $e) {
                // Hai người cùng mở trang lần đầu: người kia đã thêm cột rồi → bỏ qua lỗi "duplicate column".
                if (stripos($e->getMessage(), 'duplicate column') === false) {
                    throw $e;
                }
            }
        }
    }
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_reg_visit ON registrations(visit_date, visit_time)');

    $pdo->exec("CREATE TABLE IF NOT EXISTS courses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT NOT NULL UNIQUE,
        level TEXT NOT NULL DEFAULT 'tieuhoc',
        icon TEXT NOT NULL DEFAULT 'book-open',
        title TEXT NOT NULL,
        ages TEXT NOT NULL DEFAULT '',
        description TEXT NOT NULL DEFAULT '',
        headline TEXT NOT NULL DEFAULT '',
        outcome TEXT NOT NULL DEFAULT '',
        points TEXT NOT NULL DEFAULT '',
        image TEXT NOT NULL DEFAULT '',
        sort INTEGER NOT NULL DEFAULT 0,
        active INTEGER NOT NULL DEFAULT 1
    )");
    // Lần đầu: nạp 4 khoá học mẫu từ content.php để admin sửa tiếp. Chỉ nạp một lần.
    $seeded = $pdo->query("SELECT v FROM settings WHERE k = 'courses_seeded'")->fetchColumn();
    if (!$seeded) {
        $ins = $pdo->prepare('INSERT OR IGNORE INTO courses (code, level, icon, title, ages, description, headline, outcome, points, sort, active)
                              VALUES (?,?,?,?,?,?,?,?,?,?,1)');
        foreach (sunny_default_courses() as $i => $c) {
            $ins->execute([$c['id'], $c['level'], $c['icon'], $c['title'], $c['ages'], $c['desc'], $c['headline'], $c['outcome'],
                implode("\n", $c['points']), ($i + 1) * 10]);
        }
        $pdo->exec("INSERT OR REPLACE INTO settings (k, v) VALUES ('courses_seeded', '1')");
    }

    // ----- Sunny World: chủ đề từ vựng, thẻ từ, truyện, từ điển chung -----
    $pdo->exec("CREATE TABLE IF NOT EXISTS sw_topics (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT NOT NULL UNIQUE,
        title_en TEXT NOT NULL,
        title_vi TEXT NOT NULL DEFAULT '',
        color TEXT NOT NULL DEFAULT '#FDBE10',
        art TEXT NOT NULL DEFAULT '',
        image TEXT NOT NULL DEFAULT '',
        sort INTEGER NOT NULL DEFAULT 0,
        active INTEGER NOT NULL DEFAULT 1
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS sw_cards (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        topic_id INTEGER NOT NULL,
        word TEXT NOT NULL,
        meaning TEXT NOT NULL DEFAULT '',
        example TEXT NOT NULL DEFAULT '',
        example_vi TEXT NOT NULL DEFAULT '',
        art TEXT NOT NULL DEFAULT '',
        image TEXT NOT NULL DEFAULT '',
        sort INTEGER NOT NULL DEFAULT 0,
        active INTEGER NOT NULL DEFAULT 1
    )");
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_sw_cards_topic ON sw_cards(topic_id, sort)');
    $pdo->exec("CREATE TABLE IF NOT EXISTS sw_stories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        slug TEXT NOT NULL UNIQUE,
        kind TEXT NOT NULL DEFAULT 'story',
        title TEXT NOT NULL,
        title_vi TEXT NOT NULL DEFAULT '',
        art TEXT NOT NULL DEFAULT '',
        image TEXT NOT NULL DEFAULT '',
        color TEXT NOT NULL DEFAULT '#FFE9A8',
        pages TEXT NOT NULL DEFAULT '[]',
        glossary TEXT NOT NULL DEFAULT '',
        sort INTEGER NOT NULL DEFAULT 0,
        active INTEGER NOT NULL DEFAULT 1,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    $pdo->exec('CREATE TABLE IF NOT EXISTS sw_dict (word TEXT PRIMARY KEY, meaning TEXT NOT NULL)');
    if (!$pdo->query("SELECT v FROM settings WHERE k = 'sw_seeded'")->fetchColumn()) {
        sw_seed($pdo); // nội dung mẫu, chỉ nạp một lần
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS login_attempts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip_hash TEXT NOT NULL,
        created_at INTEGER NOT NULL
    )');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_login_ip ON login_attempts(ip_hash, created_at)');
}

/** Khoá bí mật của site, tự sinh một lần, lưu trong CSDL. */
function app_secret(): string
{
    static $secret = null;
    if ($secret !== null) {
        return $secret;
    }
    $pdo = db();
    $v = $pdo->query("SELECT v FROM settings WHERE k = 'secret'")->fetchColumn();
    if (!$v) {
        $v = bin2hex(random_bytes(32));
        $pdo->prepare("INSERT OR IGNORE INTO settings (k, v) VALUES ('secret', ?)")->execute([$v]);
        $v = $pdo->query("SELECT v FROM settings WHERE k = 'secret'")->fetchColumn();
    }
    return $secret = (string) $v;
}

/** Đọc / ghi cài đặt dạng khoá-giá trị (lưu trong bảng settings). */
function setting_get(string $key, ?string $default = null): ?string
{
    $st = db()->prepare('SELECT v FROM settings WHERE k = ?');
    $st->execute([$key]);
    $v = $st->fetchColumn();
    return $v === false ? $default : (string) $v;
}

function setting_set(string $key, string $value): void
{
    db()->prepare('INSERT OR REPLACE INTO settings (k, v) VALUES (?, ?)')->execute([$key, $value]);
}
