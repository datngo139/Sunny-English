<?php
/**
 * NỘI DUNG TRANG CHỦ — sửa chữ ở đây (không cần biết lập trình, chỉ giữ nguyên dấu nháy và dấu phẩy).
 *
 * Lưu ý: toàn bộ nội dung dưới đây là NỘI DUNG MẪU. Hãy thay bằng thông tin thật của trung tâm
 * (sĩ số lớp, lịch học, học phí, chứng chỉ...) trước khi chạy quảng cáo.
 */

function sunny_courses(): array
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

/** Tab lọc theo độ tuổi: nhãn => mã khoá học (null = tất cả). Khoá luyện thi luôn hiện. */
function sunny_age_filters(): array
{
    return ['Tất cả' => null, '3–6 tuổi' => 'mamnon', '6–11 tuổi' => 'tieuhoc', '11–14 tuổi' => 'trunghoc'];
}

function sunny_why(): array
{
    return [
        ['icon' => 'mic', 'tint' => 'sun', 'title' => 'Con được nói nhiều hơn', 'body' => 'Lớp tối đa 12 bạn, buổi nào con cũng được gọi nói.'],
        ['icon' => 'smile', 'tint' => 'green', 'title' => 'Con thích đến lớp', 'body' => 'Bài hát, trò chơi và hoạt động nhóm trong mỗi buổi.'],
        ['icon' => 'flag', 'tint' => 'blue', 'title' => 'Ba mẹ thấy rõ mốc', 'body' => 'Mỗi cấp độ kết thúc bằng một mốc Cambridge hoặc bài kiểm tra.'],
        ['icon' => 'notebook-pen', 'tint' => 'red', 'title' => 'Ba mẹ biết con học gì', 'body' => 'Nhận xét sau mỗi buổi và báo cáo hằng tháng.'],
    ];
}

function sunny_steps(): array
{
    return [
        ['title' => 'Ba mẹ để lại số điện thoại', 'body' => 'Điền form dưới đây, chưa cần quyết định học.'],
        ['title' => 'Sunny gọi lại trong 24 giờ', 'body' => 'Hẹn giờ phù hợp để con học thử.'],
        ['title' => 'Con học thử 30 phút', 'body' => 'Học 1 kèm 1 với giáo viên, ba mẹ nhận báo cáo trình độ của con.'],
    ];
}

/** Các khung giờ học thử trong form đăng ký (giá trị => nhãn). */
function sunny_slots(): array
{
    return ['sang-t7' => 'Sáng thứ 7', 'chieu-cn' => 'Chiều chủ nhật'];
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
