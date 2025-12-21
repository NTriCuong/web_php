<?php
session_start();

/**
 * ====== PREFILL TỪ SESSION (khi user đã login) ======
 * Support nhiều key khác nhau để bạn khỏi phải sửa login ngay lập tức.
 */
$sess = [
  'user_id'         => (int)($_SESSION['user_id'] ?? 0),

  'full_name'       => (string)($_SESSION['full_name'] ?? ($_SESSION['user_full_name'] ?? '')),
  'phone'           => (string)($_SESSION['phone'] ?? ($_SESSION['user_phone'] ?? '')),

  'province'        => (string)($_SESSION['province'] ?? ($_SESSION['user_province'] ?? '')),
  'district'        => (string)($_SESSION['district'] ?? ($_SESSION['user_district'] ?? '')),
  'address_detail'  => (string)($_SESSION['address_detail'] ?? ($_SESSION['user_address_detail'] ?? '')),

  // nếu bạn chỉ lưu address gộp 1 chuỗi
  'address'         => (string)($_SESSION['address'] ?? ($_SESSION['user_address'] ?? '')),
];

// Nếu không có address_detail nhưng có address gộp, dùng address gộp làm address_detail để hiển thị
if ($sess['address_detail'] === '' && $sess['address'] !== '') {
  $sess['address_detail'] = $sess['address'];
}

/**
 * ====== XỬ LÝ POST: ĐẶT HÀNG (INSERT DB) ======
 * - Guest: tự tạo user guest
 * - Tạo orders + order_items + payments (COD pending)
 * - Trừ kho variant
 */
