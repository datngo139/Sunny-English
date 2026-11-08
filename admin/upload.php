<?php
/** Tải ảnh lên (dùng cho ảnh bìa và ảnh trong bài). Trả JSON. */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

if (!current_admin()) {
    json_response(['ok' => false, 'message' => 'Phiên đăng nhập đã hết hạn. Hãy tải lại trang.'], 401);
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_response(['ok' => false, 'message' => 'Phương thức không hợp lệ.'], 405);
}
csrf_check();

const UPLOAD_MAX_BYTES = 6 * 1024 * 1024;

$f = $_FILES['image'] ?? null;
if (!$f || !is_array($f) || ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    $code = $f['error'] ?? UPLOAD_ERR_NO_FILE;
    $msg = in_array($code, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
        ? 'Ảnh quá lớn (tối đa 6MB).' : 'Không nhận được ảnh, hãy thử lại.';
    json_response(['ok' => false, 'message' => $msg], 400);
}
if ($f['size'] > UPLOAD_MAX_BYTES) {
    json_response(['ok' => false, 'message' => 'Ảnh quá lớn (tối đa 6MB).'], 413);
}
if (!is_uploaded_file($f['tmp_name'])) {
    json_response(['ok' => false, 'message' => 'Tệp tải lên không hợp lệ.'], 400);
}

// Kiểm tra bằng nội dung thật của tệp, không tin đuôi file hay Content-Type do trình duyệt gửi.
$info = @getimagesize($f['tmp_name']);
$types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
if (!$info || !isset($types[$info[2]])) {
    json_response(['ok' => false, 'message' => 'Chỉ nhận ảnh JPG, PNG, WebP hoặc GIF.'], 415);
}
if ($info[0] * $info[1] > 40_000_000) {
    json_response(['ok' => false, 'message' => 'Kích thước ảnh quá lớn.'], 413);
}
$ext = $types[$info[2]];

$sub = date('Y') . '/' . date('m');
$dir = SUNNY_ROOT . '/uploads/' . $sub;
if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
    json_response(['ok' => false, 'message' => 'Không tạo được thư mục lưu ảnh (kiểm tra quyền thư mục uploads/).'], 500);
}
$name = bin2hex(random_bytes(8)) . '.' . $ext;
$dest = $dir . '/' . $name;

$saved = false;
// Nén lại bằng GD: thu nhỏ còn tối đa 1600px, đồng thời xoá thông tin ẩn (EXIF/GPS) trong ảnh.
// Từng hàm GD đều được kiểm tra có sẵn; thiếu hàm nào thì lưu nguyên ảnh gốc (đã xác thực là ảnh).
$loaders = ['jpg' => 'imagecreatefromjpeg', 'png' => 'imagecreatefrompng', 'webp' => 'imagecreatefromwebp'];
$writers = ['jpg' => 'imagejpeg', 'png' => 'imagepng', 'webp' => 'imagewebp'];
if (isset($loaders[$ext]) && function_exists('imagecreatetruecolor') && function_exists($loaders[$ext]) && function_exists($writers[$ext])) {
    $src = @($loaders[$ext])($f['tmp_name']);
    if ($src) {
        if ($ext === 'jpg' && function_exists('exif_read_data') && function_exists('imagerotate')) {
            $exif = @exif_read_data($f['tmp_name']);
            $rot = [3 => 180, 6 => -90, 8 => 90][(int) ($exif['Orientation'] ?? 1)] ?? 0;
            if ($rot) {
                $src = imagerotate($src, $rot, 0) ?: $src;
            }
        }
        $w = imagesx($src);
        $h = imagesy($src);
        $max = 1600;
        if ($w > $max) {
            $nh = (int) round($h * $max / $w);
            $dst = imagecreatetruecolor($max, $nh);
            if ($ext !== 'jpg') {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            }
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $max, $nh, $w, $h);
            imagedestroy($src);
            $src = $dst;
        }
        if ($ext !== 'jpg') {
            imagesavealpha($src, true);
        }
        $saved = match ($ext) {
            'jpg' => imagejpeg($src, $dest, 85),
            'png' => imagepng($src, $dest, 6),
            'webp' => imagewebp($src, $dest, 85),
        };
        imagedestroy($src);
    }
}
if (!$saved) {
    $saved = move_uploaded_file($f['tmp_name'], $dest);
}
if (!$saved) {
    json_response(['ok' => false, 'message' => 'Không lưu được ảnh. Hãy thử lại.'], 500);
}
@chmod($dest, 0644);

$path = '/uploads/' . $sub . '/' . $name;
json_response(['ok' => true, 'path' => $path, 'url' => url($path)]);
