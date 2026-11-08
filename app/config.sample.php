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

    // Email nhận thông báo khi có phụ huynh đăng ký học thử. Bạn nên nhập danh sách ngay trong trang quản trị
    // (Admin → Thông báo). Dòng này chỉ là dự phòng khi danh sách trong admin còn trống.
    'notify_email' => '',
    // Chỉ dùng khi KHÔNG cấu hình smtp bên dưới (gửi bằng hàm mail() của hosting): email người gửi, nên cùng tên miền.
    'from_email' => '',

    // GỬI THƯ QUA GMAIL (khuyến nghị). Bỏ dấu // ở các dòng dưới và điền thông tin:
    //  - user: địa chỉ Gmail dùng để gửi (nên là Gmail riêng cho website)
    //  - pass: "mật khẩu ứng dụng" 16 ký tự tạo tại https://myaccount.google.com/apppasswords
    //          (KHÔNG phải mật khẩu đăng nhập Gmail; cần bật xác minh 2 bước trước)
    // 'smtp' => [
    //     'host' => 'smtp.gmail.com',
    //     'port' => 587,          // 587 + 'tls' (khuyến nghị) hoặc 465 + 'ssl'
    //     'secure' => 'tls',
    //     'user' => 'gmail-gui-thong-bao@gmail.com',
    //     'pass' => 'xxxxxxxxxxxxxxxx',
    //     'from_name' => 'Sunny Website',
    // ],

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
