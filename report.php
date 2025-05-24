<?php
session_start();
require 'db.php';

// Kiểm tra trạng thái đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['user'];

// Lấy user_id từ bảng users
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_id = $user['id'];

$success = '';
$errors = [];

// Xử lý gửi báo cáo
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_report'])) {
    $post_id = (int)$_POST['post_id'];
    $message = trim($_POST['message']);

    if (!$post_id || !$message) {
        $errors[] = "Vui lòng nhập đầy đủ thông tin.";
    } else {
        $stmt = $conn->prepare("INSERT INTO reports (post_id, user_id, message, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$post_id, $user_id, $message]);
        $success = "Gửi báo cáo thành công!";
    }
}

// Lấy danh sách bài đăng để chọn (tùy chọn)
$stmt = $conn->query("SELECT id, title FROM posts");
$posts_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gửi báo cáo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
      <?php include('include/heard.php'); ?>

    <!-- Nội dung chính -->
    <div class="container mt-3 pt-3">
        <h2>Gửi báo cáo</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endforeach; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="post_id" class="form-label">Chọn tin đăng</label>
                <select name="post_id" class="form-control" required>
                    <?php foreach ($posts_list as $post): ?>
                        <option value="<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Nội dung báo cáo</label>
                <textarea name="message" class="form-control" rows="5" required></textarea>
            </div>
            <button type="submit" name="submit_report" class="btn btn-primary">Gửi báo cáo</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>