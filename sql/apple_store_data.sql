/*
 * Dữ liệu mẫu (Dummy Data) cho Database apple_store
 * Vui lòng chạy SAU khi đã tạo xong cấu trúc bảng (schema)
 */

USE apple_store;

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- 1. Dữ liệu USERS
-- --------------------------------------------------------
INSERT INTO users (id, email, password_hash, full_name, phone, role, status) VALUES
(1, 'admin@apple.com', 'hashed_password_admin_123', 'Quản Trị Viên', '0901112222', 'admin', 'active'),
(2, 'customer_a@gmail.com', 'hashed_password_custA_456', 'Nguyễn Văn A', '0903334444', 'customer', 'active'),
(3, 'customer_b@gmail.com', 'hashed_password_custB_789', 'Trần Thị B', '0905556666', 'customer', 'active');

-- --------------------------------------------------------
-- 2. Dữ liệu PRODUCTS
-- --------------------------------------------------------
INSERT INTO products (id, slug, name, product_type, brand, description, is_active) VALUES
(1, 'iphone-15-pro-max', 'iPhone 15 Pro Max', 'iphone', 'Apple', 'Chip A17 Bionic, camera 48MP.', 1),
(2, 'iphone-15', 'iPhone 15', 'iphone', 'Apple', 'Chip A16 Bionic, camera kép.', 1),
(3, 'macbook-pro-m3', 'MacBook Pro 14 inch M3 Max', 'macbook', 'Apple', 'Hiệu năng cực cao cho dân chuyên nghiệp.', 1),
(4, 'macbook-air-m2', 'MacBook Air 13 inch M2', 'macbook', 'Apple', 'Thiết kế mỏng nhẹ, pin trâu.', 1);

-- --------------------------------------------------------
-- 3. Dữ liệu PRODUCT_VARIANTS
-- --------------------------------------------------------
INSERT INTO product_variants (id, product_id, variant_sku, color, storage_gb, price, stock, ram_gb, cpu, screen_inch) VALUES
-- iPhone 15 Pro Max (Product_id: 1)
(101, 1, 'IP15PM-BL-256', 'Titanium Black', 256, 34990000.00, 50, NULL, NULL, NULL),
(102, 1, 'IP15PM-WH-512', 'Titanium White', 512, 37990000.00, 30, NULL, NULL, NULL),

-- iPhone 15 (Product_id: 2)
(201, 2, 'IP15-PINK-128', 'Pink', 128, 22990000.00, 100, NULL, NULL, NULL),
(202, 2, 'IP15-BLUE-256', 'Blue', 256, 25990000.00, 80, NULL, NULL, NULL),

-- MacBook Pro M3 Max (Product_id: 3)
(301, 3, 'MBP14-M3-16-512', 'Space Gray', 512, 49990000.00, 15, 16, 'Apple M3 Pro', 14.2),
(302, 3, 'MBP14-M3-36-1T', 'Silver', 1000, 65990000.00, 5, 36, 'Apple M3 Max', 14.2),

-- MacBook Air M2 (Product_id: 4)
(401, 4, 'MBA13-M2-8-256', 'Starlight', 256, 24990000.00, 40, 8, 'Apple M2', 13.6);

-- --------------------------------------------------------
-- 4. Dữ liệu CARTS
-- --------------------------------------------------------
INSERT INTO carts (id, user_id, status) VALUES
(1, 2, 'converted'), -- Giỏ hàng đã chuyển đổi thành đơn hàng (Order #1)
(2, 3, 'active'),    -- Giỏ hàng đang active của Trần Thị B
(3, 2, 'abandoned'); -- Giỏ hàng cũ bị bỏ của Nguyễn Văn A

-- --------------------------------------------------------
-- 5. Dữ liệu CART_ITEMS (cho Cart ID 2)
-- --------------------------------------------------------
INSERT INTO cart_items (cart_id, variant_id, quantity, unit_price) VALUES
(2, 201, 1, 22990000.00), -- 1 iPhone 15 Pink 128GB
(2, 401, 1, 24990000.00); -- 1 MacBook Air M2 Starlight

-- --------------------------------------------------------
-- 6. Dữ liệu ORDERS
-- --------------------------------------------------------
INSERT INTO orders (id, order_no, user_id, cart_id, status, subtotal, shipping_fee, discount_amount, total_amount, shipping_name, shipping_phone, shipping_address, note, created_at) VALUES
(1001, 'AS-20251214-0001', 2, 1, 'completed', 37990000.00, 50000.00, 0.00, 38040000.00, 'Nguyễn Văn A', '0903334444', 'Số 1, đường ABC, Q.1, TP.HCM', NULL, '2025-12-14 10:00:00'),
(1002, 'AS-20251214-0002', 3, NULL, 'pending_payment', 47980000.00, 0.00, 1000000.00, 46980000.00, 'Trần Thị B', '0905556666', 'Số 5, đường XYZ, TP. Hà Nội', 'Giao hàng buổi chiều', '2025-12-14 14:30:00');

-- --------------------------------------------------------
-- 7. Dữ liệu ORDER_ITEMS
-- --------------------------------------------------------
INSERT INTO order_items (order_id, variant_id, product_name, variant_desc, quantity, unit_price, line_total) VALUES
-- Cho Order 1001
(1001, 102, 'iPhone 15 Pro Max', 'Titanium White / 512GB', 1, 37990000.00, 37990000.00),
-- Cho Order 1002
(1002, 301, 'MacBook Pro 14 inch M3 Max', 'Space Gray / 512GB / RAM 16GB / M3 Pro', 1, 49990000.00, 49990000.00),
(1002, 401, 'MacBook Air 13 inch M2', 'Starlight / 256GB / RAM 8GB / M2', 1, 24990000.00, 24990000.00);

-- --------------------------------------------------------
-- 8. Dữ liệu PAYMENTS
-- --------------------------------------------------------
INSERT INTO payments (order_id, method, payment_tier, amount, status, transaction_ref, paid_at) VALUES
-- Payment cho Order 1001 (Đã thanh toán)
(1001, 'online_gateway', 'FULL', 38040000.00, 'paid', 'TXN-ABC-12345', '2025-12-14 10:05:00'),
-- Payment cho Order 1002 (Đang chờ thanh toán)
(1002, 'bank_transfer', 'INSTALLMENT_3', 46980000.00, 'pending', NULL, NULL);

SET FOREIGN_KEY_CHECKS = 1;