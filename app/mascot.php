<?php
/**
 * Linh vật "Sunny" (mặt trời kawaii), nhân vật bé và các cảnh minh hoạ động, vẽ bằng SVG.
 * Phong cách "sticker": viền nâu đen đậm, màu phẳng tươi. Chuyển động nằm trong assets/css/sunny.css và assets/js/site.js.
 */
declare(strict_types=1);

const SUN_LINE = '#2E241B';

/**
 * Các tia nắng cong như ngọn lửa, xoay theo một chiều.
 * @param float $r0 bán kính chân tia, $len độ dài, $w bề rộng chân, $tw độ lệch ngọn (tạo độ cong)
 */
function sun_rays(int $n, float $cx, float $cy, float $r0, float $len, float $w, float $tw, float $sw): string
{
    $h = $w / 2;
    $p = sprintf(
        'M%.1f %.1f C%.1f %.1f %.1f %.1f %.1f %.1f C%.1f %.1f %.1f %.1f %.1f %.1f Z',
        -$h, -$r0,
        -$h, -($r0 + $len * .45), $tw - $w * .25, -($r0 + $len * .82), $tw, -($r0 + $len),
        $tw + $w * .3, -($r0 + $len * .72), $h + $w * .2, -($r0 + $len * .4), $h, -$r0
    );
    $out = '';
    for ($i = 0; $i < $n; $i++) {
        $a = round($i * 360 / $n, 2);
        $out .= '<g transform="translate(' . $cx . ' ' . $cy . ') rotate(' . $a . ')">'
            . '<path d="' . $p . '" fill="url(#gRay)" stroke="' . SUN_LINE . '" stroke-width="' . $sw . '" stroke-linejoin="round"/>'
            . '<path d="M' . ($tw * .55 - $w * .08) . ' ' . -($r0 + $len * .22) . ' Q' . ($tw * .6) . ' ' . -($r0 + $len * .55) . ' ' . ($tw * .8) . ' ' . -($r0 + $len * .8) . '" stroke="#fff" stroke-opacity=".55" stroke-width="' . max(2, $sw * .55) . '" stroke-linecap="round" fill="none"/>'
            . '</g>';
    }
    return $out;
}

/** Đặt một lần trong trang: gradient dùng chung + biểu tượng mặt trời nhỏ (symbol #sunFace). */
function sun_defs(): string
{
    $L = SUN_LINE;
    return '<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false"><defs>'
        . '<radialGradient id="gSun" cx=".32" cy=".26" r=".9"><stop offset="0" stop-color="#FFEC8F"/><stop offset=".5" stop-color="#FDC018"/><stop offset="1" stop-color="#F5940C"/></radialGradient>'
        . '<linearGradient id="gRay" x1="0" y1="1" x2="0" y2="0"><stop offset="0" stop-color="#F7931E"/><stop offset="1" stop-color="#FFC83A"/></linearGradient>'
        . '<symbol id="sunFace" viewBox="0 0 200 200">'
        . sun_rays(9, 100, 98, 44, 40, 26, 11, 4)
        . '<ellipse cx="86" cy="150" rx="13" ry="9" fill="#F7931E" stroke="' . $L . '" stroke-width="4"/><ellipse cx="114" cy="150" rx="13" ry="9" fill="#F7931E" stroke="' . $L . '" stroke-width="4"/>'
        . '<circle cx="100" cy="98" r="52" fill="url(#gSun)" stroke="' . $L . '" stroke-width="5"/>'
        . '<path d="M66 72 Q76 54 98 48" stroke="#fff" stroke-opacity=".75" stroke-width="6" stroke-linecap="round" fill="none"/>'
        . '<ellipse cx="82" cy="96" rx="9" ry="12" fill="#2A1D17"/><circle cx="79" cy="91" r="4" fill="#fff"/><circle cx="85" cy="102" r="2" fill="#fff"/>'
        . '<ellipse cx="118" cy="96" rx="9" ry="12" fill="#2A1D17"/><circle cx="115" cy="91" r="4" fill="#fff"/><circle cx="121" cy="102" r="2" fill="#fff"/>'
        . '<ellipse cx="68" cy="112" rx="8" ry="5.5" fill="#FF6F4E" fill-opacity=".7"/><ellipse cx="132" cy="112" rx="8" ry="5.5" fill="#FF6F4E" fill-opacity=".7"/>'
        . '<path d="M84 112 Q100 138 116 112 Q100 119 84 112Z" fill="#B3261E" stroke="' . $L . '" stroke-width="3.5" stroke-linejoin="round"/>'
        . '<path d="M92 125 Q100 132 108 125 Q100 120 92 125Z" fill="#FF7C8A"/>'
        . '</symbol>'
        . '</defs></svg>';
}

