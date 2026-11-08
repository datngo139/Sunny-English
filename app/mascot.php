<?php
/**
 * Linh vật "Sunny" (mặt trời vui vẻ) và các cảnh minh hoạ động, vẽ bằng SVG.
 * Màu lấy từ logo/design system. Chuyển động nằm trong assets/css/sunny.css và assets/js/site.js.
 */
declare(strict_types=1);

/** Đặt một lần trong trang: gradient dùng chung + biểu tượng mặt trời nhỏ. */
function sun_defs(): string
{
    $rays = '';
    for ($i = 0; $i < 10; $i++) {
        $rays .= '<path transform="rotate(' . ($i * 36) . ' 100 100)" d="M100 28 C108 28 111 14 100 4 C89 14 92 28 100 28Z" fill="url(#gRay)" stroke="#E57E17" stroke-width="2" stroke-linejoin="round"/>';
    }
    return '<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false"><defs>'
        . '<radialGradient id="gSun" cx="35%" cy="30%" r="80%"><stop offset="0" stop-color="#FFE585"/><stop offset=".45" stop-color="#FDBE10"/><stop offset="1" stop-color="#F7A416"/></radialGradient>'
        . '<linearGradient id="gRay" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#FFD84D"/><stop offset="1" stop-color="#F7931E"/></linearGradient>'
        . '<symbol id="sunFace" viewBox="0 0 200 200">' . $rays
        . '<circle cx="100" cy="100" r="58" fill="url(#gSun)" stroke="#B5652B" stroke-width="5"/>'
        . '<path d="M64 78 Q74 58 98 52" stroke="#fff" stroke-opacity=".6" stroke-width="6" stroke-linecap="round" fill="none"/>'
        . '<ellipse cx="82" cy="96" rx="9" ry="12" fill="#fff" stroke="#4A2610" stroke-width="3"/><circle cx="84" cy="98" r="5.5" fill="#2E241B"/><circle cx="85.8" cy="95.8" r="1.8" fill="#fff"/>'
        . '<ellipse cx="118" cy="96" rx="9" ry="12" fill="#fff" stroke="#4A2610" stroke-width="3"/><circle cx="120" cy="98" r="5.5" fill="#2E241B"/><circle cx="121.8" cy="95.8" r="1.8" fill="#fff"/>'
        . '<ellipse cx="67" cy="114" rx="8" ry="5.5" fill="#FF8A5C" fill-opacity=".5"/><ellipse cx="133" cy="114" rx="8" ry="5.5" fill="#FF8A5C" fill-opacity=".5"/>'
        . '<path d="M82 112 Q100 140 118 112 Z" fill="#7A2A12" stroke="#4A2610" stroke-width="3" stroke-linejoin="round"/>'
        . '<path d="M91 126 Q100 134 109 126 Q100 121 91 126Z" fill="#FF7B6B"/>'
        . '</symbol>'
        . '</defs></svg>';
}

