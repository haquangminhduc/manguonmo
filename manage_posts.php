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

// Xử lý thêm tin đăng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_post'])) {
    $title = $_POST['title'];
    $price = (int)$_POST['price'];
    $location = $_POST['location'];
    $area = (int)$_POST['area'];
    $description = $_POST['description'];
    $amenities = $_POST['amenities'] ?: ''; // Tiện ích có thể để trống
    $image = '';

    if (!$title || !$price || !$location || !$area || !$description) {
        $errors[] = "Vui lòng nhập đầy đủ thông tin bắt buộc.";
    } else {
        // Xử lý upload ảnh
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
                    $image = $target_file;
                } else {
                    $errors[] = "Lỗi khi tải ảnh lên.";
                }
            } else {
                $errors[] = "Chỉ chấp nhận file ảnh JPG, JPEG, PNG, GIF.";
            }
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO posts (title, price, location, area, description, amenities, user_id, image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title, $price, $location, $area, $description, $amenities, $user_id, $image]);
            $success = "Thêm tin đăng thành công!";
        }
    }
}

// Xử lý xóa bài đăng
if (isset($_GET['delete_post'])) {
    $post_id = (int)$_GET['delete_post'];

    // Kiểm tra xem bài đăng có tồn tại và thuộc về người dùng không
    $stmt = $conn->prepare("SELECT image FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$post_id, $user_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($post) {
        // Xóa các bản ghi liên quan trong bảng reports trước
        $stmt = $conn->prepare("DELETE FROM reports WHERE post_id = ?");
        $stmt->execute([$post_id]);

        // Xóa file ảnh nếu tồn tại
        if (!empty($post['image']) && is_string($post['image']) && file_exists($post['image'])) {
            unlink($post['image']);
        }

        // Xóa bài đăng
        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "Xóa bài đăng thành công!";
        } else {
            $_SESSION['errors'][] = "Không thể xóa bài đăng.";
        }
    } else {
        $_SESSION['errors'][] = "Bài đăng không tồn tại hoặc bạn không có quyền.";
    }

    // Chuyển hướng để tránh lỗi khi reload
    header("Location: manage_posts.php");
    exit;
}

// Hiển thị thông báo từ session và xóa sau khi hiển thị
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    unset($_SESSION['errors']);
}

// Lấy danh sách bài đăng của người dùng
$stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tin đăng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .alert {
            position: relative;
            padding-left: 3rem;
        }
        .alert i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
</head>
<body>
      <?php include('include/heard.php'); ?>


    <!-- Nội dung chính -->
    <div class="container mt-3 pt-3">
        <h2>Quản lý tin đăng</h2>

        <?php if ($success): ?>
            <div class="alert alert-success" id="success-alert">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger" id="error-alert">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endforeach; ?>

        <!-- Form thêm tin đăng -->
        <h4>Thêm tin đăng</h4>
        <form method="POST" enctype="multipart/form-data" class="mb-4">
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
                <div class="col-md-2">
                    <input type="file" name="image" class="form-control mb-2" accept="image/*">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <textarea name="description" class="form-control mb-2" rows="3" placeholder="Mô tả chi tiết" required></textarea>
                </div>
                <div class="col-md-6">
                    <textarea name="amenities" class="form-control mb-2" rows="3" placeholder="Tiện ích (wifi, điều hòa, v.v.)"></textarea>
                </div>
            </div>
            <button type="submit" name="add_post" class="btn btn-primary">Thêm tin</button>
        </form>

        <!-- Danh sách tin đăng -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tiêu đề</th>
                    <th>Giá (VND)</th>
                    <th>Địa điểm</th>
                    <th>Diện tích (m²)</th>
                    <th>Mô tả</th>
                    <th>Tiện ích</th>
                    <th>Ảnh</th>
                    <th>Hành động</th>
                    <th>Sửa bài đăng</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?php echo $post['id']; ?></td>
                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                        <td><?php echo number_format($post['price']); ?></td>
                        <td><?php echo htmlspecialchars($post['location']); ?></td>
                        <td><?php echo $post['area']; ?></td>
                        <td><?php echo htmlspecialchars($post['description']); ?></td>
                        <td><?php echo htmlspecialchars($post['amenities'] ?: 'Không có'); ?></td>
                        <td>
                            <?php if ($post['image']): ?>
                                <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Ảnh tin đăng" width="50">
                            <?php else: ?>
                                <span>Không có ảnh</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="post.php?id=<?php echo $post['id']; ?>" class="btn btn-info btn-sm">Xem</a>
                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" data-post-id="<?php echo $post['id']; ?>">Xóa</button>
                        </td>
                        <td>
<a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                            
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal xác nhận xóa -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Xác nhận xóa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc chắn muốn xóa bài đăng này không?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <a href="#" id="confirmDelete" class="btn btn-danger">Xóa</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tự động ẩn thông báo sau 3 giây
        setTimeout(() => {
            const successAlert = document.getElementById('success-alert');
            const errorAlerts = document.querySelectorAll('#error-alert');
            if (successAlert) {
                successAlert.classList.add('fade');
                successAlert.style.display = 'none';
            }
            errorAlerts.forEach(alert => {
                alert.classList.add('fade');
                alert.style.display = 'none';
            });
        }, 3000);

        // Xử lý modal xác nhận xóa
        const deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const postId = button.getAttribute('data-post-id');
            const confirmDelete = document.getElementById('confirmDelete');
            confirmDelete.href = `manage_posts.php?delete_post=${postId}`;
        });
    </script>
</body>
</html>