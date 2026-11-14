<?php
/**
 * Sunny World — hình vẽ SVG: hình cho thẻ từ vựng / bìa truyện, linh vật Sunny theo cảm xúc, bản đồ thế giới.
 * Tất cả tự vẽ theo phong cách kawaii: viền nâu đậm, màu tươi, mắt đen bóng, má hồng, cùng nét với linh vật trang chủ.
 */
declare(strict_types=1);

const SW_LINE = '#2E241B';

/** Bảng màu dùng cho chủ đề "Colors" và nền số đếm. */
function sw_palette(): array
{
    return ['red' => '#F03E3E', 'blue' => '#339AF0', 'green' => '#40C057', 'yellow' => '#FFD43B', 'orange' => '#FF922B',
        'pink' => '#F783AC', 'purple' => '#9775FA', 'black' => '#2E241B'];
}

/** Trả về SVG của một hình theo khoá (ví dụ "cat", "color-red", "num-3"). Khoá lạ → chuỗi rỗng. */
function sw_art(string $key): string
{
    static $lib = null;
    if ($lib === null) {
        // Các nét dùng chung: mắt đen có đốm sáng, má hồng, miệng cười, vệt bóng, cả khuôn mặt.
        $eye = fn(float $x, float $y, float $r = 4) => '<ellipse cx="' . $x . '" cy="' . $y . '" rx="' . $r . '" ry="' . round($r * 1.25, 1) . '" fill="#2A1D17" stroke="none"/>'
            . '<circle cx="' . round($x - $r * .3, 1) . '" cy="' . round($y - $r * .45, 1) . '" r="' . round($r * .4, 1) . '" fill="#fff" stroke="none"/>';
        $blush = fn(float $x, float $y, string $c = '#FF8FA8') => '<ellipse cx="' . $x . '" cy="' . $y . '" rx="5" ry="3" fill="' . $c . '" stroke="none"/>';
        $smile = fn(float $x, float $y, float $w = 4.5) => '<path d="M' . ($x - $w) . ' ' . $y . 'Q' . $x . ' ' . ($y + $w) . ' ' . ($x + $w) . ' ' . $y . '" fill="none" stroke-width="2.2"/>';
        $hl = fn(string $d, string $c = '#fff') => '<path d="' . $d . '" fill="none" stroke="' . $c . '" stroke-opacity=".75" stroke-width="3.5"/>';
        $face = fn(float $cx, float $cy, float $g = 11, float $r = 4, string $bc = '#FF8FA8') => $eye($cx - $g, $cy, $r) . $eye($cx + $g, $cy, $r)
            . $smile($cx, $cy + $r * 1.8, $r * 1.1) . $blush($cx - $g - 7, $cy + $r * 1.9, $bc) . $blush($cx + $g + 7, $cy + $r * 1.9, $bc);
        // Nét màu có viền đậm (cuống, thân cây, vệt gió…)
        $ol = fn(string $d, string $c, float $w = 3) => '<path d="' . $d . '" fill="none" stroke-width="' . ($w + 4.5) . '"/><path d="' . $d . '" fill="none" stroke="' . $c . '" stroke-width="' . $w . '"/>';
        $cloud = 'M26 74Q8 74 8 58Q8 44 26 43Q29 22 50 22Q66 22 73 35Q92 34 94 54Q95 74 76 74Z';
        $cloudUp = 'M26 58Q10 58 10 44Q10 32 26 31Q29 12 50 12Q65 12 72 24Q90 23 92 42Q93 58 76 58Z';

        $lib = [
            // ---------- Con vật ----------
            'cat' => '<path d="M22 41L25 13L48 29Z" fill="#FFA94D"/><path d="M78 41L75 13L52 29Z" fill="#FFA94D"/>'
                . '<path d="M29 33L31 22L40 28Z" fill="#FFC9C9" stroke="none"/><path d="M71 33L69 22L60 28Z" fill="#FFC9C9" stroke="none"/>'
                . '<ellipse cx="50" cy="58" rx="35" ry="29" fill="#FFA94D"/><path d="M43 31V39M50 30V41M57 31V39" fill="none" stroke="#E8590C" stroke-width="3"/>'
                . $hl('M21 52Q24 41 33 36') . '<ellipse cx="50" cy="70" rx="15" ry="10" fill="#FFF4E6" stroke="none"/>' . $eye(35, 57, 4.6) . $eye(65, 57, 4.6)
                . '<path d="M47 64Q50 62.5 53 64Q50 68 47 64Z" fill="#FF8FA8" stroke-width="1.6"/><path d="M50 68Q47 73 42.5 70.5M50 68Q53 73 57.5 70.5" fill="none" stroke-width="2"/>'
                . $blush(25, 68) . $blush(75, 68),
            'dog' => '<ellipse cx="20" cy="58" rx="10.5" ry="21" transform="rotate(12 20 58)" fill="#A0522D"/><ellipse cx="80" cy="58" rx="10.5" ry="21" transform="rotate(-12 80 58)" fill="#A0522D"/>'
                . '<ellipse cx="50" cy="57" rx="31" ry="29" fill="#F0C59A"/><ellipse cx="64" cy="52" rx="12" ry="14" fill="#C98A4B" stroke="none"/>'
                . $hl('M25 49Q28 39 37 34') . '<ellipse cx="50" cy="71" rx="17.5" ry="12" fill="#FFF8F0" stroke="none"/>' . $eye(36, 54, 4.6) . $eye(64, 54, 4.6)
                . '<rect x="45.5" y="73" width="9" height="11" rx="4.5" fill="#FF8FA8" stroke-width="2"/><ellipse cx="50" cy="65" rx="5.6" ry="4" fill="#2A1D17" stroke="none"/>'
                . '<path d="M50 69V73M50 73Q46 77.5 41 74.5M50 73Q54 77.5 59 74.5" fill="none" stroke-width="2"/>' . $blush(25, 67) . $blush(75, 67),
            'bird' => '<path d="M50 29Q42 14 51 10Q50 19 59 14Q57.5 22.5 54 29Z" fill="#4DABF7" stroke-width="2.8"/>'
                . '<ellipse cx="19" cy="62" rx="9" ry="16" transform="rotate(20 19 62)" fill="#339AF0"/><ellipse cx="81" cy="62" rx="9" ry="16" transform="rotate(-20 81 62)" fill="#339AF0"/>'
                . $ol('M42.5 82V92M57.5 82V92', '#FFA94D', 2.6) . '<circle cx="50" cy="56" r="30" fill="#74C0FC"/><ellipse cx="50" cy="70" rx="17.5" ry="13" fill="#E7F5FF" stroke="none"/>'
                . $hl('M26 49Q29 39 37.5 34') . $eye(39, 51, 4.6) . $eye(61, 51, 4.6) . '<path d="M44.5 58.5H55.5L50 66Z" fill="#FFD43B" stroke-width="2.6"/>' . $blush(29, 62) . $blush(71, 62),
            'fish' => '<path d="M70 54L92 36Q87 54 92 71Z" fill="#FF8787"/><path d="M31 37.5Q46 16 61 37.5Z" fill="#FF8787"/><ellipse cx="45" cy="55" rx="31" ry="22.5" fill="#FF6B6B"/>'
                . '<path d="M19 65Q45 82.5 71 65Q45 74 19 65Z" fill="#FFC9C9" stroke="none"/><path d="M47.5 57.5Q60 60 57.5 71Q47.5 69 47.5 57.5Z" fill="#FF8787" stroke-width="2.6"/>'
                . $hl('M22.5 45Q29 37.5 39 35.5') . $eye(30, 52.5, 4.6) . '<path d="M17 62.5Q20.5 66 24.5 63.5" fill="none" stroke-width="2"/>' . $blush(38, 64, '#FFE3E3')
                . '<circle cx="10" cy="35" r="3.8" fill="#fff" stroke-width="2"/><circle cx="17.5" cy="22.5" r="2.8" fill="#fff" stroke-width="2"/>',
            'rabbit' => '<ellipse cx="37" cy="27" rx="9" ry="22" fill="#fff"/><ellipse cx="63" cy="27" rx="9" ry="22" fill="#fff"/>'
                . '<ellipse cx="37" cy="29" rx="4" ry="15" fill="#FFC9C9" stroke="none"/><ellipse cx="63" cy="29" rx="4" ry="15" fill="#FFC9C9" stroke="none"/>'
                . '<ellipse cx="50" cy="64" rx="30" ry="26" fill="#fff"/>' . $eye(38, 61, 4.4) . $eye(62, 61, 4.4)
                . '<path d="M47.5 68Q50 67 52.5 68Q50 71.5 47.5 68Z" fill="#FF8FA8" stroke-width="1.6"/><path d="M50 71.5Q47 76 43 74M50 71.5Q53 76 57 74" fill="none" stroke-width="2"/>'
                . $blush(27, 71) . $blush(73, 71),
            'duck' => '<path d="M50 25Q43 12 52 8Q51 17 59 13Q57 21 54 25Z" fill="#FFE066" stroke-width="2.8"/><circle cx="50" cy="55" r="31" fill="#FFE066"/>'
                . $hl('M25 48Q28 37 38 32') . $eye(38, 49, 4.6) . $eye(62, 49, 4.6) . '<ellipse cx="50" cy="67" rx="15" ry="8.5" fill="#FFA94D"/><path d="M38 67Q50 71 62 67" fill="none" stroke-width="2"/>'
                . $blush(26, 60) . $blush(74, 60),
            'pig' => '<path d="M24 37L20 16L41 27Z" fill="#F783AC"/><path d="M76 37L80 16L59 27Z" fill="#F783AC"/><circle cx="50" cy="57" r="33" fill="#FFB3C7"/>'
                . $hl('M23 51Q26 39 36 33') . $eye(36, 52, 4.6) . $eye(64, 52, 4.6) . '<ellipse cx="50" cy="68" rx="15" ry="10.5" fill="#F783AC"/>'
                . '<circle cx="44.5" cy="68" r="2.4" fill="#2A1D17" stroke="none"/><circle cx="55.5" cy="68" r="2.4" fill="#2A1D17" stroke="none"/>' . $blush(25, 65, '#FF6B9D') . $blush(75, 65, '#FF6B9D'),
            'cow' => '<path d="M29 30Q18 25 20 12Q31 16 35 26Z" fill="#FFE8CC"/><path d="M71 30Q82 25 80 12Q69 16 65 26Z" fill="#FFE8CC"/>'
                . '<ellipse cx="17" cy="45" rx="9" ry="6" transform="rotate(-20 17 45)" fill="#fff"/><ellipse cx="83" cy="45" rx="9" ry="6" transform="rotate(20 83 45)" fill="#fff"/>'
                . '<ellipse cx="50" cy="55" rx="30" ry="30" fill="#fff"/><ellipse cx="65" cy="40" rx="8" ry="8" fill="#5C4A3D" stroke="none"/><ellipse cx="30" cy="62" rx="5" ry="7" fill="#5C4A3D" stroke="none"/>'
                . $eye(38, 51, 4.4) . $eye(62, 51, 4.4) . '<ellipse cx="50" cy="73" rx="20" ry="12" fill="#FFC9C9"/>'
                . '<circle cx="43" cy="73" r="2.4" fill="#2A1D17" stroke="none"/><circle cx="57" cy="73" r="2.4" fill="#2A1D17" stroke="none"/>',
            'lion' => '<circle cx="50" cy="52" r="40" fill="#E8590C"/><g fill="#F76707" stroke="none"><circle cx="50" cy="15" r="6"/><circle cx="76" cy="26" r="6"/><circle cx="87" cy="52" r="6"/><circle cx="76" cy="78" r="6"/><circle cx="50" cy="89" r="6"/><circle cx="24" cy="78" r="6"/><circle cx="13" cy="52" r="6"/><circle cx="24" cy="26" r="6"/></g>'
                . '<circle cx="31" cy="31" r="8" fill="#FFC078"/><circle cx="69" cy="31" r="8" fill="#FFC078"/><circle cx="50" cy="54" r="27" fill="#FFC078"/>'
                . '<ellipse cx="50" cy="66" rx="13" ry="9" fill="#FFF4E6" stroke="none"/>' . $eye(39, 51, 4.4) . $eye(61, 51, 4.4)
                . '<path d="M46.5 60Q50 58.5 53.5 60Q50 64.5 46.5 60Z" fill="#A0522D" stroke-width="1.6"/><path d="M50 64.5Q47 69 43 67M50 64.5Q53 69 57 67" fill="none" stroke-width="2"/>'
                . $blush(31, 62) . $blush(69, 62),
            'tortoise' => '<rect x="24" y="70" width="11" height="17" rx="5" fill="#8BDD5E"/><rect x="58" y="70" width="11" height="17" rx="5" fill="#8BDD5E"/>'
                . '<path d="M14 72L6 76L15 78Z" fill="#8BDD5E"/><circle cx="82" cy="58" r="13" fill="#8BDD5E"/>'
                . '<path d="M14 73A32 32 0 0 1 78 73Z" fill="#40C057"/><path d="M32 73Q34 52 46 44M60 73Q58 52 46 44M22 60H70" fill="none" stroke="#2B8A3E" stroke-width="2.4"/>'
                . $hl('M24 58Q30 48 40 45') . '<rect x="11" y="69" width="70" height="8" rx="4" fill="#B2F2BB"/>' . $eye(85, 55, 3) . '<path d="M83 63Q87 66 91 63" fill="none" stroke-width="2"/>' . $blush(80, 64),

            // ---------- Trái cây ----------
            'apple' => $ol('M50 36Q50 26 56 19', '#A0522D', 2.6) . '<path d="M55 27.5Q65 12.5 80 21Q69 36 55 27.5Z" fill="#51CF66"/>'
                . '<path d="M50 36C37.5 26 16 32.5 16 57.5C16 79 34 90 42.5 90C46 90 47.5 88 50 88C52.5 88 54 90 57.5 90C66 90 84 79 84 57.5C84 32.5 62.5 26 50 36Z" fill="#FF6B6B"/>'
                . $hl('M25 55Q26 45 34 40') . $face(50, 60, 11, 4, '#FFC9C9'),
            'banana' => '<path d="M24 27.5Q21 70 60 82.5Q80 87.5 87.5 74Q65 74 49 57.5Q37.5 45 35 26Z" fill="#FFE066" stroke="none"/>'
                . '<path d="M24 31Q22.5 70 60 82.5Q80 87.5 87.5 74Q76 80 61 75.5Q31 65 30 30Z" fill="#FCC419" stroke="none"/>'
                . '<path d="M24 27.5Q21 70 60 82.5Q80 87.5 87.5 74Q65 74 49 57.5Q37.5 45 35 26Z" fill="none"/>'
                . '<path d="M24 29L35.5 27L34.5 17.5L25.5 19Z" fill="#A0522D" stroke-width="2.6"/><circle cx="87.5" cy="74" r="3.2" fill="#A0522D" stroke-width="2"/>'
                . '<circle cx="38" cy="56" r="2.6" fill="#2A1D17" stroke="none"/><circle cx="47.5" cy="64.5" r="2.6" fill="#2A1D17" stroke="none"/><path d="M37.5 64Q39.5 68 44 67.5" fill="none" stroke-width="2"/>',
            'orange' => '<circle cx="50" cy="56" r="34" fill="#FFA94D"/><path d="M50 22Q58 9 73 15Q65 30 50 22Z" fill="#51CF66"/><circle cx="50" cy="23" r="2.4" fill="#2B8A3E" stroke="none"/>'
                . $hl('M24 50Q26 36 38 29') . $face(50, 57, 11, 4, '#FFC9C9'),
            'grapes' => $ol('M50 22V10', '#A0522D', 2.6) . '<path d="M52 16Q64 5 77 13Q66 26 52 16Z" fill="#51CF66"/>'
                . '<g fill="#B197FC"><circle cx="30" cy="35" r="12"/><circle cx="70" cy="35" r="12"/><circle cx="50" cy="33" r="12"/><circle cx="50" cy="72" r="12"/><circle cx="39" cy="53" r="12.5"/><circle cx="61" cy="53" r="12.5"/></g>'
                . $hl('M22 33Q24 27 30 26') . $hl('M43 30Q45 25 51 24') . $eye(39, 52, 3.4) . $eye(61, 52, 3.4) . $smile(50, 63, 3.6),
            'strawberry' => '<path d="M50 30C26 22 12 42 20 60C27 76 42 90 50 93C58 90 73 76 80 60C88 42 74 22 50 30Z" fill="#FF6B6B"/>'
                . '<path d="M28 30Q38 16 50 25Q62 16 72 30Q61 39 50 32Q39 39 28 30Z" fill="#51CF66"/>' . $ol('M50 26V15', '#2B8A3E', 2.4)
                . '<g fill="#FFE066" stroke="none"><ellipse cx="31" cy="50" rx="2" ry="3"/><ellipse cx="69" cy="50" rx="2" ry="3"/><ellipse cx="38" cy="76" rx="2" ry="3"/><ellipse cx="62" cy="76" rx="2" ry="3"/><ellipse cx="50" cy="84" rx="2" ry="3"/></g>'
                . $hl('M24 52Q25 44 31 40') . $face(50, 57, 10, 3.8, '#FFC9C9'),
            'watermelon' => '<path d="M8 38A42 42 0 0 0 92 38Z" fill="#51CF66"/><path d="M14 39A36 36 0 0 0 86 39Z" fill="#D3F9D8" stroke="none"/><path d="M19 39A31 31 0 0 0 81 39Z" fill="#FF6B6B" stroke="none"/>'
                . '<path d="M8 38H92" fill="none"/><g fill="#2A1D17" stroke="none"><ellipse cx="28" cy="46" rx="1.8" ry="2.8"/><ellipse cx="72" cy="46" rx="1.8" ry="2.8"/><ellipse cx="50" cy="64" rx="1.8" ry="2.8"/></g>'
                . $eye(40, 48, 3.6) . $eye(60, 48, 3.6) . $smile(50, 55, 3.8),
            'lemon' => '<path d="M10 52Q28 20 58 24Q78 26 91 50Q78 76 58 78Q28 82 10 52Z" fill="#FFE066"/><path d="M57 24Q63 9 78 12Q73 27 61 28Z" fill="#51CF66"/>'
                . $hl('M22 48Q28 36 40 31') . $face(50, 52, 11, 4),
            'cherry' => $ol('M32 54Q40 28 62 14Q60 34 68 52', '#2B8A3E', 2.4) . '<path d="M62 14Q75 5 88 14Q77 26 62 14Z" fill="#51CF66"/>'
                . '<circle cx="32" cy="70" r="19" fill="#FF6B6B"/><circle cx="68" cy="68" r="19" fill="#F03E3E"/>' . $hl('M19 66Q20 59 26 56') . $hl('M55 64Q56 57 62 54')
                . $eye(27, 70, 2.8) . $eye(37, 70, 2.8) . $smile(32, 76, 3) . $eye(63, 68, 2.8) . $eye(73, 68, 2.8) . $smile(68, 74, 3),

            // ---------- Trường học ----------
            'book' => '<path d="M6 30L50 34L94 30V86L50 90L6 86Z" fill="#4DABF7"/><path d="M10 24Q30 16 50 26V82Q30 72 10 80Z" fill="#fff"/><path d="M90 24Q70 16 50 26V82Q70 72 90 80Z" fill="#FFF4E6"/>'
                . '<path d="M18 36Q30 31 42 36M18 47Q30 42 42 47M18 58Q30 53 42 58" fill="none" stroke="#A5D8FF" stroke-width="2.4"/>' . $eye(62, 45, 3.4) . $eye(78, 45, 3.4) . $smile(70, 52, 3.6) . $blush(57, 53) . $blush(83, 53),
            'pencil' => '<g transform="rotate(-40 50 50)"><rect x="5" y="39.5" width="15" height="21" rx="5.5" fill="#FF8FA8"/><rect x="15" y="39.5" width="9" height="21" fill="#CED4DA"/>'
                . '<rect x="24" y="39.5" width="47" height="21" fill="#FFD43B" stroke="none"/><rect x="24" y="53" width="47" height="7.5" fill="#FAB005" stroke="none"/><rect x="24" y="39.5" width="47" height="21" fill="none"/>'
                . '<path d="M71 39.5L94 50L71 60.5Z" fill="#FFE8CC"/><path d="M86.5 46.5L94 50L86.5 53.5Z" fill="#2E241B" stroke-width="1.2"/>'
                . '<circle cx="40" cy="47.5" r="2.5" fill="#2A1D17" stroke="none"/><circle cx="55" cy="47.5" r="2.5" fill="#2A1D17" stroke="none"/><path d="M44 51.5Q47.5 55 51 51.5" fill="none" stroke-width="2"/></g>',
            'bag' => '<path d="M37 30Q37 12 50 12Q63 12 63 30" fill="none" stroke-width="8"/><path d="M37 30Q37 12 50 12Q63 12 63 30" fill="none" stroke="#E8590C" stroke-width="3"/>'
                . '<rect x="19" y="27" width="62" height="64" rx="18" fill="#FFA94D"/>' . $hl('M26 50Q26 38 36 34') . '<rect x="30" y="60" width="40" height="23" rx="8" fill="#FFD43B"/><path d="M44 60V67H56V60" fill="none" stroke-width="2.4"/>'
                . $eye(40, 43, 3.6) . $eye(60, 43, 3.6) . $smile(50, 50, 3.8) . $blush(31, 51) . $blush(69, 51),
            'ruler' => '<g transform="rotate(-30 50 50)"><rect x="6" y="37" width="88" height="26" rx="6" fill="#FFE066"/><path d="M18 37V47M30 37V43M42 37V47M54 37V43M66 37V47M78 37V43" fill="none" stroke-width="2.2"/>'
                . '<circle cx="42" cy="54" r="2.4" fill="#2A1D17" stroke="none"/><circle cx="56" cy="54" r="2.4" fill="#2A1D17" stroke="none"/><path d="M46 57.5Q49 60 52 57.5" fill="none" stroke-width="1.8"/></g>',
            'eraser' => '<g transform="rotate(-20 50 50)"><rect x="12" y="33" width="76" height="34" rx="9" fill="#fff"/><path d="M38 33H79Q88 33 88 42V58Q88 67 79 67H38Z" fill="#4DABF7"/>'
                . $hl('M18 46Q19 40 25 38') . $eye(54, 47, 3.4) . $eye(72, 47, 3.4) . $smile(63, 54, 3.6) . $blush(48, 55) . $blush(78, 55) . '</g>',
            'clock' => '<circle cx="27" cy="19" r="9" fill="#FFD43B"/><circle cx="73" cy="19" r="9" fill="#FFD43B"/><path d="M28 82L21 92M72 82L79 92" fill="none" stroke-width="5"/>'
                . '<circle cx="50" cy="52" r="37" fill="#FF6B6B"/><circle cx="50" cy="52" r="28" fill="#fff"/><path d="M50 28V32M50 72V76M26 52H30M70 52H74" fill="none" stroke-width="2.4"/>'
                . '<path d="M50 52V36M50 52L61 58" fill="none" stroke-width="3.2"/><circle cx="50" cy="52" r="2.6" fill="#2E241B" stroke="none"/>' . $blush(36, 62) . $blush(64, 62),
            'board' => '<path d="M30 72L22 92M70 72L78 92" fill="none" stroke-width="5"/><rect x="9" y="17" width="82" height="58" rx="7" fill="#C98A4B"/><rect x="16" y="24" width="68" height="44" rx="3" fill="#2F9E44"/>'
                . '<text x="50" y="54" text-anchor="middle" font-size="22" font-weight="900" fill="#fff" stroke="none" font-family="Be Vietnam Pro,system-ui,sans-serif">ABC</text>'
                . '<rect x="58" y="72" width="16" height="6" rx="3" fill="#fff" stroke-width="2"/>',
            'chair' => '<path d="M30 62V90M70 62V90" fill="none" stroke-width="6"/><path d="M32 40V54M68 40V54" fill="none" stroke-width="6"/>'
                . '<rect x="26" y="14" width="48" height="28" rx="8" fill="#FFA94D"/><rect x="22" y="52" width="56" height="13" rx="6" fill="#FFA94D"/>'
                . $eye(42, 26, 3) . $eye(58, 26, 3) . $smile(50, 32, 3.2),

            // ---------- Thời tiết ----------
            'sun' => '<g fill="#FFA94D">' . implode('', array_map(fn(int $a) => '<path transform="translate(50 52) rotate(' . $a . ')" d="M-7 -27C-7 -38 3 -45 7 -49C12 -43 12 -36 8 -27Z"/>', range(0, 320, 40))) . '</g>'
                . '<circle cx="50" cy="52" r="29" fill="#FFD43B"/>' . $hl('M29 46Q32 35 42 30') . $eye(40, 50, 4.4) . $eye(60, 50, 4.4)
                . '<path d="M43 59Q50 71 57 59Z" fill="#B3261E" stroke-width="2.2"/>' . $blush(30, 60, '#FF8A65') . $blush(70, 60, '#FF8A65'),
            'cloud' => '<path d="' . $cloud . '" fill="#fff"/>' . $hl('M19 52Q22 40 34 35', '#C5E4FB') . $face(50, 52, 11, 4),
            'rain' => '<path d="' . $cloudUp . '" fill="#A5D8FF"/>' . $hl('M20 40Q23 28 34 24') . $face(50, 37, 10, 3.6)
                . '<g fill="#4DABF7" stroke-width="2.6"><path d="M30 67Q37 78 30 83Q23 78 30 67Z"/><path d="M50 74Q57 85 50 90Q43 85 50 74Z"/><path d="M70 67Q77 78 70 83Q63 78 70 67Z"/></g>',
            'snow' => '<path d="' . $cloudUp . '" fill="#E7F5FF"/>' . $hl('M20 40Q23 28 34 24', '#fff') . $face(50, 37, 10, 3.6)
                . '<g fill="none" stroke="#339AF0" stroke-width="2.6"><path d="M30 68V84M23 72L37 80M23 80L37 72"/><path d="M50 75V91M43 79L57 87M43 87L57 79"/><path d="M70 68V84M63 72L77 80M63 80L77 72"/></g>',
            'wind' => $ol('M10 34H58Q72 34 72 24Q72 15 62 16', '#74C0FC', 4) . $ol('M10 52H74Q90 52 90 64Q90 74 78 72', '#A5D8FF', 4) . $ol('M10 70H46Q57 70 57 79', '#74C0FC', 4)
                . '<path d="M26 20Q34 10 46 16Q38 26 26 20Z" fill="#51CF66" stroke-width="2.6"/>',
            'rainbow' => '<g fill="none"><path d="M6 80A44 44 0 0 1 94 80" stroke-width="3.5"/><path d="M10 80A40 40 0 0 1 90 80" stroke="#FF6B6B" stroke-width="8"/><path d="M18 80A32 32 0 0 1 82 80" stroke="#FFD43B" stroke-width="8"/>'
                . '<path d="M26 80A24 24 0 0 1 74 80" stroke="#69DB7C" stroke-width="8"/><path d="M34 80A16 16 0 0 1 66 80" stroke="#4DABF7" stroke-width="8"/><path d="M38 80A12 12 0 0 1 62 80" stroke-width="3.5"/></g>'
                . '<path d="M4 90Q-2 90 0 82Q2 76 8 77Q10 68 20 69Q29 70 30 78Q38 79 37 86Q36 90 30 90Z" fill="#fff" stroke-width="3"/><path d="M96 90Q102 90 100 82Q98 76 92 77Q90 68 80 69Q71 70 70 78Q62 79 63 86Q64 90 70 90Z" fill="#fff" stroke-width="3"/>',

            // ---------- Hình khối ----------
            'circle' => '<circle cx="50" cy="50" r="37" fill="#4DABF7"/>' . $hl('M22 44Q25 30 38 24') . $face(50, 50, 12, 4.4),
            'square' => '<rect x="14" y="14" width="72" height="72" rx="9" fill="#FF6B6B"/>' . $hl('M22 40V28Q22 22 28 22H38') . $face(50, 50, 12, 4.4, '#FFC9C9'),
            'triangle' => '<path d="M50 11L91 85H9Z" fill="#51CF66"/>' . $hl('M30 62L44 36') . $face(50, 62, 10, 4),
            'rectangle' => '<rect x="7" y="27" width="86" height="46" rx="9" fill="#FFA94D"/>' . $hl('M14 46V40Q14 34 20 34H30') . $face(50, 49, 12, 4.2),
            'star' => '<path d="M50 8L62 36L92 38L69 58L77 88L50 71L23 88L31 58L8 38L38 36Z" fill="#FFD43B"/>' . $hl('M37 42L44 27') . $face(50, 52, 9, 3.8),
            'heart' => '<path d="M50 87C16 63 8 45 14 31C20 15 42 15 50 33C58 15 80 15 86 31C92 45 84 63 50 87Z" fill="#FF6B8B"/>' . $hl('M22 40Q22 30 31 26') . $face(50, 46, 11, 4, '#FFC9C9'),

            // ---------- Khác ----------
            'ball' => '<circle cx="50" cy="50" r="37" fill="#FF6B6B"/><path d="M19 34Q50 50 81 34M19 66Q50 50 81 66M50 13V87" fill="none" stroke="#fff" stroke-width="3.2"/><circle cx="50" cy="50" r="37" fill="none"/>',
            'sky' => '<g fill="#FFA94D" stroke-width="2.6">' . implode('', array_map(fn(int $a) => '<path transform="translate(68 32) rotate(' . $a . ')" d="M-4 -19L0 -29L4 -19Z"/>', range(0, 315, 45))) . '</g>'
                . '<circle cx="68" cy="32" r="17" fill="#FFD43B"/><path d="M22 84Q4 84 4 68Q4 55 21 54Q24 36 44 36Q59 36 65 48Q83 47 85 66Q86 84 68 84Z" fill="#fff"/>' . $face(44, 64, 10, 3.8),
        ];
    }
    $inner = $lib[$key] ?? null;
    if ($inner === null && str_starts_with($key, 'color-')) {
        $name = substr($key, 6);
        $c = sw_palette()[$name] ?? null;
        if ($c) {
            $ink = $name === 'black' ? '#fff' : '#2A1D17';
            $inner = '<path d="M50 12C72 10 90 28 86 50C92 70 72 90 50 86C28 92 10 72 14 50C8 30 28 12 50 12Z" fill="' . $c . '"/><path d="M27 40Q30 28 44 23" stroke="#fff" stroke-opacity=".6" fill="none"/>'
                . '<ellipse cx="38" cy="50" rx="4.2" ry="5.2" fill="' . $ink . '" stroke="none"/><ellipse cx="62" cy="50" rx="4.2" ry="5.2" fill="' . $ink . '" stroke="none"/>'
                . ($name === 'black' ? '' : '<circle cx="36.7" cy="48" r="1.7" fill="#fff" stroke="none"/><circle cx="60.7" cy="48" r="1.7" fill="#fff" stroke="none"/>')
                . '<path d="M45 58Q50 63 55 58" fill="none" stroke="' . $ink . '" stroke-width="2.2"/>'
                . '<ellipse cx="29" cy="59" rx="5" ry="3" fill="#fff" fill-opacity=".45" stroke="none"/><ellipse cx="71" cy="59" rx="5" ry="3" fill="#fff" fill-opacity=".45" stroke="none"/>';
        }
    }
    if ($inner === null && preg_match('/^num-(\d{1,2})$/', $key, $m)) {
        $bg = array_values(sw_palette())[((int) $m[1] - 1) % 7];
        $inner = '<rect x="10" y="10" width="80" height="80" rx="24" fill="' . $bg . '"/><path d="M19 40V32Q19 20 31 20H40" stroke="#fff" stroke-opacity=".6" fill="none"/>'
            . '<text x="50" y="69" text-anchor="middle" font-size="' . ((int) $m[1] > 9 ? 44 : 56) . '" font-weight="900" fill="#fff" stroke="#2E241B" stroke-width="5" paint-order="stroke" font-family="Be Vietnam Pro,system-ui,sans-serif">' . (int) $m[1] . '</text>';
    }
    if ($inner === null) {
        return '';
    }
    return '<svg class="art" viewBox="0 0 100 100" aria-hidden="true" focusable="false"><g stroke="' . SW_LINE . '" stroke-width="3.5" stroke-linejoin="round" stroke-linecap="round">' . $inner . '</g></svg>';
}

