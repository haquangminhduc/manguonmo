    <!-- Menu -->
   
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid"> <!-- Thay container thành container-fluid -->
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="logo.png" alt="Logo" class="logo-img me-2">
            
            </a>
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