<?php
session_start();
require '../db.php'; // Điều chỉnh đường dẫn để trỏ đến db.php ở thư mục gốc

// Kiểm tra trạng thái đăng nhập và quyền admin
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$success = '';
$errors = [];

// Xử lý thêm người dùng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!$username || !$email || !$password) {
        $errors[] = "Vui lòng nhập đầy đủ thông tin.";
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Tên người dùng hoặc email đã tồn tại.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$username, $email, $password]);
            $success = "Thêm người dùng thành công!";
        }
    }
}

// Xử lý cập nhật người dùng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $user_id = (int)$_POST['user_id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'] ?: null;

    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ? AND role != 'admin'");
    $stmt->execute([$username, $email, $user_id]);
    if ($password) {
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ? AND role != 'admin'");
        $stmt->execute([$password, $user_id]);
    }
    $success = "Cập nhật người dùng thành công!";
}

// Xử lý reset mật khẩu
if (isset($_GET['reset_password'])) {
    $user_id = (int)$_GET['reset_password'];
    $new_password = '1'; // Có thể thay bằng logic sinh mật khẩu ngẫu nhiên
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ? AND role != 'admin'");
    $stmt->execute([$new_password, $user_id]);
    $success = "Reset mật khẩu thành công! Mật khẩu mới: 1";
}

// Xử lý thêm tin đăng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_post'])) {
    $title = $_POST['title'];
    $price = (int)$_POST['price'];
    $location = $_POST['location'];
    $area = (int)$_POST['area'];
    $user_id = $_SESSION['user_id']; // Giả sử có cột user_id trong posts

    $stmt = $conn->prepare("INSERT INTO posts (title, price, location, area, user_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$title, $price, $location, $area, $user_id]);
    $success = "Thêm tin đăng thành công!";
}

// Xử lý cập nhật trạng thái tin đăng
if (isset($_POST['update_post_status'])) {
    $post_id = (int)$_POST['post_id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE posts SET status = ? WHERE id = ?");
    $stmt->execute([$status, $post_id]);
    $success = "Cập nhật trạng thái tin đăng thành công!";
}

// Lấy danh sách người dùng
$stmt = $conn->prepare("SELECT id, username, email FROM users WHERE role != 'admin'");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách bài đăng
$stmt = $conn->prepare("SELECT posts.id, posts.title, posts.price, posts.location, posts.status, users.username 
                        FROM posts 
                        JOIN users ON posts.user_id = users.id");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Thống kê
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_posts = $conn->query("SELECT COUNT(*) FROM posts")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <!-- Menu -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Thuê Phòng Trọ</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Trang chủ</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown">
                            Tài khoản
                        </a>
                        <ul class="dropdown-menu">
                            <?php if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user'])): ?>
                                <li><a class="dropdown-item" href="../account.php">Chỉnh sửa thông tin</a></li>
                                <li><a class="dropdown-item" href="../manage_posts.php">Quản lý tin đăng</a></li>
                              <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
    <li><a class="dropdown-item" href="admin/admin.php">Quản trị viên</a></li>
<?php endif; ?>
                                <li><a class="dropdown-item" href="../logout.php">Đăng xuất</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="../login.php">Đăng nhập</a></li>
                                <li><a class="dropdown-item" href="../login.php">Đăng ký</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../report.php">Gửi báo cáo</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Nội dung chính -->
    <div class="container mt-5 pt-5">
        <h2>Trang quản trị viên</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endforeach; ?>

        <!-- Quản lý tài khoản -->
        <h3>Quản lý tài khoản</h3>
        <h4>Thêm người dùng</h4>
        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="username" class="form-control mb-2" placeholder="Tên người dùng" required>
                </div>
                <div class="col-md-4">
                    <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
                </div>
                <div class="col-md-4">
                    <input type="password" name="password" class="form-control mb-2" placeholder="Mật khẩu" required>
                </div>
            </div>
            <button type="submit" name="add_user" class="btn btn-primary">Thêm</button>
        </form>

        <h4>Sửa người dùng</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên người dùng</th>
                    <th>Email</th>
                    <th>Mật khẩu</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td>
                            <form method="POST">
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" name="username">
                        </td>
                        <td>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" name="email">
                        </td>
                        <td>
                            <input type="password" class="form-control" placeholder="Nhập để thay đổi" name="password">
                        </td>
                        <td>
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" name="update_user" class="btn btn-warning btn-sm">Sửa</button>
                            <a href="admin.php?reset_password=<?php echo $user['id']; ?>" class="btn btn-info btn-sm" onclick="return confirm('Bạn có chắc chắn reset mật khẩu?')">Reset mật khẩu</a>
                            </form>
                        </td>x
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Quản lý tin đăng -->
        <h3 class="mt-5">Quản lý tin đăng</h3>
        <h4>Thêm tin đăng</h4>
        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" name="title" class="form-control mb-2" placeholder="Tiêu đề" required>
                </div>
                <div class="col-md-2">
                    <input type="number" name="price" class="form-control mb-2" placeholder="Giá (VND)" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="location" class="form-control mb-2" placeholder="Địa điểm" required>
                </div>
                <div class="col-md-2">
                    <input type="number" name="area" class="form-control mb-2" placeholder="Diện tích (m²)" required>
                </div>
            </div>
            <button type="submit" name="add_post" class="btn btn-primary">Thêm tin</button>
        </form>

        <h4>Cập nhật trạng thái tin đăng</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tiêu đề</th>
                    <th>Giá (VND)</th>
                    <th>Địa điểm</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?php echo $post['id']; ?></td>
                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                        <td><?php echo number_format($post['price']); ?></td>
                        <td><?php echo htmlspecialchars($post['location']); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="active" <?php echo $post['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $post['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                                <input type="hidden" name="update_post_status" value="1">
                            </form>
                        </td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Thống kê -->
        <h3 class="mt-5">Thống kê</h3>
        <div class="card">
            <div class="card-body">
                <p>Tổng số người dùng: <?php echo $total_users; ?></p>
                <p>Tổng số tin đăng: <?php echo $total_posts; ?></p>
            </div>
        </div>

        <!-- Thông tin liên hệ -->
        <?php include('../include/ttlh.php'); ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>