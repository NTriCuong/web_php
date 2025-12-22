<?php
ob_start();
// 1) SESSION
    session_start();

// 2) DB
include('./config/config.php');

// 3) USER SESSION
$fullName = $_SESSION['user']['full_name'] ?? '';
$userId   = (int)($_SESSION['user']['id'] ?? 0);

// Nếu bạn muốn demo user_id = 2 khi chưa login thì bật dòng dưới:
// if ($userId <= 0) $userId = 2;

// 4) MINI CART DATA
$mini_cart_items = [];
$mini_cart_total = 0;

// Hàm hỗ trợ tạo slug (dùng khi DB không có image_url)
if (!function_exists('createSlug')) {
    function createSlug($str) {
        $str = trim(mb_strtolower($str));
        $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
        $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
        $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
        $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
        $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
        $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
        $str = preg_replace('/(đ)/', 'd', $str);
        $str = preg_replace('/[^a-z0-9-\s]/', '', $str);
        $str = preg_replace('/([\s]+)/', '-', $str);
        return $str;
    }
}

// Load mini cart nếu có userId và có $conn
if (isset($conn) && $userId > 0) {
    try {
        // Lấy cart active
        $stmtC = $conn->prepare("SELECT id FROM carts WHERE user_id = ? AND status = 'active' LIMIT 1");
        $stmtC->execute([$userId]);
        $cartRow = $stmtC->fetch(PDO::FETCH_ASSOC);

        if ($cartRow) {
            // Lấy items
            $sqlMini = "SELECT ci.quantity, p.name, p.slug, pv.color, pv.price, pv.image_url
                        FROM cart_items ci
                        JOIN product_variants pv ON ci.variant_id = pv.id
                        JOIN products p ON pv.product_id = p.id
                        WHERE ci.cart_id = ?";
            $stmtMini = $conn->prepare($sqlMini);
            $stmtMini->execute([$cartRow['id']]);
            $mini_cart_items = $stmtMini->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }
    } catch (Exception $e) {
        // bỏ qua lỗi DB
        $mini_cart_items = [];
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
    <link rel="stylesheet" href="/DA-cuoiky/cart/cart.css">

    <style>
        .header-cart-wrapper { position: relative; display: inline-block; z-index: 9999; }
        .cart-btn { cursor: pointer; position: relative; padding: 5px; display: flex; align-items: center; gap: 5px; }
        .cart-btn:hover { color: #ccc; }
        .cart-badge {
            position: absolute; top: -8px; right: -5px;
            background-color: #d70018; color: white;
            font-size: 10px; border-radius: 50%; padding: 2px 5px; font-weight: bold;
            min-width: 15px; text-align: center;
        }
        .mini-cart-dropdown {
            display: none; position: absolute;
            top: 100%; right: 0; margin-top: 10px;
            width: 320px; background-color: #fff;
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
            border-radius: 8px; border: 1px solid #eee;
            text-align: left; color: #333;
        }
        .mini-cart-dropdown::before {
            content: ""; position: absolute; top: -10px; right: 20px;
            border-left: 10px solid transparent; border-right: 10px solid transparent;
            border-bottom: 10px solid #fff;
        }
        .mini-cart-dropdown.show { display: block; }
        .mini-cart-header { padding: 12px 15px; border-bottom: 1px solid #f0f0f0; font-weight: bold; font-size: 14px; }
        .mini-cart-body { max-height: 300px; overflow-y: auto; }
        .mini-cart-item { display: flex; gap: 10px; padding: 10px 15px; border-bottom: 1px solid #f9f9f9; align-items: center; }
        .mini-cart-item:hover { background-color: #f5f5f7; }
        .mini-item-img { width: 50px; height: 50px; object-fit: contain; border: 1px solid #eee; border-radius: 4px; }
        .mini-item-info { flex: 1; }
        .mini-item-name { font-size: 13px; font-weight: 600; color: #333; display: block; margin-bottom: 3px; text-decoration: none;}
        .mini-item-meta { font-size: 11px; color: #888; }
        .mini-item-price { font-size: 13px; font-weight: bold; color: #d70018; }
        .mini-cart-footer { padding: 15px; background-color: #fafafa; border-top: 1px solid #eee; border-radius: 0 0 8px 8px; text-align: center; }
        .mini-total { display: flex; justify-content: space-between; margin-bottom: 10px; font-weight: bold; font-size: 14px; }
        .btn-view-cart {
            display: block; width: 100%; padding: 10px 0;
            background-color: #0066CC; color: white;
            text-align: center; border-radius: 5px;
            font-weight: bold; text-decoration: none; font-size: 13px;
        }
        .btn-view-cart:hover { background-color: #005bb5; }
        .empty-msg { padding: 30px; text-align: center; color: #999; font-size: 13px; }

        /* Account dropdown */
        .account-wrap { position: relative; display: inline-block; }
        .account-btn { background: transparent; border: 0; cursor: pointer; color: inherit; font: inherit; display:flex; align-items:center; gap:6px; }
        .account-menu {
            display:none; position:absolute; top:110%; right:0;
            background:#fff; border:1px solid #ddd; border-radius:8px;
            min-width:140px; z-index:9999;
        }
        .account-menu a { display:block; padding:8px 12px; text-decoration:none; color:#111; }
        .account-menu a:hover { background:#f5f5f7; }
    </style>
</head>
<body>

<header>
    <div class="header-top">
        <div class="logo-placeholder">
            <a href="/DA-cuoiky/index.php?mod=home" style="text-decoration:none; color:inherit; font-weight:bold;">Apple</a>
        </div>

        <div class="search-bar">
            <input type="text" placeholder="Bạn tìm gì...">
            <i class="fa-solid fa-magnifying-glass"></i>
        </div>

        <div class="header-actions">

            <!-- MINI CART -->
            <div class="header-cart-wrapper">
                <div class="cart-btn" id="cartBtn">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span>Giỏ hàng</span>
                    <?php if (count($mini_cart_items) > 0): ?>
                        <span class="cart-badge"><?php echo count($mini_cart_items); ?></span>
                    <?php endif; ?>
                </div>

                <div class="mini-cart-dropdown" id="miniCart">
                    <div class="mini-cart-header">Giỏ hàng của bạn</div>

                    <div class="mini-cart-body">
                        <?php if (empty($mini_cart_items)): ?>
                            <div class="empty-msg">Giỏ hàng đang trống</div>
                        <?php else: ?>
                            <?php foreach ($mini_cart_items as $item):
                                $sub = (float)$item['price'] * (int)$item['quantity'];
                                $mini_cart_total += $sub;

                                $img = !empty($item['image_url'])
                                    ? $item['image_url']
                                    : 'img/products/' . createSlug($item['name']) . '.jpg';
                            ?>
                                <div class="mini-cart-item">
                                    <img src="<?php echo htmlspecialchars($img); ?>" alt="Img" class="mini-item-img"
                                         onerror="this.src='img/no-image.jpg'">
                                    <div class="mini-item-info">
                                        <a href="#" class="mini-item-name"><?php echo htmlspecialchars($item['name']); ?></a>
                                        <div class="mini-item-meta">
                                            Màu: <?php echo htmlspecialchars($item['color']); ?><br>
                                            SL: <?php echo (int)$item['quantity']; ?>
                                        </div>
                                    </div>
                                    <div class="mini-item-price"><?php echo number_format((float)$item['price'], 0, ',', '.'); ?>₫</div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($mini_cart_items)): ?>
                        <div class="mini-cart-footer">
                            <div class="mini-total">
                                <span>Tổng cộng:</span>
                                <span style="color:#d70018"><?php echo number_format((float)$mini_cart_total, 0, ',', '.'); ?>₫</span>
                            </div>
                            <a href="/DA-cuoiky/cart/cart.php" class="btn-view-cart">XEM GIỎ HÀNG & THANH TOÁN</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ACCOUNT -->
            <div class="account-wrap">
                <?php if ($fullName !== ''): ?>
                    <button type="button" class="account-btn" id="accountBtn">
                        <i class="fa-regular fa-user"></i>
                        <span><?=$fullName ?></span>
                    </button>
                    <div class="account-menu" id="accountMenu">
                        <a href="/DA-cuoiky/index.php?mod=logout">Đăng xuất</a>
                    </div>
                <?php else: ?>
                    <a href="/DA-cuoiky/index.php?mod=login" class="account-btn" style="text-decoration:none;">
                        <i class="fa-regular fa-user"></i>
                       <span>Đăng nhập</span>
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <nav class="header-nav">
        <div class="nav-container">
            <ul class="nav-menu">
                <li class="nav-item"><a href="/DA-cuoiky/index.php?mod=categories&type=iphone">iPhone</a></li>
                <li class="nav-item"><a href="/DA-cuoiky/index.php?mod=categories&type=macbook">Mac</a></li>
                <li class="nav-item">iPad</li>
                <li class="nav-item">Watch</li>
                <li class="nav-item">Âm thanh</li>
                <li class="nav-item">Phụ kiện</li>
                <li class="nav-item">Dịch vụ</li>
            </ul>
        </div>
    </nav>
</header>

<?php include('./mod.php'); ?>

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

<script>
  // MINI CART toggle
  const cartBtn = document.getElementById('cartBtn');
  const miniCart = document.getElementById('miniCart');

  if (cartBtn && miniCart) {
    cartBtn.addEventListener('click', function(e){
      e.stopPropagation();
      miniCart.classList.toggle('show');
    });

    document.addEventListener('click', function(e){
      if (!e.target.closest('.header-cart-wrapper')) {
        miniCart.classList.remove('show');
      }
    });
  }

  // ACCOUNT dropdown
  const accBtn = document.getElementById('accountBtn');
  const accMenu = document.getElementById('accountMenu');

  if (accBtn && accMenu) {
    accBtn.addEventListener('click', function(e){
      e.stopPropagation();
      accMenu.style.display = (accMenu.style.display === 'block') ? 'none' : 'block';
    });

    document.addEventListener('click', function(){
      accMenu.style.display = 'none';
    });
  }
</script>

</body>
</html>
<?php ob_end_flush(); ?>

