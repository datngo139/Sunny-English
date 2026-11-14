<?php
/**
 * Sunny World — dữ liệu (chủ đề, thẻ từ, truyện, từ điển) và khung trang dành cho bé.
 * Không có tài khoản cho bé: tiến độ chỉ lưu trên máy của bé (localStorage, xem assets/js/sw.js).
 */
declare(strict_types=1);

function sw_url(string $view = '', string $slug = ''): string
{
    return url('/sunny-world' . ($view !== '' ? '/' . $view : '') . ($slug !== '' ? '/' . $slug : ''));
}

/* ---------- Từ vựng ---------- */

function sw_topics(bool $all = false): array
{
    $sql = 'SELECT t.*, (SELECT COUNT(*) FROM sw_cards c WHERE c.topic_id = t.id' . ($all ? '' : ' AND c.active = 1') . ') AS n
            FROM sw_topics t' . ($all ? '' : ' WHERE t.active = 1') . ' ORDER BY t.sort, t.id';
    return db()->query($sql)->fetchAll();
}

function sw_topic_by_slug(string $slug): ?array
{
    $st = db()->prepare('SELECT * FROM sw_topics WHERE slug = ? AND active = 1');
    $st->execute([$slug]);
    return $st->fetch() ?: null;
}

function sw_cards(int $topicId, bool $all = false): array
{
    $st = db()->prepare('SELECT * FROM sw_cards WHERE topic_id = ?' . ($all ? '' : ' AND active = 1') . ' ORDER BY sort, id');
    $st->execute([$topicId]);
    return $st->fetchAll();
}

/* ---------- Truyện ---------- */

const SW_KINDS = ['story' => 'Truyện', 'science' => 'Khoa học'];