/** Danh sách khoá hình có sẵn (cho admin chọn). */
function sw_art_keys(): array
{
    return ['cat', 'dog', 'bird', 'fish', 'rabbit', 'duck', 'pig', 'cow', 'lion', 'tortoise', 'apple', 'banana', 'orange', 'grapes',
        'strawberry', 'watermelon', 'lemon', 'cherry', 'book', 'pencil', 'bag', 'ruler', 'eraser', 'clock', 'board', 'chair',
        'sun', 'cloud', 'rain', 'snow', 'wind', 'rainbow', 'circle', 'square', 'triangle', 'rectangle', 'star', 'heart', 'ball', 'sky'];
}

/* ====================== LINH VẬT SUNNY THEO CẢM XÚC ====================== */

/**
 * Phần thân + khuôn mặt của Sunny trong khung 400 x 428 (cùng nét với linh vật trang chủ).
 * $mood: hello (chào), cheer (khen), think (suy nghĩ), oops (động viên thử lại).
 * Cần sun_defs() trên trang (gradient #gSun, #gRay).
 */
function sw_sunny_inner(string $mood = 'hello'): string
{
    static $n = 0;
    $n++;
    $L = SW_LINE;
    $base = sun_rays(9, 200, 205, 88, 74, 66, 26, 7)
        . '<ellipse cx="164" cy="318" rx="30" ry="20" fill="#F7931E" stroke="' . $L . '" stroke-width="7"/><path d="M146 314 Q158 305 174 309" stroke="#FFD66B" stroke-width="5" stroke-linecap="round" fill="none"/>'
        . '<ellipse cx="236" cy="318" rx="30" ry="20" fill="#F7931E" stroke="' . $L . '" stroke-width="7"/><path d="M218 309 Q230 305 246 314" stroke="#FFD66B" stroke-width="5" stroke-linecap="round" fill="none"/>'
        . '<circle cx="200" cy="205" r="102" fill="url(#gSun)" stroke="' . $L . '" stroke-width="8"/>'
        . '<path d="M126 152 Q144 118 188 106" stroke="#fff" stroke-opacity=".8" stroke-width="12" stroke-linecap="round" fill="none"/><circle cx="212" cy="102" r="5" fill="#fff" fill-opacity=".85"/>'
        . '<ellipse cx="120" cy="246" rx="20" ry="13" fill="#FF6F4E" fill-opacity=".72"/><ellipse cx="280" cy="246" rx="20" ry="13" fill="#FF6F4E" fill-opacity=".72"/>';
    $eyes = fn(float $dy, float $sx, float $sy) => '<ellipse cx="158" cy="' . (200 + $dy) . '" rx="26" ry="33" fill="#2A1D17"/><circle cx="' . (158 + $sx) . '" cy="' . (200 + $dy + $sy) . '" r="11" fill="#fff"/><circle cx="' . (158 - $sx * 1.1) . '" cy="' . (214 + $dy) . '" r="5" fill="#fff"/>'
        . '<ellipse cx="242" cy="' . (200 + $dy) . '" rx="26" ry="33" fill="#2A1D17"/><circle cx="' . (242 + $sx) . '" cy="' . (200 + $dy + $sy) . '" r="11" fill="#fff"/><circle cx="' . (242 - $sx * 1.1) . '" cy="' . (214 + $dy) . '" r="5" fill="#fff"/>';
    $mouth = fn(string $d, int $ty, int $rx, int $ry) => '<clipPath id="swm' . $n . '"><path d="' . $d . '"/></clipPath><path d="' . $d . '" fill="#B3261E"/>'
        . '<g clip-path="url(#swm' . $n . ')"><ellipse cx="200" cy="' . $ty . '" rx="' . $rx . '" ry="' . $ry . '" fill="#FF7C8A"/></g><path d="' . $d . '" fill="none" stroke="' . $L . '" stroke-width="6" stroke-linejoin="round"/>';
    switch ($mood) {
        case 'cheer':
            return '<g transform="translate(0 -22) rotate(-6 200 205)">' . $base
                . '<path d="M134 210 Q158 172 182 210M218 210 Q242 172 266 210" fill="none" stroke="#2A1D17" stroke-width="11" stroke-linecap="round"/>'
                . $mouth('M150 238 Q200 326 250 238Z', 296, 36, 22) . '</g>';
        case 'think':
            return '<g transform="rotate(7 200 205)">' . $base
                . '<path d="M134 156 Q158 148 182 158M220 146 Q246 130 268 148" fill="none" stroke="' . $L . '" stroke-width="7" stroke-linecap="round"/>'
                . $eyes(0, 9, -14) . '<ellipse cx="208" cy="264" rx="13" ry="11" fill="#B3261E" stroke="' . $L . '" stroke-width="6"/></g>';
        case 'oops':
            return $base . '<path d="M132 162 L180 148M268 162 L220 148" fill="none" stroke="' . $L . '" stroke-width="7" stroke-linecap="round"/>'
                . $eyes(4, -9, -12) . '<circle cx="150" cy="222" r="3.5" fill="#fff"/><circle cx="234" cy="222" r="3.5" fill="#fff"/>'
                . '<path d="M172 268 Q186 252 200 264 Q214 276 228 260" fill="none" stroke="' . $L . '" stroke-width="8" stroke-linecap="round"/>'
                . '<path d="M322 84 Q344 118 322 132 Q300 118 322 84Z" fill="#74C0FC" stroke="' . $L . '" stroke-width="5" stroke-linejoin="round"/>';
    }
    return $base . $eyes(0, -9, -12) . $mouth('M160 244 Q200 308 240 244Z', 288, 30, 18);
}

