<?php
$idProduct = $_GET['id'] ?? '';
$type      = $_GET['type'] ?? '';
// lấy thông tin sản phẩm
foreach ($dataProduct[$type] as $value) {
    if($value['id'] == $idProduct){
        $dataDetail=$value; 
        break;
    }
}
// truy vấn variant ở csdl
$sql = "SELECT id, storage_gb, color, price, stock, image_url
        FROM product_variants
        WHERE product_id = :id AND is_active = 1
        ORDER BY storage_gb ASC, color ASC";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':id', $idProduct, PDO::PARAM_INT);
$stmt->execute();
$variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$variants) {
    die('Không tìm thấy sản phẩm');
}

/** Parse 'Titanium Black(#333333)' -> ['name'=>'Titanium Black','hex'=>'#333333'] */
function parseColorNameHex($str) {
    $str = trim((string)$str);
    $name = $str;
    $hex  = '#999999';
    if (preg_match('/^(.*)\((#[0-9A-Fa-f]{6})\)\s*$/', $str, $m)) {
        $name = trim($m[1]);
        $hex  = $m[2];
    }
    if ($name === '') $name = 'Màu';
    return ['name' => $name, 'hex' => $hex];
}

// Gom theo dung lượng: [256 => [variant...], 512 => [variant...]]
$byStorage = [];
foreach ($variants as $v) {
    $s = (int)$v['storage_gb'];
    $c = parseColorNameHex($v['color'] ?? '');

    $byStorage[$s][] = [
        'id'         => (int)$v['id'],
        'storage_gb' => $s,
        'color_name' => $c['name'],
        'color_hex'  => $c['hex'],
        'price'      => (float)$v['price'],
        'stock'      => (int)$v['stock'],
        'image_url'  => $v['image_url'],
    ];
}

ksort($byStorage);
$defaultStorage = array_key_first($byStorage);
$defaultVariant = $byStorage[$defaultStorage][0] ?? null;

$fmtVnd = fn($n) => number_format((float)$n, 0, ',', '.') . '₫';
echo $defaultVariant['id'];
?>

<main>
    <div class="breadcrumb">
        <a href='/DA-cuoiky/index.php?mod=home'> Trang chủ</a> <span>&rsaquo;</span>
        <a href="/DA-cuoiky/index.php?mod=categories&type=<?= htmlspecialchars($type) ?>"> <?= htmlspecialchars($type) ?> </a><span>&rsaquo;</span>
        <!-- fix nhỏ: nếu bạn muốn link detail thì phải là &id= -->
        <a href="/DA-cuoiky/index.php?mod=detail&type=<?= urlencode($type) ?>&id=<?= (int)$idProduct ?>"></a>
    </div>

    <div class="detail-container">
        <div class="detail-left">
            <div class="main-image-box">
                <div class="img-placeholder-large">
                    <img src="<?=$defaultVariant['image_url']?>" alt="lỗi ảnh <?=$defaultVariant['image_url']?>">
                </div>
            </div>
        </div>

        <div class="detail-right">
            <div class="product-header">
                <h1><?=$dataDetail['name']?></h1>
            </div>

            <div class="price-box">
                <!-- Giá hiện tại -->
                <span class="current-price" id="currentPrice">
                    <?= $defaultVariant ? $fmtVnd($defaultVariant['price']) : '' ?>
                </span>

                <!-- nếu bạn muốn giữ giá cũ thì để, còn không xoá dòng dưới -->
                <!-- <span class="old-price">37.999.000₫</span> -->

                <div class="vat-note">(Đã bao gồm VAT)</div>
            </div>

            <!-- Dung lượng (render từ DB) -->
            <div class="option-section">
                <label>Dung lượng</label>
                <div class="option-group" id="storageGroup">
                    <?php foreach ($byStorage as $storage => $items): ?>
                        <button
                            type="button"
                            class="option-btn <?= ((int)$storage === (int)$defaultStorage) ? 'active' : '' ?>"
                            data-storage="<?= (int)$storage ?>"
                        >
                            <?= (int)$storage ?>GB
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Màu (render theo dung lượng đang chọn) -->
            <div class="option-section">
                <label>
                    Màu sắc:
                    <span id="colorText"><?= $defaultVariant ? htmlspecialchars($defaultVariant['color_name']) : '' ?></span>
                </label>
                <div class="color-group" id="colorGroup"></div>
            </div>

            <!-- Hidden để bạn dùng add-to-cart -->
            <input type="hidden" id="variantId" name="variant_id" value="<?= $defaultVariant ? (int)$defaultVariant['id'] : '' ?>">

            <div class="promo-box">
                <div class="promo-header">
                    <i class="fa-solid fa-gift"></i> Ưu đãi
                </div>
                <div class="promo-content">
                    <div class="promo-expiry">(Khuyến mãi dự kiến áp dụng đến 23h59 | 31/12/2025)</div>

                    <div class="promo-group-title">I. Ưu đãi thanh toán</div>
                    <ul class="promo-list">
                        <li><i class="fa-solid fa-circle-check"></i> Ưu đãi <b>Mở thẻ tín dụng HSBC</b> hoàn tiền đến 2 triệu</li>
                        <li><i class="fa-solid fa-circle-check"></i> Ưu đãi hoàn <b>500.000đ</b> khi mở thẻ tại ShopDunk</li>
                        <li><i class="fa-solid fa-circle-check"></i> Quét VNPAY-QR giảm đến <b>200.000đ</b></li>
                    </ul>

                    <div class="promo-group-title">II. Ưu đãi mua kèm</div>
                    <ul class="promo-list">
                        <li><i class="fa-solid fa-circle-check"></i> Ốp chính hãng Apple giảm <b>100.000đ</b></li>
                        <li><i class="fa-solid fa-circle-check"></i> Mua combo phụ kiện giảm đến <b>300.000đ</b></li>
                        <li><i class="fa-solid fa-circle-check"></i> Giảm đến <b>40%</b> khi mua gói bảo hành</li>
                    </ul>

                    <div class="promo-group-title">III. Ưu đãi khác</div>
                    <ul class="promo-list">
                        <li><i class="fa-solid fa-circle-check"></i> Thu cũ đổi mới trợ giá lên đến <b>4 triệu</b></li>
                    </ul>
                </div>
            </div>

           <div class="action-buttons">
            <form action="/DA-cuoiky/index.php" method="GET">
                <input type="hidden" name="mod" value="order">
                <input type="hidden" name="id" value="<?=$idProduct?>">
                <input type="hidden" name="variant" id="variantId" value="<?= $defaultVariant['id'] ?>">
                <button class="btn-buy-now" type="submit">MUA NGAY</button>
            </form>
            </div>


        </div>
    </div>