/** Linh vật lớn ở đầu trang: bấm để chào, mắt nhìn theo con trỏ. */
function mascot_hero(): string
{
    $L = SUN_LINE;
    $spark = fn(int $x, int $y, string $delay, string $fill = '#FFC20E') => '<path class="m-spark" style="animation-delay:' . $delay . '" transform="translate(' . $x . ' ' . $y . ')" d="M0 -13 Q2 -2 13 0 Q2 2 0 13 Q-2 2 -13 0 Q-2 -2 0 -13Z" fill="' . $fill . '" stroke="' . $L . '" stroke-width="2.5" stroke-linejoin="round"/>';
    $heart = fn(int $x, int $y, string $delay) => '<path class="m-spark" style="animation-delay:' . $delay . '" transform="translate(' . $x . ' ' . $y . ') scale(.9)" d="M0 8 C-14 -2 -12 -14 -5 -14 C-1 -14 0 -10 0 -9 C0 -10 1 -14 5 -14 C12 -14 14 -2 0 8Z" fill="#FF6F8E" stroke="' . $L . '" stroke-width="2.5" stroke-linejoin="round"/>';

    return '<button type="button" class="mascot" id="mascot" aria-label="Linh vật mặt trời Sunny. Bấm để Sunny chào con">'
        . '<svg viewBox="0 0 400 420" role="img" aria-hidden="true" focusable="false">'
        . '<defs><clipPath id="mMouth"><path d="M164 238 Q200 300 236 238 Z"/></clipPath></defs>'
        . '<ellipse class="m-shadow" cx="200" cy="394" rx="116" ry="12" fill="#4A2610" fill-opacity=".14"/>'
        . $spark(52, 110, '0s') . $spark(352, 92, '.8s', '#FFE27A') . $heart(40, 262, '1.2s') . $spark(362, 286, '.4s') . $heart(344, 188, '1.6s') . $spark(70, 356, '1s', '#FFE27A')
        . '<g class="m-jump"><g class="m-float"><g class="m-tilt">'
        . '<g class="m-rays"><g class="m-rays-in">' . sun_rays(9, 200, 205, 90, 76, 50, 18, 7) . '</g></g>'
        // chân
        . '<g class="m-foot-l"><ellipse cx="166" cy="312" rx="26" ry="18" fill="#F7931E" stroke="' . $L . '" stroke-width="7"/><path d="M150 308 Q160 300 174 304" stroke="#FFD66B" stroke-width="5" stroke-linecap="round" fill="none"/></g>'
        . '<g class="m-foot-r"><ellipse cx="234" cy="312" rx="26" ry="18" fill="#F7931E" stroke="' . $L . '" stroke-width="7"/><path d="M218 308 Q228 300 242 304" stroke="#FFD66B" stroke-width="5" stroke-linecap="round" fill="none"/></g>'
        // thân bóng
        . '<circle cx="200" cy="205" r="102" fill="url(#gSun)" stroke="' . $L . '" stroke-width="8"/>'
        . '<path d="M126 152 Q144 118 188 106" stroke="#fff" stroke-opacity=".8" stroke-width="12" stroke-linecap="round" fill="none"/>'
        . '<circle cx="212" cy="102" r="5" fill="#fff" fill-opacity=".85"/>'
        // mắt
        . '<g class="m-eye"><g class="m-pupil" data-eye="l"><ellipse cx="160" cy="200" rx="23" ry="29" fill="#2A1D17"/><circle cx="152" cy="190" r="9.5" fill="#fff"/><circle cx="168" cy="212" r="4.5" fill="#fff"/></g></g>'
        . '<g class="m-eye"><g class="m-pupil" data-eye="r"><ellipse cx="240" cy="200" rx="23" ry="29" fill="#2A1D17"/><circle cx="232" cy="190" r="9.5" fill="#fff"/><circle cx="248" cy="212" r="4.5" fill="#fff"/></g></g>'
        . '<ellipse cx="124" cy="242" rx="19" ry="12" fill="#FF6F4E" fill-opacity=".72"/><ellipse cx="276" cy="242" rx="19" ry="12" fill="#FF6F4E" fill-opacity=".72"/>'
        // miệng
        . '<g class="m-mouth"><path d="M164 238 Q200 300 236 238 Z" fill="#B3261E"/>'
        . '<g clip-path="url(#mMouth)"><ellipse cx="200" cy="280" rx="28" ry="17" fill="#FF7C8A"/></g>'
        . '<path d="M164 238 Q200 300 236 238 Z" fill="none" stroke="' . $L . '" stroke-width="6" stroke-linejoin="round"/></g>'
        . '</g></g></g>'
        // bóng thoại
        . '<g class="m-bubble" id="mBubble"><path d="M118 82 L104 112 L152 82Z" fill="#fff" stroke="' . $L . '" stroke-width="5" stroke-linejoin="round"/>'
        . '<rect x="20" y="16" width="190" height="66" rx="22" fill="#fff" stroke="' . $L . '" stroke-width="5"/><path d="M122 80 H148" stroke="#fff" stroke-width="8"/>'
        . '<text id="mSay" x="115" y="60" text-anchor="middle" font-size="24" font-weight="800" fill="#7A3F18" font-family="Be Vietnam Pro,system-ui,sans-serif">Hello!</text></g>'
        . '</svg></button>';
}

/**
 * Một nhân vật bé kiểu sticker. Gốc toạ độ ở giữa hai chân, cao khoảng 205 đơn vị.
 * $o: x, y, s (tỉ lệ), skin, hair, style (short|bob|pigtails|bun|curly), shirt, pants, shoe, open (miệng mở),
 *     al/ar = vị trí bàn tay trái/phải [x,y], waveL/waveR (cánh tay vẫy), cls (k-bob|k-jump), delay, holdR (SVG cầm trong tay phải).
 */