/** Sunny hoàn chỉnh (kèm bóng và hoạ tiết trang trí theo cảm xúc). */
function sw_sunny(string $mood = 'hello', string $label = 'Linh vật Sunny'): string
{
    $L = SW_LINE;
    $spark = fn(int $x, int $y, float $s, string $fill) => '<path transform="translate(' . $x . ' ' . $y . ') scale(' . $s . ')" d="M0 -13 Q2 -2 13 0 Q2 2 0 13 Q-2 2 -13 0 Q-2 -2 0 -13Z" fill="' . $fill . '" stroke="' . $L . '" stroke-width="2.5" stroke-linejoin="round"/>';
    $star = fn(int $x, int $y, int $rot, string $fill) => '<path transform="translate(' . $x . ' ' . $y . ') rotate(' . $rot . ')" d="M0 -22 L6.5 -7.5 L21 -6.8 L9.6 3.3 L13 18 L0 10 L-13 18 L-9.6 3.3 L-21 -6.8 L-6.5 -7.5Z" fill="' . $fill . '" stroke="' . $L . '" stroke-width="4" stroke-linejoin="round"/>';
    $extra = match ($mood) {
        'cheer' => $spark(46, 96, 1.5, '#FFC20E') . $spark(360, 300, 1.2, '#fff') . $star(350, 84, 12, '#FFD43B') . $star(42, 292, -14, '#FF8FA8'),
        'think' => '<text x="346" y="104" text-anchor="middle" font-size="96" font-weight="900" fill="#4DABF7" stroke="' . $L . '" stroke-width="7" stroke-linejoin="round" paint-order="stroke" transform="rotate(14 346 74)" font-family="Be Vietnam Pro,system-ui,sans-serif">?</text>',
        'oops' => '',
        default => $spark(52, 110, 1, '#FFC20E') . $spark(352, 92, 1, '#fff') . $spark(362, 300, 1, '#FFC20E')
            . '<path transform="translate(40 262) scale(1.3)" d="M0 8 C-14 -2 -12 -14 -5 -14 C-1 -14 0 -10 0 -9 C0 -10 1 -14 5 -14 C12 -14 14 -2 0 8Z" fill="#FF6F8E" stroke="' . $L . '" stroke-width="2.5" stroke-linejoin="round"/>',
    };
    return '<svg class="sunny sunny-' . e($mood) . '" viewBox="0 0 400 428" role="img" aria-label="' . e($label) . '" focusable="false">'
        . '<ellipse cx="200" cy="404" rx="' . ($mood === 'cheer' ? 78 : 110) . '" ry="11" fill="' . $L . '" fill-opacity=".14"/>' . $extra . sw_sunny_inner($mood) . '</svg>';
}

