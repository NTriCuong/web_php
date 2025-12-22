<?php
    require_once __DIR__ . '/../config/config.php';// import luôn đúng dù có ở đâu
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
        // 1) Lấy cart từ session (hỗ trợ nhiều key)
    $cart = $_SESSION['cart'] ?? [];
    if (!is_array($cart) || empty($cart)) {
        $cart = [];
    }
    
     // 2) Chỉ lấy item đúng type hiện tại (iphone/macbook...)
    $cartOfType = [];
    foreach ($cart as $item) {
        $itemType = (string)($item['type'] ?? '');
        if ($itemType === (string)$type) {
            $cartOfType[] = $item;
        }
    }

    // 3) Tạo map variant_id => quantity (gộp nếu trùng)
    $qtyMap = []; // [variant_id => qty]
    foreach ($cartOfType as $item) {
        $vid = (int)($item['variant_id'] ?? 0);
        $qty = (int)($item['quality'] ?? 0); // bạn đang dùng "quality" (số lượng)
        if ($vid > 0 && $qty > 0) {
            $qtyMap[$vid] = ($qtyMap[$vid] ?? 0) + $qty;
        }
    }

    // 4) Dò sản phẩm trong dataProduct theo variant_id và đổ vào listProductDisplay
    //    (mỗi item thêm field 'quality' để hiển thị số lượng)
    foreach (($dataProduct[$type] ?? []) as $value) {
        $vid = (int)($value['variant_id'] ?? 0);
        if ($vid > 0 && isset($qtyMap[$vid])) {
            $value['quality'] = $qtyMap[$vid]; // gắn số lượng từ session vào
            $listProductDisplay[] = $value;
        }
    }
    // Ví dụ cart session lưu:
    // $_SESSION['cart'] = [
    //   ['variant_id' => 101, 'id' => 2, type=>'iphone', quality=>1],
    //   ['variant_id' => 205, 'id' => 1, type=>'iphone', quality=>1],
    // ];
  }
  // xử lý thông tin người mua
  
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
              <?= number_format((float)($value['price'] ?? 0), 0, ',', '.') ?>đ
            </div>
            <div class="qty">
              <input name="quantity_show"
                value="<?= (int)($value['quality'] ?? 1) ?: 1 ?>"
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
              <input class="input" name="full_name" value="Nguyễn Văn A" />
            </div>

            <div class="field">
              <label>Số điện thoại</label>
              <input class="input" name="phone" value="0901234567" />
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
              <select name="province">
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
              </select>

              <input class="input full" name="address_detail" value="123 Đường ABC, Phường XYZ" placeholder="Địa chỉ cụ thể" />
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
              29.990.000đ
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

          <button class="btn" id="btnCheckout" style="font-size:15px" type="submit">
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
