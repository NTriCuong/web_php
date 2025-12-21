<?php
require_once __DIR__ . '/../config/config.php'; // chỉnh path nếu khác

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';
    } else {
        // Lấy user theo username
        $sql = "SELECT id, user_name, hash_pass, full_name, phone, address, role, created_at
                FROM users
                WHERE user_name = :u
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':u', $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // So khớp mật khẩu:
        // - Nếu bạn lưu mật khẩu bằng password_hash -> dùng password_verify
        // - Nếu bạn đang lưu plaintext (không khuyến nghị) -> so sánh trực tiếp
        $ok = false;
        if ($user) {
            if (password_verify($password, $user['hash_pass'])) {
                $ok = true;
            } elseif ($password === $user['hash_pass']) {
                // fallback nếu DB đang lưu mật khẩu thô
                $ok = true;
            }
        }

        if ($ok) {
            // Không lưu hash_pass vào session
            unset($user['hash_pass']);

            // Lưu toàn bộ info user vào session
            $_SESSION['user'] = $user;

            // Redirect về home
            header('Location: /DA-cuoiky/index.php?mod=home');
            exit;
        } else {
            $error = 'Sai tên đăng nhập hoặc mật khẩu.';
        }
    }
}
?>

    <main>
        <div class="breadcrumb">
            Trang chủ <span>&rsaquo;</span> Đăng nhập
        </div>

        <section class="login-section">
            <div class="login-image">
                <div class="img-placeholder">
                    <i class="fa-solid fa-image" style="font-size: 40px; margin-right: 10px;"></i>
                    <span>Ảnh minh họa (Upload sau)</span>
                </div>
            </div>

            <div class="login-form-container">
                <h1>Đăng nhập</h1>
                <?php if (!empty($error)): ?>
            <div style="margin:10px 0; padding:10px; border:1px solid #fca5a5; background:#fee2e2; border-radius:8px; color:#991b1b;">
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

                <form action="" method="POST">
                    <div class="form-group">
                        <label for="username">Tên đăng nhập:</label>
                        <input type="text" id="username" name="username">
                    </div>

                    <div class="form-group">
                        <label for="password">Mật khẩu:</label>
                        <input type="password" id="password" name="password">
                    </div>

                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox"> Nhớ mật khẩu
                        </label>
                        <a href="#" class="forgot-pass">Quên mật khẩu?</a>
                    </div>

                    <button type="submit" class="btn-submit">Đăng nhập</button>

                    <div class="register-link">
                        Bạn Chưa Có Tài Khoản? <a href="#">Tạo tài khoản ngay</a>
                    </div>
                </form>
            </div>
        </section>
    </main>