/** Linh vật lớn ở đầu trang: bấm để chào, mắt nhìn theo con trỏ. */
function mascot_hero(): string
{
    $rays = '';
    for ($i = 0; $i < 12; $i++) {
        $rays .= '<path transform="rotate(' . ($i * 30) . ' 200 190)" d="M200 62 C212 62 217 40 200 14 C183 40 188 62 200 62Z" fill="url(#gRay)" stroke="#E57E17" stroke-width="3" stroke-linejoin="round"/>';
    }
    $spark = fn(int $x, int $y, int $s, string $delay) => '<path class="m-spark" style="animation-delay:' . $delay . '" transform="translate(' . $x . ' ' . $y . ') scale(' . $s . ')" d="M0 -12 Q2 -2 12 0 Q2 2 0 12 Q-2 2 -12 0 Q-2 -2 0 -12Z" fill="#FFC20E" stroke="#E08A0C" stroke-width="1.5" stroke-linejoin="round"/>';

    return '<button type="button" class="mascot" id="mascot" aria-label="Linh vật mặt trời Sunny. Bấm để Sunny chào con">'
        . '<svg viewBox="0 0 400 440" role="img" aria-hidden="true" focusable="false">'
        . '<defs><clipPath id="mMouth"><path d="M160 214 Q200 284 240 214 Z"/></clipPath></defs>'
        . '<ellipse class="m-shadow" cx="200" cy="418" rx="128" ry="13" fill="#4A2610" fill-opacity=".13"/>'
        . $spark(66, 96, 1, '0s') . $spark(338, 140, 1, '.8s') . $spark(52, 300, 1, '1.4s') . $spark(352, 318, 1, '.4s')
        . '<g class="m-jump"><g class="m-float">'
        // sách mở (như logo) làm bục đứng
        . '<g class="m-book">'
        . '<path d="M84 352 Q142 332 200 358 L200 398 Q142 372 84 394Z" fill="#fff" stroke="#205984" stroke-width="5" stroke-linejoin="round"/>'
        . '<path d="M316 352 Q258 332 200 358 L200 398 Q258 372 316 394Z" fill="#fff" stroke="#205984" stroke-width="5" stroke-linejoin="round"/>'
        . '<path d="M92 346 Q146 326 200 352" stroke="#3BAA4A" stroke-width="8" stroke-linecap="round" fill="none"/>'
        . '<path d="M308 346 Q254 326 200 352" stroke="#3BAA4A" stroke-width="8" stroke-linecap="round" fill="none"/>'
        . '<path d="M96 366 Q146 348 196 372" stroke="#2F72A8" stroke-width="6" stroke-linecap="round" fill="none"/>'
        . '<path d="M304 366 Q254 348 204 372" stroke="#2F72A8" stroke-width="6" stroke-linecap="round" fill="none"/>'
        . '<path d="M98 382 Q146 366 196 388" stroke="#D2302A" stroke-width="6" stroke-linecap="round" fill="none"/>'
        . '<path d="M302 382 Q254 366 204 388" stroke="#D2302A" stroke-width="6" stroke-linecap="round" fill="none"/>'
        . '</g>'
        // tia nắng quay chậm
        . '<g class="m-rays">' . $rays . '</g>'
        // chân + giày
        . '<g class="m-legs">'
        . '<rect x="158" y="282" width="20" height="56" rx="10" fill="#FDBE10" stroke="#B5652B" stroke-width="5"/>'
        . '<rect x="222" y="282" width="20" height="56" rx="10" fill="#FDBE10" stroke="#B5652B" stroke-width="5"/>'
        . '<ellipse cx="164" cy="342" rx="28" ry="13" fill="#205984" stroke="#0F2C45" stroke-width="5"/>'
        . '<ellipse cx="236" cy="342" rx="28" ry="13" fill="#205984" stroke="#0F2C45" stroke-width="5"/>'
        . '<path d="M146 338 Q164 330 178 336" stroke="#7FAED6" stroke-width="4" stroke-linecap="round" fill="none"/>'
        . '<path d="M218 336 Q236 330 254 338" stroke="#7FAED6" stroke-width="4" stroke-linecap="round" fill="none"/>'
        . '</g>'
        // tay phải (rủ xuống)
        . '<g class="m-arm-r"><path d="M298 222 Q334 238 340 284" stroke="#B5652B" stroke-width="30" stroke-linecap="round" fill="none"/><path d="M298 222 Q334 238 340 284" stroke="#FDBE10" stroke-width="20" stroke-linecap="round" fill="none"/><circle cx="340" cy="288" r="17" fill="#fff" stroke="#B5652B" stroke-width="5"/></g>'
        // thân
        . '<circle cx="200" cy="190" r="104" fill="url(#gSun)" stroke="#B5652B" stroke-width="6"/>'
        . '<path d="M128 132 Q148 102 192 94" stroke="#fff" stroke-opacity=".6" stroke-width="10" stroke-linecap="round" fill="none"/>'
        // mặt
        . '<path d="M144 144 Q164 128 184 140" stroke="#4A2610" stroke-width="6" stroke-linecap="round" fill="none"/>'
        . '<path d="M216 140 Q236 128 256 144" stroke="#4A2610" stroke-width="6" stroke-linecap="round" fill="none"/>'
        . '<g class="m-eye"><ellipse cx="164" cy="180" rx="22" ry="28" fill="#fff" stroke="#4A2610" stroke-width="5"/><g class="m-pupil" data-eye="l"><circle cx="164" cy="184" r="12" fill="#2E241B"/><circle cx="168" cy="178" r="4.5" fill="#fff"/></g></g>'
        . '<g class="m-eye"><ellipse cx="236" cy="180" rx="22" ry="28" fill="#fff" stroke="#4A2610" stroke-width="5"/><g class="m-pupil" data-eye="r"><circle cx="236" cy="184" r="12" fill="#2E241B"/><circle cx="240" cy="178" r="4.5" fill="#fff"/></g></g>'
        . '<ellipse cx="136" cy="226" rx="17" ry="11" fill="#FF8A5C" fill-opacity=".5"/><ellipse cx="264" cy="226" rx="17" ry="11" fill="#FF8A5C" fill-opacity=".5"/>'
        . '<g class="m-mouth"><path d="M160 214 Q200 284 240 214 Z" fill="#7A2A12"/>'
        . '<g clip-path="url(#mMouth)"><rect x="160" y="212" width="80" height="14" fill="#fff"/><ellipse cx="200" cy="262" rx="26" ry="16" fill="#FF7B6B"/></g>'
        . '<path d="M160 214 Q200 284 240 214 Z" fill="none" stroke="#4A2610" stroke-width="5" stroke-linejoin="round"/></g>'
        // mũ cử nhân
        . '<g transform="translate(244 86) rotate(16)"><g class="m-tassel"><path d="M-46 4 Q-56 30 -52 52" stroke="#B5652B" stroke-width="5" stroke-linecap="round" fill="none"/><ellipse cx="-52" cy="58" rx="7" ry="10" fill="#FDBE10" stroke="#B5652B" stroke-width="3"/></g>'
        . '<path d="M-36 8 L-36 38 Q0 56 36 38 L36 8 Q0 28 -36 8Z" fill="#C0621F" stroke="#7A3F18" stroke-width="5" stroke-linejoin="round"/>'
        . '<path d="M-68 0 L0 -30 L68 0 L0 30Z" fill="#D8742D" stroke="#7A3F18" stroke-width="5" stroke-linejoin="round"/><path d="M-40 -2 L0 -18" stroke="#fff" stroke-opacity=".35" stroke-width="5" stroke-linecap="round"/><circle cx="0" cy="0" r="5" fill="#7A3F18"/></g>'
        // tay trái (vẫy)
        . '<g class="m-wave"><path d="M104 220 Q64 204 56 154" stroke="#B5652B" stroke-width="30" stroke-linecap="round" fill="none"/><path d="M104 220 Q64 204 56 154" stroke="#FDBE10" stroke-width="20" stroke-linecap="round" fill="none"/><circle cx="54" cy="146" r="19" fill="#fff" stroke="#B5652B" stroke-width="5"/><path d="M45 136 L48 126 M54 132 L55 121 M63 136 L67 127" stroke="#B5652B" stroke-width="4" stroke-linecap="round"/></g>'
        . '</g></g>'
        // bóng thoại
        . '<g class="m-bubble" id="mBubble"><path d="M118 82 L104 112 L152 82Z" fill="#fff" stroke="#B5652B" stroke-width="5" stroke-linejoin="round"/>'
        . '<rect x="20" y="16" width="190" height="66" rx="22" fill="#fff" stroke="#B5652B" stroke-width="5"/><path d="M122 80 H148" stroke="#fff" stroke-width="8"/>'
        . '<text id="mSay" x="115" y="60" text-anchor="middle" font-size="24" font-weight="800" fill="#7A3F18" font-family="Be Vietnam Pro,system-ui,sans-serif">Hello!</text></g>'
        . '</svg></button>';
}

