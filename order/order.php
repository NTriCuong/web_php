<?php
    require_once __DIR__ . '/../config/config.php';// import luôn đúng dù có ở đâu
    $userinfo = $_SESSION['user'] ?? null;
    $idproduct = $_GET['id'] ?? '';
    $idvariant = $_GET['variant'] ?? '';
    $type = $_GET['type'] ?? '';
    $listProductDisplay = [];
    if($idproduct != '' || $idvariant != ''){ // khách hàng bấm mua ngay trực tiếp trong sản phẩm
      // không qua giỏ hàng
      foreach ($dataProduct[$type] as $value) { // lấy sản phâm
        if((int)$value['variant_id'] === (int)$idvariant){
          $listProductDisplay[] = $value;
          break;
        }
      }
    }else{ // khách hàng mua từ giỏ hàng
        $cartId = $_GET['cart-id'] ?? '';
        // 1) Lấy cart (DB)
        $stmtCart = $conn->prepare("
          SELECT id, user_id, status, total_amount, created_at
          FROM carts
          WHERE id = ?
          LIMIT 1
        ");
        $stmtCart->execute([$cartId]);
        $cart = $stmtCart->fetch(PDO::FETCH_ASSOC);

        $sqlItems = "
            SELECT
              ci.id AS cart_item_id,
              ci.cart_id,
              ci.product_id,
              ci.product_variant_id AS variant_id,
              ci.quality AS quality,
              ci.unit_price,
              (ci.unit_price * ci.quality) AS line_total,

              p.name,
              p.type_id,

              pv.color,
              pv.color_hex,
              pv.storage_gb,
              pv.image_url
            FROM cart_items ci
            JOIN products p ON p.id = ci.product_id
            JOIN product_variants pv ON pv.id = ci.product_variant_id
            WHERE ci.cart_id = ?
            ORDER BY ci.id DESC
          ";
          $stmtItems = $conn->prepare($sqlItems);
          $stmtItems->execute([$cartId]);
          $listProductDisplay = $stmtItems->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }
  // xử lý tiến hành đặt hàng 
 
  $cartTotal = 0;
foreach ($listProductDisplay as $it) {
  $price = (float)($it['unit_price'] ?? $it['price'] ?? 0);
  $qty   = (int)($it['quality'] ?? 1);
  $cartTotal += $price * $qty;
}
  
?>
<div style="width:600px; margin:0 auto; background-color:#F5F5F7;">
  <div class="wrap">

    <div class="topbar">
      <a class="back" href="/DA-cuoiky/index.php?mod=home">
        <span style="font-size:22px">‹</span>
        <span>Về trang chủ ShopDunk</span>
      </a>
      <div class="spacer"></div>
    </div>

    <div class="card">
      <?php 
        foreach($listProductDisplay as $value){
      ?>
      <!-- CART -->
      <div class="section" id="cartSection">
        <div class="cart-item">
          <div class="thumb">
            <img
              src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300&q=80"
              alt="iPhone"
            />
          </div>

          <div class="item-mid">
            <p class="name">
              <?=$value["name"]?>
            </p>

            <div class="meta">
              Màu sắc:
              <span class="color-text" style=""><?=$value['color']?></span>
            </div>

            <div class="swatch" style="background:<?=$value['color_hex']?>" data-name="Đen"></div>
          </div>

          <div class="item-right">
            <div class="price" style="font-size:16px">
              <?= number_format((float)($value['line_total']??$value['price'] ), 0, ',', '.') ?>đ
            </div>
            <div class="qty">
              <input name="quantity_show"
                value="<?= $value['quality']??1?>"
                readonly />

            </div>
          </div>
        </div>
      </div>
      <!-- end cart section -->
          <?php }  ?>

      <!-- FORM (HTML THUẦN - KHÔNG SUBMIT DB) -->
      <form id="orderForm" method="POST">
        <!-- Bạn giữ hidden nếu muốn test submit form -->
         <input type="hidden" name="cart_id" value="<?= (int)($_GET['cart-id'] ?? 0) ?>">
        <input type="hidden" name="place_order" value="1">
        <input type="hidden" name="product_id" value="1">
        <input type="hidden" name="variant_id" value="101">
        <input type="hidden" name="quantity" value="1">

        <!-- CUSTOMER -->
        <div class="section">
          <div class="h2" style="font-size:18px">Thông tin khách hàng</div>

          <div class="radio-row">
            <label class="radio"><input type="radio" name="salute" checked> Anh</label>
            <label class="radio"><input type="radio" name="salute"> Chị</label>
          </div>

          <div class="grid-2">
            <div class="field">
              <label>Họ và Tên</label>
              <input class="input" name="full_name" value="<?=$userinfo['full_name']??''?>" />
            </div>

            <div class="field">
              <label>Số điện thoại</label>
              <input class="input" name="phone" value="<?=$userinfo['phone']??''?>" />
            </div>
          </div>
        </div>

        <!-- SHIPPING -->
        <div class="section">
          <div class="h2" style="font-size:18px">Hình thức nhận hàng</div>

          <div class="radio-row">
            <label class="radio"><input type="radio" name="ship" checked> Giao tận nơi</label>
            <label class="radio"><input type="radio" name="ship"> Nhận tại cửa hàng</label>
          </div>

          <div class="ship-box">
            <div class="ship-grid">
              <!-- <select name="province">
                <option value="">Chọn tỉnh, thành phố</option>
                <option selected>TP. Hồ Chí Minh</option>
                <option>Hà Nội</option>
                <option>Đà Nẵng</option>
              </select>

              <select name="district">
                <option value="">Chọn quận, huyện</option>
                <option selected>Quận 7</option>
                <option>Quận 1</option>
                <option>Hải Châu</option>
              </select> -->

              <input class="input full" name="address_detail" value="<?=$userinfo['address']??''?>" placeholder="Địa chỉ cụ thể" />
            </div>
          </div>

          <div class="note">
            <input class="input" name="note" value="" placeholder="Nhập ghi chú (nếu có)" />
          </div>
        </div>

        <!-- TOTAL -->
        <div class="section">
          <div class="total-row">
            <div>Tổng tiền:</div>
            <div class="total" style="font-size:18px">
              <?= number_format((float)($cartTotal ?? 0), 0, ',', '.') ?>đ
            </div>
          </div>

          <div class="check-row" style="margin-top:10px;">
            <input type="checkbox" checked id="agree" name="agree" />
            <div style="font-size:13px">
              Tôi đã đọc và đồng ý với
              <a href="#">điều khoản và điều kiện</a>
              của website
            </div>
          </div>

          <button class="btn" id="btnCheckout" style="font-size:15px" type="submit" name="submit_order">
            Tiến hành đặt hàng
          </button>

          <div class="hint">
            Bạn có thể lựa chọn các hình thức thanh toán ở bước sau
          </div>
        </div>
      </form>

    </div>
  </div>
</div>

<?php
if (isset($_POST['submit_order'])) {
    $userId = (int)($_SESSION['user']['id'] ?? -1);
    var_dump($userId);
    if ($userId == -1) { // chưa đăng nhập
      header('Location: /DA-cuoiky/index.php?mod=login');
      exit;
    }

    // // LẤY cart_id TỪ POST
    // $cartId = (int)($_POST['cart_id'] ?? 0);
    // if ($cartId <= 0) { // a
    //    header('Location: /DA-cuoiky/index.php?mod=login');
    // }

    // Lấy items trong cart
    $stmt = $conn->prepare("
        SELECT product_id, product_variant_id, quality, unit_price
        FROM cart_items
        WHERE cart_id = ?
        ORDER BY id ASC
    ");
    $stmt->execute([$cartId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    if (empty($cartItems)) {
        $_SESSION['alert_error'] = "Giỏ hàng trống!";
        // header("Location: /DA-cuoiky/index.php?mod=home");
        exit;
    }

    // Tính tổng số lượng + tổng tiền (để bạn dùng sau)
    $totalQty = 0;
    $totalAmount = 0;
    foreach ($cartItems as $it) {
        $qty = (int)$it['quality'];
        $price = (float)$it['unit_price'];
        $totalQty += $qty;
        $totalAmount += $price * $qty;
    }

    // Insert order (tối giản đúng theo schema của bạn có total_amount)
    $insertOrderSql = "
        INSERT INTO orders (user_id, cart_id, total_amount, quality, status, created_at)
        VALUES (?, ?, ?, ?, 'pending', NOW())
    ";
    $stmtInsertOrder = $conn->prepare($insertOrderSql);
    $stmtInsertOrder->execute([$userId, $cartId, $totalAmount, $totalQty]);

    $orderId = (int)$conn->lastInsertId();

    // Insert order items
    $insertItemSql = "
        INSERT INTO order_items (order_id, product_id, product_variant_id, quality, unit_price, line_total)
        VALUES (?, ?, ?, ?, ?, ?)
    ";
    $stmtInsertItem = $conn->prepare($insertItemSql);

    foreach ($cartItems as $it) {
        $qty = (int)$it['quality'];
        $price = (float)$it['unit_price'];
        $lineTotal = $price * $qty;

        $stmtInsertItem->execute([
            $orderId,
            (int)$it['product_id'],
            (int)$it['product_variant_id'],
            $qty,
            $price,
            $lineTotal
        ]);
    }

    // (tuỳ chọn) đổi trạng thái cart
    $conn->prepare("UPDATE carts SET status='converted', total_amount=? WHERE id=?")
         ->execute([$totalAmount, $cartId]);

    $_SESSION['alert_success'] = "Đặt hàng thành công! Mã đơn #".$orderId;
    header('Location: /DA-cuoiky/index.php?mod=home');
    exit;
}
?>