function kid(array $o): string
{
    $L = '#3A2A1F';
    $skin = $o['skin'] ?? '#FFD3B0';
    $hair = $o['hair'] ?? '#3A2A1F';
    $style = $o['style'] ?? 'short';
    $shirt = $o['shirt'] ?? '#2F72A8';
    $pants = $o['pants'] ?? '#46392D';
    $shoe = $o['shoe'] ?? '#D2302A';
    $open = $o['open'] ?? true;
    $al = $o['al'] ?? [-62, -64];
    $ar = $o['ar'] ?? [62, -64];
    $cls = $o['cls'] ?? 'k-bob';
    $delay = $o['delay'] ?? '0s';
    $s = $o['s'] ?? 1;

    $arm = function (int $sx, int $sy, array $h, bool $wave) use ($shirt, $skin, $L): string {
        $cx = (int) (($sx + $h[0]) / 2 + ($sx > 0 ? 12 : -12));
        $cy = (int) (($sy + $h[1]) / 2 + 8);
        $d = "M$sx $sy Q$cx $cy {$h[0]} {$h[1]}";
        $g = '<path d="' . $d . '" stroke="' . $L . '" stroke-width="22" stroke-linecap="round" fill="none"/>'
            . '<path d="' . $d . '" stroke="' . $shirt . '" stroke-width="14" stroke-linecap="round" fill="none"/>'
            . '<circle cx="' . $h[0] . '" cy="' . $h[1] . '" r="10" fill="' . $skin . '" stroke="' . $L . '" stroke-width="4"/>';
        return $wave ? '<g class="k-wave" style="transform-origin:' . $sx . 'px ' . $sy . 'px">' . $g . '</g>' : $g;
    };

    $hairBack = match ($style) {
        'pigtails' => '<circle cx="-46" cy="-142" r="15" fill="' . $hair . '" stroke="' . $L . '" stroke-width="4"/><circle cx="46" cy="-142" r="15" fill="' . $hair . '" stroke="' . $L . '" stroke-width="4"/>',
        'bun' => '<circle cx="0" cy="-194" r="16" fill="' . $hair . '" stroke="' . $L . '" stroke-width="4"/>',
        'bob' => '<path d="M-42 -150 Q-46 -198 0 -198 Q46 -198 42 -150 L40 -118 Q28 -116 28 -134 L-28 -134 Q-28 -116 -40 -118Z" fill="' . $hair . '" stroke="' . $L . '" stroke-width="4" stroke-linejoin="round"/>',
        'curly' => '<g fill="' . $hair . '" stroke="' . $L . '" stroke-width="4"><circle cx="-36" cy="-178" r="17"/><circle cx="0" cy="-190" r="18"/><circle cx="36" cy="-178" r="17"/><circle cx="-48" cy="-152" r="15"/><circle cx="48" cy="-152" r="15"/></g>',
        default => '',
    };
    $cap = '<path d="M-37 -150 C-41 -196 41 -196 37 -150 C29 -166 13 -172 -2 -170 C-18 -168 -31 -162 -37 -150Z" fill="' . $hair . '" stroke="' . $L . '" stroke-width="4" stroke-linejoin="round"/>';
    $mouth = $open
        ? '<path d="M-10 -134 Q0 -118 10 -134 Z" fill="#B3261E" stroke="' . $L . '" stroke-width="3" stroke-linejoin="round"/>'
        : '<path d="M-9 -135 Q0 -126 9 -135" stroke="' . $L . '" stroke-width="3.5" stroke-linecap="round" fill="none"/>';

    $g = '<g transform="translate(' . ($o['x'] ?? 0) . ' ' . ($o['y'] ?? 0) . ') scale(' . $s . ')"><g class="' . $cls . '" style="animation-delay:' . $delay . '">'
        . '<ellipse cx="0" cy="4" rx="46" ry="8" fill="#4A2610" fill-opacity=".14"/>'
        // chân + giày
        . '<rect x="-25" y="-60" width="21" height="54" rx="9" fill="' . $pants . '" stroke="' . $L . '" stroke-width="4"/><rect x="4" y="-60" width="21" height="54" rx="9" fill="' . $pants . '" stroke="' . $L . '" stroke-width="4"/>'
        . '<ellipse cx="-17" cy="-4" rx="18" ry="9" fill="' . $shoe . '" stroke="' . $L . '" stroke-width="4"/><ellipse cx="17" cy="-4" rx="18" ry="9" fill="' . $shoe . '" stroke="' . $L . '" stroke-width="4"/>'
        // thân
        . '<rect x="-32" y="-122" width="64" height="74" rx="24" fill="' . $shirt . '" stroke="' . $L . '" stroke-width="4"/>'
        . '<path d="M-31 -94 H31" stroke="#fff" stroke-opacity=".45" stroke-width="7"/>'
        . $arm(-30, -104, $al, !empty($o['waveL'])) . $arm(30, -104, $ar, !empty($o['waveR']))
        . ($o['holdR'] ?? '')
        // đầu
        . $hairBack
        . '<circle cx="-36" cy="-148" r="8" fill="' . $skin . '" stroke="' . $L . '" stroke-width="4"/><circle cx="36" cy="-148" r="8" fill="' . $skin . '" stroke="' . $L . '" stroke-width="4"/>'
        . '<circle cx="0" cy="-150" r="36" fill="' . $skin . '" stroke="' . $L . '" stroke-width="4"/>'
        . $cap
        . '<ellipse cx="-13" cy="-148" rx="5" ry="7" fill="#2A1D17"/><circle cx="-14.6" cy="-150.6" r="2.1" fill="#fff"/><ellipse cx="13" cy="-148" rx="5" ry="7" fill="#2A1D17"/><circle cx="11.4" cy="-150.6" r="2.1" fill="#fff"/>'
        . '<ellipse cx="-23" cy="-137" rx="7" ry="4.5" fill="#FF6F4E" fill-opacity=".55"/><ellipse cx="23" cy="-137" rx="7" ry="4.5" fill="#FF6F4E" fill-opacity=".55"/>'
        . $mouth
        . '</g></g>';
    return $g;
}

