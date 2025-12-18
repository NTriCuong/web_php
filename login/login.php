<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - ShopDunk</title>
    <link rel="stylesheet" href="login.css">
    
</head>
<body>

    <header>
        <div class="header-top">
            <div class="logo-area">
                <div class="logo-placeholder">LOGO SHOPDUNK</div>
            </div>

            <div class="search-bar">
                <input type="text" placeholder="Bạn tìm gì...">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>

            <div class="header-actions">
                <div class="action-item">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span>Giỏ hàng</span>
                </div>
                <div class="action-item">
                    <i class="fa-regular fa-user"></i>
                    <span>Tài khoản</span>
                </div>
                <div class="action-item lang-flags">
                    <span>VN</span>
                    <span>🇺🇸</span>
                </div>
            </div>
        </div>

        <nav class="header-nav">
            <div class="nav-container">
                <ul class="nav-menu">
                    <li class="nav-item"><i class="fa-solid fa-bars"></i> &nbsp; Dịch vụ</li>
                    <li class="nav-item">iPhone</li>
                    <li class="nav-item">iPad</li>
                    <li class="nav-item">Mac</li>
                    <li class="nav-item">Watch</li>
                    <li class="nav-item">Phụ kiện</li>
                    <li class="nav-item">Âm thanh</li>
                    <li class="nav-item">Camera</li>
                    <li class="nav-item">Gia dụng</li>
                    <li class="nav-item">Máy lướt</li>
                </ul>
            </div>
        </nav>
    </header>

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
                <form action="#" method="POST">
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

    <div class="shop-msg">
        <div style="width: 20px; height: 20px; background: green; border-radius: 50%;"></div>
        <div>
            <strong>SHOPDUNK</strong><br>
            ShopDunk xin chào!
        </div>
    </div>
    <div class="chat-icon-circle">
        <i class="fa-solid fa-comment-dots"></i>
    </div>

</body>
</html>