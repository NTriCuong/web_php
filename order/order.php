 <?php
    $idProduct = $_GET['id'] ?? '';
   $variant = $_GET['variant'] ?? null;   // lấy variant từ URL
    $dataDisplay = null;
   
    if (!empty($variant)) {
    // kết nối csld lấy data trong variant table
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

            foreach ($variants as $value) {
                if ($value['id'] == $variant) {
                    $dataDisplay = $value;
                    break;
                }
            }
        // lay thong tin sản phẩm
        foreach ($dataProduct as $value) {
            foreach ($value as $val) {
                if($val['id'] == $idProduct){
                    $dataDisplay['name'] = $val['name'];
                    break;
                }
            }
        }
    }

    // nếu từ giỏ hàng chuyển qua
 ?>
 <div style="width:600px; margin:0 auto">
 <div class="wrap">
    <div class="topbar">
      <a class="back" href="#">
        <span style="font-size:22px">‹</span>
        <span>Về trang chủ ShopDunk</span>
      </a>
      <div class="spacer"></div>
    </div>

    <div class="card">
      <!-- CART -->
      <div class="section" id="cartSection">
        <!-- Item 1 -->
        <div class="cart-item">
          <div class="thumb">
            <img src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300&q=80" alt="">
          </div>

          <div class="item-mid">
            <p class="name"><?=$dataDisplay['name']?> <?=$dataDisplay['storage_gb']?></p>
            <div class="meta">Màu sắc: <span class="color-text">Tím Oải Hương</span></div>

              <div class="swatch" style="background:#000" data-name="Đen"></div>

          </div>

          <div class="item-right">
            <div class="price" style="font-size:16px">24.690.000đ</div>
            <div class="qty">
              <button type="button">−</button>
              <input value="1" />
              <button type="button">+</button>
            </div>
          </div>
        </div>


      <!-- CUSTOMER -->
      <div class="section">
        <div class="h2" style="font-size:18px">Thông tin khách hàng</div>
        <div class="radio-row">
          <label class="radio"><input type="radio" name="salute" checked style="font-size:16px"> Anh</label>
          <label class="radio"><input type="radio" name="salute" style="font-size:16px"> Chị</label>
        </div>

        <div class="grid-2">
          <div class="field">
            <label>Họ và Tên</label>
            <input class="input" placeholder="" />
          </div>
          <div class="field">
            <label>Số điện thoại</label>
            <input class="input" placeholder="" />
          </div>
        </div>
      </div>

      <!-- SHIPPING -->
      <div class="section">
        <div class="h2" style="font-size:18px" >Hình thức nhận hàng</div>
        <div class="radio-row">
          <label class="radio"><input type="radio" name="ship" checked> Giao tận nơi</label>
          <label class="radio"><input type="radio" name="ship"> Nhận tại cửa hàng</label>
        </div>

        <div class="ship-box">
          <div class="ship-grid">
            <select>
              <option>Chọn tỉnh, thành phố</option>
              <option>Hà Nội</option>
              <option>TP. Hồ Chí Minh</option>
              <option>Đà Nẵng</option>
            </select>

            <select>
              <option>Chọn quận, huyện</option>
              <option>Quận 1</option>
              <option>Quận 7</option>
              <option>Hải Châu</option>
            </select>

            <input class="input full" placeholder="Địa chỉ cụ thể" />
          </div>
        </div>

        <div class="note">
          <input class="input" placeholder="Nhập ghi chú (nếu có)" />
        </div>

      </div>

      <!-- COUPON + TOTAL -->
      <div class="section">
        <div class="total-row">
          <div>Tổng tiền:</div>
          <div class="total" style="font-size:18px">80.170.000đ</div>
        </div>

        <div class="check-row" style="margin-top:10px;">
          <input type="checkbox" checked id="agree" />
          <div style="font-size:13px">
            Tôi đã đọc và đồng ý với
            <a href="#">điều khoản và điều kiện</a>
            điều khoản và điều kiện của website
          </div>
        </div>

        <button class="btn" id="btnCheckout" style="font-size:15px">Tiến hành đặt hàng</button>
        <div class="hint">Bạn có thể lựa chọn các hình thức thanh toán ở bước sau</div>
      </div>
    </div>
  </div>
</div>
  <script>
    // UI chọn màu: click swatch -> active + update text "Màu sắc:"
    document.querySelectorAll('.swatches').forEach(group=>{
      group.addEventListener('click', (e)=>{
        const sw = e.target.closest('.swatch');
        if(!sw) return;

        group.querySelectorAll('.swatch').forEach(x=>x.classList.remove('active'));
        sw.classList.add('active');

        const wrap = group.closest('.item-mid');
        const text = wrap.querySelector('.color-text');
        if(text) text.textContent = sw.dataset.name || 'Màu';
      });
    });

    // disable checkout nếu chưa tick đồng ý
    const agree = document.getElementById('agree');
    const btn = document.getElementById('btnCheckout');
    function sync(){
      btn.disabled = !agree.checked;
    }
    agree.addEventListener('change', sync);
    sync();
  </script>