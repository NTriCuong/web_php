<?php
// --- 1) KẾT NỐI DATABASE ---
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apple_store"; // ✅ DB mới bạn tạo

try {
  $conn = new PDO(
    "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
    $username,
    $password
  );
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Kết nối thất bại: " . $e->getMessage());
}

// --- 2) KHỞI TẠO BIẾN ---
$dataProduct = [
  'iphone'  => [],
  'macbook' => []
];

// map type_id -> key string để UI dùng
$typeMap = [
  1 => 'iphone',
  2 => 'macbook'
];

// --- 3) LẤY LOẠI SẢN PHẨM (iphone/macbook) ---
try {
  $sqlType = "SELECT DISTINCT type_id FROM products WHERE active = 1";
  $stmtType = $conn->prepare($sqlType);
  $stmtType->execute();
  $typeIds = $stmtType->fetchAll(PDO::FETCH_COLUMN);

  $productType = [];
  foreach ($typeIds as $tid) {
    $tid = (int)$tid;
    if (isset($typeMap[$tid])) $productType[] = $typeMap[$tid];
  }
  // ví dụ kết quả: ['iphone','macbook']
} catch (Exception $e) {
  echo "Lỗi truy vấn loại sản phẩm: " . $e->getMessage();
  $productType = [];
}

// --- 4) LẤY DANH SÁCH SẢN PHẨM + GIÁ THẤP NHẤT (min price) ---
try {
  // ✅ chú ý: schema mới là active, type_id
  // ✅ muốn lấy min price + 1 variant đại diện cho ảnh/màu/storage
  $sqlProduct = "
    SELECT
      p.id,
      p.name,
      p.type_id,
      MIN(v.price) AS price,

      -- Lấy 1 variant đại diện (ảnh, màu, storage) theo variant có giá thấp nhất
      (SELECT vv.color FROM product_variants vv
        WHERE vv.product_id = p.id AND vv.active = 1
        ORDER BY vv.price ASC, vv.storage_gb ASC
        LIMIT 1
      ) AS color,

      (SELECT vv.storage_gb FROM product_variants vv
        WHERE vv.product_id = p.id AND vv.active = 1
        ORDER BY vv.price ASC, vv.storage_gb ASC
        LIMIT 1
      ) AS storage_gb,

      (SELECT vv.image_url FROM product_variants vv
        WHERE vv.product_id = p.id AND vv.active = 1
        ORDER BY vv.price ASC, vv.storage_gb ASC
        LIMIT 1
      ) AS image_url

    FROM products p
    JOIN product_variants v ON v.product_id = p.id
    WHERE p.active = 1 AND v.active = 1
    GROUP BY p.id, p.name, p.type_id
    ORDER BY p.id DESC
  ";

  $stmtProduct = $conn->prepare($sqlProduct);
  $stmtProduct->execute();
  $allProducts = $stmtProduct->fetchAll(PDO::FETCH_ASSOC);

  foreach ($allProducts as $prod) {
    $typeId = (int)$prod['type_id'];
    $key = $typeMap[$typeId] ?? 'iphone'; // fallback

    $dataProduct[$key][] = $prod;
  }

} catch (Exception $e) {
  echo "Lỗi truy vấn sản phẩm: " . $e->getMessage();
}
?>
