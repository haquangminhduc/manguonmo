<?php
session_start();
include 'config.php';

// Phân trang
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Tìm kiếm
$search_conditions = [];
$search_query = '';
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['search'])) {
    $price = $_GET['price'] ?? '';
    $location = $_GET['location'] ?? '';
    $area = $_GET['area'] ?? '';
    $amenities = $_GET['amenities'] ?? '';

    if ($price) {
        $search_conditions[] = "price <= " . (int)$price;
    }
    if ($location) {
        $search_conditions[] = "location LIKE '%" . $conn->real_escape_string($location) . "%'";
    }
    if ($area) {
        $search_conditions[] = "area >= " . (int)$area;
    }
    if ($amenities) {
        $search_conditions[] = "amenities LIKE '%" . $conn->real_escape_string($amenities) . "%'";
    }

    if ($search_conditions) {
        $search_query = " WHERE " . implode(" AND ", $search_conditions);
    }
}

// Lấy danh sách phòng trọ
$sql = "SELECT * FROM posts $search_query ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// Tổng số bài đăng để phân trang
$total_sql = "SELECT COUNT(*) as total FROM posts $search_query";
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Tin mới nhất
$new_posts = $conn->query("SELECT * FROM posts ORDER BY created_at DESC LIMIT 5");

// Tin xem nhiều
$popular_posts = $conn->query("SELECT * FROM posts ORDER BY views DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ - Thuê phòng trọ</title>
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
        <!-- Tìm kiếm -->
        <h2>Tìm kiếm phòng trọ</h2>
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <input type="number" name="price" class="form-control" placeholder="Giá tối đa (VND)">
                </div>
                <div class="col-md-3">
                    <input type="text" name="location" class="form-control" placeholder="Địa điểm (gần ĐH Vinh)">
                </div>
                <div class="col-md-3">
                    <input type="number" name="area" class="form-control" placeholder="Diện tích tối thiểu (m²)">
                </div>
                <div class="col-md-3">
                    <input type="text" name="amenities" class="form-control" placeholder="Tiện ích (wifi, điều hòa...)">
                </div>
            </div>
            <button type="submit" name="search" class="btn btn-primary mt-3">Tìm kiếm</button>
        </form>

        <!-- Bố cục hai cột -->
        <div class="row">
            <!-- Cột trái: Danh sách phòng trọ -->
            <div class="col-md-8">
                <h2>Danh sách phòng trọ</h2>
                <div class="row">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <img src="<?php echo $row['image'] ?: 'default.jpg'; ?>" class="card-img-top" alt="Phòng trọ">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                                    <p class="card-text">Giá: <?php echo number_format($row['price']); ?> VND</p>
                                    <p class="card-text">Địa điểm: <?php echo htmlspecialchars($row['location']); ?></p>
                                    <p class="card-text">Diện tích: <?php echo $row['area']; ?> m²</p>
                                    <p class="card-text">Lượt xem: <?php echo $row['views']; ?></p>
                                    <a href="post.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Xem chi tiết</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Phân trang -->
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>

            <!-- Cột phải: Tin mới nhất và Tin xem nhiều -->
            <div class="col-md-4">
                <!-- Tin mới nhất -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h2 class="h5">Tin mới nhất</h2>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <?php while ($row = $new_posts->fetch_assoc()): ?>
                                <li class="mb-2">
                                    <a href="post.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['title']); ?></a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>

                <!-- Tin xem nhiều -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="h5">Tin xem nhiều nhất</h2>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <?php while ($row = $popular_posts->fetch_assoc()): ?>
                                <li class="mb-2">
                                    <a href="post.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['title']); ?></a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thông tin liên hệ -->
        <?php include('include/ttlh.php'); ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>