/** Cảnh nền cho khung tranh của trang đọc truyện: Sunny và mây cười ở trên, đồi cỏ ở dưới. */
function sw_scene(bool $withSun = true): string
{
    $L = SW_LINE;
    return ($withSun ? '<svg class="rd-sun" viewBox="0 0 200 200" aria-hidden="true" focusable="false"><use href="#sunFace"/></svg>' : '')
        . '<svg class="rd-cloud" viewBox="398 34 156 94" aria-hidden="true" focusable="false"><g stroke="' . $L . '" stroke-width="5" stroke-linejoin="round" stroke-linecap="round">'
        . '<path d="M430 122 Q404 122 404 98 Q404 76 428 73 Q433 40 470 40 Q496 40 508 58 Q540 55 546 88 Q549 122 520 122Z" fill="#fff"/>'
        . '<ellipse cx="462" cy="88" rx="5.5" ry="7" fill="#2A1D17" stroke="none"/><ellipse cx="494" cy="88" rx="5.5" ry="7" fill="#2A1D17" stroke="none"/>'
        . '<path d="M471 99 Q478 106 485 99" fill="none" stroke-width="3"/><ellipse cx="447" cy="99" rx="7" ry="4.5" fill="#FF8FA8" stroke="none"/><ellipse cx="509" cy="99" rx="7" ry="4.5" fill="#FF8FA8" stroke="none"/></g></svg>'
        . '<svg class="rd-hills" viewBox="0 380 600 140" preserveAspectRatio="xMidYMax slice" aria-hidden="true" focusable="false"><g stroke="' . $L . '" stroke-width="5" stroke-linejoin="round">'
        . '<ellipse cx="130" cy="520" rx="360" ry="130" fill="#B2F2BB"/><ellipse cx="500" cy="540" rx="300" ry="110" fill="#8CE99A"/>'
        . '<path d="M-10 462 Q150 440 300 462 T610 462 V530 H-10Z" fill="#69DB7C"/>'
        . '<g stroke-width="3"><circle cx="180" cy="486" r="8" fill="#FF8FA8"/><circle cx="420" cy="492" r="8" fill="#FFD43B"/><circle cx="250" cy="506" r="8" fill="#fff"/><circle cx="360" cy="508" r="8" fill="#FF8FA8"/></g>'
        . '<g fill="#FFD43B" stroke="none"><circle cx="180" cy="486" r="2.5"/><circle cx="250" cy="506" r="2.5"/><circle cx="360" cy="508" r="2.5"/></g><circle cx="420" cy="492" r="2.5" fill="#FF6B6B" stroke="none"/>'
        . '</g></svg>';
}

