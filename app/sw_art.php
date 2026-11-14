<?php
/**
 * Sunny World — hình vẽ SVG: hình cho thẻ từ vựng / bìa truyện và bản đồ thế giới.
 * Tất cả tự vẽ, cùng phong cách "sticker" viền đậm với linh vật.
 */
declare(strict_types=1);

/** Bảng màu dùng cho chủ đề "Colors" và nền số đếm. */
function sw_palette(): array
{
    return ['red' => '#E53935', 'blue' => '#1E88E5', 'green' => '#43A047', 'yellow' => '#FDD835', 'orange' => '#FB8C00',
        'pink' => '#F06292', 'purple' => '#8E24AA', 'black' => '#2E241B'];
}

/** Trả về SVG của một hình theo khoá (ví dụ "cat", "color-red", "num-3"). Khoá lạ → chuỗi rỗng. */
function sw_art(string $key): string
{
    static $lib = null;
    if ($lib === null) {
        $eye = fn(float $x, float $y, float $r = 4.5) => '<circle cx="' . $x . '" cy="' . $y . '" r="' . $r . '" fill="#2E241B" stroke="none"/>';
        $lib = [
            // Con vật
            'cat' => '<path d="M24 34L22 12L42 24Z M76 34L78 12L58 24Z" fill="#F7A23B"/><circle cx="50" cy="54" r="32" fill="#F7A23B"/>' . $eye(39, 50) . $eye(61, 50) . '<path d="M46 60L54 60L50 65Z" fill="#FF7C8A"/><path d="M50 65Q44 72 38 68M50 65Q56 72 62 68M20 58L32 60M20 66L32 64M80 58L68 60M80 66L68 64" fill="none"/>',
            'dog' => '<ellipse cx="22" cy="48" rx="11" ry="22" fill="#8D5A3B"/><ellipse cx="78" cy="48" rx="11" ry="22" fill="#8D5A3B"/><circle cx="50" cy="50" r="30" fill="#E0A66B"/><ellipse cx="50" cy="62" rx="15" ry="12" fill="#fff"/>' . $eye(39, 44) . $eye(61, 44) . '<ellipse cx="50" cy="56" rx="6" ry="4.5" fill="#2E241B"/><path d="M50 61V67M44 70Q50 74 56 70" fill="none"/>',
            'bird' => '<circle cx="48" cy="50" r="28" fill="#4FC3F7"/><path d="M74 44L92 50L74 56Z" fill="#FDBE10"/><path d="M34 54Q46 44 56 56Q44 70 34 54Z" fill="#1E88E5"/>' . $eye(58, 40) . '<path d="M42 78V90M54 78V90M36 90H46M50 90H60" fill="none"/>',
            'fish' => '<path d="M70 50L92 30V70Z" fill="#FB8C00"/><ellipse cx="46" cy="50" rx="32" ry="22" fill="#FDBE10"/>' . $eye(30, 44) . '<path d="M46 34Q54 50 46 66M14 56Q20 60 26 56" fill="none"/>',
            'rabbit' => '<ellipse cx="38" cy="24" rx="9" ry="22" fill="#fff"/><ellipse cx="62" cy="24" rx="9" ry="22" fill="#fff"/><ellipse cx="38" cy="26" rx="4" ry="14" fill="#FF7C8A" stroke="none"/><ellipse cx="62" cy="26" rx="4" ry="14" fill="#FF7C8A" stroke="none"/><circle cx="50" cy="62" r="28" fill="#fff"/>' . $eye(40, 58) . $eye(60, 58) . '<path d="M46 68L54 68L50 72Z" fill="#FF7C8A"/><path d="M50 72V78M44 80Q50 84 56 80" fill="none"/>',
            'duck' => '<circle cx="42" cy="34" r="20" fill="#FDD835"/><ellipse cx="54" cy="68" rx="32" ry="20" fill="#FDD835"/><path d="M24 34L6 38L24 44Z" fill="#FB8C00"/>' . $eye(38, 28, 4) . '<path d="M50 66Q62 56 76 66" fill="none"/>',
            'pig' => '<path d="M24 30L20 14L38 22Z M76 30L80 14L62 22Z" fill="#F48FB1"/><circle cx="50" cy="54" r="32" fill="#F8BBD0"/><ellipse cx="50" cy="62" rx="15" ry="11" fill="#F48FB1"/>' . $eye(44, 62, 3) . $eye(56, 62, 3) . $eye(38, 44) . $eye(62, 44),
            'cow' => '<path d="M26 26Q14 22 14 10Q26 12 32 22Z M74 26Q86 22 86 10Q74 12 68 22Z" fill="#F6E6D8"/><ellipse cx="50" cy="48" rx="30" ry="28" fill="#fff"/><path d="M26 34Q34 26 42 36Q36 48 26 44Z" fill="#2E241B"/><ellipse cx="50" cy="68" rx="20" ry="13" fill="#F8BBD0"/>' . $eye(43, 68, 3) . $eye(57, 68, 3) . $eye(40, 46) . $eye(62, 46),
            'lion' => '<circle cx="50" cy="50" r="40" fill="#C0621F"/><circle cx="50" cy="52" r="27" fill="#FDBE10"/>' . $eye(40, 46, 4) . $eye(60, 46, 4) . '<path d="M45 56H55L50 62Z" fill="#7A3F18"/><path d="M50 62Q44 70 38 66M50 62Q56 70 62 66" fill="none"/>',
            'tortoise' => '<path d="M22 74V86M66 74V86" stroke-width="9" stroke="#2E241B" fill="none"/><path d="M22 74V85M66 74V85" stroke-width="5" stroke="#9CCC65" fill="none"/><circle cx="84" cy="50" r="11" fill="#9CCC65"/>' . $eye(87, 47, 2.5) . '<ellipse cx="46" cy="56" rx="32" ry="22" fill="#43A047"/><path d="M30 44L46 36L62 44M30 68L46 76L62 68M46 36V76" stroke="#1B5E20" fill="none"/>',
            // Trái cây
            'apple' => '<path d="M50 30C30 16 10 34 16 58C20 78 36 92 50 84C64 92 80 78 84 58C90 34 70 16 50 30Z" fill="#E53935"/><path d="M50 30Q50 18 58 12" fill="none"/><path d="M58 18Q70 8 78 18Q68 26 58 18Z" fill="#43A047"/>',
            'banana' => '<path d="M18 30Q20 70 62 82Q84 84 88 70Q52 70 34 26Z" fill="#FDD835"/><path d="M18 30L30 22L34 26Z" fill="#8D5A3B"/>',
            'orange' => '<circle cx="50" cy="56" r="34" fill="#FB8C00"/><path d="M50 22Q56 12 68 14Q64 26 50 22Z" fill="#43A047"/><path d="M32 44Q38 34 48 32" stroke="#fff" stroke-opacity=".6" fill="none"/>',
            'grapes' => '<g fill="#8E24AA"><circle cx="36" cy="40" r="12"/><circle cx="60" cy="40" r="12"/><circle cx="24" cy="58" r="12"/><circle cx="72" cy="58" r="12"/><circle cx="48" cy="58" r="12"/><circle cx="36" cy="76" r="12"/><circle cx="60" cy="76" r="12"/></g><path d="M48 28V12" fill="none"/><path d="M48 18Q62 6 72 16Q62 26 48 18Z" fill="#43A047"/>',
            'strawberry' => '<path d="M50 28C24 22 12 44 22 62C30 78 44 90 50 92C56 90 70 78 78 62C88 44 76 22 50 28Z" fill="#E53935"/><path d="M30 26L42 30L50 18L58 30L70 26L62 38H38Z" fill="#43A047"/><g fill="#FDD835" stroke="none"><circle cx="38" cy="52" r="2.5"/><circle cx="54" cy="48" r="2.5"/><circle cx="64" cy="60" r="2.5"/><circle cx="46" cy="66" r="2.5"/><circle cx="56" cy="78" r="2.5"/></g>',
            'watermelon' => '<path d="M10 40A40 40 0 0 0 90 40Z" fill="#43A047"/><path d="M19 42A31 31 0 0 0 81 42Z" fill="#EF5350" stroke="none"/><g fill="#2E241B" stroke="none"><ellipse cx="38" cy="52" rx="2.5" ry="4"/><ellipse cx="50" cy="60" rx="2.5" ry="4"/><ellipse cx="62" cy="52" rx="2.5" ry="4"/></g>',
            'lemon' => '<path d="M12 50Q30 18 60 24Q76 26 88 50Q76 74 60 76Q30 82 12 50Z" fill="#FDD835"/><path d="M30 44Q40 32 54 32" stroke="#fff" stroke-opacity=".7" fill="none"/>',
            'cherry' => '<path d="M34 60Q40 30 62 14Q60 36 68 58" fill="none"/><circle cx="32" cy="68" r="17" fill="#E53935"/><circle cx="68" cy="68" r="17" fill="#E53935"/><path d="M62 14Q76 8 86 18Q74 26 62 14Z" fill="#43A047"/>',
            // Đồ dùng học tập
            'book' => '<path d="M10 24Q30 14 50 24V82Q30 72 10 82Z" fill="#4FC3F7"/><path d="M90 24Q70 14 50 24V82Q70 72 90 82Z" fill="#81D4FA"/><path d="M20 36Q30 32 40 36M20 48Q30 44 40 48M60 36Q70 32 80 36M60 48Q70 44 80 48" fill="none" stroke="#fff"/>',
            'pencil' => '<g transform="rotate(40 50 50)"><rect x="40" y="10" width="20" height="58" fill="#FDD835"/><path d="M40 68L50 90L60 68Z" fill="#F6E6D8"/><path d="M46 81L50 90L54 81Z" fill="#2E241B"/><rect x="40" y="4" width="20" height="12" rx="3" fill="#F48FB1"/></g>',
            'bag' => '<rect x="18" y="28" width="64" height="62" rx="16" fill="#FB8C00"/><path d="M36 28Q36 10 50 10Q64 10 64 28" fill="none"/><rect x="30" y="58" width="40" height="22" rx="8" fill="#FDD835"/><path d="M32 44H68" fill="none"/>',
            'ruler' => '<g transform="rotate(-30 50 50)"><rect x="8" y="38" width="84" height="24" rx="4" fill="#81C784"/><path d="M20 38V50M32 38V46M44 38V50M56 38V46M68 38V50M80 38V46" fill="none"/></g>',
            'eraser' => '<g transform="rotate(-20 50 50)"><rect x="16" y="34" width="68" height="32" rx="6" fill="#F48FB1"/><rect x="16" y="34" width="26" height="32" rx="6" fill="#4FC3F7"/></g>',
            'clock' => '<circle cx="50" cy="50" r="38" fill="#E53935"/><circle cx="50" cy="50" r="29" fill="#fff"/><path d="M50 50V30M50 50L64 58" fill="none"/><circle cx="50" cy="50" r="3" fill="#2E241B"/>',
            'board' => '<path d="M30 70L22 90M70 70L78 90" fill="none"/><rect x="10" y="18" width="80" height="54" rx="5" fill="#2E7D32"/><path d="M22 34H52M22 46H40" stroke="#fff" fill="none"/>',
            'chair' => '<path d="M32 60V90M68 60V90M32 16V52M68 16V52" fill="none" stroke-width="6"/><rect x="26" y="50" width="48" height="13" rx="4" fill="#FB8C00"/><rect x="28" y="16" width="44" height="18" rx="5" fill="#FB8C00"/>',
            // Thời tiết
            'sun' => '<path d="M50 4L56 20H44Z M50 96L56 80H44Z M4 50L20 44V56Z M96 50L80 44V56Z M17 17L32 26L26 32Z M83 17L74 32L68 26Z M17 83L26 68L32 74Z M83 83L68 74L74 68Z" fill="#F7931E"/><circle cx="50" cy="50" r="26" fill="#FDBE10"/>' . $eye(42, 46, 3.5) . $eye(58, 46, 3.5) . '<path d="M42 56Q50 64 58 56" fill="none"/>',
            'cloud' => '<path d="M26 72Q8 72 10 56Q12 44 26 44Q28 26 46 26Q62 26 66 40Q84 38 88 54Q90 72 72 72Z" fill="#fff"/>',
            'rain' => '<path d="M26 56Q10 56 12 42Q14 32 26 32Q28 16 46 16Q62 16 66 30Q84 28 88 42Q90 56 72 56Z" fill="#CFD8DC"/><path d="M30 68L26 80M48 68L44 86M66 68L62 80" stroke="#1E88E5" stroke-width="5" fill="none"/>',
            'snow' => '<path d="M50 10V90M15 30L85 70M15 70L85 30" stroke="#4FC3F7" stroke-width="6" fill="none"/><path d="M40 18L50 28L60 18M40 82L50 72L60 82" stroke="#4FC3F7" stroke-width="5" fill="none"/>',
            'wind' => '<path d="M10 36H60Q74 36 74 26Q74 16 62 18M10 52H76Q90 52 90 64Q90 74 78 72M10 68H50Q60 68 60 78Q60 86 50 84" stroke="#78909C" stroke-width="6" fill="none"/>',
            'rainbow' => '<path d="M10 78A40 40 0 0 1 90 78" stroke="#E53935" stroke-width="9" fill="none"/><path d="M20 78A30 30 0 0 1 80 78" stroke="#FDD835" stroke-width="9" fill="none"/><path d="M30 78A20 20 0 0 1 70 78" stroke="#1E88E5" stroke-width="9" fill="none"/>',
            // Hình khối
            'circle' => '<circle cx="50" cy="50" r="36" fill="#1E88E5"/>',
            'square' => '<rect x="16" y="16" width="68" height="68" rx="6" fill="#E53935"/>',
            'triangle' => '<path d="M50 12L90 84H10Z" fill="#43A047"/>',
            'rectangle' => '<rect x="8" y="28" width="84" height="44" rx="6" fill="#FB8C00"/>',
            'star' => '<path d="M50 8L62 36L92 38L69 58L77 88L50 71L23 88L31 58L8 38L38 36Z" fill="#FDD835"/>',
            'heart' => '<path d="M50 86C16 62 8 44 14 30C20 14 42 14 50 32C58 14 80 14 86 30C92 44 84 62 50 86Z" fill="#F06292"/>',
            // Khác (bìa truyện)
            'ball' => '<circle cx="50" cy="50" r="36" fill="#E53935"/><path d="M20 34Q50 50 80 34M20 66Q50 50 80 66M50 14V86" stroke="#fff" fill="none"/>',
            'sky' => '<circle cx="66" cy="34" r="20" fill="#FDBE10"/><path d="M22 78Q6 78 8 64Q10 54 22 54Q24 40 40 40Q54 40 58 52Q74 50 78 64Q80 78 64 78Z" fill="#fff"/>',
        ];
    }
    $inner = $lib[$key] ?? null;
    if ($inner === null && str_starts_with($key, 'color-')) {
        $c = sw_palette()[substr($key, 6)] ?? null;
        if ($c) {
            $inner = '<path d="M50 12C72 10 90 28 86 50C92 70 72 90 50 86C28 92 10 72 14 50C8 30 28 12 50 12Z" fill="' . $c . '"/><path d="M30 36Q38 24 52 24" stroke="#fff" stroke-opacity=".55" fill="none"/>';
        }
    }
    if ($inner === null && preg_match('/^num-(\d{1,2})$/', $key, $m)) {
        $bg = array_values(sw_palette())[((int) $m[1] - 1) % 7];
        $inner = '<rect x="10" y="10" width="80" height="80" rx="22" fill="' . $bg . '"/><text x="50" y="68" text-anchor="middle" font-size="' . ((int) $m[1] > 9 ? 44 : 54) . '" font-weight="900" fill="#fff" stroke="none" font-family="Be Vietnam Pro,system-ui,sans-serif">' . (int) $m[1] . '</text>';
    }
    if ($inner === null) {
        return '';
    }
    return '<svg class="art" viewBox="0 0 100 100" aria-hidden="true" focusable="false"><g stroke="#2E241B" stroke-width="3.5" stroke-linejoin="round" stroke-linecap="round">' . $inner . '</g></svg>';
}

