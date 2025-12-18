
    <main>
        <section class="hero-banner">
            <button class="slider-btn btn-prev"></button>
            
            <div class="hero-img" style="background: linear-gradient(to right, #000, #333);">
                BANNER APPLE WATCH ULTRA 3
            </div>

            <button class="slider-btn btn-next"></button>
        </section>

        <section class="promo-section">
            <div class="promo-img-placeholder">
                BANNER GIẢI PHÁP DOANH NGHIỆP (Upload ảnh image_a3bdcf.jpg tại đây)
            </div>
        </section>

        <section class="product-section">
            <?php foreach($productType as $type){ ?>
                <h2 class="section-title"><?=$type?></h2>
                <div class="product-grid">
                <?php foreach ($dataProduct[$type] as $item){ 
                        $gia = $item['price'];
                    ?>
                    <a href="/DA-cuoiky/index.php?mod=detail&type=<?=$type?>&id=<?=(int)$item['id']?>">
                        <article class="product-card">
                            <!-- <div class="badge badge-discount"></div> -->
                            <!-- <div class="badge badge-new"><i class="fa-solid fa-tag"></i> Mới</div> -->
                            <div class="prod-img-placeholder">ảnh</div>
                            <h3 class="prod-name"><?=$item["name"]?></h3>
                            <div class="prod-price"><?=number_format((float)$gia, 0, ',', '.') . '₫';?></div>
                            <span class="old-price">37.999.000₫</span>
                        </article>
                    </a>
                <?php }?>
                </div>
                <a href="/DA-cuoiky/index.php?mod=categories&type=<?=$type?>" class="view-all-btn">Xem tất cả <?=$type?> ></a>
            <?php }?>
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
