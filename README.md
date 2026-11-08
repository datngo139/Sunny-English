# Sunny Education — website trung tâm tiếng Anh

Landing page quảng cáo trung tâm ngoại ngữ **Sunny** (bé 3–14 tuổi), gồm:

- **Trang chủ** dựng theo *Sunny Education Design System*: hero, giới thiệu 4 khoá học (lọc theo độ tuổi, bấm xem lộ trình), quy trình học thử, bài viết mới, **form đăng ký học thử**.
- **Blog** (`/bai-viet`): admin đăng bài, khách đọc, có ảnh bìa, tóm tắt, SEO/OG, sitemap.
- **Trang quản trị** (`/admin`): trình soạn bài (đậm/nghiêng, tiêu đề, danh sách, liên kết, ảnh), nháp / đăng / hẹn giờ, xem danh sách phụ huynh đăng ký, đổi trạng thái, xuất CSV (Excel).

Công nghệ: **PHP 8.1+ và SQLite** (không cần cài database riêng, chạy được trên mọi gói Hostinger). Không cần build, không cần Node.

> 👉 **Muốn đưa lên mạng?** Xem [HUONG-DAN-DEPLOY.md](HUONG-DAN-DEPLOY.md) (GitHub + Hostinger, từng bước).

## Cấu trúc

```
index.php, blog.php, post.php     Trang công khai (trang chủ, danh sách bài, bài chi tiết)
api/register.php                  Nhận đăng ký học thử
admin/                            Khu quản trị (đăng nhập, bài viết, đăng ký, tải ảnh, mật khẩu)
app/                              Mã lõi (CSDL, bảo mật, lọc HTML, giao diện chung) — bị chặn truy cập từ web
  config.sample.php               Mẫu cấu hình → sao chép thành config.php (KHÔNG đưa lên GitHub)
  content.php                     ✏️ Nội dung trang chủ: khoá học, lợi ích, khung giờ học thử
assets/                           CSS (design tokens + component), JS, logo
data/                             CSDL SQLite + session — tự tạo, bị chặn truy cập, không lên GitHub
uploads/                          Ảnh admin tải lên — không lên GitHub, không cho chạy mã
dev/router.php                    Chỉ để chạy thử trên máy
```

## Chạy thử trên máy

Cần PHP 8.1+ (có `pdo_sqlite`, `dom`, `mbstring`, `gd`).

```bash
cp app/config.sample.php app/config.php     # rồi sửa setup_code (và debug => true khi chạy thử)
php -S localhost:8000 dev/router.php
```

Mở <http://localhost:8000>, vào <http://localhost:8000/admin/> để tạo tài khoản admin lần đầu (cần nhập `setup_code`).

## Chỉnh nội dung

| Muốn đổi | Sửa ở |
|---|---|
| Khoá học, lợi ích, các bước học thử, khung giờ | `app/content.php` |
| Số điện thoại, Zalo, địa chỉ, link Google Maps | `app/config.php` → `contact` |
| Màu, font, bo góc | `assets/css/tokens.css` |
| Logo | `assets/img/` |

> ⚠️ Toàn bộ chữ trong `content.php` (sĩ số lớp 12, lịch học, mốc Cambridge…) là **nội dung mẫu** theo design system. Hãy thay bằng thông tin thật của Sunny trước khi chạy quảng cáo.

## Bảo mật (đã làm sẵn)

- Mật khẩu băm bằng `password_hash`; khoá đăng nhập 15 phút sau 6 lần sai; session cookie `HttpOnly` + `SameSite`.
- CSRF token cho mọi thao tác trong admin và tải ảnh.
- Nội dung bài viết được **lọc theo danh sách cho phép** (`app/sanitize.php`): chặn `<script>`, `onerror`, link `javascript:`… kể cả khi dán nội dung từ nguồn khác.
- Ảnh tải lên kiểm tra bằng nội dung thật, đổi tên ngẫu nhiên, nén lại (xoá EXIF/GPS), thư mục `uploads/` không cho chạy PHP.
- Form đăng ký: kiểm tra phía máy chủ, ô bẫy bot, giới hạn 5 lần/giờ/người, chặn gửi chéo website.
- Truy vấn dùng prepared statement; mọi dữ liệu hiển thị đều được escape; `app/`, `data/`, `.git` trả 404 qua web.
- Tạo admin lần đầu cần **mã cài đặt** do bạn tự đặt, nên người lạ không thể chiếm quyền trước.
