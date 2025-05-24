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

// Xử lý thêm bình luận
$comment_success = '';
$comment_errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_comment'])) {
    if (!isset($_SESSION['user_id'])) {
        $comment_errors[] = "Bạn cần đăng nhập để bình luận.";
    } else {
        $user_id = $_SESSION['user_id'];
        $content = trim($_POST['content']);

        if (empty($content)) {
            $comment_errors[] = "Nội dung bình luận không được để trống.";
        } else {
            $sql = "INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $post_id, $user_id, $content);
            if ($stmt->execute()) {
                $comment_success = "Bình luận đã được thêm thành công!";
            } else {
                $comment_errors[] = "Có lỗi xảy ra khi thêm bình luận.";
            }
        }
    }
}

// Lấy danh sách bình luận
$sql = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
      <?php include('include/heard.php'); ?>

    <div class="container mt-3 pt-3">
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
                                
        <!-- Phần bình luận -->
        <div class="mt-5">
            <h4>Bình luận</h4>

            <!-- Hiển thị thông báo -->
            <?php if ($comment_success): ?>
                <div class="alert alert-success"><?php echo $comment_success; ?></div>
            <?php endif; ?>
            <?php foreach ($comment_errors as $error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endforeach; ?>

            <!-- Form thêm bình luận -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" class="mb-4">
                    <div class="mb-3">
                        <textarea name="content" class="form-control" rows="3" placeholder="Nhập bình luận của bạn..." required></textarea>
                    </div>
                    <button type="submit" name="add_comment" class="btn btn-primary">Gửi bình luận</button>
                </form>
            <?php else: ?>
                <p>Vui lòng <a href="login.php">đăng nhập</a> để bình luận.</p>
            <?php endif; ?>

            <!-- Danh sách bình luận -->
            <?php if (count($comments) > 0): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="card mb-2">
                        <div class="card-body">
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                            <p class="card-subtitle text-muted">
                                <small>
                                    Đăng bởi <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                                    vào lúc <?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?>
                                </small>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Chưa có bình luận nào. Hãy là người đầu tiên bình luận!</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>