/** Danh sách khoá hình có sẵn (cho admin chọn). */
function sw_art_keys(): array
{
    return ['cat', 'dog', 'bird', 'fish', 'rabbit', 'duck', 'pig', 'cow', 'lion', 'tortoise', 'apple', 'banana', 'orange', 'grapes',
        'strawberry', 'watermelon', 'lemon', 'cherry', 'book', 'pencil', 'bag', 'ruler', 'eraser', 'clock', 'board', 'chair',
        'sun', 'cloud', 'rain', 'snow', 'wind', 'rainbow', 'circle', 'square', 'triangle', 'rectangle', 'star', 'heart', 'ball', 'sky'];
}

/**
 * Các khu trên bản đồ. 'open' = đã mở ở đợt này.
 * x, y = vị trí chân ngôi nhà trên bản đồ (khung 1000 x 640).
 */
function sw_places(): array
{
    return [
        ['key' => 'tu-vung', 'name' => 'Vườn Từ vựng', 'desc' => 'Lật thẻ học từ mới theo chủ đề', 'x' => 190, 'y' => 372, 'open' => true, 'color' => '#3BAA4A'],
        ['key' => 'truyen', 'name' => 'Thư viện Truyện', 'desc' => 'Đọc truyện, chạm vào từ để xem nghĩa', 'x' => 500, 'y' => 268, 'open' => true, 'color' => '#7C4DFF'],
        ['key' => 'tro-choi', 'name' => 'Nhà Trò chơi', 'desc' => 'Chơi mà học từ vựng', 'x' => 812, 'y' => 372, 'open' => false, 'color' => '#E53935'],
        ['key' => 'khoa-hoc', 'name' => 'Trạm Khoa học', 'desc' => 'Đố vui kiến thức', 'x' => 318, 'y' => 548, 'open' => false, 'color' => '#2F72A8'],
        ['key' => 'rap-phim', 'name' => 'Rạp phim Sunny', 'desc' => 'Xem video tiếng Anh', 'x' => 690, 'y' => 548, 'open' => false, 'color' => '#F06292'],
    ];
}

