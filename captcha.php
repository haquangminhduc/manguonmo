<?php
session_start();

// Xác định loại CAPTCHA (login hoặc register)
$type = isset($_GET['type']) && in_array($_GET['type'], ['login', 'register']) ? $_GET['type'] : 'login';
$session_key = 'captcha_' . $type;

// Chỉ tạo mã mới nếu chưa có hoặc được yêu cầu làm mới
if (!isset($_SESSION[$session_key]) || isset($_GET['refresh'])) {
    $captcha = rand(1000, 9999);
    $_SESSION[$session_key] = $captcha;
} else {
    $captcha = $_SESSION[$session_key];
}

// Tạo ảnh
header('Content-type: image/png');
$image = imagecreate(70, 30);
$bg = imagecolorallocate($image, 255, 255, 255);
$text = imagecolorallocate($image, 0, 0, 0);
imagestring($image, 5, 10, 8, $captcha, $text);

// Thêm nhiễu (tùy chọn, để chống bot)
for ($i = 0; $i < 5; $i++) {
    $line_color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
    imageline($image, rand(0, 70), rand(0, 30), rand(0, 70), rand(0, 30), $line_color);
}

imagepng($image);
imagedestroy($image);
?>