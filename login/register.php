<?php
    if (!isset($conn)) {
        require_once __DIR__ . '/../config/config.php';
    }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { // đã bấm đăng ký
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? ''); 
            $password1 = trim($_POST['password1'] ?? ''); // xác nhận mật khẩu
            $phone = trim($_POST['phone'] ?? '');
            $fullname = trim($_POST['fullname'] ?? '');
            if ($username === '' || $password === '' || $password1 === '' ||  $phone === '') { 
                // kiểm tra username pass có rỗng không
                $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';
            } elseif ($password !== $password1) {
                $error = 'Mật khẩu xác nhận không khớp.';
            }
            else {
                    
                 // Mã hóa mật khẩu trước khi lưu vào CSDL
$hash_pass = password_hash($password, PASSWORD_DEFAULT);

// (khuyến nghị) kiểm tra user_name đã tồn tại chưa
$checkSql = "SELECT id FROM users WHERE user_name = :u LIMIT 1";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bindValue(':u', $username, PDO::PARAM_STR);
$checkStmt->execute();

if ($checkStmt->fetch()) {
    $error = "Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác.";
} else {
            // INSERT đúng tên cột theo DB của bạn
            $sql = "INSERT INTO users (role, user_name, hash_pass, full_name, phone, address, created_at)
                    VALUES ('customer', :u, :p, :ful, :ph, NULL, NOW())";

            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':u',  $username,  PDO::PARAM_STR);
            $stmt->bindValue(':p',  $hash_pass, PDO::PARAM_STR);
            $stmt->bindValue(':ph', $phone,     PDO::PARAM_STR);
            $stmt->bindValue(':ful', $fullname,     PDO::PARAM_STR);

            try {
                $stmt->execute();

                // Đăng ký thành công -> về trang login
                header('Location: /DA-cuoiky/index.php?mod=login&&check=true');
                exit;
            } catch (PDOException $e) {
                // Lỗi khác (DB, kết nối,...)
                $error = "Đã có lỗi xảy ra. Vui lòng thử lại.";
            }
        }


                }
    }else{

    }
?>
<main class="auth-page">
    <div class="breadcrumb">
        Trang chủ <span>&rsaquo;</span> Đăng ký
    </div>

    <section class="login-section">
        <div class="login-form-container">
            <h1>Đăng ký</h1>
    <!-- hiển thị lỗi -->
            <?php if (!empty($error)): ?>
                <div class="auth-error">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="/DA-cuoiky/index.php?mod=register" method="POST" class="auth-form">
                 <div class="form-group">
                    <label for="password">Họ và tên:</label>
                    <input type="text" id="fullname" name="fullname">
                </div>
                <div class="form-group">
                    <label for="username">Tên đăng nhập:</label>
                    <input type="text" id="username" name="username" value="">
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu:</label>
                    <input type="password" id="password" name="password">
                </div>
                <div class="form-group">
                    <label for="password">Xác nhận mật khẩu:</label>
                    <input type="password" id="password1" name="password1">
                </div>

                <div class="form-group">
                    <label for="password">Số điện thoại:</label>
                    <input type="number" id="phone" name="phone">
                </div>


                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember"> Chấp nhận điều khoản và chính sách
                    </label>
                </div>

                <button type="submit" class="btn-submit">Đăng ký</button>

                <div class="register-link">
                    Đã có tài khoản?
                    <a href="/DA-cuoiky/index.php?mod=login&&check=true">Đăng nhập</a>
                </div>
            </form>
        </div>
    </section>
</main>
