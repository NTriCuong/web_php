<?php
// --- 1. KẾT NỐI DATABASE ---
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "apple_store";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Lỗi kết nối: " . $e->getMessage());
}

// --- 2. XỬ LÝ: THÊM SẢN PHẨM MỚI ---
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $slug = $_POST['slug'];
    $type = $_POST['type'];
    $price = $_POST['price'];
    $storage = $_POST['storage'];
    $color = $_POST['color'];

    try {
        // Bước 1: Thêm vào bảng products
        $sql1 = "INSERT INTO products (slug, name, product_type, brand, is_active) VALUES (?, ?, ?, 'Apple', 1)";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->execute([$slug, $name, $type]);
        $productId = $conn->lastInsertId();

        // Bước 2: Thêm vào bảng product_variants
        $sku = strtoupper($slug) . '-' . rand(100,999); 
        $sql2 = "INSERT INTO product_variants (product_id, variant_sku, color, storage_gb, price, stock, is_active) 
                 VALUES (?, ?, ?, ?, ?, 10, 1)";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute([$productId, $sku, $color, $storage, $price]);

        echo "<script>alert('Thêm sản phẩm thành công!'); window.location.href='admin_products.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Lỗi: " . $e->getMessage() . "');</script>";
    }
}

// --- 3. XỬ LÝ: XÓA SẢN PHẨM ---
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    try {
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        header("Location: admin_products.php");
    } catch (Exception $e) {
        echo "<script>alert('Lỗi xóa: " . $e->getMessage() . "');</script>";
    }
}

// --- 4. LẤY DANH SÁCH SẢN PHẨM ---
$sqlList = "SELECT p.*, v.price, v.color, v.storage_gb 
            FROM products p 
            LEFT JOIN product_variants v ON p.id = v.product_id 
            GROUP BY p.id ORDER BY p.id DESC";
$stmtList = $conn->prepare($sqlList);
$stmtList->execute();
$products = $stmtList->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Quản Trị - ShopDunk Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin_product.css">
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">ADMIN SHOPDUNK</div>
        <ul class="menu">
            <li><a href="#" class="active"><i class="fa-solid fa-box"></i> Quản lý sản phẩm</a></li>
            <li><a href="#"><i class="fa-solid fa-cart-shopping"></i> Quản lý đơn hàng</a></li>
            <li><a href="../index.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Xem trang chủ</a></li>
        </ul>
    </div>

    <div class="content">
        
        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-plus-circle"></i> Thêm sản phẩm mới</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tên sản phẩm:</label>
                            <input type="text" name="name" placeholder="Ví dụ: iPhone 16 Pro Max" required>
                        </div>
                        <div class="form-group">
                            <label>Slug (Mã URL ảnh):</label>
                            <input type="text" name="slug" placeholder="iphone-16-pro-max" required>
                            <small style="color:red; font-size: 12px;">*Tên này phải trùng với tên file ảnh trong thư mục img/products</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Loại sản phẩm:</label>
                            <select name="type">
                                <option value="iphone">iPhone</option>
                                <option value="macbook">MacBook</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Giá bán (VNĐ):</label>
                            <input type="number" name="price" placeholder="30000000" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Dung lượng (GB):</label>
                            <input type="number" name="storage" value="256">
                        </div>
                        <div class="form-group">
                            <label>Màu sắc:</label>
                            <input type="text" name="color" placeholder="Titan Tự nhiên">
                        </div>
                    </div>

                    <button type="submit" name="add_product" class="btn-submit">THÊM SẢN PHẨM</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-list"></i> Danh sách sản phẩm</h3>
            </div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Hình ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Phân loại</th>
                            <th>Giá (Bản chuẩn)</th>
                            <th>Màu / Dung lượng</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $row): ?>
                        <tr>
                            <td>#<?php echo $row['id']; ?></td>
                            <td>
                                <img src="img/products/<?php echo $row['slug']; ?>.jpg" alt="Img" style="width: 50px; height: 50px; object-fit: contain; border: 1px solid #ddd; background: #fff;">
                            </td>
                            <td style="font-weight: bold;"><?php echo $row['name']; ?></td>
                            <td>
                                <span class="badge <?php echo ($row['product_type'] == 'iphone') ? 'badge-iphone' : 'badge-macbook'; ?>">
                                    <?php echo ucfirst($row['product_type']); ?>
                                </span>
                            </td>
                            <td style="color: #d70018; font-weight: bold;">
                                <?php echo number_format($row['price'], 0, ',', '.'); ?>₫
                            </td>
                            <td>
                                <?php echo $row['color']; ?> / <?php echo $row['storage_gb']; ?>GB
                            </td>
                            <td>
                                <a href="#" class="btn-action btn-edit" onclick="alert('Tính năng đang phát triển!')"><i class="fa-solid fa-pen"></i> Sửa</a>
                                <a href="admin_products.php?delete_id=<?php echo $row['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này không?');">
                                    <i class="fa-solid fa-trash"></i> Xóa
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html> 