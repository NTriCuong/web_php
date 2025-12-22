<?php
$idProduct = (int)($_GET['id'] ?? 0);
$type      = $_GET['type'] ?? '';

if ($idProduct <= 0 || empty($type) || !isset($dataProduct[$type])) {
    die('Thiếu dữ liệu sản phẩm');
}

// lấy thông tin sản phẩm từ $dataProduct
$dataDetail = null;
foreach ($dataProduct[$type] as $value) {
    if ((int)$value['id'] === $idProduct) {
        $dataDetail = $value;
        break;
    }
}
if (!$dataDetail) {
    die('Không tìm thấy sản phẩm');
}

// truy vấn variant ở csdl (sửa is_active -> active)
$sql = "SELECT id, storage_gb, color, color_hex, price, stock, image_url
        FROM product_variants
        WHERE product_id = :id AND active = 1
        ORDER BY storage_gb ASC, color ASC";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':id', $idProduct, PDO::PARAM_INT);
$stmt->execute();
$variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$variants) {
    die('Không tìm thấy variant');
}



// Gom theo dung lượng: [256 => [variant...], 512 => [variant...]]
$byStorage = [];
foreach ($variants as $v) {
    $s = (int)($v['storage_gb'] ?? 0);
    if ($s <= 0) $s = 0;

       $hex = trim((string)($v['color_hex'] ?? ''));
    if (!preg_match('/^#([0-9a-fA-F]{6}|[0-9a-fA-F]{3})$/', $hex)) {
        $hex = '#000000'; // fallback nếu DB rỗng/sai định dạng
    }

    $byStorage[$s][] = [
        'id'         => (int)$v['id'],
        'storage_gb' => $s,
        'color_name' => (string)($v['color'] ?? ''), // tên màu lấy từ cột color
        'color_hex'  => $hex,                         // hex lấy từ cột color_hex
        'price'      => (float)$v['price'],
        'stock'      => (int)$v['stock'],
        'image_url'  => $v['image_url'],
    ];

}

ksort($byStorage);
$defaultStorage = array_key_first($byStorage);
$defaultVariant = $byStorage[$defaultStorage][0] ?? null;

$fmtVnd = fn($n) => number_format((float)$n, 0, ',', '.') . '₫';

// ảnh fallback nếu NULL
$defaultImg = $defaultVariant['image_url'] ?? null;
if (empty($defaultImg)) {
    $defaultImg = "macbook.png";
}
?>

<main>
    <div class="breadcrumb">
        <a href='/DA-cuoiky/index.php?mod=home'>Trang chủ</a> <span>&rsaquo;</span>
        <a href="/DA-cuoiky/index.php?mod=categories&type=<?= $type ?>">
            <?= $type ?>
        </a>
        <span>&rsaquo;</span>
        <a href="/DA-cuoiky/index.php?mod=detail&type=<?= urlencode($type) ?>&id=<?= (int)$idProduct ?>">
            <?= $dataDetail['name'] ?? '' ?>
        </a>
    </div>

    <div class="detail-container">
        <div class="detail-left">
            <div class="main-image-box">
                <div class="img-placeholder-large">
                    <img id="mainImg" src="/DA-cuoiky/image/<?= $defaultImg ?>" alt="<?= $dataDetail['name'] ?? 'Sản phẩm' ?>">
                </div>
            </div>
        </div>

        <div class="detail-right">
            <div class="product-header">
                <h1><?= $dataDetail['name'] ?? '' ?></h1>
            </div>

            <div class="price-box">
                <span class="current-price" id="currentPrice">
                    <?= $defaultVariant ? $fmtVnd($defaultVariant['price']) : '' ?>
                </span>
                <div class="vat-note">(Đã bao gồm VAT)</div>
            </div>

            <!-- Dung lượng -->
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

            <!-- Màu -->
            <div class="option-section">
                <label>
                    Màu sắc:
                    <span id="colorText"><?= $defaultVariant ? $defaultVariant['color_name'] : '' ?></span>
                </label>
                <div class="color-group" id="colorGroup"></div>
            </div>

            <!-- CHỈ GIỮ 1 hidden cho JS -->
            <input type="hidden" id="variantId" value="<?= $defaultVariant ? (int)$defaultVariant['id'] : '' ?>">

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
                <div class="btn-row-group">
                    <form action="/DA-cuoiky/index.php" method="POST" class="form-half">
                        <input type="hidden" name="mod" value="cart_add"> 
                        <input type="hidden" name="id" value="<?= (int)$idProduct ?>">
                        <input type="hidden" name="type" value="<?=$type?>">
                        <input type="hidden" name="variant" id="variantIdCart" value="<?= $defaultVariant ? (int)$defaultVariant['id'] : '' ?>">
                        
                        <button class="btn-add-cart" type="submit">
                            <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ
                        </button>
                    </form>

                    <form action="/DA-cuoiky/index.php" method="GET" id="buyNowForm" class="form-half">
                        <input type="hidden" name="mod" value="order">
                        <input type="hidden" name="id" value="<?= (int)$idProduct ?>">
                        <input type="hidden" name="type" value="<?=$type?>">
                        <input type="hidden" name="variant" id="variantIdForm" value="<?= $defaultVariant ? (int)$defaultVariant['id'] : '' ?>">
                        
                        <button class="btn-buy-now" type="submit">MUA NGAY</button>
                    </form>
                </div>
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

