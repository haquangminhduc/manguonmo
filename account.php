<?php
session_start();
require 'db.php';

// Kiểm tra trạng thái đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['user'];
$errors = [];
$success = '';

// Lấy thông tin người dùng
$stmt = $conn->prepare("SELECT username, email, avatar FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_info'])) {
    $new_username = trim($_POST['username']);
    $email = trim($_POST['email']);

    if (!$new_username || !$email) {
        $errors[] = "Vui lòng nhập đầy đủ thông tin.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE username = ?");
        $stmt->execute([$new_username, $email, $username]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['user'] = $new_username; // Cập nhật session
            $success = "Cập nhật thông tin thành công!";
            $username = $new_username; // Cập nhật biến để hiển thị
            $user['username'] = $new_username; // Cập nhật dữ liệu để hiển thị
            $user['email'] = $email;
        } else {
            $errors[] = "Lỗi khi cập nhật thông tin.";
        }
    }
}

// Xử lý cập nhật avatar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_avatar'])) {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $target_file = $target_dir . basename($_FILES["avatar"]["name"]);
        if (move_uploaded_file($_FILES["avatar"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE username = ?");
            $stmt->execute([$target_file, $username]);
            if ($stmt->rowCount() > 0) {
                $success = "Cập nhật ảnh đại diện thành công!";
                $user['avatar'] = $target_file;
            } else {
                $errors[] = "Lỗi khi cập nhật ảnh vào cơ sở dữ liệu.";
            }
        } else {
            $errors[] = "Lỗi khi tải ảnh lên.";
        }
    } else {
        $errors[] = "Vui lòng chọn một ảnh.";
    }
}

// Xử lý avatar
$avatar_path = $user['avatar'] ?: 'default_avatar.jpg';
if (!file_exists($avatar_path)) {
    $avatar_path = 'default_avatar.jpg';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tài khoản</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Menu -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">Thuê Phòng Trọ</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Trang chủ</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown">
                            Tài khoản
                        </a>
                        <ul class="dropdown-menu">
                            <?php if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user'])): ?>
                                <li><a class="dropdown-item" href="account.php">Chỉnh sửa thông tin</a></li>
                                <li><a class="dropdown-item" href="manage_posts.php">Quản lý tin đăng</a></li>
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <li><a class="dropdown-item" href="admin/admin.php">Quản trị viên</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="login.php">Đăng nhập</a></li>
                                <li><a class="dropdown-item" href="login.php">Đăng ký</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="report.php">Gửi báo cáo</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Nội dung chính -->
    <div class="container mt-5 pt-5">
        <h2>Quản lý tài khoản</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endforeach; ?>

        <!-- Hiển thị thông tin người dùng và avatar -->
        <div class="text-center mb-4">
            <img src="<?php echo htmlspecialchars($avatar_path); ?>" alt="Avatar" width="100" class="rounded-circle">
            <h4 class="mt-2"><?php echo htmlspecialchars($username); ?></h4>
        </div>

        <!-- Chỉnh sửa thông tin -->
        <h3>Chỉnh sửa thông tin</h3>
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Tên người dùng</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <button type="submit" name="update_info" class="btn btn-primary">Cập nhật thông tin</button>
        </form>

        <!-- Cập nhật ảnh đại diện -->
        <h3 class="mt-4">Cập nhật ảnh đại diện</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="avatar" class="form-label">Chọn ảnh mới</label>
                <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*" required>
            </div>
            <button type="submit" name="update_avatar" class="btn btn-primary">Cập nhật ảnh</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>