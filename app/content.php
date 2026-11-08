<?php
/**
 * NỘI DUNG TRANG CHỦ — sửa chữ ở đây (không cần biết lập trình, chỉ giữ nguyên dấu nháy và dấu phẩy).
 *
 * Lưu ý: toàn bộ nội dung dưới đây là NỘI DUNG MẪU. Hãy thay bằng thông tin thật của trung tâm
 * (sĩ số lớp, lịch học, học phí, chứng chỉ...) trước khi chạy quảng cáo.
 */

/** 4 khoá học mẫu: chỉ dùng để nạp vào CSDL ở lần chạy đầu. Sau đó admin sửa trong trang quản trị → Khoá học. */
function sunny_default_courses(): array
{
    return [
        [
            'id' => 'mamnon', 'level' => 'mamnon', 'icon' => 'baby',
            'title' => 'Tiếng Anh Mầm Non', 'ages' => '3–6 tuổi',
            'desc' => 'Bé làm quen tiếng Anh qua bài hát, câu chuyện và trò chơi vận động.',
            'headline' => 'Con làm quen tiếng Anh tự nhiên, không sợ nói',
            'outcome' => 'Con mạnh dạn chào hỏi, hát và trả lời câu hỏi đơn giản bằng tiếng Anh.',
            'points' => ['Học qua bài hát, flashcard và trò chơi', 'Phát âm chuẩn ngay từ đầu', 'Lớp nhỏ, cô giáo theo sát từng bé'],
        ],
        [
            'id' => 'tieuhoc', 'level' => 'tieuhoc', 'icon' => 'backpack',
            'title' => 'Tiếng Anh Tiểu Học', 'ages' => '6–11 tuổi',
            'desc' => 'Phát triển đủ 4 kỹ năng nghe, nói, đọc, viết theo lộ trình rõ ràng.',
            'headline' => 'Con tự tin nói tiếng Anh, sẵn sàng thi Movers – Flyers',
            'outcome' => 'Mỗi cấp độ kết thúc bằng một mốc Cambridge Starters, Movers hoặc Flyers.',
            'points' => ['Bám sát chương trình Cambridge Starters – Movers – Flyers', 'Nói tiếng Anh trong mọi tiết học', 'Báo cáo tiến bộ gửi ba mẹ hằng tháng'],
        ],
        [
            'id' => 'trunghoc', 'level' => 'trunghoc', 'icon' => 'school',
            'title' => 'Tiếng Anh Trung Học', 'ages' => '11–14 tuổi',
            'desc' => 'Củng cố ngữ pháp, mở rộng từ vựng và tự tin thuyết trình bằng tiếng Anh.',
            'headline' => 'Con vững ngữ pháp, nâng điểm tiếng Anh trên trường',
            'outcome' => 'Kiểm tra định kỳ bám theo đề trên lớp để ba mẹ thấy điểm con thay đổi.',
            'points' => ['Ngữ pháp và từ vựng theo chủ đề', 'Dự án thuyết trình nhóm', 'Hỗ trợ chương trình tiếng Anh trên lớp'],
        ],
        [
            'id' => 'luyenthi', 'level' => 'luyenthi', 'icon' => 'award',
            'title' => 'Luyện thi chứng chỉ', 'ages' => 'Mọi lứa tuổi',
            'desc' => 'Lộ trình luyện thi các chứng chỉ tiếng Anh quốc tế với đề thi thử định kỳ.',
            'headline' => 'Con đi thi chứng chỉ với tâm thế đã quen đề',
            'outcome' => 'Thi thử định kỳ, chấm như thi thật, báo điểm từng kỹ năng cho ba mẹ.',
            'points' => ['Kiểm tra đầu vào, xếp lớp theo trình độ', 'Thi thử định kỳ, chấm như thi thật', 'Chữa bài chi tiết từng kỹ năng'],
        ],
    ];
}

/** Khoá học đang hiển thị (lấy từ CSDL, admin chỉnh được). $all = true: lấy cả khoá đã ẩn. */
function sunny_courses(bool $all = false): array
{
    $sql = 'SELECT * FROM courses' . ($all ? '' : ' WHERE active = 1') . ' ORDER BY sort, id';
    $out = [];
    foreach (db()->query($sql) as $r) {
        $out[] = [
            'id' => $r['code'], 'dbid' => (int) $r['id'], 'level' => $r['level'], 'icon' => $r['icon'],
            'title' => $r['title'], 'ages' => $r['ages'], 'desc' => $r['description'],
            'headline' => $r['headline'], 'outcome' => $r['outcome'],
            'points' => array_values(array_filter(array_map('trim', preg_split('/\R/u', (string) $r['points'])))),
            'image' => $r['image'], 'active' => (int) $r['active'],
        ];
    }
    return $out;
}

