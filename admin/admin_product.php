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

$isEditing = false;
$editId = 0;
$name = ''; $type_id = 1; $description = '';
$editVariants = [];

// --- 2. LẤY DỮ LIỆU ĐỂ SỬA ---
if (isset($_GET['edit_id'])) {
    $isEditing = true;
    $editId = (int)$_GET['edit_id'];

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$editId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $name = $product['name'];
        $type_id = $product['type_id'];
        $description = $product['description'];

        $stmtV = $conn->prepare("SELECT * FROM product_variants WHERE product_id = ?");
        $stmtV->execute([$editId]);
        $editVariants = $stmtV->fetchAll(PDO::FETCH_ASSOC);
    }
}

// --- 3. XỬ LÝ: THÊM MỚI HOẶC CẬP NHẬT (Cập nhật logic dò tên trùng) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_product']) || isset($_POST['update_product']))) {
    $name = trim($_POST['name']);
    $type_id = (int)$_POST['type_id'];
    $description = trim($_POST['description']);
    $variants = $_POST['variants'] ?? []; 

    try {
        $conn->beginTransaction();

        if (isset($_POST['update_product'])) {
            // LOGIC SỬA: Cập nhật thông tin cha và thay mới hoàn toàn variant
            $productId = (int)$_POST['product_id'];
            $sqlProd = "UPDATE products SET name=?, type_id=?, description=? WHERE id=?";
            $conn->prepare($sqlProd)->execute([$name, $type_id, $description, $productId]);
            $conn->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$productId]);
            $msg = "Cập nhật thành công!";
        } else {
            // LOGIC THÊM: Dò xem tên sản phẩm đã tồn tại chưa
            $stmtCheck = $conn->prepare("SELECT id FROM products WHERE name = ? LIMIT 1");
            $stmtCheck->execute([$name]);
            $existingProduct = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($existingProduct) {
                // Nếu đã có sp tên này, lấy ID cũ để chèn thêm variant
                $productId = $existingProduct['id'];
                $msg = "Sản phẩm đã tồn tại. Đã thêm các phiên bản mới vào sản phẩm sẵn có!";
            } else {
                // Nếu chưa có, tạo mới sp cha
                $sqlProd = "INSERT INTO products (name, type_id, description, active, created_at) VALUES (?, ?, ?, 1, NOW())";
                $stmt1 = $conn->prepare($sqlProd);
                $stmt1->execute([$name, $type_id, $description]);
                $productId = $conn->lastInsertId();
                $msg = "Đã tạo sản phẩm mới và lưu các phiên bản thành công!";
            }
        }

        // Lưu danh sách variants (Dùng cho cả Thêm và Sửa)
        $sqlVar = "INSERT INTO product_variants (product_id, color, color_hex, storage_gb, price, stock, image_url, active, created_at) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())";
        $stmt2 = $conn->prepare($sqlVar);

        foreach ($variants as $v) {
            $stmt2->execute([
                $productId, 
                trim($v['color']), 
                trim($v['color_hex']), 
                (int)$v['storage_gb'], 
                (float)$v['price'], 
                (int)$v['stock'], 
                trim($v['image_url'])
            ]);
        }

        $conn->commit();
        echo "<script>alert('$msg'); window.location.href='admin_product.php';</script>";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "<script>alert('Lỗi: " . $e->getMessage() . "');</script>";
    }
}

// --- 4. XỬ LÝ XÓA ---
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $conn->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    header("Location: admin_product.php");
    exit;
}

// --- 5. LẤY DANH SÁCH ---
$sqlList = "SELECT p.id, p.name, p.type_id, COUNT(v.id) as total_variants, MIN(v.price) as min_price 
            FROM products p LEFT JOIN product_variants v ON p.id = v.product_id 
            GROUP BY p.id ORDER BY p.id DESC";
