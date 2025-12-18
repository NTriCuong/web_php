<?php
// --- 1. KẾT NỐI DATABASE ---
$servername = "localhost";
$username = "root";
$password = ""; // Mật khẩu của bạn (XAMPP thường để trống)
$dbname = "apple_store";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Thiết lập chế độ báo lỗi exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Kết nối thất bại: " . $e->getMessage());
}

// --- 2. KHỞI TẠO BIẾN ---
// Mảng chứa sản phẩm, chia theo danh mục để dễ hiển thị
$dataProduct = [
    'iphone' => [],
    'macbook' => []
];
$productType = [];

// --- 3. TRUY VẤN DỮ LIỆU: LOẠI SẢN PHẨM ($productType) ---
try {
    // Lấy các loại sản phẩm duy nhất đang hoạt động
    $sqlType = "SELECT DISTINCT product_type FROM products WHERE is_active = 1";
    $stmtType = $conn->prepare($sqlType);
    $stmtType->execute();
    
    // Đổ dữ liệu vào mảng $productType (kết quả: ['iphone', 'macbook'])
    $productType = $stmtType->fetchAll(PDO::FETCH_COLUMN);

} catch(Exception $e) {
    echo "Lỗi truy vấn loại sản phẩm: " . $e->getMessage();
}

// --- 4. TRUY VẤN DỮ LIỆU: DANH SÁCH SẢN PHẨM ($dataProduct) ---
try {
    // Lấy tên, slug, loại và giá thấp nhất (min_price) của từng sản phẩm
    $sqlProduct = "
        SELECT 
            p.id, 
            p.name, 
            p.slug, 
            p.product_type, 
            MIN(v.price) as price,
            v.storage_gb,
            v.color,
            v.image_url
        FROM products p
        JOIN product_variants v ON p.id = v.product_id
        WHERE p.is_active = 1 AND v.is_active = 1
        GROUP BY p.id, p.name, p.slug, p.product_type
        ORDER BY p.id DESC
    ";

    $stmtProduct = $conn->prepare($sqlProduct);
    $stmtProduct->execute();
    $allProducts = $stmtProduct->fetchAll(PDO::FETCH_ASSOC);

    // Phân loại sản phẩm vào mảng $dataProduct theo key
    foreach ($allProducts as $prod) {
        $type = $prod['product_type']; // ví dụ: 'iphone'
        // Kiểm tra xem key này có tồn tại trong mảng $dataProduct chưa, nếu chưa thì tạo mới
        if (!isset($dataProduct[$type])) {
            $dataProduct[$type] = [];
        }
        $dataProduct[$type][] = $prod;
    }

} catch(Exception $e) {
    echo "Lỗi truy vấn sản phẩm: " . $e->getMessage();
}

?>