$orderSuccess = false;
$orderError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    try {
        if (!isset($_POST['agree'])) {
            throw new Exception("Vui lòng đồng ý điều khoản trước khi đặt hàng!");
        }

        // dữ liệu khách hàng (ưu tiên POST, trống thì fallback SESSION)
        $fullName = trim($_POST['full_name'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $province = trim($_POST['province'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $addr     = trim($_POST['address_detail'] ?? '');
        $note     = trim($_POST['note'] ?? ''); // ✅ CSDL bạn KHÔNG có cột note => chỉ giữ để hiển thị, không insert DB

        if ($fullName === '') $fullName = trim($sess['full_name']);
        if ($phone    === '') $phone    = trim($sess['phone']);
        if ($province === '') $province = trim($sess['province']);
        if ($district === '') $district = trim($sess['district']);
        if ($addr     === '') $addr     = trim($sess['address_detail']);

        if ($fullName === '' || $phone === '' || $addr === '') {
            throw new Exception("Vui lòng nhập Họ tên, SĐT và Địa chỉ.");
        }

        // dữ liệu sản phẩm
        $productId = (int)($_POST['product_id'] ?? 0);
        $variantId = (int)($_POST['variant_id'] ?? 0);
        $qty       = (int)($_POST['quantity'] ?? 1);
        if ($qty <= 0) $qty = 1;

        if ($productId <= 0 || $variantId <= 0) {
            throw new Exception("Thiếu thông tin sản phẩm (product/variant).");
        }

        // lấy giá thật từ DB (chống sửa POST)
        $sqlV = "SELECT id, product_id, price, stock
                 FROM product_variants
                 WHERE id = :vid AND active = 1";
        $stV = $conn->prepare($sqlV);
        $stV->execute([':vid' => $variantId]);
        $vrow = $stV->fetch(PDO::FETCH_ASSOC);

        if (!$vrow) throw new Exception("Variant không tồn tại hoặc đã ngừng bán.");
        if ((int)$vrow['product_id'] !== $productId) throw new Exception("Variant không thuộc sản phẩm.");
        if ((int)$vrow['stock'] < $qty) throw new Exception("Số lượng vượt quá tồn kho.");

        $unitPrice = (float)$vrow['price'];
        $total     = $unitPrice * $qty;

        // user_id: login hoặc guest
        $userId = $sess['user_id'] > 0 ? $sess['user_id'] : 0;

        // địa chỉ gộp (CSDL users chỉ có 1 cột address)
        $shippingAddress = trim(($province ? $province . ' ' : '') . ($district ? $district . ' ' : '') . $addr);

        $conn->beginTransaction();

        // tạo user guest nếu chưa login (user_name UNIQUE + NOT NULL)
        if ($userId <= 0) {
            $guestUsername = 'guest_' . bin2hex(random_bytes(6)); // unique

            $sqlU = "INSERT INTO users (role, user_name, hash_pass, full_name, phone, address)
                     VALUES ('guest', :user_name, '', :full_name, :phone, :address)";
            $stU = $conn->prepare($sqlU);
            $stU->execute([
                ':user_name'  => $guestUsername,
                ':full_name'  => $fullName,
                ':phone'      => $phone,
                ':address'    => $shippingAddress
            ]);
            $userId = (int)$conn->lastInsertId();
        } else {
            // ✅ User đã login: cập nhật lại users theo info mới (đúng schema users)
            $sqlUp = "UPDATE users
                      SET full_name = :full_name, phone = :phone, address = :address
                      WHERE id = :id";
            $stUp = $conn->prepare($sqlUp);
            $stUp->execute([
                ':full_name' => $fullName,
                ':phone'     => $phone,
                ':address'   => $shippingAddress,
                ':id'        => $userId
            ]);
        }

        /**
         * ✅ Insert orders (đúng schema: KHÔNG có shipping_* và note)
         */
        $sqlO = "INSERT INTO orders (cart_id, user_id, total_amount, quality)
                 VALUES (NULL, :user_id, :total_amount, :quality)";
        $stO = $conn->prepare($sqlO);
        $stO->execute([
            ':user_id'      => $userId,
            ':total_amount' => $total,
            ':quality'      => $qty
        ]);
        $orderId = (int)$conn->lastInsertId();

        /**
         * ✅ Insert order_items (đúng bảng + đúng cột)
         */
        $lineTotal = $unitPrice * $qty;
        $sqlI = "INSERT INTO order_items (order_id, product_id, product_variant_id, quality, unit_price, line_total)
                 VALUES (:order_id, :product_id, :variant_id, :quality, :unit_price, :line_total)";
        $stI = $conn->prepare($sqlI);
        $stI->execute([
            ':order_id'   => $orderId,
            ':product_id' => $productId,
            ':variant_id' => $variantId,
            ':quality'    => $qty,
            ':unit_price' => $unitPrice,
            ':line_total' => $lineTotal
        ]);

        /**
         * ✅ Insert payments (đúng schema payments)
         */
        $sqlP = "INSERT INTO payments (order_id, method, status, total)
                 VALUES (:order_id, 'cod', 'pending', :total)";
        $stP = $conn->prepare($sqlP);
        $stP->execute([
            ':order_id' => $orderId,
            ':total'    => $total
        ]);

        // trừ kho
        $sqlS = "UPDATE product_variants SET stock = stock - :qty WHERE id = :vid";
        $stS = $conn->prepare($sqlS);
        $stS->execute([':qty' => $qty, ':vid' => $variantId]);

        // ✅ Đồng bộ session
        $_SESSION['full_name'] = $fullName;
        $_SESSION['phone'] = $phone;
        $_SESSION['province'] = $province;
        $_SESSION['district'] = $district;
        $_SESSION['address_detail'] = $addr;
        $_SESSION['address'] = $shippingAddress;

        $conn->commit();
        $orderSuccess = true;

    } catch (Exception $e) {
        if ($conn && $conn->inTransaction()) $conn->rollBack();
        $orderError = $e->getMessage();
    }
}

/**
 * ====== PHẦN BẠN ĐANG CÓ: LOAD SẢN PHẨM / VARIANT ĐỂ HIỂN THỊ ======
 */
$idProduct = (int)($_GET['id'] ?? 0);
$variant   = isset($_GET['variant']) ? (int)$_GET['variant'] : 0;
$dataDisplay = null;
$type = $_GET['type'] ?? '';

if ($variant > 0 && $idProduct > 0) {

    if (isset($dataProduct[$type])) {
        foreach ($dataProduct[$type] as $value) {
            if ((int)$value['id'] === $idProduct) {
                $dataDetail = $value;
                break;
            }
        }
    }

    $sql = "SELECT id, storage_gb, color, price, stock, image_url
            FROM product_variants
            WHERE product_id = :id AND active = 1
            ORDER BY storage_gb ASC, color ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $idProduct, PDO::PARAM_INT);
    $stmt->execute();
    $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($variants as $value) {
        if ((int)$value['id'] === $variant) {
            $dataDisplay = $value;
            break;
        }
    }

    if ($dataDisplay) {
        foreach ($dataProduct as $group) {
            foreach ($group as $val) {
                if ((int)$val['id'] === $idProduct) {
                    $dataDisplay['name'] = $val['name'];
                    break 2;
                }
            }
        }
    }
}

if (!$dataDisplay) {
    $dataDisplay = [
        'name' => 'Sản phẩm',
        'storage_gb' => '',
        'color' => '',
        'price' => 0,
        'image_url' => null
    ];
}

$img = $dataDisplay['image_url'];
if (empty($img)) {
    $img = "https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300&q=80";
}

$fmtVnd = function($n){
    return number_format((float)$n, 0, ',', '.') . 'đ';
};

/**
 * ✅ GIỮ DỮ LIỆU TRÊN FORM:
 * - Nếu vừa POST bị lỗi: giữ POST
 * - Nếu GET bình thường: dùng SESSION
 */
$pref_fullname = htmlspecialchars($_POST['full_name'] ?? $sess['full_name'], ENT_QUOTES);
$pref_phone    = htmlspecialchars($_POST['phone'] ?? $sess['phone'], ENT_QUOTES);
$pref_province = $_POST['province'] ?? $sess['province'];
$pref_district = $_POST['district'] ?? $sess['district'];
$pref_addr     = htmlspecialchars($_POST['address_detail'] ?? $sess['address_detail'], ENT_QUOTES);
$pref_note     = htmlspecialchars($_POST['note'] ?? '', ENT_QUOTES);
?>

<div style="width:600px; margin:0 auto; background-color: #F5F5F7;">
  <div class="wrap">

    <div class="topbar">
      <a class="back" href="/DA-cuoiky/index.php?mod=home">
        <span style="font-size:22px">‹</span>
        <span>Về trang chủ ShopDunk</span>
      </a>
      <div class="spacer"></div>
    </div>

    <div class="card">
      <!-- CART -->
      <div class="section" id="cartSection">

        <div class="cart-item">
          <div class="thumb">
            <img src="<?= htmlspecialchars($img) ?>" alt="">
          </div>

          <div class="item-mid">
            <p class="name">
              <?= htmlspecialchars($dataDisplay['name']) ?>
              <?= !empty($dataDisplay['storage_gb']) ? htmlspecialchars($dataDisplay['storage_gb']) . 'GB' : '' ?>
            </p>

            <div class="meta">
              Màu sắc:
              <span class="color-text"><?= htmlspecialchars($dataDisplay['color'] ?: '---') ?></span>
            </div>

            <div class="swatch" style="background:#000" data-name="Đen"></div>
          </div>

          <div class="item-right">
            <div class="price" style="font-size:16px">
              <?= $fmtVnd($dataDisplay['price']) ?>
            </div>
            <div class="qty">
              <input name="quantity_show" value="1" readonly />
            </div>
          </div>
        </div>

      </div><!-- end cart section -->

      <form id="orderForm" method="POST">
        <input type="hidden" name="place_order" value="1">
        <input type="hidden" name="product_id" value="<?= (int)$idProduct ?>">
        <input type="hidden" name="variant_id" value="<?= (int)$variant ?>">
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
              <input class="input" name="full_name" value="<?= $pref_fullname ?>" />
            </div>
            <div class="field">
              <label>Số điện thoại</label>
              <input class="input" name="phone" value="<?= $pref_phone ?>" />
            </div>
          </div>
        </div>

        <!-- SHIPPING (chỉ dùng để gộp thành users.address, không insert orders) -->
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
                <option <?= ($pref_province === 'Hà Nội') ? 'selected' : '' ?>>Hà Nội</option>
                <option <?= ($pref_province === 'TP. Hồ Chí Minh') ? 'selected' : '' ?>>TP. Hồ Chí Minh</option>
                <option <?= ($pref_province === 'Đà Nẵng') ? 'selected' : '' ?>>Đà Nẵng</option>
              </select>

              <select name="district">
                <option value="">Chọn quận, huyện</option>
                <option <?= ($pref_district === 'Quận 1') ? 'selected' : '' ?>>Quận 1</option>
                <option <?= ($pref_district === 'Quận 7') ? 'selected' : '' ?>>Quận 7</option>
                <option <?= ($pref_district === 'Hải Châu') ? 'selected' : '' ?>>Hải Châu</option>
              </select>

              <input class="input full" name="address_detail" value="<?= $pref_addr ?>" placeholder="Địa chỉ cụ thể" />
            </div>
          </div>

          <div class="note">
            <input class="input" name="note" value="<?= $pref_note ?>" placeholder="Nhập ghi chú (nếu có)" />
          </div>
        </div>

        <!-- TOTAL -->
        <div class="section">
          <div class="total-row">
            <div>Tổng tiền:</div>
            <div class="total" style="font-size:18px">
              <?= $fmtVnd($dataDisplay['price']) ?>
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

          <div class="hint">Bạn có thể lựa chọn các hình thức thanh toán ở bước sau</div>
        </div>
      </form>

    </div>
  </div>
</div>

<script>
  const agree = document.getElementById('agree');
  const btn = document.getElementById('btnCheckout');

  function sync(){
    btn.disabled = !agree.checked;
  }
  agree.addEventListener('change', sync);
  sync();
</script>

<?php if (!empty($orderError)): ?>
  <script>alert("<?= htmlspecialchars($orderError, ENT_QUOTES) ?>");</script>
<?php endif; ?>

<?php if ($orderSuccess): ?>
  <script>
    alert("Đặt hàng thành công!");
    window.location.href = "/DA-cuoiky/index.php?mod=home";
  </script>
<?php endif; ?>