/* ====================== BẢN ĐỒ ====================== */

/**
 * Các khu trên bản đồ. 'open' = đã mở ở đợt này.
 * x, y = góc trên bên trái của ngôi nhà trên bản đồ (khung 1280 x 800, nhà rộng 240, chân nhà ở y + 206).
 */
function sw_places(): array
{
    return [
        ['key' => 'tu-vung', 'name' => 'Vườn Từ vựng', 'desc' => 'Lật thẻ học từ mới theo chủ đề', 'x' => 100, 'y' => 234, 'open' => true, 'color' => '#51CF66'],
        ['key' => 'truyen', 'name' => 'Thư viện Truyện', 'desc' => 'Đọc truyện, chạm vào từ để xem nghĩa', 'x' => 380, 'y' => 164, 'open' => true, 'color' => '#B197FC'],
        ['key' => 'tro-choi', 'name' => 'Nhà Trò chơi', 'desc' => 'Chơi mà học từ vựng', 'x' => 680, 'y' => 184, 'open' => false, 'color' => '#3BC9DB'],
        ['key' => 'khoa-hoc', 'name' => 'Trạm Khoa học', 'desc' => 'Đố vui kiến thức', 'x' => 950, 'y' => 244, 'open' => false, 'color' => '#5C7CFA'],
    ];
}