function sw_stories(?string $kind = null, bool $all = false): array
{
    $where = [];
    $args = [];
    if (!$all) {
        $where[] = 'active = 1';
    }
    if ($kind !== null) {
        $where[] = 'kind = ?';
        $args[] = $kind;
    }
    $st = db()->prepare('SELECT * FROM sw_stories' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY sort, id');
    $st->execute($args);
    return $st->fetchAll();
}

function sw_story_by_slug(string $slug): ?array
{
    $st = db()->prepare('SELECT * FROM sw_stories WHERE slug = ? AND active = 1');
    $st->execute([$slug]);
    return $st->fetch() ?: null;
}

/** Các trang của truyện: [['en' => ..., 'vi' => ...], ...] */
function sw_story_pages(array $story): array
{
    $pages = json_decode((string) $story['pages'], true);
    $out = [];
    foreach (is_array($pages) ? $pages : [] as $p) {
        if (is_array($p) && trim((string) ($p['en'] ?? '')) !== '') {
            $out[] = ['en' => trim((string) $p['en']), 'vi' => trim((string) ($p['vi'] ?? ''))];
        }
    }
    return $out;
}

/** "word = nghĩa" mỗi dòng → ['word' => 'nghĩa'] (từ viết thường). */
function sw_parse_glossary(string $text): array
{
    $map = [];
    foreach (preg_split('/\R/u', $text) as $line) {
        if (preg_match('/^\s*([A-Za-z][A-Za-z\'’\- ]{0,40}?)\s*[=:]\s*(.+?)\s*$/u', $line, $m)) {
            $map[strtolower(str_replace('’', "'", $m[1]))] = mb_substr($m[2], 0, 120);
        }
    }
    return $map;
}

/** Từ điển chung (tải một lần mỗi lượt xem trang). */
function sw_dict(): array
{
    static $d = null;
    if ($d === null) {
        $d = [];
        foreach (db()->query('SELECT word, meaning FROM sw_dict') as $r) {
            $d[$r['word']] = $r['meaning'];
        }
    }
    return $d;
}

/** Tra nghĩa một từ: ưu tiên bảng từ của truyện, rồi từ điển chung, rồi thử dạng gốc đơn giản (cats → cat, played → play). */
function sw_lookup(string $word, array $gloss): ?string
{
    $w = strtolower(str_replace('’', "'", $word));
    $dict = sw_dict();
    $try = [$w];
    if (str_ends_with($w, "'s")) {
        $try[] = substr($w, 0, -2);
    }
    if (strlen($w) > 3) {
        if (str_ends_with($w, 'ies')) {
            $try[] = substr($w, 0, -3) . 'y';
        }
        if (str_ends_with($w, 'es')) {
            $try[] = substr($w, 0, -2);
        }
        if (str_ends_with($w, 's')) {
            $try[] = substr($w, 0, -1);
        }
        if (str_ends_with($w, 'ed')) {
            $try[] = substr($w, 0, -2);
            $try[] = substr($w, 0, -1);
        }
        if (str_ends_with($w, 'ing')) {
            $try[] = substr($w, 0, -3);
            $try[] = substr($w, 0, -3) . 'e';
        }
    }
    foreach ($try as $t) {
        if (isset($gloss[$t])) {
            return $gloss[$t];
        }
        if (isset($dict[$t])) {
            return $dict[$t];
        }
    }
    return null;
}

/** Tách một đoạn tiếng Anh thành các mảnh [chuỗi, có phải là từ?]. */
function sw_tokens(string $text): array
{
    $parts = preg_split('/([A-Za-z]+(?:[\'’][A-Za-z]+)?)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    $out = [];
    foreach ($parts as $p) {
        $out[] = [$p, (bool) preg_match('/^[A-Za-z]/', $p)];
    }
    return $out;
}

/** Bọc từng từ tiếng Anh trong thẻ <span> kèm nghĩa để rê chuột / chạm xem nghĩa. */
function sw_words_html(string $text, array $gloss): string
{
    $h = '';
    foreach (sw_tokens($text) as [$p, $isWord]) {
        if (!$isWord) {
            $h .= e($p);
            continue;
        }
        $m = sw_lookup($p, $gloss);
        $h .= $m !== null
            ? '<span class="w" tabindex="0" data-m="' . e($m) . '">' . e($p) . '</span>'
            : '<span class="w nom" tabindex="0">' . e($p) . '</span>';
    }
    return $h;
}

/** Các từ trong truyện chưa có nghĩa (để admin bổ sung). */
function sw_missing_words(array $pages, array $gloss): array
{
    $miss = [];
    foreach ($pages as $p) {
        foreach (sw_tokens($p['en']) as [$w, $isWord]) {
            if ($isWord && sw_lookup($w, $gloss) === null) {
                $miss[strtolower($w)] = true;
            }
        }
    }
    return array_keys($miss);
}

/* ---------- Hình ---------- */

/** Hình của một thẻ / chủ đề / truyện: ảnh admin tải lên → hình vẽ sẵn → chữ cái đầu. */
function sw_picture(array $row, string $fallbackText = ''): string
{
    $img = (string) ($row['image'] ?? '');
    if ($img !== '') {
        return '<img class="art" src="' . e(url($img)) . '" alt="" loading="lazy">';
    }
    $svg = sw_art((string) ($row['art'] ?? ''));
    if ($svg !== '') {
        return $svg;
    }
    $ch = mb_strtoupper(mb_substr($fallbackText !== '' ? $fallbackText : '?', 0, 1));
    return '<span class="art art-letter">' . e($ch) . '</span>';
}

/** Kiểm tra đường dẫn ảnh do admin gửi lên: chỉ nhận ảnh đã tải qua trang quản trị. */
function sw_clean_image(string $path): string
{
    return preg_match('#^/uploads/\d{4}/\d{2}/[a-f0-9]{16}\.(?:jpe?g|png|webp|gif)$#', $path) ? $path : '';
}

/** Màu nền hợp lệ (#RRGGBB), sai thì dùng màu mặc định. */
function sw_clean_color(string $c, string $default = '#FFE9A8'): string
{
    return preg_match('/^#[0-9A-Fa-f]{6}$/', $c) ? strtoupper($c) : $default;
}

/* ---------- Khung trang cho bé ---------- */

function sw_head(array $o = []): void
{
    $o['head_extra'] = ($o['head_extra'] ?? '') . '<link rel="stylesheet" href="' . e(asset('/assets/css/sw.css')) . '">' . "\n";
    $o['body_class'] = 'sw';
    $o['title'] = ($o['title'] ?? 'Sunny World') . ' — Sunny World';
    $o['description'] = $o['description'] ?? 'Sunny World: thế giới tiếng Anh của bé. Lật thẻ học từ vựng, đọc truyện tiếng Anh có nghĩa tiếng Việt cho từng từ.';
    site_head($o);
    echo sun_defs();
}

function sw_topbar(string $backUrl, string $backLabel): void
{
    ?>
<header class="sw-top">
  <a class="sw-back" href="<?= e($backUrl) ?>"><?= icon('chevron-left') ?><span><?= e($backLabel) ?></span></a>
  <a class="sw-brand" href="<?= e(sw_url()) ?>"><svg viewBox="0 0 200 200" width="38" height="38" aria-hidden="true"><use href="#sunFace"/></svg><span>Sunny World</span></a>
  <span class="sw-stars" title="Số sao của bé"><?= icon('star') ?><b id="sw-stars">0</b></span>
</header>
<?php
}

function sw_foot(string $inlineJson = ''): void
{
    $c = sunny_contact();
    echo '<footer class="sw-foot"><a href="' . e(url('/')) . '">' . e($c['center']) . '</a> · <a href="' . e(sw_url('ba-me')) . '">Góc ba mẹ</a></footer>' . "\n";
    echo '<div class="sw-tip" id="sw-tip" role="tooltip" hidden></div>' . "\n";
    echo $inlineJson;
    echo '<script src="' . e(asset('/assets/js/sw.js')) . '" defer></script>' . "\n</body>\n</html>\n";
}

/** Dữ liệu nhúng vào trang cho sw.js (an toàn trong thẻ <script>). */
function sw_json(string $var, $data): string
{
    return '<script>window.' . $var . ' = ' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ";</script>\n";
}
