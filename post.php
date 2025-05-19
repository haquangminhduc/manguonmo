<?php
session_start();
include 'config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$post_id = (int)$_GET['id'];

// Tăng lượt xem
$sql = "UPDATE posts SET views = views + 1 WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();

// Lấy thông tin bài đăng
$sql = "SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
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
                    <a class="nav-link active" href="index.php">Trang chủ</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown">
                        Tài khoản
                    </a>
                    <ul class="dropdown-menu">
                        <?php if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])): ?>
                            <li><a class="dropdown-item" href="account.php">Chỉnh sửa thông tin</a></li>
                            <li><a class="dropdown-item" href="manage_posts.php">Quản lý tin đăng</a></li>
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

    <div class="container mt-5 pt-5">
        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
        <img src="<?php echo $post['image'] ?: 'default.jpg'; ?>" class="img-fluid mb-3" alt="Phòng trọ">
        <p><strong>Giá:</strong> <?php echo number_format($post['price']); ?> VND</p>
        <p><strong>Địa điểm:</strong> <?php echo htmlspecialchars($post['location']); ?></p>
        <p><strong>Diện tích:</strong> <?php echo $post['area']; ?> m²</p>
        <p><strong>Tiện ích:</strong> <?php echo htmlspecialchars($post['amenities'] ?: 'Không có'); ?></p>
        <p><strong>Mô tả:</strong> <?php echo nl2br(htmlspecialchars($post['description'])); ?></p>
        <p><strong>Người đăng:</strong> <?php echo htmlspecialchars($post['username']); ?></p>
        <p><strong>Lượt xem:</strong> <?php echo $post['views']; ?></p>
        <a href="index.php" class="btn btn-secondary">Quay lại</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>