/** Hình một ngôi nhà trong khung 240 x 220 (chân nhà ở y = 206). */
function sw_house_art(string $key): string
{
    $s = '<g stroke="' . SW_LINE . '" stroke-width="5" stroke-linejoin="round" stroke-linecap="round">';
    $shine = fn(float $x, float $y, float $r = 4.5) => '<circle cx="' . $x . '" cy="' . $y . '" r="' . $r . '" fill="#fff" stroke="none"/>';
    $blush = fn(float $x, float $y) => '<ellipse cx="' . $x . '" cy="' . $y . '" rx="9" ry="5" fill="#FF8FA8" stroke="none"/>';
    $stem = fn(string $d) => '<path d="' . $d . '" fill="none" stroke-width="11"/><path d="' . $d . '" fill="none" stroke="#51CF66" stroke-width="5"/>';
    $txt = fn(float $x, float $y, string $t, string $fill) => '<text x="' . $x . '" y="' . $y . '" text-anchor="middle" font-size="21" font-weight="900" fill="' . $fill . '" font-family="Be Vietnam Pro,system-ui,sans-serif">' . $t . '</text>';
    switch ($key) {
        case 'tu-vung': // nhà mái lá giữa ba bông hoa chữ cái
            return $s . $stem('M26 204 V152') . '<circle cx="26" cy="136" r="20" fill="#FF6B8B"/>' . $stem('M214 204 V148') . '<circle cx="214" cy="132" r="20" fill="#4DABF7"/>'
                . $stem('M120 64 V42') . '<circle cx="120" cy="26" r="20" fill="#FFD43B"/>'
                . '<path d="M52 112 H188 V190 Q188 206 172 206 H68 Q52 206 52 190Z" fill="#FFF4D6"/><path d="M28 116 Q120 6 212 116 Q212 128 200 128 H40 Q28 128 28 116Z" fill="#51CF66"/>'
                . '<circle cx="96" cy="96" r="9" fill="#B2F2BB" stroke="none"/><circle cx="142" cy="74" r="8" fill="#B2F2BB" stroke="none"/><circle cx="168" cy="106" r="7" fill="#B2F2BB" stroke="none"/>'
                . '<path d="M54 104 Q72 64 104 46" fill="none" stroke="#fff" stroke-opacity=".7" stroke-width="6"/>'
                . '<path d="M104 206 V170 A16 16 0 0 1 136 170 V206Z" fill="#FF8FA8"/><circle cx="129" cy="188" r="3" fill="#2E241B" stroke="none"/>'
                . '<circle cx="78" cy="160" r="15" fill="#A5E3FF"/>' . $shine(73, 155) . '<circle cx="162" cy="160" r="15" fill="#A5E3FF"/>' . $shine(157, 155) . $blush(78, 187) . $blush(162, 187) . '</g>'
                . $txt(26, 143.5, 'A', '#fff') . $txt(214, 139.5, 'C', '#fff') . $txt(120, 33.5, 'B', '#2E241B');
        case 'truyen': // nhà có mái là quyển sách mở
            return $s . '<rect x="6" y="190" width="40" height="14" rx="4" fill="#FF6B6B" stroke-width="4"/><rect x="10" y="176" width="32" height="14" rx="4" fill="#FFD43B" stroke-width="4"/><rect x="8" y="162" width="36" height="14" rx="4" fill="#51CF66" stroke-width="4"/>'
                . '<path d="M54 104 H186 V190 Q186 206 170 206 H70 Q54 206 54 190Z" fill="#B197FC"/><path d="M12 110 L120 64 L228 110 V96 L120 50 L12 96Z" fill="#FF6B6B"/>'
                . '<path d="M22 94 L120 52 V22 L22 64Z" fill="#fff"/><path d="M218 94 L120 52 V22 L218 64Z" fill="#FFF4D6"/>'
                . '<path d="M42 74 L100 49 M42 86 L100 61" fill="none" stroke="#A5D8FF" stroke-width="4"/><path d="M198 74 L140 49 M198 86 L140 61" fill="none" stroke="#FFC078" stroke-width="4"/>'
                . '<path d="M164 70 V116 L174 106 L184 116 V62Z" fill="#FFD43B" stroke-width="4"/>'
                . '<path d="M104 206 V172 A16 16 0 0 1 136 172 V206Z" fill="#FFD43B"/><circle cx="129" cy="190" r="3" fill="#2E241B" stroke="none"/>'
                . '<rect x="66" y="132" width="28" height="28" rx="8" fill="#C5F6FA"/>' . $shine(74, 140, 4) . '<rect x="146" y="136" width="28" height="28" rx="8" fill="#C5F6FA"/>' . $shine(154, 144, 4) . $blush(80, 176) . $blush(160, 180) . '</g>';
        case 'tro-choi': // nhà mái hồng chấm bi, bóng bay và xúc xắc
            return $s . '<path d="M56 150 Q42 124 46 100 M56 150 Q30 138 24 120" fill="none" stroke-width="3"/><ellipse cx="22" cy="100" rx="16" ry="20" fill="#FF6B6B" stroke-width="4"/>'
                . '<ellipse cx="46" cy="76" rx="18" ry="23" fill="#FFD43B" stroke-width="4"/><path d="M38 70 Q40 62 46 60" fill="none" stroke="#fff" stroke-opacity=".8" stroke-width="4"/>'
                . '<path d="M120 36 V8" fill="none"/><path d="M120 8 L150 16 L120 26Z" fill="#FFD43B" stroke-width="4"/>'
                . '<path d="M56 104 H184 V190 Q184 206 168 206 H72 Q56 206 56 190Z" fill="#3BC9DB"/><path d="M38 106 L120 36 L202 106Z" fill="#F783AC"/>'
                . $shine(80, 94, 5.5) . $shine(160, 94, 5.5) . $shine(120, 54, 4.5) . '<circle cx="120" cy="84" r="13" fill="#FFF3BF" stroke-width="4"/>'
                . '<path d="M74 206 V170 A16 16 0 0 1 106 170 V206Z" fill="#FFD43B"/><circle cx="99" cy="188" r="3" fill="#2E241B" stroke="none"/>'
                . '<rect x="124" y="134" width="34" height="30" rx="8" fill="#C5F6FA"/>' . $shine(133, 143, 4) . $blush(141, 178)
                . '<g transform="rotate(8 198 180)"><rect x="174" y="156" width="48" height="48" rx="11" fill="#fff"/><g fill="#2E241B" stroke="none"><circle cx="186" cy="168" r="4.5"/><circle cx="210" cy="168" r="4.5"/><circle cx="198" cy="180" r="4.5"/><circle cx="186" cy="192" r="4.5"/><circle cx="210" cy="192" r="4.5"/></g></g></g>';
        case 'khoa-hoc': // đài thiên văn có kính viễn vọng và tên lửa
            return $s . '<path d="M206 26 Q208.5 37.5 220 40 Q208.5 42.5 206 54 Q203.5 42.5 192 40 Q203.5 37.5 206 26Z" fill="#FFD43B" stroke-width="3"/>'
                . '<path d="M58 34 Q59.5 42.5 68 44 Q59.5 45.5 58 54 Q56.5 45.5 48 44 Q56.5 42.5 58 34Z" fill="#fff" stroke-width="3"/>'
                . '<circle cx="216" cy="98" r="12" fill="#FFA94D" stroke-width="4"/><ellipse cx="216" cy="98" rx="23" ry="6" fill="none" stroke-width="7" transform="rotate(-20 216 98)"/><ellipse cx="216" cy="98" rx="23" ry="6" fill="none" stroke="#FFE066" stroke-width="3" transform="rotate(-20 216 98)"/>'
                . '<path d="M12 172 L2 198 L18 188Z" fill="#FF6B6B" stroke-width="4"/><path d="M44 172 L54 198 L38 188Z" fill="#FF6B6B" stroke-width="4"/><path d="M20 182 H36 L28 206Z" fill="#FFD43B" stroke-width="4"/>'
                . '<path d="M28 122 Q47 148 44 184 H12 Q9 148 28 122Z" fill="#fff"/><path d="M28 122 Q39 137 42.5 152 H13.5 Q17 137 28 122Z" fill="#FF6B6B" stroke-width="4"/><circle cx="28" cy="167" r="6" fill="#74C0FC" stroke-width="3"/>'
                . '<path d="M58 126 H182 V190 Q182 206 166 206 H74 Q58 206 58 190Z" fill="#F8F9FA"/><path d="M48 128 A72 72 0 0 1 192 128Z" fill="#5C7CFA"/><path d="M110 58 H134 V128 H110Z" fill="#364FC7" stroke-width="4"/>'
                . '<path d="M66 112 Q72 84 96 70" fill="none" stroke="#fff" stroke-opacity=".7" stroke-width="6"/>'
                . '<g transform="rotate(-38 124 100)"><rect x="112" y="88" width="84" height="24" rx="12" fill="#FFD43B"/><rect x="178" y="84" width="20" height="32" rx="8" fill="#FFA94D" stroke-width="4"/></g>'
                . '<rect x="42" y="120" width="156" height="16" rx="8" fill="#BAC8FF"/>'
                . '<path d="M88 206 V174 A16 16 0 0 1 120 174 V206Z" fill="#845EF7"/><circle cx="113" cy="190" r="3" fill="#FFD43B" stroke="none"/>'
                . '<circle cx="152" cy="170" r="14" fill="#A5E3FF"/>' . $shine(147, 165) . $blush(152, 194) . '</g>';
    }
    return '';
}

