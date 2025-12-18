<?php
    $idProduct = $_GET['id'] ?? '';
    $type = $_GET['type'] ?? '';
 
    $sql = "SELECT id, storage_gb, color, price, stock, image_url
            FROM product_variants
            WHERE product_id = :id AND is_active = 1
            ORDER BY storage_gb ASC, color ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $idProduct);
    $stmt->execute();
    $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);// danh sách các phiên bản của sản phẩm
    var_dump($variants);
    if (!$variants) {
        die('Không tìm thấy sản phẩm');
    }
    $variant = [];// ['128'=>['red', 'white', ...]]
    foreach ($variants as $v) {
        
    }
// (tuỳ chọn) sắp xếp key storage tăng dần
    uksort($variant, fn($a,$b) => (int)$a <=> (int)$b);

    var_dump($variant);

    $gia = 1000; // custom lại giá hiển thị
?>
    <main>
        <div class="breadcrumb">
           <a href='/DA-cuoiky/index.php?mod=home'> Trang chủ</a> <span>&rsaquo;</span> 
           <a href="/DA-cuoiky/index.php?mod=categories&type=<?=$type?>"> <?=$type?> </a><span>&rsaquo;</span> 
            <a href="/DA-cuoiky/index.php?mod=categories&type=<?=$type?>?id=<?=$idProduct?>"></a>
        </div>

        <div class="detail-container">
            <div class="detail-left">
                <div class="main-image-box">
                    <div class="img-placeholder-large">
                        ẢNH IPHONE TITAN SA MẠC
                    </div>
                </div>
                <!-- <div class="thumbnail-list">
                    <div class="thumb-item active">Mặt sau</div>
                    <div class="thumb-item">Mặt trước</div>
                    <div class="thumb-item">Cạnh bên</div>
                    <div class="thumb-item">Camera</div>
                </div> -->
            </div>

            <div class="detail-right">
                <div class="product-header">
                    <h1>iphone 5</h1>
                    <!-- <div class="region-select">
                        Khu vực miền Bắc <i class="fa-solid fa-chevron-down"></i>
                    </div> -->
                </div>

                <div class="price-box">
                    <!-- <span class="current-price"><?=number_format((float)$gia, 0, ',', '.') . '₫';?></span> -->
                    <span class="old-price">37.999.000₫</span>
                    <div class="vat-note">(Đã bao gồm VAT)</div>
                </div>

                <div class="option-section">
                    <label>Dung lượng</label>
                    <div class="option-group">
                        <button class="option-btn active">256GB</button>
                        <button class="option-btn">512GB</button>
                        <button class="option-btn">1TB</button>
                        <button class="option-btn">2TB</button>
                    </div>
                </div>

                <div class="option-section">
                    <label>Màu sắc</label>
                    <div class="color-group">
                        <div class="color-circle desert active"></div>
                        <div class="color-circle white"></div>
                        <div class="color-circle black"></div>
                        <div class="color-circle blue"></div>
                    </div>
                </div>

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
                    <button class="btn-buy-now">
                        MUA NGAY
                    </button>
                    <!-- <button class="btn-installment">
                        TRẢ GÓP 0%
                    </button> -->
                </div>

            </div>
        </div>
    </main>

    <div class="floating-group">
        <div class="float-btn btn-hotline"><i class="fa-solid fa-phone"></i></div>
        <div class="float-btn btn-chat"><i class="fa-solid fa-comment-dots"></i></div>
    </div>