const storageGroup  = document.getElementById('storageGroup');
const colorGroup    = document.getElementById('colorGroup');
const currentPrice  = document.getElementById('currentPrice');
const colorText     = document.getElementById('colorText');
const mainImg       = document.getElementById('mainImg');

const variantIdForm = document.getElementById('variantIdForm'); // form mua ngay
const variantIdCart = document.getElementById('variantIdCart'); // form thêm giỏ
const variantIdInp  = document.getElementById('variantId');     // hidden chung (nếu có)

function fmtVnd(n){
  return new Intl.NumberFormat('vi-VN').format(Number(n || 0)) + '₫';
}

function setActiveStorage(storage){
  storageGroup.querySelectorAll('.option-btn').forEach(btn=>{
    btn.classList.toggle('active', Number(btn.dataset.storage) === Number(storage));
  });
}

function setVariant(v){
  if (!v) return;

  if (currentPrice) currentPrice.textContent = fmtVnd(v.price);
  if (colorText) colorText.textContent = v.color_name || '';

  if (variantIdForm) variantIdForm.value = v.id;
  if (variantIdCart) variantIdCart.value = v.id;
  if (variantIdInp)  variantIdInp.value  = v.id;

  if (mainImg && v.image_url && v.image_url.trim() !== '') {
    mainImg.src = v.image_url;
  }
}

/** Render TẤT CẢ màu của 1 dung lượng */
function renderColorsByStorage(storage){
  const list = variantsByStorage[storage] || [];
  colorGroup.innerHTML = '';

  if (!list.length) {
    colorGroup.innerHTML = '<div style="font-size:13px;color:#6b7280">Không có màu cho dung lượng này</div>';
    return;
  }

  let pick = null; // variant sẽ auto chọn
  for (const v of list) {
    if (Number(v.stock) > 0) { pick = v; break; }
  }
  if (!pick) pick = list[0]; // nếu hết hàng hết thì vẫn chọn cái đầu

  list.forEach((v) => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'color-circle';
    btn.title = v.color_name || '';
    btn.style.backgroundColor = v.color_hex || '#000';

    if (Number(v.stock) <= 0) {
      btn.disabled = true;
      btn.style.opacity = '0.35';
      btn.style.cursor = 'not-allowed';
      btn.title = (v.color_name || '') + ' (Hết hàng)';
    }

    btn.addEventListener('click', () => {
      colorGroup.querySelectorAll('.color-circle').forEach(x => x.classList.remove('active'));
      btn.classList.add('active');
      setVariant(v);
    });

    colorGroup.appendChild(btn);

    // set active cho màu đang pick
    if (Number(v.id) === Number(pick.id)) {
      // click giả để đồng bộ UI + hidden + giá
      setTimeout(() => btn.click(), 0);
    }
  });
}

// Click dung lượng => đổi danh sách màu
storageGroup.addEventListener('click', (e) => {
  const btn = e.target.closest('.option-btn');
  if (!btn) return;

  const storage = btn.dataset.storage;
  setActiveStorage(storage);
  renderColorsByStorage(storage); // ✅ đây là phần hiển thị tất cả màu theo dung lượng
});

// INIT
(() => {
  const defaultStorage =
    storageGroup.querySelector('.option-btn.active')?.dataset.storage
    || Object.keys(variantsByStorage)[0];

  setActiveStorage(defaultStorage);
  renderColorsByStorage(defaultStorage);
})();
</script>

