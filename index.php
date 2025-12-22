<?php
ob_start();
session_start();

// 1) DB CONFIG
include('./config/config.php');

// 2) USER SESSION
$fullName = $_SESSION['user']['full_name'] ?? '';
$userId   = (int)($_SESSION['user']['id'] ?? 0);

// =========================================================================
// 3) CONTROLLER: THÊM VÀO GIỎ HÀNG (cart_add)
//    - Login: lưu DB (carts + cart_items)
//    - Guest: lưu session
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['mod'] ?? '') === 'cart_add') {

    $p_id     = (int)($_POST['id'] ?? 0);
    $v_id     = (int)($_POST['variant'] ?? 0);
    $type     = (string)($_POST['type'] ?? 'iphone');
    $quantity = 1;

    if ($p_id > 0 && $v_id > 0) {

        // ===== A) ĐÃ LOGIN => LƯU DB =====
        if ($userId > 0) {

            // A1) Lấy cart active hoặc tạo mới
            $stmt = $conn->prepare("SELECT id FROM carts WHERE user_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
            $stmt->execute([$userId]);
            $cart = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cart) {
                $stmtNew = $conn->prepare("INSERT INTO carts (user_id, status, created_at) VALUES (?, 'active', NOW())");
                $stmtNew->execute([$userId]);
                $cartId = (int)$conn->lastInsertId();
            } else {
                $cartId = (int)$cart['id'];
            }

            // A2) Lấy giá variant hiện tại để lưu unit_price
            $stmtPrice = $conn->prepare("SELECT price FROM product_variants WHERE id = ? LIMIT 1");
            $stmtPrice->execute([$v_id]);
            $vRow  = $stmtPrice->fetch(PDO::FETCH_ASSOC);
            $price = (float)($vRow['price'] ?? 0);

            // A3) Insert/Update cart_items (schema có UNIQUE(cart_id, product_variant_id))
            $sqlInsert = "INSERT INTO cart_items (cart_id, product_id, product_variant_id, quality, unit_price, created_at)
                          VALUES (:cid, :pid, :vid, :qty, :price, NOW())
                          ON DUPLICATE KEY UPDATE quality = quality + VALUES(quality)";
            $stmtIns = $conn->prepare($sqlInsert);
            $stmtIns->execute([
                ':cid'   => $cartId,
                ':pid'   => $p_id,
                ':vid'   => $v_id,
                ':qty'   => $quantity,
                ':price' => $price
            ]);

        }
        // ===== B) CHƯA LOGIN => LƯU SESSION =====
        else {
            if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            $found = false;
            foreach ($_SESSION['cart'] as &$item) {
                if ((int)($item['variant_id'] ?? 0) === $v_id) {
                    $item['quantity'] = (int)($item['quantity'] ?? 0) + $quantity;
                    $found = true;
                    break;
                }
            }
            unset($item);

            if (!$found) {
                $_SESSION['cart'][] = [
                    'id'         => $p_id,
                    'variant_id' => $v_id,
                    'type'       => $type,
                    'quantity'   => $quantity
                ];
            }
        }

        $_SESSION['alert_success'] = "Đã thêm vào giỏ hàng thành công!";

        // Redirect về trang detail
        $url = "index.php?mod=detail&type=" . urlencode($type) . "&id=" . (int)$p_id;
        header("Location: $url");
        exit;
    } else {
        $_SESSION['alert_error'] = "Thiếu dữ liệu sản phẩm!";
        header("Location: index.php?mod=home");
        exit;
    }
}

// 4) LOAD MINI CART DATA (Hiển thị)
$mini_cart_items = [];
$mini_cart_total = 0;

