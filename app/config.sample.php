<?php
/**
 * CẤU HÌNH SUNNY — file mẫu.
 *
 * Cách dùng: sao chép file này thành `config.php` (cùng thư mục) rồi sửa các giá trị bên dưới.
 * `config.php` đã được .gitignore nên KHÔNG bị đưa lên GitHub.
 */
return [
    // Địa chỉ website, không có dấu "/" ở cuối. Dùng cho thẻ chia sẻ Facebook/Zalo và Google.
    'site_url' => 'https://tenmiencuaban.com',

    // Chỉ đổi nếu đặt web trong thư mục con (ví dụ '/sunny'). Thường để trống.
    'base_path' => '',

    // MÃ CÀI ĐẶT: chuỗi bí mật do BẠN tự đặt (dài, khó đoán). Cần nhập khi tạo tài khoản admin lần đầu
    // tại /admin/setup.php. Chặn người lạ chiếm quyền admin trước bạn.
    'setup_code' => 'DOI-CHUOI-NAY-THANH-MA-BI-MAT-CUA-BAN',

    // Email nhận thông báo khi có phụ huynh đăng ký học thử (để trống = không gửi email).
    'notify_email' => '',
    // Email người gửi, nên cùng tên miền website (ví dụ 'no-reply@tenmiencuaban.com').
    'from_email' => '',

    // Thông tin liên hệ hiển thị trên website.
    'contact' => [
        'center'  => 'Trung tâm ngoại ngữ Sunny',
        'address' => 'Phú Điền, Hàm Hiệp, Hàm Thuận Bắc, Bình Thuận',
        // Link Google Maps chính xác của trung tâm. Để trống = tìm theo địa chỉ ở trên.
        'maps'    => '',
        'phone'   => '0979 74 74 01',
        'zalo'    => '0979 74 74 01',
        'facebook' => '',
    ],

    // Bật để xem chi tiết lỗi khi đang chạy thử trên máy. LUÔN để false khi chạy thật.
    'debug' => false,
];
