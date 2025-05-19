<?php
session_start();
// Khởi tạo CAPTCHA riêng cho đăng nhập và đăng ký nếu chưa có
if (!isset($_SESSION['captcha_login'])) {
    $_SESSION['captcha_login'] = rand(1000, 9999);
}
if (!isset($_SESSION['captcha_register'])) {
    $_SESSION['captcha_register'] = rand(1000, 9999);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Signup Form</title>
    <link rel="stylesheet" href="SignUp_LogIn_Form.css">
    <link rel="stylesheet" href="css/login.css">
    <style>
        .form-box.register { display: none; }
        .container.active .form-box.login { display: none; }
        .container.active .form-box.register { display: block; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Form Dang Nhap -->
        <div class="form-box login">
            <form action="auth.php" method="POST">
                <h1>Login</h1>
                <input type="hidden" name="action" value="login">
                <?php
                if (isset($_SESSION['error'])) {
                    echo '<p style="color:red; text-align:center;">' . $_SESSION['error'] . '</p>';
                    unset($_SESSION['error']);
                }
                ?>
                <div class="input-box">
                    <input type="text" name="username" placeholder="Username or Email" required>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="input-box">
                    <img id="captcha_img" src="captcha.php?type=login" alt="CAPTCHA">
                    <button type="button" onclick="document.getElementById('captcha_img').src='captcha.php?type=login&refresh=1&'+Math.random()">Làm mới mã</button>
                </div>
                <div class="input-box">
                    <input type="text" name="captcha" placeholder="Nhập mã CAPTCHA" required>
                </div>
                <button type="submit" class="btn">Đăng Nhập</button>
            </form>
        </div>

        <!-- Form Dang Ky -->
        <div class="form-box register">
            <form action="auth.php" method="POST">
                <h1>Register</h1>
                <input type="hidden" name="action" value="register">
                <div class="input-box">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="input-box">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="input-box">
                    <img id="captcha_img2" src="captcha.php?type=register" alt="CAPTCHA">
                    <button type="button" onclick="document.getElementById('captcha_img2').src='captcha.php?type=register&refresh=1&'+Math.random()">Làm mới mã</button>
                </div>
                <div class="input-box">
                    <input type="text" name="captcha" placeholder="Nhập mã CAPTCHA" required>
                </div>
                <button type="submit" class="btn">Đăng Ký</button>
            </form>
        </div>

        <!-- Toggle Box -->
        <div class="toggle-box">
            <div class="toggle-panel toggle-left">
                <h1>Hello, Welcome!</h1>
                <p>Don't have an account?</p>
                <button class="btn register-btn">Đăng Ký</button>
            </div>
            <div class="toggle-panel toggle-right">
                <h1>Welcome Back!</h1>
                <p>Already have an account?</p>
                <button class="btn login-btn">Login</button>
            </div>
        </div>
    </div>

    <script>
        const container = document.querySelector('.container');
        const loginBtn = document.querySelector('.login-btn');
        const registerBtn = document.querySelector('.register-btn');

        registerBtn.addEventListener('click', () => {
            container.classList.add('active');
        });

        loginBtn.addEventListener('click', () => {
            container.classList.remove('active');
        });
    </script>
</body>
</html>