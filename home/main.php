<?php
session_start();

// ====== 0) helper ======
function post($k, $def='') { return isset($_POST[$k]) ? trim((string)$_POST[$k]) : $def; }

// ====== 1) lấy GET như bạn đang làm ======
$idProduct = (int)($_GET['id'] ?? 0);
$variant   = isset($_GET['variant']) ? (int)$_GET['variant'] : 0;
$type      = $_GET['type'] ?? '';

$dataDisplay = null;

// ====== 2) xử lý POST: bấm "Tiến hành đặt hàng" ======
$orderSuccess = false;
$orderError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    try {
        // --- validate ---
        $agree = isset($_POST['agree']) ? 1 : 0;
        if (!$agree) throw new Exception("Vui lòng đồng ý điều khoản trước khi đặt hàng!");

        $fullName = post('full_name');
        $phone    = post('phone');
        $province = post('province');
        $district = post('district');
        $addr     = post('address_detail');
        $note     = post('note');

        if ($fullName === '' || $phone === '' || $addr === '') {
            throw new Exception("Vui lòng nhập Họ tên, SĐT và Địa chỉ.");
        }

        $variantId = (int)($_POST['variant_id'] ?? 0);
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty       = (int)($_POST['quantity'] ?? 1);
        if ($variantId <= 0 || $productId <= 0) throw new Exception("Thiếu thông tin sản phẩm.");
        if ($qty <= 0) $qty = 1;

        // --- lấy giá thật từ DB để tránh sửa POST ---
        $sqlV = "SELECT id, product_id, price, stock FROM product_variants WHERE id = :vid AND active = 1";
        $stV = $conn->prepare($sqlV);
        $stV->execute([':vid' => $variantId]);
        $vrow = $stV->fetch(PDO::FETCH_ASSOC);
        if (!$vrow) throw new Exception("Variant không tồn tại hoặc đã ngừng bán.");
        if ((int)$vrow['product_id'] !== $productId) throw new Exception("Variant không thuộc sản phẩm.");
        if ((int)$vrow['stock'] < $qty) throw new Exception("Số lượng vượt quá tồn kho.");

        $unitPrice = (float)$vrow['price'];
        $total = $unitPrice * $qty;

        // --- xác định user (login / guest) ---
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

        $conn->beginTransaction();

        if ($userId <= 0) {
            // ✅ tạo user guest (username/pass bỏ trống)
            $sqlU = "INSERT INTO users (role, user_name, hash_pass, full_name, phone, address)
                     VALUES ('guest', '', '', :full_name, :phone, :address)";
            $stU = $conn->prepare($sqlU);
            $stU->execute([
                ':full_name' => $fullName,
                ':phone'     => $phone,
                ':address'   => trim($province . ' ' . $district . ' ' . $addr),
            ]);
            $userId = (int)$conn->lastInsertId();
            // nếu bạn muốn lưu session để các bước sau coi như đăng nhập:
            // $_SESSION['user_id'] = $userId;
        }

        // --- insert orders ---
        $sqlO = "INSERT INTO orders (cart_id, user_id, total_amount, quality, shipping_name, shipping_phone, shipping_address, note)
                 VALUES (NULL, :user_id, :total_amount, :quality, :ship_name, :ship_phone, :ship_addr, :note)";
        $stO = $conn->prepare($sqlO);
        $stO->execute([
            ':user_id'      => $userId,
            ':total_amount' => $total,
            ':quality'      => $qty,
            ':ship_name'    => $fullName,
            ':ship_phone'   => $phone,
            ':ship_addr'    => trim($province . ' ' . $district . ' ' . $addr),
            ':note'         => $note,
        ]);
        $orderId = (int)$conn->lastInsertId();

        // --- insert item_order ---
        $sqlI = "INSERT INTO item_order (order_id, product_id, product_varian_id, quality)
                 VALUES (:order_id, :product_id, :variant_id, :quality)";
        $stI = $conn->prepare($sqlI);
        $stI->execute([
            ':order_id'   => $orderId,
            ':product_id' => $productId,
            ':variant_id' => $variantId,
            ':quality'    => $qty,
        ]);

        // --- insert payment (mặc định COD/pending) ---
        $sqlP = "INSERT INTO payments (order_id, method, status, total)
                 VALUES (:order_id, 'cod', 'pending', :total)";
        $stP = $conn->prepare($sqlP);
        $stP->execute([
            ':order_id' => $orderId,
            ':total'    => $total,
        ]);

        // --- trừ kho (tuỳ bạn có muốn) ---
        $sqlS = "UPDATE product_variants SET stock = stock - :qty WHERE id = :vid";
        $stS = $conn->prepare($sqlS);
        $stS->execute([':qty' => $qty, ':vid' => $variantId]);

        $conn->commit();
        $orderSuccess = true;

    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        $orderError = $e->getMessage();
    }
}
?>



<main>
  <section class="hero-banner">
    <button class="slider-btn btn-prev" type="button"></button>

    <div class="hero-img" style="background: linear-gradient(to right, #000, #333);">
      BANNER APPLE WATCH ULTRA 3
    </div>

    <button class="slider-btn btn-next" type="button"></button>
  </section>

  <section class="promo-section">
    <div class="promo-img-placeholder">
      BANNER GIẢI PHÁP DOANH NGHIỆP (Upload ảnh image_a3bdcf.jpg tại đây)
    </div>
  </section>

  <section class="product-section">
    <?php
      // map tên hiển thị
      $typeLabel = [
        'iphone' => 'iPhone',
        'macbook' => 'MacBook'
      ];
    ?>

    <?php foreach ($productType as $type): ?>
      <?php
        $title = $typeLabel[$type] ?? ucfirst($type);
        $list = $dataProduct[$type] ?? [];
        $first4 = array_slice($list, 0, 4); 
        if (empty($list)) continue;
      ?>

      <h2 class="section-title"><?= htmlspecialchars($title) ?></h2>

      <div class="product-grid">
        <?php foreach ($first4 as $item): ?>
          <?php
            $id = (int)$item['id'];
            $name = $item['name'] ?? '';
            $gia = (float)($item['price'] ?? 0);

            $img = $item['image_url'] ?? null;
            $color = $item['color'] ?? '';
          ?>
          <a href="/DA-cuoiky/index.php?mod=detail&type=<?= urlencode($type) ?>&id=<?= $id ?>">
            <article class="product-card">
              <div class="prod-img-placeholder">
                <?php if (!empty($img)): ?>
                  <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>" />
                <?php else: ?>
                  Ảnh sản phẩm
                <?php endif; ?>
              </div>

              <h3 class="prod-name"><?= htmlspecialchars($name) ?></h3>

              <div class="prod-price">
                <?= number_format($gia, 0, ',', '.') ?>₫
              </div>

              <!-- Nếu chưa có old-price thật thì ẩn, đừng hard-code -->
              <span class="old-price">37.999.000₫</span>
            </article>
          </a>
        <?php endforeach; ?>
      </div>

      <a href="/DA-cuoiky/index.php?mod=categories&type=<?= urlencode($type) ?>" class="view-all-btn">
        Xem tất cả <?= htmlspecialchars($title) ?> >
      </a>
    <?php endforeach; ?>
  </section>
</main>

<div class="floating-group">
  <div class="float-btn btn-hotline">
    <i class="fa-solid fa-phone"></i>
  </div>
  <div class="float-btn btn-chat">
    <i class="fa-solid fa-comment-dots"></i>
  </div>
</div>
