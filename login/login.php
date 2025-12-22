<?php

// Nếu login.php được include từ index.php thì $conn đã có.
// Nếu chạy trực tiếp login.php thì mới cần include config.
if (!isset($conn)) {
    require_once __DIR__ . '/../config/config.php';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') { // đã bấm đăng nhập
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? ''); 

    if ($username === '' || $password === '') { // kiểm tra username pass có rỗng không
        $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';
    } else {// không rỗng truy vấn csdl
        $sql = "SELECT id, user_name, hash_pass, full_name, phone, address, role, created_at
                FROM users
                WHERE user_name = :u
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':u', $username, PDO::PARAM_STR); // tránh SQL Injection
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
// so khớp với hash pass trong database
        $ok = false; 
        if ($user) {
            // DB dùng password_hash()
            if (!empty($user['hash_pass']) && password_verify($password, $user['hash_pass'])) {
                $ok = true;
            }
            // Fallback nếu DB đang lưu mật khẩu thô (không khuyến nghị)
            elseif ($password === $user['hash_pass']) {
                $ok = true;
            }
        }

        if ($ok) { // nếu đăg nhập thành công thì chuyển về home
            
            unset($user['hash_pass']);
            $_SESSION['user'] = $user;
            header('Location: /DA-cuoiky/index.php?mod=home');
            exit;
        } else {
            $error = 'Sai tên đăng nhập hoặc mật khẩu.';
        }
    }
}
?>

<main class="auth-page">
    <div class="breadcrumb">
      <a href="/DA-cuoiky/index.php?mod=home">Trang chủ </a>  <span>&rsaquo;</span> <a href="#"> Đăng nhập</a>
    </div>

    <section class="login-section">
        <div class="login-form-container">
            <h1>Đăng nhập</h1>

            <?php 
                $check = $_GET['check'] ?? '';
                if (!empty($error) && !$check ) : ?>
                <div class="auth-error">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="/DA-cuoiky/index.php?mod=login" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username">Tên đăng nhập:</label>
                    <input type="text" id="username" name="username" >
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu:</label>
                    <input type="password" id="password" name="password">
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember"> Nhớ mật khẩu
                    </label>
                    <a href="#" class="forgot-pass">Quên mật khẩu?</a>
                </div>

                <button type="submit" class="btn-submit">Đăng nhập</button>

                <div class="register-link">
                    Bạn Chưa Có Tài Khoản?
                    <a href="/DA-cuoiky/index.php?mod=register">Tạo tài khoản ngay</a>
                </div>
            </form>
        </div>
    </section>
</main>