/** Hình một ngôi nhà (gốc toạ độ ở chân nhà). */
function sw_house_art(string $key): string
{
    $L = '#2E241B';
    $s = 'stroke="' . $L . '" stroke-width="5" stroke-linejoin="round" stroke-linecap="round"';
    switch ($key) {
        case 'tu-vung': // nhà nấm giữa vườn hoa
            return '<g ' . $s . '><path d="M-96 0V-26M96 0V-30" fill="none"/><circle cx="-96" cy="-36" r="13" fill="#F06292"/><circle cx="96" cy="-40" r="13" fill="#FDD835"/>'
                . '<rect x="-36" y="-74" width="72" height="74" rx="16" fill="#FFF3C4"/><path d="M-14 0V-32A14 14 0 0 1 14 -32V0Z" fill="#B5652B"/><circle cx="-20" cy="-52" r="8" fill="#81D4FA"/><circle cx="20" cy="-52" r="8" fill="#81D4FA"/>'
                . '<path d="M-84 -64Q-76 -150 0 -150Q76 -150 84 -64Q0 -44 -84 -64Z" fill="#E53935"/><g fill="#fff" stroke="none"><circle cx="-40" cy="-96" r="12"/><circle cx="6" cy="-122" r="10"/><circle cx="44" cy="-90" r="13"/></g></g>';
        case 'truyen': // ngôi nhà mái sách mở
            return '<g ' . $s . '><rect x="-64" y="-88" width="128" height="88" rx="12" fill="#B39DDB"/><path d="M-16 0V-36A16 16 0 0 1 16 -36V0Z" fill="#5E35B1"/>'
                . '<rect x="-50" y="-66" width="24" height="24" rx="5" fill="#FFF3C4"/><rect x="26" y="-66" width="24" height="24" rx="5" fill="#FFF3C4"/>'
                . '<path d="M-86 -84Q-44 -128 0 -100Q44 -128 86 -84L86 -62Q44 -104 0 -78Q-44 -104 -86 -62Z" fill="#fff"/><path d="M0 -100V-78" fill="none"/>'
                . '<path d="M-60 -92Q-40 -104 -18 -98M60 -92Q40 -104 18 -98" stroke="#7C4DFF" stroke-width="4" fill="none"/></g>';
        case 'tro-choi': // lều xiếc
            return '<g ' . $s . '><path d="M-78 0V-58L0 -136L78 -58V0Z" fill="#FDBE10"/><path d="M-78 0V-58L-34 -102V0Z M34 0V-102L78 -58V0Z" fill="#E53935"/>'
                . '<path d="M-18 0V-40A18 18 0 0 1 18 -40V0Z" fill="#7A3F18"/><path d="M0 -136V-164" fill="none"/><path d="M0 -164L30 -154L0 -144Z" fill="#1E88E5"/></g>';
        case 'khoa-hoc': // đài thiên văn
            return '<g ' . $s . '><rect x="-44" y="-72" width="88" height="72" rx="8" fill="#CFE5F7"/><path d="M-14 0V-32A14 14 0 0 1 14 -32V0Z" fill="#17446A"/>'
                . '<g transform="rotate(-32 10 -112)"><rect x="6" y="-122" width="62" height="20" rx="7" fill="#78909C"/><rect x="60" y="-126" width="14" height="28" rx="5" fill="#455A64"/></g>'
                . '<path d="M-58 -72A58 58 0 0 1 58 -72Z" fill="#2F72A8"/><path d="M-30 -100Q-10 -120 14 -122" stroke="#fff" stroke-opacity=".5" fill="none"/></g>';
        case 'rap-phim': // rạp chiếu phim
            return '<g ' . $s . '><rect x="-72" y="-92" width="144" height="92" rx="10" fill="#F8BBD0"/><rect x="-46" y="-70" width="92" height="46" rx="6" fill="#fff"/><path d="M-10 -60L16 -47L-10 -34Z" fill="#E53935"/>'
                . '<rect x="-84" y="-118" width="168" height="34" rx="10" fill="#7C4DFF"/><g fill="#FDD835" stroke-width="3"><circle cx="-60" cy="-101" r="7"/><circle cx="-30" cy="-101" r="7"/><circle cx="0" cy="-101" r="7"/><circle cx="30" cy="-101" r="7"/><circle cx="60" cy="-101" r="7"/></g></g>';
    }
    return '';
}