</main>

<div class="floating-group">
    <div class="float-btn btn-hotline"><i class="fa-solid fa-phone"></i></div>
    <div class="float-btn btn-chat"><i class="fa-solid fa-comment-dots"></i></div>
</div>

<script>
const variantsByStorage = <?= json_encode($byStorage, JSON_UNESCAPED_UNICODE); ?>;

const storageGroup = document.getElementById('storageGroup');
const colorGroup   = document.getElementById('colorGroup');
const currentPrice = document.getElementById('currentPrice');
const colorText    = document.getElementById('colorText');
const variantIdInp = document.getElementById('variantId');

function fmtVnd(n){
  return new Intl.NumberFormat('vi-VN').format(n) + '₫';
}

function setActiveStorageBtn(storage){
  storageGroup.querySelectorAll('.option-btn').forEach(b=>{
    b.classList.toggle('active', Number(b.dataset.storage) === Number(storage));
  });
}

function renderColors(storage){
  const list = variantsByStorage[storage] || [];
  colorGroup.innerHTML = '';

  list.forEach((v, idx) => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'color-circle' + (idx === 0 ? ' active' : '');
    btn.title = v.color_name;
    btn.dataset.variantId = v.id;
    btn.dataset.price = v.price;
    btn.dataset.colorName = v.color_name;
    btn.style.backgroundColor = v.color_hex;

    if (v.stock <= 0) {
      btn.disabled = true;
      btn.style.opacity = '0.35';
      btn.style.cursor = 'not-allowed';
      btn.title = v.color_name + ' (Hết hàng)';
    }

    btn.addEventListener('click', () => {
      colorGroup.querySelectorAll('.color-circle').forEach(x => x.classList.remove('active'));
      btn.classList.add('active');

      currentPrice.textContent = fmtVnd(v.price);
      variantIdInp.value = v.id;
      if (colorText) colorText.textContent = v.color_name;

      // Nếu bạn có ảnh theo variant:
      // if (v.image_url) document.querySelector('.main-image-box img').src = v.image_url;
    });

    colorGroup.appendChild(btn);
  });

  // auto chọn màu đầu tiên còn hàng (nếu có)
  const firstEnabled = colorGroup.querySelector('.color-circle:not([disabled])');
  if (firstEnabled) firstEnabled.click();
}

storageGroup.addEventListener('click', (e) => {
  const btn = e.target.closest('.option-btn');
  if (!btn) return;

  const storage = btn.dataset.storage;
  setActiveStorageBtn(storage);
  renderColors(storage);
});

// init
const defaultStorage = storageGroup.querySelector('.option-btn.active')?.dataset.storage
                    || Object.keys(variantsByStorage)[0];
setActiveStorageBtn(defaultStorage);
renderColors(defaultStorage);
</script>