/** Một cảnh minh hoạ động cho khối "Về Sunny". $name: speak | fun | milestone | report */
function scene_svg(string $name): string
{
    $L = SUN_LINE;
    $svg = fn(string $inner) => '<svg class="scene scene-' . $name . '" viewBox="0 0 480 380" aria-hidden="true" focusable="false">'
        . '<path class="s-blob" d="M64 196 C44 112 128 40 242 44 C352 48 444 92 438 192 C432 294 350 346 240 342 C128 338 84 272 64 196Z"/>' . $inner . '</svg>';
    $star = fn(int $x, int $y, float $s, string $cls = '', string $fill = '#FFC20E') => '<g transform="translate(' . $x . ' ' . $y . ') scale(' . $s . ')"><path class="s-spark ' . $cls . '" d="M0 -14 L4 -4 L15 -3 L7 4 L9 15 L0 9 L-9 15 L-7 4 L-15 -3 L-4 -4Z" fill="' . $fill . '" stroke="' . $L . '" stroke-width="3" stroke-linejoin="round"/></g>';
    $bubble = fn(int $x, int $y, int $w, string $text, string $cls, string $fill = '#fff') => '<g class="s-pop ' . $cls . '">'
        . '<path d="M' . ($x + 24) . ' ' . ($y + 44) . ' L' . ($x + 14) . ' ' . ($y + 66) . ' L' . ($x + 46) . ' ' . ($y + 44) . 'Z" fill="' . $fill . '" stroke="' . $L . '" stroke-width="4" stroke-linejoin="round"/>'
        . '<rect x="' . $x . '" y="' . $y . '" width="' . $w . '" height="50" rx="18" fill="' . $fill . '" stroke="' . $L . '" stroke-width="4"/>'
        . '<path d="M' . ($x + 28) . ' ' . ($y + 46) . ' H' . ($x + 44) . '" stroke="' . $fill . '" stroke-width="7"/>'
        . '<text x="' . ($x + $w / 2) . '" y="' . ($y + 34) . '" text-anchor="middle" font-size="22" font-weight="800" fill="#7A3F18" font-family="Be Vietnam Pro,system-ui,sans-serif">' . $text . '</text></g>';
    $sun = fn(int $x, int $y, int $w, string $cls = 's-bob') => '<use class="' . $cls . '" href="#sunFace" x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $w . '"/>';
    $ground = fn(int $cx, int $cy, int $rx) => '<ellipse cx="' . $cx . '" cy="' . $cy . '" rx="' . $rx . '" ry="10" fill="#4A2610" fill-opacity=".12"/>';

    switch ($name) {
        case 'speak':
            $mic = '<g transform="translate(46 -176) rotate(-18)"><rect x="-7" y="-4" width="14" height="40" rx="7" fill="#205984" stroke="' . $L . '" stroke-width="4"/><rect x="-14" y="-40" width="28" height="42" rx="14" fill="#6B5E52" stroke="' . $L . '" stroke-width="4"/><path d="M-9 -26 h18 M-9 -16 h18 M-9 -6 h18" stroke="#D3CBC1" stroke-width="3"/></g>';
            return $svg(
                $ground(240, 338, 150)
                . $sun(14, 168, 170)
                // bục đứng
                . '<g><rect x="186" y="262" width="128" height="76" rx="14" fill="#2F72A8" stroke="' . $L . '" stroke-width="5"/><rect x="186" y="262" width="128" height="16" rx="8" fill="#7FAED6" stroke="' . $L . '" stroke-width="4"/>'
                . '<circle cx="250" cy="308" r="17" fill="#FFC20E" stroke="' . $L . '" stroke-width="4"/><path d="M250 297 l4 8 9 1 -7 6 2 9 -8 -5 -8 5 2 -9 -7 -6 9 -1Z" fill="#fff" stroke="' . $L . '" stroke-width="2" stroke-linejoin="round"/></g>'
                . kid(['x' => 250, 'y' => 266, 's' => .8, 'skin' => '#F0B48A', 'hair' => '#3A2A1F', 'style' => 'pigtails', 'shirt' => '#F7931E', 'pants' => '#205984', 'shoe' => '#FFFFFF',
                    'al' => [-64, -150], 'waveL' => true, 'ar' => [44, -146], 'holdR' => $mic, 'cls' => 'k-bob'])
                . '<g class="s-waves"><path d="M312 150 Q328 170 312 190" stroke="#F7931E" stroke-width="7" stroke-linecap="round" fill="none"/><path d="M330 138 Q354 170 330 202" stroke="#F7931E" stroke-width="7" stroke-linecap="round" fill="none"/><path d="M348 126 Q380 170 348 214" stroke="#F7931E" stroke-width="7" stroke-linecap="round" fill="none"/></g>'
                . $bubble(18, 40, 112, 'Hello!', 'd0') . $bubble(338, 34, 100, 'Yes!', 'd1') . $bubble(30, 262, 78, 'Hi!', 'd2', '#FFF3C4')
                . $star(430, 250, .9, 'sp1') . $star(60, 130, .7, 'sp2', '#7FD0F7') . $star(196, 52, .6, 'sp3')
            );
        case 'fun':
            return $svg(
                $ground(240, 338, 190)
                . kid(['x' => 112, 'y' => 322, 's' => .8, 'skin' => '#B97A4F', 'hair' => '#2E241B', 'style' => 'curly', 'shirt' => '#FFC20E', 'pants' => '#2F72A8', 'shoe' => '#D2302A',
                    'al' => [-56, -176], 'ar' => [56, -176], 'cls' => 'k-jump'])
                . kid(['x' => 372, 'y' => 322, 's' => .8, 'skin' => '#FFD3B0', 'hair' => '#7A3F18', 'style' => 'bun', 'shirt' => '#FF6F8E', 'pants' => '#3BAA4A', 'shoe' => '#FFFFFF',
                    'al' => [-56, -172], 'ar' => [56, -172], 'cls' => 'k-jump', 'delay' => '-.45s'])
                . $sun(150, 92, 190)
                // bóng bay, nốt nhạc, bóng
                . '<g transform="translate(60 120)"><g class="s-float f2"><path d="M0 34 Q-8 54 2 74" stroke="#6B5E52" stroke-width="3" fill="none"/><ellipse rx="22" ry="28" fill="#D2302A" stroke="' . $L . '" stroke-width="4"/><path d="M-11 -12 Q-9 -20 -2 -22" stroke="#fff" stroke-opacity=".7" stroke-width="5" stroke-linecap="round" fill="none"/></g></g>'
                . '<g transform="translate(424 110)"><g class="s-float f3"><path d="M0 34 Q8 54 -2 74" stroke="#6B5E52" stroke-width="3" fill="none"/><ellipse rx="22" ry="28" fill="#2F72A8" stroke="' . $L . '" stroke-width="4"/><path d="M-11 -12 Q-9 -20 -2 -22" stroke="#fff" stroke-opacity=".7" stroke-width="5" stroke-linecap="round" fill="none"/></g></g>'
                . '<g transform="translate(252 58)"><g class="s-float f1"><ellipse cx="-8" cy="22" rx="13" ry="10" fill="#7C4DFF" stroke="' . $L . '" stroke-width="4"/><path d="M4 22 V-14 Q20 -8 22 6" stroke="' . $L . '" stroke-width="5" stroke-linecap="round" fill="none"/></g></g>'
                . '<g transform="translate(240 322)"><g class="s-bounce"><circle r="26" fill="#D2302A" stroke="' . $L . '" stroke-width="4"/><path d="M-22 -12 Q0 0 22 -12 M-22 12 Q0 0 22 12" stroke="#fff" stroke-width="4.5" fill="none" stroke-linecap="round"/><path d="M0 -26 V26" stroke="#fff" stroke-width="4.5"/></g></g>'
                . $star(196, 70, .9, 'sp1') . $star(340, 150, .7, 'sp2', '#7FD0F7') . $star(44, 250, .8, 'sp3', '#FF6F8E')
                . '<circle class="s-float f2" cx="150" cy="40" r="6" fill="#3BAA4A"/><circle class="s-float f3" cx="410" cy="230" r="7" fill="#FFC20E"/><rect class="s-float f1" x="30" y="190" width="13" height="13" rx="3" fill="#2F72A8"/><rect class="s-float f3" x="300" y="44" width="12" height="12" rx="3" fill="#FF6F8E"/>'
            );
        case 'milestone':
            $step = function (int $x, int $y, int $h, string $c, string $dark, string $label, string $d) use ($L) {
                $cx = $x + 55;
                return '<rect x="' . $x . '" y="' . $y . '" width="110" height="' . $h . '" rx="12" fill="' . $c . '" stroke="' . $L . '" stroke-width="5"/>'
                    . '<rect x="' . ($x + 8) . '" y="' . ($y + 7) . '" width="94" height="9" rx="4.5" fill="#fff" fill-opacity=".3"/>'
                    . '<text x="' . $cx . '" y="' . ($y + $h - 18) . '" text-anchor="middle" font-size="18" font-weight="800" fill="#fff" font-family="Be Vietnam Pro,system-ui,sans-serif">' . $label . '</text>'
                    . '<g class="s-flag ' . $d . '" transform="translate(' . ($x + 16) . ' ' . $y . ')"><path d="M0 0 V-54" stroke="' . $L . '" stroke-width="5" stroke-linecap="round"/><path class="flag-cloth" d="M2 -54 Q18 -62 30 -54 T58 -54 V-32 Q44 -24 30 -32 T2 -32Z" fill="' . $c . '" stroke="' . $L . '" stroke-width="3.5" stroke-linejoin="round"/></g>';
            };
            $trophy = '<g transform="translate(-4 -214)"><path d="M-16 -8 H16 V10 Q16 26 0 28 Q-16 26 -16 10Z" fill="#FFC20E" stroke="' . $L . '" stroke-width="4" stroke-linejoin="round"/><path d="M-16 -2 Q-30 -2 -28 10 Q-26 20 -14 20 M16 -2 Q30 -2 28 10 Q26 20 14 20" stroke="' . $L . '" stroke-width="4" fill="none" stroke-linecap="round"/><rect x="-8" y="28" width="16" height="10" fill="#E08A0C" stroke="' . $L . '" stroke-width="4"/><rect x="-18" y="38" width="36" height="9" rx="4" fill="#FFC20E" stroke="' . $L . '" stroke-width="4"/></g>';
            $conf = '';
            foreach ([[90, 40, '#D2302A'], [170, 20, '#FFC20E'], [250, 36, '#3BAA4A'], [330, 18, '#7C4DFF'], [410, 44, '#FF6F8E'], [130, 70, '#2F72A8'], [370, 80, '#F7931E']] as $i => [$cx, $cy, $col]) {
                $conf .= '<rect class="s-fall" style="animation-delay:-' . ($i * 0.7) . 's" x="' . $cx . '" y="' . $cy . '" width="11" height="15" rx="3" fill="' . $col . '" stroke="' . $L . '" stroke-width="2"/>';
            }
            return $svg(
                $ground(240, 340, 200)
                . $step(52, 276, 66, '#3BAA4A', '#24782F', 'Starters', 'd0')
                . $step(184, 232, 110, '#2F72A8', '#17446A', 'Movers', 'd1')
                . $step(316, 188, 154, '#D2302A', '#A21F1A', 'Flyers', 'd2')
                . kid(['x' => 370, 'y' => 190, 's' => .62, 'skin' => '#E8A878', 'hair' => '#2E241B', 'style' => 'short', 'shirt' => '#FFC20E', 'pants' => '#205984', 'shoe' => '#FFFFFF',
                    'al' => [-58, -176], 'ar' => [52, -198], 'holdR' => $trophy, 'cls' => 'k-jump'])
                . kid(['x' => 108, 'y' => 276, 's' => .5, 'skin' => '#FFD3B0', 'hair' => '#B97A4F', 'style' => 'pigtails', 'shirt' => '#FF6F8E', 'pants' => '#3BAA4A', 'shoe' => '#FFFFFF',
                    'al' => [-56, -166], 'waveL' => true, 'ar' => [56, -64], 'cls' => 'k-bob', 'delay' => '-1s'])
                . $sun(212, 142, 96)
                . $conf . $star(444, 150, .8, 'sp1') . $star(40, 160, .7, 'sp2', '#7FD0F7') . $star(250, 140, .6, 'sp3', '#FF6F8E')
            );
        case 'report':
            $row = fn(int $y, string $d, int $w) => '<g class="s-row ' . $d . '"><circle cx="248" cy="' . $y . '" r="17" fill="#3BAA4A" stroke="' . $L . '" stroke-width="3.5"/><path class="s-check" d="M239 ' . $y . ' l7 8 l13 -15" stroke="#fff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" fill="none" pathLength="30"/>'
                . '<rect x="276" y="' . ($y - 12) . '" width="' . $w . '" height="11" rx="5.5" fill="#E8E3DC"/><rect x="276" y="' . ($y + 5) . '" width="' . ($w - 30) . '" height="8" rx="4" fill="#F5F2EE"/></g>';
            return $svg(
                $ground(250, 340, 190)
                . $sun(330, 236, 130)
                . '<g transform="rotate(5 300 190)"><rect x="216" y="48" width="170" height="282" rx="30" fill="#fff" stroke="' . $L . '" stroke-width="6"/>'
                . '<rect x="232" y="64" width="138" height="38" rx="12" fill="#3BAA4A" stroke="' . $L . '" stroke-width="4"/><text x="301" y="89" text-anchor="middle" font-size="16" font-weight="800" fill="#fff" font-family="Be Vietnam Pro,system-ui,sans-serif">Báo cáo của con</text>'
                . $row(136, 'd0', 78) . $row(190, 'd1', 70) . $row(244, 'd2', 80)
                . '<rect x="276" y="282" width="80" height="10" rx="5" fill="#E8E3DC"/>'
                . '<g transform="translate(352 60)"><g class="s-stamp"><path d="M0 -30 L9 -10 L31 -8 L14 7 L19 29 L0 18 L-19 29 L-14 7 L-31 -8 L-9 -10Z" fill="#FFC20E" stroke="' . $L . '" stroke-width="4" stroke-linejoin="round"/></g></g></g>'
                . kid(['x' => 104, 'y' => 336, 's' => .95, 'skin' => '#F0B48A', 'hair' => '#2E241B', 'style' => 'bob', 'shirt' => '#7C4DFF', 'pants' => '#2F72A8', 'shoe' => '#D2302A',
                    'al' => [-58, -66], 'ar' => [86, -116], 'cls' => 'k-bob'])
                . kid(['x' => 178, 'y' => 336, 's' => .56, 'skin' => '#FFD3B0', 'hair' => '#7A3F18', 'style' => 'short', 'shirt' => '#FFC20E', 'pants' => '#3BAA4A', 'shoe' => '#FFFFFF',
                    'al' => [-56, -166], 'waveL' => true, 'ar' => [52, -70], 'cls' => 'k-bob', 'delay' => '-1.1s'])
                . $star(60, 80, .8, 'sp1') . $star(420, 330, .7, 'sp2', '#FF6F8E') . $star(196, 60, .6, 'sp3', '#7FD0F7')
            );
    }
    return '';
}

