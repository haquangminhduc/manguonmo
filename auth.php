<?php
session_start();
require 'db.php';

$action = $_POST['action'] ?? '';
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$email = trim($_POST['email'] ?? '');
$captcha_input = $_POST['captcha'] ?? '';

if ($action === 'login') {
    if (!$username || !$password) {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin.";
        header("Location: login.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $password === $user['password']) {
        $_SESSION['user'] = $user['username'];
        $_SESSION['role'] = $user['role'] ?? 'user';
        $_SESSION['user_id'] = $user['id']; // Thêm user_id vào session
        header("Location: index.php");
        exit;
    } else {
        $_SESSION['error'] = "Sai tên đăng nhập hoặc mật khẩu.";
        header("Location: login.php");
        exit;
    }

} elseif ($action === 'register') {
    if (!$username || !$email || !$password) {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin.";
        header("Location: login.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "Tên đăng nhập hoặc email đã tồn tại.";
        header("Location: login.php");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
    $stmt->execute([$username, $email, $password]);

    $_SESSION['error'] = "Đăng ký thành công! Vui lòng đăng nhập.";
    header("Location: login.php");
    exit;

} else {
    $_SESSION['error'] = "Hành động không hợp lệ.";
    header("Location: login.php");
    exit;
}
?>