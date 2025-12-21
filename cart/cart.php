<?php
// 1. KẾT NỐI DB (Quan trọng: Phải ở đầu file)
include('./config/config.php');

// --- 2. LOGIC LẤY MINI CART ---
$mini_cart_items = [];
$mini_cart_total = 0;
// Giả sử User ID = 1 (Sau này thay bằng $_SESSION['user_id'])
$user_id_demo = 1;

if (isset($conn)) {
    try {
        // Lấy giỏ hàng active
        $stmtC = $conn->prepare("SELECT id FROM carts WHERE user_id = ? AND status = 'active' LIMIT 1");
        $stmtC->execute([$user_id_demo]);
        $cartRow = $stmtC->fetch(PDO::FETCH_ASSOC);

        if ($cartRow) {
            // Lấy chi tiết sản phẩm
            // SQL đã sửa theo đúng file apple_store.sql bạn gửi:
            // Bảng cart_items dùng cột 'variant_id' và 'quantity'
            $sqlMini = "SELECT ci.quantity, p.name, p.slug, pv.color, pv.price 
                        FROM cart_items ci
                        JOIN product_variants pv ON ci.variant_id = pv.id
                        JOIN products p ON pv.product_id = p.id
                        WHERE ci.cart_id = ?";
            $stmtMini = $conn->prepare($sqlMini);
            $stmtMini->execute([$cartRow['id']]);
            $mini_cart_items = $stmtMini->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        // Có thể log lỗi vào file log nếu cần
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apple Store</title>
    
    <link rel="stylesheet" href="/DA-cuoiky/home/main.css">
    <link rel="stylesheet" href="/DA-cuoiky/login/login.css">
    <link rel="stylesheet" href="/DA-cuoiky/home/main.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/DA-cuoiky/category/detailiphone.css">
    <link rel="stylesheet" href="/DA-cuoiky/category/iphone.css">
    <link rel="stylesheet" href="/DA-cuoiky/order/order.css">
    <link rel="stylesheet" href="cart.css">
</head>
<body>
    
    <header>
        <div class="header-top">
            <div class="logo-placeholder">
                <a href="index.php" style="text-decoration:none; color:inherit; font-weight:bold;">Apple</a>
            </div>
            <div class="search-bar">
                <input type="text" placeholder="Bạn tìm gì...">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <div class="header-actions">
                
                <div class="header-cart-wrapper">
                    <div class="cart-btn" onclick="toggleMiniCart()">
                        <i class="fa-solid fa-cart-shopping"></i> 
                        <span>Giỏ hàng</span>
                        <?php if(count($mini_cart_items) > 0): ?>
                            <span class="cart-badge"><?php echo count($mini_cart_items); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="mini-cart-dropdown" id="miniCart">
                        <div class="mini-cart-header">Giỏ hàng của bạn</div>
                        
                        <div class="mini-cart-body">
                            <?php if(empty($mini_cart_items)): ?>
                                <div class="empty-msg">Giỏ hàng đang trống</div>
                            <?php else: ?>
                                <?php foreach($mini_cart_items as $item): 
                                    $sub = $item['price'] * $item['quantity'];
                                    $mini_cart_total += $sub;
                                    // Tạo link ảnh: img/products/slug.jpg
                                    $img = 'img/products/' . $item['slug'] . '.jpg';
                                ?>
                                <div class="mini-cart-item">
                                    <img src="<?php echo $img; ?>" alt="Img" class="mini-item-img" onerror="this.src='img/no-image.jpg'">
                                    <div class="mini-item-info">
                                        <a href="#" class="mini-item-name"><?php echo $item['name']; ?></a>
                                        <div class="mini-item-meta">
                                            Màu: <?php echo $item['color']; ?> <br>
                                            SL: <?php echo $item['quantity']; ?>
                                        </div>
                                    </div>
                                    <div class="mini-item-price"><?php echo number_format($item['price'], 0, ',', '.'); ?>₫</div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <?php if(!empty($mini_cart_items)): ?>
                        <div class="mini-cart-footer">
                            <div class="mini-total">
                                <span>Tổng cộng:</span> 
                                <span style="color:#d70018"><?php echo number_format($mini_cart_total, 0, ',', '.'); ?>₫</span>
                            </div>
                            <a href="cart/cart.php" class="btn-view-cart">XEM GIỎ HÀNG & THANH TOÁN</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="action-item"><i class="fa-regular fa-user"></i> <span>Tài khoản</span></div>
                <div class="action-item">VN 🇺🇸</div>
            </div>
        </div>
        
        <nav class="header-nav">
            <div class="nav-container">
                <ul class="nav-menu">
                    <li class="nav-item"><a href="index.php?mod=categories&type=iphone">iPhone</a></li>
                    <li class="nav-item"><a href="index.php?mod=categories&type=macbook">Mac</a></li>
                    <li class="nav-item">iPad</li>
                    <li class="nav-item">Watch</li>
                    <li class="nav-item">Âm thanh</li>
                    <li class="nav-item">Phụ kiện</li>
                    <li class="nav-item">Dịch vụ</li>
                </ul>
            </div>
        </nav>
    </header>

    <?php include ('./mod.php'); ?>
    <?php include('../config/config.php'); ?>

    <footer class="site-footer">
      <div class="footer-wrap">
        <div class="footer-col footer-brand">
          <div class="footer-logos">
            <div class="logo-box">Apple</div>
          </div>
          <p class="footer-desc">
            Năm 2020, ShopDunk trở thành đại lý uỷ quyền của Apple.
          </p>
        </div>
        <div class="footer-col">
          <h4>Thông tin</h4>
          <a href="#">Giới thiệu</a>
          <a href="#">Bảo hành</a>
        </div>
        <div class="footer-col">
          <h4>Liên hệ</h4>
          <a href="#">1900.6626</a>
        </div>
      </div>
      <div class="footer-bottom">
        <p>© 2025 DA-cuoiky. All rights reserved.</p>
      </div>
    </footer>

    <script>
        function toggleMiniCart() {
            var cart = document.getElementById("miniCart");
            cart.classList.toggle("show");
        }
        
        // Đóng khi click ra ngoài
        window.onclick = function(event) {
            if (!event.target.closest('.header-cart-wrapper')) {
                var dropdowns = document.getElementsByClassName("mini-cart-dropdown");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>

</body>
</html>