/** Màu thẻ khoá học (khớp 4 màu cấp độ của design system). */
function sunny_levels(): array
{
    return ['mamnon' => 'Cam', 'tieuhoc' => 'Xanh lá', 'trunghoc' => 'Xanh dương', 'luyenthi' => 'Đỏ'];
}

/** Biểu tượng cho thẻ khoá học (tên icon Lucide). */
function sunny_icons(): array
{
    return ['baby' => 'Em bé', 'backpack' => 'Ba lô', 'school' => 'Trường học', 'award' => 'Huy chương', 'book-open' => 'Sách mở',
        'graduation-cap' => 'Mũ cử nhân', 'languages' => 'Ngôn ngữ', 'star' => 'Ngôi sao', 'rocket' => 'Tên lửa',
        'music' => 'Âm nhạc', 'palette' => 'Màu vẽ', 'trophy' => 'Cúp', 'mic' => 'Micro', 'pencil' => 'Bút chì'];
}

/** 4 lý do chọn Sunny. 'scene' = tên cảnh minh hoạ động trong app/mascot.php. */
function sunny_why(): array
{
    return [
        ['scene' => 'speak', 'tint' => 'sun', 'title' => 'Con nói tiếng Anh ngay từ buổi đầu',
            'body' => 'Lớp nhỏ, tối đa 12 bạn: buổi nào con cũng được gọi nói và được cô sửa phát âm tại chỗ. Nói nhiều rồi, con sẽ hết ngại.'],
        ['scene' => 'fun', 'tint' => 'green', 'title' => 'Đến lớp là con háo hức',
            'body' => 'Bài hát, trò chơi và hoạt động nhóm trong mỗi buổi. Con học mà không thấy mình đang học.'],
        ['scene' => 'milestone', 'tint' => 'blue', 'title' => 'Ba mẹ thấy rõ con đi đến đâu',
            'body' => 'Mỗi cấp độ kết thúc bằng một mốc rõ ràng: Cambridge Starters, Movers, Flyers hoặc bài kiểm tra định kỳ. Có đích đến, con có thêm động lực.'],
        ['scene' => 'report', 'tint' => 'red', 'title' => 'Ba mẹ biết hôm nay con học gì',
            'body' => 'Cô nhận xét sau mỗi buổi và gửi báo cáo tiến bộ hằng tháng. Ba mẹ không còn phải hỏi "hôm nay con học gì?" rồi nhận về một câu "không biết".'],
    ];
}

function sunny_steps(): array
{
    return [
        ['title' => 'Ba mẹ để lại số điện thoại', 'body' => 'Điền form dưới đây, chưa cần quyết định học.'],
        ['title' => 'Sunny gọi lại trong 24 giờ', 'body' => 'Xác nhận ngày giờ phù hợp để con học thử.'],
        ['title' => 'Con học thử 30 phút', 'body' => 'Học 1 kèm 1 với giáo viên, ba mẹ nhận báo cáo trình độ của con.'],
    ];
}

/** Ảnh cơ sở vật chất (băng chuyền tự chạy). 'pos' = vùng ảnh được giữ khi cắt khung (object-position). */
function sunny_facility(): array
{
    return [
        ['file' => 'co-so-1.jpg', 'caption' => 'Sảnh đón ba mẹ và các bé', 'pos' => '50% 55%'],
        ['file' => 'co-so-2.jpg', 'caption' => 'Khu chờ rộng rãi cho ba mẹ', 'pos' => '50% 50%'],
        ['file' => 'co-so-3.jpg', 'caption' => 'Quầy lễ tân tư vấn', 'pos' => '50% 55%'],
        ['file' => 'co-so-4.jpg', 'caption' => 'Phòng học cho các bé nhỏ, có thảm chữ cái', 'pos' => '50% 60%'],
        ['file' => 'co-so-5.jpg', 'caption' => 'Phòng học lớp lớn, bàn ghế gọn gàng', 'pos' => '50% 55%'],
    ];
}

function sunny_contact(): array
{
    $c = config('contact', []) + [
        'center' => 'Trung tâm ngoại ngữ Sunny', 'address' => '', 'maps' => '', 'phone' => '', 'zalo' => '', 'facebook' => '',
    ];
    if ($c['maps'] === '' && $c['address'] !== '') {
        $c['maps'] = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($c['address']);
    }
    $digits = preg_replace('/\D+/', '', (string) $c['phone']);
    $c['tel'] = $digits !== '' ? 'tel:' . $digits : '';
    $zd = preg_replace('/\D+/', '', (string) $c['zalo']);
    $c['zaloUrl'] = $zd !== '' ? 'https://zalo.me/' . $zd : '';
    return $c;
}