// -- LOGIC LOAD MINI CART --
if (isset($conn)) {

    // A) Nếu đã login -> lấy từ DB
    if ($userId > 0) {
        try {
            $stmtC = $conn->prepare("SELECT id FROM carts WHERE user_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
            $stmtC->execute([$userId]);
            $cartRow = $stmtC->fetch(PDO::FETCH_ASSOC);

            if ($cartRow) {
                $cartId = (int)$cartRow['id'];

                // Lấy items: ưu tiên unit_price trong cart_items (đúng lúc user add)
                $sqlMini = "
    SELECT
  ci.id,
  ci.cart_id AS cart_id,
  ci.quality AS quantity,
  p.name,
  pv.color,
  pv.image_url,
  ci.unit_price AS price
FROM cart_items ci
JOIN product_variants pv ON ci.product_variant_id = pv.id
JOIN products p ON pv.product_id = p.id
WHERE ci.cart_id = ?
ORDER BY ci.id DESC

";
                $stmtMini = $conn->prepare($sqlMini);
                $stmtMini->execute([$cartId]);
                $mini_cart_items = $stmtMini->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }
        } catch (Throwable $e) {
            // ignore
        }
    }

    // B) Nếu chưa login -> lấy từ SESSION + query DB để lấy thông tin hiển thị
    else {
        if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {

            $ids = [];
            foreach ($_SESSION['cart'] as $s) {
                $vid = (int)($s['variant_id'] ?? 0);
                if ($vid > 0) $ids[] = $vid;
            }
            $ids = array_values(array_unique($ids));

            if (!empty($ids)) {
                $placeholders = implode(',', array_fill(0, count($ids), '?'));

                // lấy info theo variant_id
                $sqlSess = "
                    SELECT
                      pv.id AS variant_id,
                      p.name,
                      pv.color,
                      pv.price AS price,
                      pv.image_url
                    FROM product_variants pv
                    JOIN products p ON pv.product_id = p.id
                    WHERE pv.id IN ($placeholders)
                ";
                try {
                    $stmtSess = $conn->prepare($sqlSess);
                    $stmtSess->execute($ids);
                    $productsInfo = $stmtSess->fetchAll(PDO::FETCH_ASSOC) ?: [];

                    // map quantity từ session
                    foreach ($productsInfo as $prod) {
                        $qty = 0;
                        foreach ($_SESSION['cart'] as $sessItem) {
                            if ((int)($sessItem['variant_id'] ?? 0) === (int)$prod['variant_id']) {
                                $qty = (int)($sessItem['quantity'] ?? 0);
                                break;
                            }
                        }
                        $prod['quantity'] = $qty;
                        $mini_cart_items[] = [
                            'name'     => $prod['name'],
                            'color'    => $prod['color'],
                            'image_url'=> $prod['image_url'],
                            'price'    => $prod['price'],
                            'quantity' => $qty
                        ];
                    }

                } catch (Throwable $e) {
                    // ignore
                }
            }
        }
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
    <!-- <link rel="stylesheet" href="/DA-cuoiky/admin/admin_product.css"> -->


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

            <div class="header-cart-wrapper">
                <div class="cart-btn" id="cartBtn">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span>Giỏ hàng</span>
                    <?php
                        $totalCount = 0;
                        foreach($mini_cart_items as $itm) {
                            $totalCount += (int)($itm['quantity'] ?? 0);
                        }
                    ?>
                    <?php if ($totalCount > 0): ?>
                        <span class="cart-badge"><?php echo $totalCount; ?></span>
                    <?php endif; ?>
                </div>

                <div class="mini-cart-dropdown" id="miniCart">
                    <div class="mini-cart-header">Giỏ hàng của bạn</div>

                    <div class="mini-cart-body">
                        <?php if (empty($mini_cart_items)): ?>
                            <div class="empty-msg">Giỏ hàng đang trống</div>
                        <?php else: ?>
                            <?php foreach ($mini_cart_items as $item):
                                $price = (float)($item['price'] ?? 0);
                                $qty   = (int)($item['quantity'] ?? 0);
                                $sub   = $price * $qty;
                                $mini_cart_total += $sub;

                                $img = !empty($item['image_url'])
                                    ? $item['image_url']
                                    : 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=900&q=80';
                            ?>
                                <div class="mini-cart-item">
                                    <img src="<?php echo htmlspecialchars($img); ?>" alt="Img" class="mini-item-img"
                                         onerror="this.src='img/no-image.jpg'">
                                    <div class="mini-item-info">
                                        <a href="#" class="mini-item-name"><?php echo htmlspecialchars($item['name'] ?? ''); ?></a>
                                        <div class="mini-item-meta">
                                            Màu: <?php echo htmlspecialchars($item['color'] ?? ''); ?><br>
                                            SL: <?php echo $qty; ?>
                                        </div>
                                    </div>
                                    <div class="mini-item-price"><?php echo number_format($price, 0, ',', '.'); ?>₫</div>
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
                            <a href="/DA-cuoiky/index.php?mod=order&cart-id=<?=$mini_cart_items[0]['cart_id']?>" class="btn-view-cart">Thanh Toán</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

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
                <?php
                        $user = $_SESSION['user'] ?? null;
                     if(isset($user) && $user['role'] == 'admin'){
                ?>
                <br>
               <a href="/DA-cuoiky/admin/admin_product.php" class="account-btn" style="text-decoration:none; margin-left:10px;">
                        <i class="fa-solid fa-user-shield"></i>
                        <span>vào trang quản lý</span>
                    </a>
                <?php } ?>
                    
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
