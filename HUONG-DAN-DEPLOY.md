# Hướng dẫn: đưa Sunny lên GitHub và Hostinger

Làm lần lượt từ trên xuống. Tổng cộng khoảng 20–30 phút.

---

## Phần A — Đưa mã nguồn lên GitHub (public)

Repo public nghĩa là **ai cũng đọc được mã nguồn**. Đã chuẩn bị sẵn để không lộ gì nhạy cảm: mật khẩu, cấu hình thật (`app/config.php`), CSDL (`data/`) và ảnh tải lên (`uploads/`) đều nằm trong `.gitignore` nên **không** bị đưa lên.

1. Đăng nhập <https://github.com> → bấm **New repository**.
2. Đặt tên (ví dụ `sunny-english`), chọn **Public**, **không** tick "Add a README" → **Create repository**.
3. Trong thư mục dự án trên máy, chạy (thay `TEN-GITHUB-CUA-BAN`):

```bash
git remote add origin https://github.com/TEN-GITHUB-CUA-BAN/sunny-english.git
git push -u origin main
```

Nếu Git hỏi mật khẩu: GitHub không còn nhận mật khẩu tài khoản. Hãy tạo **Personal access token** (GitHub → Settings → Developer settings → Tokens, quyền `repo`) và dán token vào ô mật khẩu; hoặc cài [GitHub Desktop](https://desktop.github.com) rồi chọn *Add existing repository* → *Publish repository* (bỏ tick "Keep this code private").

> Kiểm tra lại: mở repo trên GitHub, đảm bảo **không có** file `app/config.php` và thư mục `data/` có nội dung.

---

## Phần B — Đưa lên Hostinger

Điều kiện: gói hosting có **PHP** (Premium / Business / Cloud đều có). Gói chỉ có "Website Builder" thì không chạy được.

### B1. Chuẩn bị tên miền và PHP
1. hPanel → **Websites** → chọn website (hoặc **Add website** → gắn tên miền).
2. **Advanced → PHP Configuration**: chọn **PHP 8.1 trở lên** (khuyến nghị 8.2/8.3). Ở tab *PHP extensions*, đảm bảo bật: `pdo_sqlite`, `sqlite3`, `dom`, `mbstring`, `fileinfo`, `gd` (thường đã bật sẵn).

### B2. Lấy mã nguồn từ GitHub (cách 1 — khuyến nghị, cập nhật dễ)
1. hPanel → **Advanced → Git**.
2. Mục *Create a new repository*: dán `https://github.com/TEN-GITHUB-CUA-BAN/sunny-english.git`, Branch `main`, **Install path để trống** (= `public_html`) → **Create**.
   - Nếu báo thư mục không trống: vào **File Manager**, xoá file mặc định trong `public_html` (ví dụ `default.php`) rồi thử lại.
3. Bấm **Deploy** để kéo mã về.
4. (Tuỳ chọn) Bật **Auto deployment** (webhook) để mỗi lần bạn push lên GitHub, website tự cập nhật.

> **Cách 2 — tải lên thủ công:** trên máy nén toàn bộ dự án (trừ thư mục `.git`) thành ZIP → hPanel → **File Manager** → vào `public_html` → *Upload* → giải nén tại đó.

### B3. Tạo file cấu hình (bắt buộc)
Vì `config.php` không có trên GitHub nên phải tạo trên hosting:

1. **File Manager** → vào `public_html/app/`.
2. Chuột phải `config.sample.php` → **Copy** → đặt tên `config.php`.
3. Mở `config.php` → **Edit** và sửa:
   - `site_url`: địa chỉ thật, ví dụ `https://tenmiencuaban.com` (không có dấu `/` cuối).
   - `setup_code`: **đổi thành chuỗi bí mật của riêng bạn** (dài, khó đoán, ví dụ `Sunny-8f3k2-xQ9m-2026`). Không dùng lại chuỗi mẫu, nếu không trang cài đặt sẽ không mở.
   - `contact`: số điện thoại, Zalo, địa chỉ, và link Google Maps chính xác của trung tâm.
   - Email thông báo đăng ký mới: xem mục **"Thông báo đăng ký qua Gmail"** bên dưới (làm sau khi đã vào được admin).
   - Giữ `'debug' => false`.
4. Lưu lại.

### B4. Bật HTTPS
1. hPanel → **Security → SSL** → cài SSL miễn phí cho tên miền.
2. Bật **Force HTTPS** (hoặc bỏ dấu `#` ở 2 dòng "Ép HTTPS" trong file `.htaccess`).

### B5. Tạo tài khoản admin
1. Mở `https://tenmiencuaban.com/admin/` → tự chuyển sang trang cài đặt lần đầu.
2. Nhập **mã cài đặt** (chính là `setup_code` vừa đặt), tên đăng nhập, mật khẩu (≥ 10 ký tự, có chữ và số) → **Tạo tài khoản**.
3. Đăng nhập xong, bạn có thể bắt đầu **Viết bài mới** và xem **Đăng ký học thử**.

### B6. Kiểm tra an toàn (2 phút)
Mở từng địa chỉ sau, **phải ra trang "không tìm thấy" (404)**, không được tải file hay hiện chữ lạ:

- `https://tenmiencuaban.com/data/sunny.sqlite`
- `https://tenmiencuaban.com/app/config.php`
- `https://tenmiencuaban.com/.git/config`

Nếu một trong các địa chỉ trên **không** ra 404, hãy dừng và nhắn lại để xử lý ngay trước khi chạy quảng cáo.

Sau đó tự thử một lần: điền form đăng ký học thử trên trang chủ → vào *Admin → Đăng ký học thử* xem có thấy dòng mới không.

---

## Thông báo đăng ký qua Gmail

Mỗi khi có phụ huynh đăng ký, website gửi email báo cho trung tâm. Cần 2 phần:

**1. Gmail dùng để gửi** (nên là một Gmail riêng cho website)
1. Vào <https://myaccount.google.com/security> bật **Xác minh 2 bước**.
2. Vào <https://myaccount.google.com/apppasswords>, tạo mật khẩu ứng dụng tên "Sunny website" → được chuỗi **16 ký tự**.
3. Mở `app/config.php` trên hosting (File Manager → Edit), bỏ dấu `//` ở khối `'smtp'` và điền:
   `user` = địa chỉ Gmail gửi, `pass` = chuỗi 16 ký tự vừa tạo. Giữ nguyên `host`, `port`, `secure`. **Không gửi chuỗi này cho ai**, và không đưa lên GitHub (file `config.php` đã được loại khỏi Git).

**2. Gmail nhận thông báo**
- Vào **Admin → Thông báo**, nhập một hoặc nhiều địa chỉ (mỗi dòng một địa chỉ) → **Lưu danh sách**.
- Bấm **Gửi email thử** để kiểm tra. Nếu báo lỗi, đọc thông báo: thường là sai mật khẩu ứng dụng, hoặc hosting chặn cổng gửi thư (hosting miễn phí hay chặn → dùng hosting trả phí như Hostinger).

## Cập nhật website sau này

- **Sửa nội dung khoá học / giao diện:** sửa file trên máy → `git add . && git commit -m "..." && git push` → trong hPanel → Git → **Deploy** (hoặc tự động nếu đã bật auto deployment).
- **Viết bài, sửa khoá học, đặt lịch học thử, xem đăng ký:** làm thẳng trong trang `/admin`, **không** cần đụng GitHub.
- Dữ liệu thật (`config.php`, `data/`, `uploads/`) không nằm trong Git nên **không bị ghi đè** khi deploy.

## Sao lưu

Hai thứ quan trọng nhất cần sao lưu định kỳ (tải về máy từ File Manager):

1. `public_html/data/sunny.sqlite` — bài viết, danh sách đăng ký, tài khoản admin.
2. `public_html/uploads/` — ảnh đã tải lên.

Hostinger cũng có sao lưu tự động hằng ngày/tuần (hPanel → **Files → Backups**). Bạn cũng có thể tải file đăng ký ra Excel bất kỳ lúc nào ở *Admin → Đăng ký học thử → Tải file Excel*.

## Xử lý sự cố thường gặp

| Hiện tượng | Cách xử lý |
|---|---|
| "Website chưa được cấu hình" | Chưa tạo `app/config.php` (xem B3). |
| Trang cài đặt báo "Cần đặt mã cài đặt" | `setup_code` trong `config.php` vẫn là chuỗi mẫu → đổi sang chuỗi của bạn. |
| Trắng trang / "Có lỗi xảy ra" | Xem `data/php-error.log` trong File Manager. Kiểm tra PHP ≥ 8.1 và đã bật `pdo_sqlite`. |
| Không tải được ảnh lên | Kiểm tra thư mục `uploads/` cho phép ghi (quyền 755) và ảnh ≤ 6MB. |
| Đường dẫn `/bai-viet` báo 404 | `.htaccess` chưa được tải lên (file ẩn). Bật "Show hidden files" trong File Manager. |
| Quên mật khẩu admin | Trong File Manager, tạo một file trống tên `reset-admin.txt` trong thư mục `public_html/data/`, rồi mở `/admin/setup.php`, nhập mã cài đặt và tạo tài khoản mới (bài viết và đăng ký được giữ nguyên, file cờ tự xoá sau khi xong). |
| Email thông báo không đến | Vào **Admin → Thông báo → Gửi email thử** để xem lỗi cụ thể; xem cả thư mục Spam. Chi tiết lỗi gửi thư cũng ghi trong `data/mail-error.log`. Đăng ký vẫn được lưu và xem được trong admin. |
| Ảnh tải lên bị lỗi 500 / không hiện | Thư mục `uploads/` có file `.htaccess` mà hosting không chấp nhận. Dùng bản `.htaccess` tối giản mới trong dự án; nếu vẫn lỗi, đổi tên `uploads/.htaccess` thành `.htaccess.bak`. |
