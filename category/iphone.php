
    <?php
        $type = $_GET['type'] ?? '';
        $filter = strtolower(trim($_GET['filter'] ?? ''));
        $products = $dataProduct[$type];
        if ($filter !== '') {
        $products = array_filter($products, function($item) use ($filter) {
            $name = strtolower($item['name'] ?? '');
            return strpos($name, $filter) !== false;
        });
        }
?>
    <main>
        <div class="breadcrumb">
           <a href="/DA-cuoiky/index.php?mod=home">   Trang chủ </a>
           <span>&rsaquo;</span>  
           <a href="/DA-cuoiky/index.php?mod=categories&type=<?=$type?>">  <?=$type?> </a>
        </div>
<!-- banner -->
        <section class="category-banner">
            <div class="banner-content">
                <h1>iPhone 17</h1>
                <p>Sẵn hàng. Đủ màu. Mua ngay</p>
                <div class="banner-price">Chỉ từ 22.990.000đ</div>
                <div class="banner-offer">Thu cũ đổi mới trợ giá tới 2 Triệu | 0% | 2 Triệu</div>
                <a href="#" class="btn-buy-now">Mua ngay</a>
            </div>
            <div class="banner-image">
                <div class="banner-img-placeholder">ẢNH BANNER IPHONE 17</div>
            </div>
        </section>
<!--  menu filter -->
        <section class="filter-bar-container">
            <div class="filter-list">
                <?php
                $filters = $type === "iphone"?[
                    'iPhone 17 series' => 'iphone 17',
                    'iPhone 16 series' => 'iphone 16',
                    'iPhone 15 series' => 'iphone 15',
                    'iPhone 14 series' => 'iphone 14',
                    'iPhone 13 series' => 'iphone 13',
                    'iPhone 11 series' => 'iphone 11',
                    'iPhone Air'       => 'iphone air',
                ]: [
                    'macbook pro' => 'macbook pro',
                    'macbook air' => 'macbook air',
                    'macbook M1' => 'm1',
                    'macbook M2' =>'m2',
                    'macbook M3' =>'m3',
                ];
                $filter = strtolower(trim($_GET['filter'] ?? ''));
                ?>
                <?php foreach ($filters as $label => $keyword): ?>
                <a
                    href="/DA-cuoiky/index.php?mod=categories&type=<?=$type?>&filter=<?=urlencode($keyword)?>"
                    class="filter-item <?=($filter === strtolower($keyword) ? 'active' : '')?>"
                >
                    <?=$label?>
                </a>
                <?php endforeach; ?>
                
            </div>
        </section>
<!-- products -->
        <section class="product-container">
            <div class="product-grid">
                <?php foreach($products as $item){ 
                        $gia = $item['price'];
                ?>
                    <a href="/DA-cuoiky/index.php?mod=detail&type=<?=$type?>&id=<?=(int)$item['id']?>">
                        <div class="product-card">
                            <!-- <div class="badge badge-discount">Giảm 1%</div>
                            <div class="badge badge-new"><i class="fa-solid fa-tag"></i> Mới</div> -->
                            <div class="product-img">
                                <div class="img-box">IMG iPhone 17 Pro Max</div>
                            </div>
                            <h3 class="product-name"><?=$item['name']?></h3>
                            <div class="price-row">
                                <span class="curr-price"><?=number_format((float)$gia, 0, ',', '.') . '₫';?></span>
                                <span class="old-price">37.999.000₫</span>
                            </div>
                        </div>
                    </a>
                <?php }?>
            </div>
        </section>
    </main>

    <div class="floating-group">
        <div class="float-btn btn-hotline"><i class="fa-solid fa-phone"></i></div>
        <div class="float-btn btn-chat"><i class="fa-solid fa-comment-dots"></i></div>
    </div>
