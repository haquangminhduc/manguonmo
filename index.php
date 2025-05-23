<?php
session_start();
include 'config.php';

// Phân trang
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Tìm kiếm và bộ lọc
$search_conditions = [];
$search_query = '';
$filter = $_GET['filter'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Tìm kiếm
    $price = $_GET['price'] ?? '';
    $location = $_GET['location'] ?? '';
    $area = $_GET['area'] ?? '';
    $amenities = $_GET['amenities'] ?? '';
    $title = $_GET['title'] ?? '';
    if ($title) {
        $search_conditions[] = "title LIKE '%" . $conn->real_escape_string($title) . "%'";
    }

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

    // Bộ lọc "Phòng khép kín"
    if ($filter == 'khep_kin') {
        $search_conditions[] = "amenities LIKE '%khép kín%'";
    }

    if ($search_conditions) {
        $search_query = " WHERE " . implode(" AND ", $search_conditions);
    }
}

// Lấy danh sách phòng trọ dựa trên bộ lọc
$sql = "SELECT * FROM posts $search_query ORDER BY created_at DESC";
if ($filter != 'khep_kin') {
    $sql .= " LIMIT $limit OFFSET $offset"; // Phân trang cho các tab khác
}
$result = $conn->query($sql);

// Tổng số bài đăng để phân trang
$total_sql = "SELECT COUNT(*) as total FROM posts $search_query";
$total_result = $conn->query($total_sql);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Tin mới nhất (không bị ảnh hưởng bởi bộ lọc)
$new_posts_sql = "SELECT * FROM posts ORDER BY created_at DESC LIMIT 5";
$new_posts = $conn->query($new_posts_sql);

// Tin xem nhiều (không bị ảnh hưởng bởi bộ lọc)
$popular_posts_sql = "SELECT * FROM posts ORDER BY views DESC LIMIT 5";
$popular_posts = $conn->query($popular_posts_sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ - Thuê phòng trọ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
     
    <!-- Menu -->
    <?php include('include/heard.php'); ?>

    <!-- Nội dung chính -->
    <div class="container mt-3 pt-3">
        <!-- Tìm kiếm -->
        <h2>Tìm kiếm phòng trọ</h2>
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <input type="number" name="price" class="form-control" placeholder="Giá tối đa (VND)" value="<?php echo htmlspecialchars($_GET['price'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="location" class="form-control" placeholder="Địa điểm (gần ĐH Vinh)" value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <input type="number" name="area" class="form-control" placeholder="Diện tích tối thiểu (m²)" value="<?php echo htmlspecialchars($_GET['area'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="amenities" class="form-control" placeholder="Tiện ích (wifi, điều hòa...)" value="<?php echo htmlspecialchars($_GET['amenities'] ?? ''); ?>">
                </div>
            </div>
            <button type="submit" name="search" class="btn btn-primary mt-3">Tìm kiếm</button>
        </form>

        <!-- Bố cục hai cột -->
        <div class="row">
            <!-- Cột trái: Danh sách phòng trọ -->
            <div class="col-md-8">
                <h2>
                    <?php 
                    if ($filter == 'khep_kin') {
                        echo "Phòng khép kín";
                    } elseif ($filter == 'most_viewed') {
                        echo "Tin xem nhiều nhất";
                    } else {
                        echo "Danh sách phòng trọ";
                    }
                    ?>
                </h2>
                <div class="row">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card post-card">
                                    <img src="<?php echo $row['image'] ?: 'default.jpg'; ?>" class="card-img-top" alt="Phòng trọ">
                                    <div class="card-body">
                                        <div class="card-content">
                                            <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                                            <p class="card-text">Giá: <?php echo number_format($row['price']); ?> VND</p>
                                            <p class="card-text">Địa điểm: <?php echo htmlspecialchars($row['location']); ?></p>
                                            <p class="card-text">Diện tích: <?php echo $row['area']; ?> m²</p>
                                            <p class="card-text">Lượt xem: <?php echo $row['views']; ?></p>
                                        </div>
                                        <a href="post.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">Xem chi tiết</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>Không có phòng trọ nào phù hợp.</p>
                    <?php endif; ?>
                </div>

                <!-- Phân trang -->
                <?php if ($filter != 'khep_kin' && $total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $filter ? '&filter=' . $filter : ''; ?><?php echo isset($_GET['price']) ? '&price=' . $_GET['price'] : ''; ?><?php echo isset($_GET['location']) ? '&location=' . $_GET['location'] : ''; ?><?php echo isset($_GET['area']) ? '&area=' . $_GET['area'] : ''; ?><?php echo isset($_GET['amenities']) ? '&amenities=' . $_GET['amenities'] : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>

            <!-- Cột phải: Sidebar với Tin mới nhất và Tin xem nhiều -->
            <div class="col-md-4">
                <!-- Tin mới nhất -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h2 class="h5">Tin mới nhất</h2>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <?php
                            // Reset con trỏ của $new_posts để sử dụng lại
                            $new_posts->data_seek(0);
                            $seen_titles = []; // Mảng lưu các tiêu đề đã hiển thị
                            while ($row = $new_posts->fetch_assoc()):
                                $title = htmlspecialchars($row['title']);
                                if (!in_array($title, $seen_titles)) {
                                    $seen_titles[] = $title;
                                    ?>
                                    <li class="mb-2">
                                        <a href="index.php?title=<?php echo urlencode($row['title']); ?>">
                                            <?php echo $title; ?>
                                        </a>
                                    </li>
                                    <?php
                                }
                            endwhile;
                            ?>
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
                            <?php
                            // Reset con trỏ của $popular_posts để sử dụng lại
                            $popular_posts->data_seek(0);
                            $seen_titles = []; // Mảng lưu các tiêu đề đã hiển thị
                            while ($row = $popular_posts->fetch_assoc()):
                                $title = htmlspecialchars($row['title']);
                                if (!in_array($title, $seen_titles)) {
                                    $seen_titles[] = $title;
                                    ?>
                                    <li class="mb-2">
                                        <a href="index.php?title=<?php echo urlencode($row['title']); ?>">
                                            <?php echo $title; ?>
                                        </a>
                                    </li>
                                    <?php
                                }
                            endwhile;
                            ?>
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