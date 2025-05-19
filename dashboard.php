<?php
session_start();

// Kiểm tra nếu chưa đăng nhập thì quay về trang login
if (!isset($_SESSION['user'])) {
    header("Location: index.html"); // quay về trang chứa form login/signup
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang Chủ Người Dùng</title>
    <style>
        body {
            font-family: Arial;
            background-color: #f1f1f1;
            padding: 20px;
        }
        .box {
            background: white;
            padding: 20px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
            text-align: center;
        }
        a {
            text-decoration: none;
            color: red;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>Xin chào, <?php echo htmlspecialchars($_SESSION['user']); ?>!</h2>
        <p>Chào mừng bạn đến với trang người dùng.</p>
        <p><a href="logout.php">Đăng xuất</a></p>
    </div>
</body>
</html>