/** Bản đồ Sunny World (SVG). Mỗi khu đã mở là một liên kết. */
function sw_map(): string
{
    $L = '#2E241B';
    $cloud = fn(int $x, int $y, float $s, string $cls) => '<g transform="translate(' . $x . ' ' . $y . ') scale(' . $s . ')"><path class="mc ' . $cls . '" d="M-70 30Q-104 30 -100 2Q-96 -18 -70 -18Q-66 -50 -30 -50Q0 -50 8 -26Q44 -30 52 0Q56 30 22 30Z" fill="#fff"/></g>';
    $out = '<svg class="sw-map" viewBox="0 0 1000 640" role="img" aria-label="Bản đồ Sunny World"><title>Bản đồ Sunny World</title>'
        . $cloud(120, 90, 1.0, 'c1') . $cloud(840, 80, 1.2, 'c2') . $cloud(520, 60, .8, 'c3') . $cloud(60, 500, .9, 'c2') . $cloud(950, 520, 1.0, 'c1')
        // đảo nổi
        . '<ellipse cx="500" cy="458" rx="468" ry="176" fill="#8D5A3B"/><ellipse cx="500" cy="432" rx="468" ry="176" fill="#7CCB6B" stroke="' . $L . '" stroke-width="6"/>'
        . '<path d="M90 398Q160 356 250 384" stroke="#A5E08F" stroke-width="16" stroke-linecap="round" fill="none"/><path d="M700 300Q790 280 880 330" stroke="#A5E08F" stroke-width="16" stroke-linecap="round" fill="none"/>'
        // đường đi
        . '<path d="M190 386Q330 420 500 290Q670 420 812 386M500 290V470M500 470Q400 500 318 556M500 470Q600 500 690 556" stroke="#F3D9A4" stroke-width="30" stroke-linecap="round" stroke-linejoin="round" fill="none"/>'
        . '<path d="M190 386Q330 420 500 290Q670 420 812 386M500 290V470M500 470Q400 500 318 556M500 470Q600 500 690 556" stroke="#fff" stroke-opacity=".7" stroke-width="4" stroke-dasharray="2 20" stroke-linecap="round" fill="none"/>'
        // cây
        . '<g stroke="' . $L . '" stroke-width="4"><rect x="92" y="470" width="12" height="30" fill="#8D5A3B"/><circle cx="98" cy="454" r="26" fill="#3BAA4A"/><rect x="892" y="460" width="12" height="30" fill="#8D5A3B"/><circle cx="898" cy="444" r="26" fill="#3BAA4A"/><rect x="494" y="560" width="12" height="26" fill="#8D5A3B"/><circle cx="500" cy="546" r="22" fill="#3BAA4A"/></g>';

    foreach (sw_places() as $p) {
        $w = 34 + mb_strlen($p['name']) * 11;
        $label = '<g class="lbl"><rect x="' . (-$w / 2) . '" y="12" width="' . $w . '" height="38" rx="19" fill="#fff" stroke="' . $L . '" stroke-width="4"/>'
            . '<text x="0" y="38" text-anchor="middle" font-size="19" font-weight="800" fill="' . ($p['open'] ? '#205984' : '#8C8075') . '" font-family="Be Vietnam Pro,system-ui,sans-serif">' . e($p['name']) . '</text></g>';
        $house = '<g class="hs">' . sw_house_art($p['key']) . '</g>';
        if ($p['open']) {
            $out .= '<a class="mh" href="' . e(sw_url($p['key'])) . '" aria-label="' . e($p['name'] . ': ' . $p['desc']) . '"><g transform="translate(' . $p['x'] . ' ' . $p['y'] . ')">' . $house . $label . '</g></a>';
        } else {
            $out .= '<g class="mh locked" transform="translate(' . $p['x'] . ' ' . $p['y'] . ')" aria-label="' . e($p['name']) . ' (sắp mở)">' . $house . $label
                . '<g transform="translate(58 -34) rotate(-6)"><rect x="-46" y="-16" width="92" height="32" rx="16" fill="#FDBE10" stroke="' . $L . '" stroke-width="4"/><text x="0" y="6" text-anchor="middle" font-size="16" font-weight="800" fill="#4A2610" font-family="Be Vietnam Pro,system-ui,sans-serif">Sắp mở</text></g></g>';
        }
    }
    // Góc ba mẹ: biển chỉ đường nhỏ
    $out .= '<a class="mh" href="' . e(sw_url('ba-me')) . '" aria-label="Góc ba mẹ: xem con đã học được gì"><g transform="translate(500 470)"><g class="hs" stroke="' . $L . '" stroke-width="4" stroke-linejoin="round"><rect x="-5" y="-50" width="10" height="50" fill="#8D5A3B"/><rect x="-58" y="-86" width="116" height="40" rx="10" fill="#FFF3C4"/>'
        . '<text x="0" y="-59" text-anchor="middle" font-size="18" font-weight="800" fill="#7A3F18" stroke="none" font-family="Be Vietnam Pro,system-ui,sans-serif">Góc ba mẹ</text></g></g></a>'
        // linh vật
        . '<use class="map-sun" href="#sunFace" x="556" y="318" width="110" height="110"/>'
        . '</svg>';
    return $out;
}