$products = $conn->query($sqlList)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin - Quản lý iPhone</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin_product.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">ADMIN SHOPDUNK</div>
        <ul class="menu">
            <li><a href="admin_product.php" class="active"><i class="fa-solid fa-box"></i> Quản lý sản phẩm</a></li>
            <li><a href="../index.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Xem trang chủ</a></li>
        </ul>
    </div>

    <div class="content">
        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid <?= $isEditing ? 'fa-pen-to-square' : 'fa-plus-circle' ?>"></i> 
                <?= $isEditing ? "Sửa sản phẩm: $name" : "Thêm sản phẩm mới" ?></h3>
            </div>
            <div class="card-body">
                <form method="POST" id="productForm">
                    <?php if($isEditing): ?>
                        <input type="hidden" name="product_id" value="<?= $editId ?>">
                    <?php endif; ?>

                    <div class="form-section-title" style="color: #007bff; font-weight:bold; margin-bottom:10px; border-bottom:2px solid #007bff;">1. Thông tin chung</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tên sản phẩm:</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Loại sản phẩm:</label>
                            <select name="type_id">
                                <option value="1" <?= $type_id == 1 ? 'selected' : '' ?>>iPhone</option>
                                <option value="2" <?= $type_id == 2 ? 'selected' : '' ?>>MacBook</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Mô tả:</label>
                            <input type="text" name="description" value="<?= htmlspecialchars($description) ?>">
                        </div>
                    </div>

                    <div class="form-section-title" style="color: #28a745; font-weight:bold; margin: 20px 0 10px 0; border-bottom:2px solid #28a745;">2. Các phiên bản</div>
                    
                    <div id="variants-container"></div>

                    <div style="margin-top: 20px; display: flex; gap: 10px;">
                        <button type="button" class="btn-add-variant" onclick="addVariant()">
                            <i class="fa-solid fa-plus"></i> Thêm phiên bản
                        </button>
                        <button type="submit" name="<?= $isEditing ? 'update_product' : 'add_product' ?>" class="btn-save-small">
                            <i class="fa-solid fa-save"></i> <?= $isEditing ? 'CẬP NHẬT' : 'LƯU' ?>
                        </button>
                        <?php if($isEditing): ?>
                            <a href="admin_product.php" class="btn-remove-variant" style="text-decoration:none; padding:10px 20px; font-size:14px; display:flex; align-items:center;">Hủy sửa</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Danh sách sản phẩm</h3></div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th><th>Tên</th><th>Loại</th><th>Số bản</th><th>Giá từ</th><th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $row): ?>
                        <tr>
                            <td>#<?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= $row['type_id'] == 1 ? 'iPhone' : 'MacBook' ?></td>
                            <td><?= $row['total_variants'] ?></td>
                            <td><?= number_format($row['min_price'], 0, ',', '.') ?>₫</td>
                            <td>
                                <a href="admin_product.php?edit_id=<?= $row['id'] ?>" class="btn-action btn-edit"><i class="fa-solid fa-pen"></i></a>
                                <a href="admin_product.php?delete_id=<?= $row['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Xóa?')"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let variantCount = 0;
        const container = document.getElementById('variants-container');

        function addVariant(data = null) {
            const index = variantCount;
            const color = data ? data.color : '';
            const hex = data ? data.color_hex : '#000000';
            const storage = data ? data.storage_gb : 256;
            const price = data ? data.price : '';
            const stock = data ? data.stock : 10;
            const img = data ? data.image_url : '';

            const html = `
            <div class="variant-item" id="variant-${index}">
                <div class="variant-header">
                    <span>Phiên bản #${index + 1}</span>
                    <button type="button" class="btn-remove-variant" onclick="removeVariant(${index})"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Màu sắc:</label><input type="text" name="variants[${index}][color]" value="${color}" required></div>
                    <div class="form-group">
                        <label>Mã màu Hex:</label>
                        <div class="color-input-wrapper">
                            <input type="text" name="variants[${index}][color_hex]" value="${hex}" oninput="this.nextElementSibling.style.backgroundColor = this.value">
                            <span class="color-preview" style="background-color: ${hex};"></span>
                        </div>
                    </div>
                    <div class="form-group"><label>Dung lượng:</label><input type="number" name="variants[${index}][storage_gb]" value="${storage}" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Giá:</label><input type="number" name="variants[${index}][price]" value="${price}" required></div>
                    <div class="form-group"><label>Kho:</label><input type="number" name="variants[${index}][stock]" value="${stock}"></div>
                    <div class="form-group"><label>Link Ảnh:</label><input type="text" name="variants[${index}][image_url]" value="${img}"></div>
                </div>
            </div>`;
            container.insertAdjacentHTML('beforeend', html);
            variantCount++;
        }

        function removeVariant(index) {
            const item = document.getElementById(`variant-${index}`);
            if (item) item.remove();
        }

        <?php if($isEditing && !empty($editVariants)): ?>
            <?php foreach($editVariants as $v): ?>
                addVariant(<?= json_encode($v) ?>);
            <?php endforeach; ?>
        <?php else: ?>
            addVariant(); 
        <?php endif; ?>
    </script>
</body>
</html>