/** Các icon 3D phẳng bay lơ lửng làm nền cho mục Cơ sở vật chất. Mỗi icon: [svg, x%, y%, kích thước px, xoay độ, độ sâu, ẩn trên mobile] */
function facility_icons(): string
{
    $L = SUN_LINE;
    $block = fn(string $ch, string $c, string $d) => '<svg viewBox="0 0 80 80"><rect x="10" y="12" width="60" height="60" rx="14" fill="' . $d . '"/><rect x="4" y="4" width="62" height="62" rx="14" fill="' . $c . '"/><rect x="10" y="9" width="50" height="9" rx="4.5" fill="#fff" fill-opacity=".28"/><text x="35" y="50" text-anchor="middle" font-size="38" font-weight="900" fill="#fff" font-family="Be Vietnam Pro,system-ui,sans-serif">' . $ch . '</text></svg>';
    $icons = [
        [$block('A', '#2F72A8', '#17446A'), 3, 10, 78, -12, 1.0, 0],
        [$block('B', '#D2302A', '#8E1F1A'), 91, 62, 72, 14, 0.8, 0],
        [$block('C', '#3BAA4A', '#1E6A2A'), 7, 70, 68, 10, 0.9, 0],
        [$block('Z', '#7C4DFF', '#4527A0'), 88, 8, 64, -16, 0.7, 1],
        // sách mở
        ['<svg viewBox="0 0 90 80"><path d="M6 20 Q30 8 45 20 V68 Q30 56 6 68Z" fill="#fff" stroke="' . $L . '" stroke-width="4" stroke-linejoin="round"/><path d="M84 20 Q60 8 45 20 V68 Q60 56 84 68Z" fill="#FFF3C4" stroke="' . $L . '" stroke-width="4" stroke-linejoin="round"/><path d="M14 30 Q28 24 38 30 M14 42 Q28 36 38 42 M52 30 Q62 24 76 30 M52 42 Q62 36 76 42" stroke="#2F72A8" stroke-width="3.5" stroke-linecap="round" fill="none"/></svg>', 80, 20, 84, 8, 0.9, 0],
        // sao
        ['<svg viewBox="0 0 80 80"><path d="M40 6 L50 30 L76 32 L56 49 L63 74 L40 60 L17 74 L24 49 L4 32 L30 30Z" fill="#FFC20E" stroke="' . $L . '" stroke-width="4" stroke-linejoin="round"/><path d="M40 16 L46 32" stroke="#fff" stroke-opacity=".7" stroke-width="5" stroke-linecap="round"/></svg>', 12, 38, 56, -8, 1.2, 0],
        ['<svg viewBox="0 0 80 80"><path d="M40 6 L50 30 L76 32 L56 49 L63 74 L40 60 L17 74 L24 49 L4 32 L30 30Z" fill="#FF6F8E" stroke="' . $L . '" stroke-width="4" stroke-linejoin="round"/></svg>', 76, 78, 46, 12, 1.3, 1],
        // bút chì
        ['<svg viewBox="0 0 90 90"><g transform="rotate(40 45 45)"><rect x="34" y="4" width="22" height="64" rx="4" fill="#FFC20E" stroke="' . $L . '" stroke-width="4"/><path d="M34 68 L45 88 L56 68Z" fill="#F6E6D8" stroke="' . $L . '" stroke-width="4" stroke-linejoin="round"/><path d="M41 80 L45 88 L49 80Z" fill="' . $L . '"/><rect x="34" y="-2" width="22" height="12" rx="4" fill="#FF6F8E" stroke="' . $L . '" stroke-width="4"/></g></svg>', 94, 36, 72, 0, 0.8, 1],
        // quả địa cầu
        ['<svg viewBox="0 0 80 80"><circle cx="40" cy="38" r="30" fill="#4FC3F7" stroke="' . $L . '" stroke-width="4"/><path d="M22 28 Q34 22 38 34 Q30 44 20 40Z M46 20 Q60 22 64 36 Q54 40 48 32Z M40 56 Q52 52 58 60 Q46 66 40 56Z" fill="#3BAA4A" stroke="' . $L . '" stroke-width="2.5" stroke-linejoin="round"/><path d="M26 74 H54" stroke="' . $L . '" stroke-width="5" stroke-linecap="round"/></svg>', 1, 44, 70, 6, 0.7, 1],
        // cặp sách
        ['<svg viewBox="0 0 80 90"><rect x="12" y="20" width="56" height="62" rx="16" fill="#F7931E" stroke="' . $L . '" stroke-width="4"/><path d="M28 20 Q28 6 40 6 Q52 6 52 20" stroke="' . $L . '" stroke-width="4" fill="none"/><rect x="22" y="50" width="36" height="22" rx="8" fill="#FFC20E" stroke="' . $L . '" stroke-width="4"/><path d="M26 38 H54" stroke="' . $L . '" stroke-width="4" stroke-linecap="round"/></svg>', 18, 90, 70, -6, 1.0, 0],
        // mũ cử nhân
        ['<svg viewBox="0 0 90 70"><path d="M20 34 V52 Q45 66 70 52 V34 Q45 48 20 34Z" fill="#C0621F" stroke="' . $L . '" stroke-width="4" stroke-linejoin="round"/><path d="M4 28 L45 8 L86 28 L45 48Z" fill="#D8742D" stroke="' . $L . '" stroke-width="4" stroke-linejoin="round"/><path d="M78 32 V56" stroke="' . $L . '" stroke-width="4" stroke-linecap="round"/><circle cx="78" cy="58" r="5" fill="#FFC20E" stroke="' . $L . '" stroke-width="3"/></svg>', 86, 88, 86, 10, 0.9, 0],
        // huy chương
        ['<svg viewBox="0 0 70 90"><path d="M18 4 L35 34 L52 4Z" fill="#D2302A" stroke="' . $L . '" stroke-width="4" stroke-linejoin="round"/><circle cx="35" cy="56" r="22" fill="#FFC20E" stroke="' . $L . '" stroke-width="4"/><path d="M35 44 l4 9 10 1 -7.5 6.5 2.5 9.5 -9 -5.5 -9 5.5 2.5 -9.5 -7.5 -6.5 10 -1Z" fill="#fff" stroke="' . $L . '" stroke-width="2.5" stroke-linejoin="round"/></svg>', 63, 6, 60, -10, 1.1, 1],
        // bóng bay
        ['<svg viewBox="0 0 60 100"><path d="M30 70 Q22 84 32 98" stroke="#6B5E52" stroke-width="3" fill="none"/><ellipse cx="30" cy="34" rx="24" ry="30" fill="#7C4DFF" stroke="' . $L . '" stroke-width="4"/><path d="M30 64 l-5 8 h10Z" fill="#7C4DFF" stroke="' . $L . '" stroke-width="3" stroke-linejoin="round"/><path d="M16 22 Q18 12 28 9" stroke="#fff" stroke-opacity=".7" stroke-width="5" stroke-linecap="round" fill="none"/></svg>', 24, 6, 52, 8, 1.2, 1],
        // nốt nhạc
        ['<svg viewBox="0 0 60 80"><ellipse cx="18" cy="62" rx="14" ry="11" fill="#3BAA4A" stroke="' . $L . '" stroke-width="4"/><path d="M30 62 V12 Q50 18 52 38" stroke="' . $L . '" stroke-width="6" stroke-linecap="round" fill="none"/></svg>', 70, 90, 52, -8, 1.0, 1],
        // tai nghe
        ['<svg viewBox="0 0 90 80"><path d="M14 50 V40 Q14 10 45 10 Q76 10 76 40 V50" stroke="' . $L . '" stroke-width="7" fill="none" stroke-linecap="round"/><rect x="6" y="44" width="22" height="30" rx="9" fill="#D2302A" stroke="' . $L . '" stroke-width="4"/><rect x="62" y="44" width="22" height="30" rx="9" fill="#D2302A" stroke="' . $L . '" stroke-width="4"/></svg>', 40, 94, 70, -4, 0.8, 1],
        // cúp
        ['<svg viewBox="0 0 80 90"><path d="M20 8 H60 V30 Q60 52 40 56 Q20 52 20 30Z" fill="#FFC20E" stroke="' . $L . '" stroke-width="4" stroke-linejoin="round"/><path d="M20 14 Q4 14 6 30 Q8 42 22 42 M60 14 Q76 14 74 30 Q72 42 58 42" stroke="' . $L . '" stroke-width="4" fill="none" stroke-linecap="round"/><rect x="34" y="56" width="12" height="14" fill="#E08A0C" stroke="' . $L . '" stroke-width="4"/><rect x="22" y="70" width="36" height="12" rx="5" fill="#FFC20E" stroke="' . $L . '" stroke-width="4"/></svg>', 52, 4, 62, 6, 0.9, 1],
    ];
    $dots = [[10, 22, 14, '#fff'], [30, 52, 10, '#FFC20E'], [66, 40, 12, '#fff'], [92, 24, 10, '#FF6F8E'], [46, 14, 8, '#7C4DFF'], [20, 56, 9, '#3BAA4A'], [84, 48, 14, '#fff'], [58, 96, 10, '#FFC20E']];
    $out = '<div class="fx-layer" aria-hidden="true">';
    foreach ($icons as $i => [$svg, $x, $y, $w, $r, $z, $hide]) {
        $out .= '<span class="fx' . ($hide ? ' hide-m' : '') . '" style="--x:' . $x . '%;--y:' . $y . '%;--w:' . $w . 'px;--r:' . $r . 'deg;--z:' . $z . ';--d:-' . round($i * 0.8, 1) . 's;--t:' . (5 + ($i % 4)) . 's">' . $svg . '</span>';
    }
    foreach ($dots as $i => [$x, $y, $d, $c]) {
        $out .= '<span class="fx-dot' . ($i % 2 ? ' hide-m' : '') . '" style="--x:' . $x . '%;--y:' . $y . '%;--w:' . $d . 'px;--c:' . $c . ';--d:-' . ($i * 0.6) . 's"></span>';
    }
    return $out . '</div>';
}