/** Bản đồ Sunny World (SVG): thế giới trên mây, mỗi khu là một ngôi nhà trên đảo riêng. Khu đã mở là một liên kết. */
function sw_map(): string
{
    $L = SW_LINE;
    $font = 'font-family="Be Vietnam Pro,system-ui,sans-serif"';
    $s5 = 'stroke="' . $L . '" stroke-width="5" stroke-linejoin="round" stroke-linecap="round"';
    $tree = fn(int $x, int $y, string $fill, bool $dots = false) => '<rect x="' . ($x - 8) . '" y="' . ($y - 38) . '" width="16" height="38" rx="6" fill="#B5652B" stroke-width="4"/><circle cx="' . $x . '" cy="' . ($y - 70) . '" r="40" fill="' . $fill . '"/>'
        . ($dots
            ? '<g fill="#fff" stroke="none"><circle cx="' . ($x - 14) . '" cy="' . ($y - 82) . '" r="6"/><circle cx="' . ($x + 12) . '" cy="' . ($y - 86) . '" r="5"/><circle cx="' . ($x + 4) . '" cy="' . ($y - 58) . '" r="6"/><circle cx="' . ($x - 18) . '" cy="' . ($y - 58) . '" r="4"/></g>'
            : '<path d="M' . ($x - 24) . ' ' . ($y - 78) . ' Q' . ($x - 18) . ' ' . ($y - 96) . ' ' . $x . ' ' . ($y - 100) . '" fill="none" stroke="#fff" stroke-opacity=".65"/>');
    $mush = fn(int $x, int $y, string $fill) => '<rect x="' . ($x - 9) . '" y="' . ($y - 14) . '" width="18" height="20" rx="6" fill="#FFF4D6"/><path d="M' . ($x - 27) . ' ' . ($y - 10) . ' Q' . $x . ' ' . ($y - 56) . ' ' . ($x + 27) . ' ' . ($y - 10) . 'Z" fill="' . $fill . '"/>'
        . '<circle cx="' . ($x - 9) . '" cy="' . ($y - 22) . '" r="4.5" fill="#fff" stroke="none"/><circle cx="' . ($x + 7) . '" cy="' . ($y - 30) . '" r="4" fill="#fff" stroke="none"/>';
    $flower = fn(int $x, int $y, string $fill, string $mid) => '<circle cx="' . $x . '" cy="' . $y . '" r="8" fill="' . $fill . '"/><circle cx="' . $x . '" cy="' . $y . '" r="2.5" fill="' . $mid . '" stroke="none"/>';
    $bridge = fn(int $x) => '<rect x="' . $x . '" y="634" width="120" height="36" rx="8" fill="#E8A95B" stroke-width="5"/><path d="' . implode('', array_map(fn(int $i) => 'M' . ($x + 20 * $i) . ' 636 V668', range(1, 5))) . '" fill="none" stroke-width="3"/>'
        . implode('', array_map(fn(array $p) => '<rect x="' . ($x + $p[0]) . '" y="' . $p[1] . '" width="13" height="20" rx="4" fill="#B5652B" stroke-width="4"/>', [[-6, 622], [113, 622], [-6, 660], [113, 660]]));
    $paths = 'M396 652 C330 652 270 580 232 496 M396 652 C396 580 460 500 498 426 M884 652 C884 580 836 500 802 446 M884 652 C950 652 1030 590 1068 506';

    $out = '<svg class="sw-map" viewBox="0 88 1280 712" role="img" aria-label="Bản đồ Sunny World"><title>Bản đồ Sunny World</title>'
        . '<defs><linearGradient id="swSky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#8FD3FF"/><stop offset="1" stop-color="#D6F2FF"/></linearGradient></defs>'
        . '<rect y="0" width="1280" height="800" fill="url(#swSky)"/>'
        // cầu vồng
        . '<g fill="none" stroke-width="13"><circle cx="1215" cy="330" r="158" stroke="' . $L . '" stroke-width="5"/><circle cx="1215" cy="330" r="150" stroke="#FF6B6B"/><circle cx="1215" cy="330" r="137" stroke="#FFA94D"/><circle cx="1215" cy="330" r="124" stroke="#FFE066"/>'
        . '<circle cx="1215" cy="330" r="111" stroke="#69DB7C"/><circle cx="1215" cy="330" r="98" stroke="#4DABF7"/><circle cx="1215" cy="330" r="85" stroke="#B197FC"/><circle cx="1215" cy="330" r="77" stroke="' . $L . '" stroke-width="5"/></g>'
        // mây cười
        . '<g class="mc c1" stroke="' . $L . '" stroke-width="4" stroke-linejoin="round" stroke-linecap="round"><path d="M940 150 Q925 150 925 136 Q925 122 941 121 Q944 100 966 100 Q982 100 990 111 Q998 104 1010 106 Q1026 109 1027 124 Q1042 126 1042 138 Q1042 150 1028 150Z" fill="#fff"/>'
        . '<circle cx="972" cy="128" r="3.5" fill="' . $L . '" stroke="none"/><circle cx="996" cy="128" r="3.5" fill="' . $L . '" stroke="none"/><path d="M978 135 Q984 141 990 135" fill="none" stroke-width="3"/>'
        . '<ellipse cx="962" cy="136" rx="6" ry="3.5" fill="#FF8FA8" stroke="none"/><ellipse cx="1006" cy="136" rx="6" ry="3.5" fill="#FF8FA8" stroke="none"/></g>'
        // khinh khí cầu
        . '<g class="mc c2" ' . $s5 . '><path d="M48 216 L58 244 M92 216 L82 244" fill="none" stroke-width="3"/><ellipse cx="70" cy="170" rx="50" ry="56" fill="#FF6B6B"/><path d="M70 114 Q46 170 70 226 Q94 170 70 114Z" fill="#FFE066" stroke-width="4"/>'
        . '<path d="M36 150 Q40 132 54 124" fill="none" stroke="#fff" stroke-opacity=".75" stroke-width="6"/><rect x="56" y="244" width="28" height="20" rx="5" fill="#E8A95B" stroke-width="4"/>'
        . '<circle cx="58" cy="174" r="4" fill="' . $L . '" stroke="none"/><circle cx="82" cy="174" r="4" fill="' . $L . '" stroke="none"/><path d="M64 182 Q70 188 76 182" fill="none" stroke-width="3"/></g>'
        // lấp lánh
        . '<g stroke="' . $L . '" stroke-width="3" stroke-linejoin="round"><path d="M330 196 Q332.5 208.5 345 211 Q332.5 213.5 330 226 Q327.5 213.5 315 211 Q327.5 208.5 330 196Z" fill="#FFD43B"/>'
        . '<path d="M650 196 Q652 205 661 207 Q652 209 650 218 Q648 209 639 207 Q648 205 650 196Z" fill="#FF8FA8"/><path d="M200 106 Q202 115 211 117 Q202 119 200 128 Q198 119 189 117 Q198 115 200 106Z" fill="#fff"/></g>'
        // nền mây
        . '<g fill="#fff"><circle cx="0" cy="340" r="90"/><circle cx="130" cy="328" r="80"/><circle cx="262" cy="346" r="86"/><circle cx="392" cy="322" r="80"/><circle cx="520" cy="336" r="86"/><circle cx="650" cy="326" r="90"/><circle cx="780" cy="338" r="86"/>'
        . '<circle cx="908" cy="324" r="80"/><circle cx="1036" cy="342" r="88"/><circle cx="1164" cy="328" r="82"/><circle cx="1290" cy="340" r="90"/><rect x="0" y="340" width="1280" height="460"/></g>'
        . '<g fill="#E1F3FF"><ellipse cx="150" cy="590" rx="110" ry="30"/><ellipse cx="1130" cy="620" rx="120" ry="32"/><ellipse cx="320" cy="350" rx="64" ry="16"/><ellipse cx="940" cy="352" rx="64" ry="16"/><ellipse cx="60" cy="440" rx="56" ry="16"/><ellipse cx="1244" cy="470" rx="50" ry="15"/><ellipse cx="640" cy="520" rx="150" ry="26"/></g>'
        . '<g fill="#CDEBFF"><circle cx="40" cy="810" r="76"/><circle cx="190" cy="818" r="76"/><circle cx="340" cy="808" r="76"/><circle cx="490" cy="820" r="76"/><circle cx="640" cy="826" r="76"/><circle cx="790" cy="820" r="76"/><circle cx="940" cy="808" r="76"/><circle cx="1090" cy="818" r="76"/><circle cx="1240" cy="810" r="76"/></g>'
        // đường đi
        . '<g fill="none" stroke-linecap="round"><path d="' . $paths . '" stroke="#E0A04A" stroke-width="52"/><path d="' . $paths . '" stroke="#FFD58A" stroke-width="40"/></g>'
        // hồ
        . '<ellipse cx="640" cy="668" rx="240" ry="100" fill="#BDEBFF" stroke="' . $L . '" stroke-width="5"/><ellipse cx="640" cy="670" rx="226" ry="89" fill="#5CC8F5"/>'
        . '<g fill="none" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-opacity=".85"><path d="M548 742 q10 -6 20 0 t20 0"/><path d="M690 748 q10 -6 20 0 t20 0"/><path d="M448 690 q8 -5 16 0"/><path d="M812 676 q8 -5 16 0 t16 0"/></g>'
        . '<g stroke="' . $L . '" stroke-width="3" stroke-linejoin="round"><ellipse cx="500" cy="728" rx="17" ry="8" fill="#51CF66"/><circle cx="504" cy="722" r="5" fill="#FF8FA8"/></g>'
        // các hòn đảo
        . '<g stroke="' . $L . '" stroke-width="5"><ellipse cx="220" cy="460" rx="135" ry="44" fill="#4CAF50"/><ellipse cx="220" cy="446" rx="135" ry="44" fill="#8BDD5E"/>'
        . '<ellipse cx="500" cy="390" rx="135" ry="44" fill="#F06595"/><ellipse cx="500" cy="376" rx="135" ry="44" fill="#FFB3C7"/>'
        . '<ellipse cx="800" cy="410" rx="135" ry="44" fill="#F59F00"/><ellipse cx="800" cy="396" rx="135" ry="44" fill="#FFE066"/>'
        . '<ellipse cx="1070" cy="470" rx="135" ry="44" fill="#845EF7"/><ellipse cx="1070" cy="456" rx="135" ry="44" fill="#D0BFFF"/>'
        . '<ellipse cx="640" cy="676" rx="150" ry="52" fill="#4CAF50"/><ellipse cx="640" cy="662" rx="150" ry="52" fill="#8BDD5E"/></g>'
        . '<g fill="#fff" fill-opacity=".45"><ellipse cx="150" cy="430" rx="40" ry="9"/><ellipse cx="430" cy="360" rx="40" ry="9"/><ellipse cx="730" cy="380" rx="40" ry="9"/><ellipse cx="1000" cy="440" rx="40" ry="9"/><ellipse cx="560" cy="640" rx="44" ry="10"/></g>'
        // cầu gỗ, vịt
        . '<g stroke="' . $L . '" stroke-linejoin="round" stroke-linecap="round">' . $bridge(390) . $bridge(770) . '</g>'
        . '<g stroke="' . $L . '" stroke-width="4" stroke-linejoin="round"><ellipse cx="756" cy="734" rx="20" ry="13" fill="#FFE066"/><circle cx="772" cy="718" r="11" fill="#FFE066"/><path d="M781 716 L793 720 L781 724Z" fill="#FFA94D" stroke-width="3"/><circle cx="775" cy="715" r="2.2" fill="' . $L . '" stroke="none"/></g>'
        // cây, nấm, hoa
        . '<g ' . $s5 . '>' . $tree(60, 644, '#51CF66') . $tree(1226, 658, '#51CF66') . $tree(300, 744, '#51CF66') . $tree(190, 706, '#FFB3C7', true) . $tree(1010, 738, '#FFB3C7', true) . '</g>'
        . '<g stroke="' . $L . '" stroke-width="4" stroke-linejoin="round">' . $mush(111, 756, '#FF6B6B') . $mush(1238, 764, '#B197FC') . '</g>'
        . '<g stroke="' . $L . '" stroke-width="3">' . $flower(350, 566, '#FF8FA8', '#FFD43B') . $flower(930, 744, '#FFD43B', '#FF6B6B') . $flower(250, 560, '#74C0FC', '#fff') . $flower(960, 560, '#FF8FA8', '#FFD43B') . $flower(80, 690, '#FFD43B', '#FF6B6B') . $flower(1110, 566, '#fff', '#FFD43B') . '</g>';

    foreach (sw_places() as $p) {
        $w = 50 + mb_strlen($p['name']) * 10 + ($p['open'] ? 0 : 78);
        $x0 = -$w / 2;
        $label = '<g class="lbl" transform="translate(120 222)"><rect x="' . $x0 . '" y="4" width="' . $w . '" height="40" rx="20" fill="' . $L . '"/><rect x="' . $x0 . '" y="0" width="' . $w . '" height="40" rx="20" fill="#fff" stroke="' . $L . '" stroke-width="3.5"/>'
            . '<circle cx="' . ($x0 + 21) . '" cy="20" r="7" fill="' . e($p['color']) . '" stroke="' . $L . '" stroke-width="2.5"/>'
            . '<text x="' . ($x0 + 36) . '" y="26.5" font-size="17" font-weight="800" fill="' . $L . '" ' . $font . '>' . e($p['name']) . '</text>'
            . ($p['open'] ? '' : '<rect x="' . ($w / 2 - 80) . '" y="6" width="74" height="28" rx="14" fill="#FFE066"/><text x="' . ($w / 2 - 43) . '" y="25" text-anchor="middle" font-size="13.5" font-weight="800" fill="#4A2610" ' . $font . '>Sắp mở</text>')
            . '</g>';
        $house = '<g class="hs">' . sw_house_art($p['key']) . '</g>';
        $inner = '<g transform="translate(' . $p['x'] . ' ' . $p['y'] . ')">' . $house . $label . '</g>';
        $out .= $p['open']
            ? '<a class="mh" href="' . e(sw_url($p['key'])) . '" aria-label="' . e($p['name'] . ': ' . $p['desc']) . '">' . $inner . '</a>'
            : '<g class="mh locked" aria-label="' . e($p['name']) . ' (sắp mở)">' . $inner . '</g>';
    }
    // Góc ba mẹ: biển chỉ đường nhỏ
    $out .= '<a class="mh" href="' . e(sw_url('ba-me')) . '" aria-label="Góc ba mẹ: xem con đã học được gì"><g transform="translate(1116 784)"><g class="hs" stroke="' . $L . '" stroke-width="4" stroke-linejoin="round"><rect x="-6" y="-52" width="12" height="52" rx="4" fill="#B5652B"/><rect x="-68" y="-94" width="136" height="46" rx="14" fill="#FFE066"/>'
        . '<text x="0" y="-64" text-anchor="middle" font-size="19" font-weight="800" fill="' . $L . '" stroke="none" ' . $font . '>Góc ba mẹ</text></g></g></a>'
        // linh vật đứng trên đảo giữa hồ
        . '<ellipse cx="640" cy="680" rx="62" ry="8" fill="' . $L . '" fill-opacity=".2"/>'
        . '<g class="map-sun"><svg x="516" y="462" width="248" height="265" viewBox="0 0 400 428">' . sw_sunny_inner('hello') . '</svg></g>'
        . '<g class="map-hello" stroke="' . $L . '" stroke-width="3.5" stroke-linejoin="round"><path d="M742 536 L738 556 L762 538Z" fill="#fff"/><rect x="724" y="496" width="98" height="42" rx="17" fill="#fff"/><path d="M745 538 H760" stroke="#fff" stroke-width="5"/>'
        . '<text x="773" y="524" text-anchor="middle" font-size="19" font-weight="900" fill="#7A3F18" stroke="none" ' . $font . '>Hello!</text></g>'
        . '</svg>';
    return $out;
}