/** Một cảnh minh hoạ động cho khối "Về Sunny". $name: speak | fun | milestone | report */
function scene_svg(string $name): string
{
    $svg = fn(string $inner) => '<svg class="scene scene-' . $name . '" viewBox="0 0 400 320" aria-hidden="true" focusable="false">' . $inner . '</svg>';
    $bubble = fn(int $x, int $y, int $w, string $text, string $cls) => '<g class="s-pop ' . $cls . '">'
        . '<path d="M' . ($x + 22) . ' ' . ($y + 46) . ' L' . ($x + 14) . ' ' . ($y + 68) . ' L' . ($x + 44) . ' ' . ($y + 46) . 'Z" fill="#fff" stroke="#B5652B" stroke-width="4" stroke-linejoin="round"/>'
        . '<rect x="' . $x . '" y="' . $y . '" width="' . $w . '" height="50" rx="16" fill="#fff" stroke="#B5652B" stroke-width="4"/>'
        . '<path d="M' . ($x + 26) . ' ' . ($y + 48) . ' H' . ($x + 42) . '" stroke="#fff" stroke-width="6"/>'
        . '<text x="' . ($x + $w / 2) . '" y="' . ($y + 33) . '" text-anchor="middle" font-size="22" font-weight="800" fill="#7A3F18" font-family="Be Vietnam Pro,system-ui,sans-serif">' . $text . '</text></g>';

    switch ($name) {
        case 'speak':
            return $svg(
                $bubble(18, 24, 108, 'Hello!', 'd0')
                . $bubble(236, 18, 96, 'Yes!', 'd1')
                . '<g class="s-waves"><path d="M286 168 Q302 188 286 208" stroke="#F7A416" stroke-width="7" stroke-linecap="round" fill="none"/><path d="M306 156 Q330 188 306 220" stroke="#F7A416" stroke-width="7" stroke-linecap="round" fill="none"/><path d="M326 144 Q358 188 326 232" stroke="#F7A416" stroke-width="7" stroke-linecap="round" fill="none"/></g>'
                . '<ellipse cx="158" cy="296" rx="82" ry="9" fill="#4A2610" fill-opacity=".12"/>'
                . '<use class="s-bob" href="#sunFace" x="58" y="98" width="200" height="200"/>'
                . '<path d="M208 206 Q228 220 244 214" stroke="#B5652B" stroke-width="18" stroke-linecap="round" fill="none"/><path d="M208 206 Q228 220 244 214" stroke="#FDBE10" stroke-width="10" stroke-linecap="round" fill="none"/>'
                . '<g transform="rotate(24 244 190)"><rect x="236" y="190" width="16" height="58" rx="8" fill="#205984" stroke="#0F2C45" stroke-width="4"/><rect x="230" y="150" width="28" height="48" rx="14" fill="#46392D" stroke="#2E241B" stroke-width="4"/><path d="M233 166 h22 M233 176 h22 M233 186 h22" stroke="#8C8075" stroke-width="3"/></g>'
                . '<circle cx="245" cy="215" r="10" fill="#fff" stroke="#B5652B" stroke-width="4"/>'
                . $bubble(30, 244, 70, 'Hi!', 'd2')
            );
        case 'fun':
            return $svg(
                '<ellipse cx="200" cy="298" rx="96" ry="10" fill="#4A2610" fill-opacity=".12"/>'
                . '<use class="s-bob" href="#sunFace" x="86" y="62" width="228" height="228"/>'
                . '<g transform="translate(76 74)"><path class="s-float f1" d="M0 -26 L8 -8 L28 -6 L13 7 L18 27 L0 16 L-18 27 L-13 7 L-28 -6 L-8 -8Z" fill="#FFC20E" stroke="#E08A0C" stroke-width="4" stroke-linejoin="round"/></g>'
                . '<g transform="translate(326 78)"><g class="s-float f2"><ellipse cx="-8" cy="34" rx="15" ry="11" fill="#2F72A8" stroke="#17446A" stroke-width="4"/><path d="M5 34 V-16 Q22 -10 26 6" stroke="#17446A" stroke-width="6" stroke-linecap="round" fill="none"/></g></g>'
                . '<g transform="translate(84 250)"><g class="s-bounce"><circle r="30" fill="#D2302A" stroke="#A21F1A" stroke-width="4"/><path d="M-26 -14 Q0 0 26 -14 M-26 14 Q0 0 26 14" stroke="#fff" stroke-width="5" fill="none" stroke-linecap="round"/><path d="M0 -30 V30" stroke="#fff" stroke-width="5"/></g></g>'
                . '<g transform="translate(326 232)"><g class="s-float f3"><path d="M0 40 Q-6 56 4 70" stroke="#8C8075" stroke-width="3" fill="none"/><ellipse rx="25" ry="31" fill="#3BAA4A" stroke="#24782F" stroke-width="4"/><path d="M-12 -14 Q-10 -22 -2 -24" stroke="#fff" stroke-opacity=".6" stroke-width="5" stroke-linecap="round" fill="none"/></g></g>'
                . '<circle class="s-float f2" cx="42" cy="160" r="7" fill="#D2302A"/><rect class="s-float f1" x="352" y="160" width="14" height="14" rx="3" fill="#F7931E"/><circle class="s-float f3" cx="130" cy="40" r="6" fill="#2F72A8"/><rect class="s-float f3" x="250" y="34" width="12" height="12" rx="3" fill="#D2302A"/>'
            );
        case 'milestone':
            $step = function (int $x, int $y, int $h, string $c, string $dark, string $label, string $flag, string $d) {
                $cx = $x + 50;
                return '<rect x="' . $x . '" y="' . $y . '" width="100" height="' . $h . '" rx="10" fill="' . $c . '" stroke="' . $dark . '" stroke-width="4"/>'
                    . '<rect x="' . ($x + 6) . '" y="' . ($y + 5) . '" width="88" height="8" rx="4" fill="#fff" fill-opacity=".3"/>'
                    . '<text x="' . $cx . '" y="' . ($y + $h - 16) . '" text-anchor="middle" font-size="17" font-weight="800" fill="#fff" font-family="Be Vietnam Pro,system-ui,sans-serif">' . $label . '</text>'
                    . '<g class="s-flag ' . $d . '" transform="translate(' . ($cx + 18) . ' ' . $y . ')"><path d="M0 0 V-48" stroke="' . $dark . '" stroke-width="4" stroke-linecap="round"/><path class="flag-cloth" d="M2 -48 Q16 -56 28 -48 T54 -48 V-28 Q40 -20 28 -28 T2 -28Z" fill="' . $flag . '" stroke="' . $dark . '" stroke-width="3" stroke-linejoin="round"/></g>';
            };
            return $svg(
                '<rect x="20" y="268" width="360" height="12" rx="6" fill="#4A2610" fill-opacity=".12"/>'
                . $step(40, 206, 62, '#3BAA4A', '#24782F', 'Starters', '#fff', 'd0')
                . $step(150, 164, 104, '#2F72A8', '#17446A', 'Movers', '#fff', 'd1')
                . $step(260, 122, 146, '#D2302A', '#A21F1A', 'Flyers', '#fff', 'd2')
                . '<use class="s-climb" href="#sunFace" x="55" y="140" width="72" height="72"/>'
                . '<g transform="translate(338 96)"><path class="s-star" d="M0 -14 L4 -4 L15 -3 L7 4 L9 15 L0 9 L-9 15 L-7 4 L-15 -3 L-4 -4Z" fill="#FFC20E" stroke="#E08A0C" stroke-width="2.5" stroke-linejoin="round"/></g>'
                . '<g transform="translate(246 100)"><path class="s-star s2" d="M0 -10 L3 -3 L11 -2 L5 3 L6 11 L0 7 L-6 11 L-5 3 L-11 -2 L-3 -3Z" fill="#FFC20E" stroke="#E08A0C" stroke-width="2" stroke-linejoin="round"/></g>'
            );
        case 'report':
            $row = fn(int $y, string $d, int $w) => '<g class="s-row ' . $d . '"><circle cx="138" cy="' . $y . '" r="17" fill="#3BAA4A" stroke="#24782F" stroke-width="3"/><path class="s-check" d="M129 ' . $y . ' l7 8 l13 -15" stroke="#fff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" fill="none" pathLength="30"/>'
                . '<rect x="168" y="' . ($y - 12) . '" width="' . $w . '" height="11" rx="5.5" fill="#E8E3DC"/><rect x="168" y="' . ($y + 5) . '" width="' . ($w - 34) . '" height="8" rx="4" fill="#F5F2EE"/></g>';
            return $svg(
                '<use class="s-bob" href="#sunFace" x="6" y="170" width="150" height="150"/>'
                . '<g transform="rotate(-4 200 160)"><rect x="96" y="38" width="216" height="246" rx="24" fill="#fff" stroke="#A21F1A" stroke-width="5"/>'
                . '<rect x="166" y="24" width="76" height="30" rx="12" fill="#D2302A" stroke="#A21F1A" stroke-width="4"/>'
                . $row(106, 'd0', 110) . $row(160, 'd1', 100) . $row(214, 'd2', 112)
                . '<g transform="translate(284 64)"><g class="s-stamp"><path d="M0 -30 L9 -10 L31 -8 L14 7 L19 29 L0 18 L-19 29 L-14 7 L-31 -8 L-9 -10Z" fill="#FFC20E" stroke="#E08A0C" stroke-width="4" stroke-linejoin="round"/></g></g></g>'
                . '<g class="s-pencil" transform="translate(300 256) rotate(38)"><rect x="-8" y="-62" width="16" height="70" rx="3" fill="#F7A416" stroke="#B5652B" stroke-width="3.5"/><path d="M-8 8 L0 28 L8 8Z" fill="#F6E6D8" stroke="#B5652B" stroke-width="3.5" stroke-linejoin="round"/><rect x="-8" y="-72" width="16" height="12" rx="3" fill="#FF8A7A" stroke="#B5652B" stroke-width="3.5"/></g>'
            );
    }
    return '';
}
