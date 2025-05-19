<?php
session_start();
$captcha = rand(1000, 9999);
$_SESSION['captcha'] = $captcha;

// Tạo ảnh
header('Content-type: image/png');
$image = imagecreate(70, 30);
$bg = imagecolorallocate($image, 255, 255, 255);
$text = imagecolorallocate($image, 0, 0, 0);
imagestring($image, 5, 10, 8, $captcha, $text);
imagepng($image);
imagedestroy($image);
?>
