# Sunny Education — website trung tâm tiếng Anh

Landing page quảng cáo trung tâm ngoại ngữ **Sunny** (bé 3–14 tuổi), gồm:

- **Trang chủ** dựng theo *Sunny Education Design System*: **linh vật mặt trời có hoạt hình** (mắt nhìn theo chuột, bấm để chào), 4 cảnh minh hoạ động, khoá học, **băng chuyền ảnh cơ sở vật chất** tự chạy, quy trình học thử, bài viết mới và **form đăng ký học thử có chọn ngày giờ**. Nút Zalo / gọi điện nổi bật, có nút Zalo nổi.
- **Blog** (`/bai-viet`): admin đăng bài, khách đọc, có ảnh bìa, tóm tắt, SEO/OG, sitemap.
- **Trang quản trị** (`/admin`): soạn bài (đậm/nghiêng, tiêu đề, danh sách, liên kết, **nhiều ảnh một lúc, kéo thả, lưới ảnh**), nháp / đăng / hẹn giờ; **quản lý khoá học** (sửa, thêm, ẩn, sắp xếp, đổi ảnh); **lịch học thử** (ngày giờ nhận, số bé mỗi suất, ngày nghỉ); danh sách phụ huynh đăng ký (đổi trạng thái, xuất CSV); **thông báo đăng ký mới qua Gmail**.

Công nghệ: **PHP 8.1+ và SQLite** (không cần cài database riêng, chạy được trên mọi gói Hostinger). Không cần build, không cần Node.

> 👉 **Muốn đưa lên mạng?** Xem [HUONG-DAN-DEPLOY.md](HUONG-DAN-DEPLOY.md) (GitHub + Hostinger, từng bước).

## Cấu trúc

```
index.php, blog.php, post.php     Trang công khai (trang chủ, danh sách bài, bài chi tiết)
api/register.php                  Nhận đăng ký học thử
admin/                            Khu quản trị (bài viết, khoá học, lịch học thử, đăng ký, thông báo, mật khẩu)
app/                              Mã lõi (CSDL, bảo mật, lọc HTML, giao diện chung) — bị chặn truy cập từ web
  config.sample.php               Mẫu cấu hình → sao chép thành config.php (KHÔNG đưa lên GitHub)
  content.php                     ✏️ Nội dung trang chủ: 4 lý do chọn Sunny, các bước học thử, danh sách ảnh cơ sở vật chất
  mascot.php, mailer.php, schedule.php   Linh vật + cảnh động (SVG), gửi email Gmail, lịch học thử
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
| Khoá học (tên, độ tuổi, ảnh…) | **Admin → Khoá học** (không cần sửa code) |
| Lịch nhận học thử, ngày nghỉ | **Admin → Lịch học thử** |
| Email nhận thông báo | **Admin → Thông báo** |
| 4 lý do chọn Sunny, các bước học thử, ảnh cơ sở vật chất | `app/content.php` (ảnh để trong `assets/img/facility/`) |
| Số điện thoại, Zalo, địa chỉ, link Google Maps | `app/config.php` → `contact` |
| Màu, font, bo góc | `assets/css/tokens.css` |
| Logo | `assets/img/` |

> ⚠️ Chữ như "sĩ số lớp 12", "mốc Cambridge" trong `content.php` và 4 khoá học mẫu là **nội dung mẫu** theo design system. Hãy thay bằng thông tin thật của Sunny trước khi chạy quảng cáo.

## Bảo mật (đã làm sẵn)

- Mật khẩu băm bằng `password_hash`; khoá đăng nhập 15 phút sau 6 lần sai; session cookie `HttpOnly` + `SameSite`.
- CSRF token cho mọi thao tác trong admin và tải ảnh.
- Nội dung bài viết được **lọc theo danh sách cho phép** (`app/sanitize.php`): chặn `<script>`, `onerror`, link `javascript:`… kể cả khi dán nội dung từ nguồn khác.
- Ảnh tải lên kiểm tra bằng nội dung thật, đổi tên ngẫu nhiên, nén lại (xoá EXIF/GPS), thư mục `uploads/` không cho chạy PHP.
- Form đăng ký: kiểm tra phía máy chủ, ô bẫy bot, giới hạn 5 lần/giờ/người, chặn gửi chéo website.
- Truy vấn dùng prepared statement; mọi dữ liệu hiển thị đều được escape; `app/`, `data/`, `.git` trả 404 qua web.
- Tạo admin lần đầu cần **mã cài đặt** do bạn tự đặt, nên người lạ không thể chiếm quyền trước.
