<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['user'];
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_id = $user['id'];

$post_id = (int)$_GET['id'] ?? 0;

// Lấy dữ liệu bài đăng
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->execute([$post_id, $user_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    die("Không tìm thấy bài đăng hoặc bạn không có quyền sửa.");
}

$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $price = (int)$_POST['price'];
    $location = $_POST['location'];
    $area = (int)$_POST['area'];
    $description = $_POST['description'];
    $amenities = $_POST['amenities'] ?: '';
    $image = $post['image'];

    // Upload ảnh mới nếu có
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_file = $target_dir . $image_name;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($image_file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                // Xóa ảnh cũ nếu có
                if (!empty($post['image']) && file_exists($post['image'])) {
                    unlink($post['image']);
                }
                $image = $target_file;
            } else {
                $errors[] = "Lỗi khi tải ảnh mới.";
            }
        } else {
            $errors[] = "Ảnh phải là JPG, JPEG, PNG hoặc GIF.";
        }
    }

    if (!$title || !$price || !$location || !$area || !$description) {
        $errors[] = "Vui lòng điền đầy đủ thông tin.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE posts SET title = ?, price = ?, location = ?, area = ?, description = ?, amenities = ?, image = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $price, $location, $area, $description, $amenities, $image, $post_id, $user_id]);
        $success = "Cập nhật thành công!";
        // Cập nhật lại dữ liệu bài đăng để hiển thị
        $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
<!-- HTML: Form tương tự như thêm bài đăng nhưng có giá trị mặc định -->
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa bài đăng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
       <?php include('include/heard.php'); ?>
<br><br>


    <h2>Sửa bài đăng</h2>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <?php foreach ($errors as $error): ?><div class="alert alert-danger"><?= $error ?></div><?php endforeach; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" class="form-control mb-2" value="<?= htmlspecialchars($post['title']) ?>" required>
        <input type="number" name="price" class="form-control mb-2" value="<?= $post['price'] ?>" required>
        <input type="text" name="location" class="form-control mb-2" value="<?= htmlspecialchars($post['location']) ?>" required>
        <input type="number" name="area" class="form-control mb-2" value="<?= $post['area'] ?>" required>
        <textarea name="description" class="form-control mb-2" required><?= htmlspecialchars($post['description']) ?></textarea>
        <textarea name="amenities" class="form-control mb-2"><?= htmlspecialchars($post['amenities']) ?></textarea>
        <p>Ảnh hiện tại: <?php if ($post['image']): ?><img src="<?= $post['image'] ?>" width="100"><?php else: ?>Không có<?php endif; ?></p>
        <input type="file" name="image" class="form-control mb-2" accept="image/*">
        <button class="btn btn-primary">Cập nhật</button>
        <a href="manage_posts.php" class="btn btn-secondary">Quay lại</a>
    </form>
</body>
</html>
