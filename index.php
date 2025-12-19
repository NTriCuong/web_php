<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apple</title>
    <link rel="stylesheet" href="/DA-cuoiky/home/main.css">
    <link rel="stylesheet" href="/DA-cuoiky/login/login.css">
    <link rel="stylesheet" href="/DA-cuoiky/home/main.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/DA-cuoiky/category/detailiphone.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/DA-cuoiky/category/iphone.css">
    <link rel="stylesheet" href="/DA-cuoiky/order/order.css">
</head>
<body>
    
    <header>
        <div class="header-top">
            <div class="logo-placeholder">Apple
                
            </div>
            <div class="search-bar">
                <input type="text" placeholder="Bạn tìm gì...">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <div class="header-actions">
                <div><i class="fa-solid fa-cart-shopping"></i> Giỏ hàng</div>
                <div><i class="fa-regular fa-user"></i> Tài khoản</div>
            </div>
        </div>
        <nav class="header-nav">
            <div class="nav-container">
                <ul class="nav-menu">
                    <li class="nav-item"><a href="/DA-cuoiky/index.php?mod=categories&type=iphone">iPhone</a></li>
                    <li class="nav-item"><a href="/DA-cuoiky/index.php?mod=categories&type=macbook"> Mac</a></li>
                    <li class="nav-item">iPad</li>
                    <li class="nav-item">Watch</li>
                    <li class="nav-item">Âm thanh</li>
                    <li class="nav-item">Phụ kiện</li>
                    <li class="nav-item">Dịch vụ</li>
                </ul>
            </div>
        </nav>
    </header>

    <?php include('./config/config.php'); // kết nối db lấy dữ liệu 
        include ('./mod.php');
    ?>
    <footer class="site-footer">
  <div class="footer-wrap">
    <div class="footer-col footer-brand">
      <div class="footer-logos">
        <div class="logo-box">Aple</div>
        <div class="logo-box">Apple Authorized</div>
      </div>

      <p class="footer-desc">
        Năm 2020, ShopDunk trở thành đại lý uỷ quyền của Apple. Chúng tôi phát triển chuỗi cửa hàng tiêu chuẩn
        và Apple Mono Store nhằm mang đến trải nghiệm tốt nhất về sản phẩm và dịch vụ của Apple cho người dùng Việt Nam.
      </p>

      <div class="footer-hotline">
        <div><b>Hotline mua hàng:</b> <a href="tel:19006626">1900.6626</a></div>
        <div><b>Hotline bảo hành:</b> <a href="tel:19008036">1900.8036</a></div>
        <div><b>Bán hàng doanh nghiệp:</b> <a href="tel:0822688668">0822.688.668</a></div>
      </div>

      <div class="footer-social">
        <a href="#" aria-label="Facebook">F</a>
        <a href="#" aria-label="TikTok">T</a>
        <a href="#" aria-label="Zalo">Z</a>
        <a href="#" aria-label="YouTube">Y</a>
      </div>
    </div>

    <div class="footer-col">
      <h4>Thông tin</h4>
      <a href="#">Newsfeed</a>
      <a href="#">Giới thiệu</a>
      <a href="#">Check IMEI</a>
      <a href="#">Phương thức thanh toán</a>
      <a href="#">Bảo hành và sửa chữa</a>
      <a href="#">Tuyển dụng</a>
    </div>

    <div class="footer-col">
      <h4>Chính sách</h4>
      <a href="#">Thu cũ đổi mới</a>
      <a href="#">Giao hàng</a>
      <a href="#">Huỷ giao dịch</a>
      <a href="#">Đổi trả</a>
      <a href="#">Bảo mật thông tin</a>
      <a href="#">Hướng dẫn thanh toán</a>
    </div>

    <div class="footer-col">
      <h4>Địa chỉ & Liên hệ</h4>
      <a href="#">Tài khoản của tôi</a>
      <a href="#">Đơn đặt hàng</a>
      <a href="#">Tìm Store trên Google Map</a>
      <a href="#">Hệ thống cửa hàng</a>
      <div class="footer-note">
        <p><b>Mua hàng:</b> <a href="tel:19006626">1900.6626</a></p>
        <p>Nhánh 1: khu vực Hà Nội và các tỉnh phía bắc</p>
        <p>Nhánh 2: khu vực Hồ Chí Minh và các tỉnh phía nam</p>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <p>© 2025 DA-cuoiky. All rights reserved.</p>
  </div>
</footer>

</body>
</html>