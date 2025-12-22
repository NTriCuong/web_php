USE apple_store;
-- =========================================
-- 1) INSERT PRODUCTS
-- type_id: 1=iPhone, 2=MacBook
-- =========================================
INSERT INTO users (role, user_name, hash_pass, full_name, phone, address, created_at)
VALUES (
  'admin',
  'admin',
  "$2y$10$H6o0Or1tsPq/esGMNXbs.uMl3LrBO4WZqfpBGUME.OObIXsMMFC.K",
  'Quản trị viên',
  '0900000000',
  'Hà Nội',
  NOW()
);

INSERT INTO products (name, type_id, description, active) VALUES
-- iPhone 14 series
('iPhone 14', 1, 'Apple iPhone 14', 1),
('iPhone 14 Plus', 1, 'Apple iPhone 14 Plus', 1),
('iPhone 14 Pro', 1, 'Apple iPhone 14 Pro', 1),
('iPhone 14 Pro Max', 1, 'Apple iPhone 14 Pro Max', 1),

-- iPhone 15 series
('iPhone 15', 1, 'Apple iPhone 15', 1),
('iPhone 15 Plus', 1, 'Apple iPhone 15 Plus', 1),
('iPhone 15 Pro', 1, 'Apple iPhone 15 Pro', 1),
('iPhone 15 Pro Max', 1, 'Apple iPhone 15 Pro Max', 1),

-- iPhone 16 series
('iPhone 16', 1, 'Apple iPhone 16', 1),
('iPhone 16 Plus', 1, 'Apple iPhone 16 Plus', 1),
('iPhone 16 Pro', 1, 'Apple iPhone 16 Pro', 1),
('iPhone 16 Pro Max', 1, 'Apple iPhone 16 Pro Max', 1),

-- iPhone 17 series
('iPhone 17', 1, 'Apple iPhone 17', 1),
('iPhone 17 Plus', 1, 'Apple iPhone 17 Plus', 1),
('iPhone 17 Pro', 1, 'Apple iPhone 17 Pro', 1),
('iPhone 17 Pro Max', 1, 'Apple iPhone 17 Pro Max', 1),

-- MacBook Air M1..M5
('MacBook Air M1', 2, 'Apple MacBook Air with M1', 1),
('MacBook Air M2', 2, 'Apple MacBook Air with M2', 1),
('MacBook Air M3', 2, 'Apple MacBook Air with M3', 1),
('MacBook Air M4', 2, 'Apple MacBook Air with M4', 1),
('MacBook Air M5', 2, 'Apple MacBook Air with M5', 1),

-- MacBook Pro M1..M5
('MacBook Pro M1', 2, 'Apple MacBook Pro with M1', 1),
('MacBook Pro M2', 2, 'Apple MacBook Pro with M2', 1),
('MacBook Pro M3', 2, 'Apple MacBook Pro with M3', 1),
('MacBook Pro M4', 2, 'Apple MacBook Pro with M4', 1),
('MacBook Pro M5', 2, 'Apple MacBook Pro with M5', 1);

-- =========================================
-- 2) INSERT VARIANTS (image_url = NULL)
-- Mỗi model 2 variants để test UI: (base + higher)
-- iPhone: color + storage
-- Mac: color + storage + ram
-- =========================================
INSERT INTO product_variants
(product_id, color, color_hex, image_url, storage_gb, ram_gb, price, stock, active)
SELECT p.id, v.color, v.color_hex, NULL, v.storage_gb, v.ram_gb, v.price, v.stock, 1
FROM products p
JOIN (
  -- ======================
  -- iPhone 14 series
  -- ======================
  SELECT 'iPhone 14' pname, 'Midnight' color, '#111827' color_hex, 128 storage_gb, NULL ram_gb, 16990000.00 price, 50 stock
  UNION ALL SELECT 'iPhone 14', 'Starlight', '#F5F5F7', 256, NULL, 18990000.00, 40

  UNION ALL SELECT 'iPhone 14 Plus', 'Midnight', '#111827', 128, NULL, 18990000.00, 40
  UNION ALL SELECT 'iPhone 14 Plus', 'Starlight', '#F5F5F7', 256, NULL, 20990000.00, 30

  UNION ALL SELECT 'iPhone 14 Pro', 'Space Black', '#111111', 128, NULL, 23990000.00, 30
  UNION ALL SELECT 'iPhone 14 Pro', 'Silver', '#D1D5DB', 256, NULL, 26990000.00, 20

  UNION ALL SELECT 'iPhone 14 Pro Max', 'Space Black', '#111111', 256, NULL, 28990000.00, 25
  UNION ALL SELECT 'iPhone 14 Pro Max', 'Silver', '#D1D5DB', 512, NULL, 32990000.00, 15

  -- ======================
  -- iPhone 15 series
  -- ======================
  UNION ALL SELECT 'iPhone 15', 'Black', '#111111', 128, NULL, 18990000.00, 60
  UNION ALL SELECT 'iPhone 15', 'Pink',  '#F9A8D4', 256, NULL, 21990000.00, 45

  UNION ALL SELECT 'iPhone 15 Plus', 'Black', '#111111', 128, NULL, 20990000.00, 45
  UNION ALL SELECT 'iPhone 15 Plus', 'Blue',  '#93C5FD', 256, NULL, 23990000.00, 30

  UNION ALL SELECT 'iPhone 15 Pro', 'Natural Titanium', '#D4D4D8', 128, NULL, 25990000.00, 30
  UNION ALL SELECT 'iPhone 15 Pro', 'Blue Titanium',    '#60A5FA', 256, NULL, 28990000.00, 20

  UNION ALL SELECT 'iPhone 15 Pro Max', 'Natural Titanium', '#D4D4D8', 256, NULL, 29990000.00, 25
  UNION ALL SELECT 'iPhone 15 Pro Max', 'Black Titanium',   '#111111', 512, NULL, 34990000.00, 15

  -- ======================
  -- iPhone 16 series
  -- ======================
  UNION ALL SELECT 'iPhone 16', 'Black', '#111111', 128, NULL, 20990000.00, 60
  UNION ALL SELECT 'iPhone 16', 'White', '#F5F5F7', 256, NULL, 23990000.00, 45

  UNION ALL SELECT 'iPhone 16 Plus', 'Black', '#111111', 128, NULL, 22990000.00, 45
  UNION ALL SELECT 'iPhone 16 Plus', 'White', '#F5F5F7', 256, NULL, 25990000.00, 30

  UNION ALL SELECT 'iPhone 16 Pro', 'Titanium Black', '#111111', 256, NULL, 29990000.00, 25
  UNION ALL SELECT 'iPhone 16 Pro', 'Titanium White', '#F5F5F7', 512, NULL, 34990000.00, 15

  UNION ALL SELECT 'iPhone 16 Pro Max', 'Titanium Black', '#111111', 256, NULL, 32990000.00, 20
  UNION ALL SELECT 'iPhone 16 Pro Max', 'Titanium White', '#F5F5F7', 512, NULL, 37990000.00, 12

  -- ======================
  -- iPhone 17 series
  -- ======================
  UNION ALL SELECT 'iPhone 17', 'Black', '#111111', 256, NULL, 22990000.00, 50
  UNION ALL SELECT 'iPhone 17', 'White', '#F5F5F7', 512, NULL, 27990000.00, 25

  UNION ALL SELECT 'iPhone 17 Plus', 'Black', '#111111', 256, NULL, 24990000.00, 35
  UNION ALL SELECT 'iPhone 17 Plus', 'White', '#F5F5F7', 512, NULL, 29990000.00, 20

  UNION ALL SELECT 'iPhone 17 Pro', 'Titanium Black', '#111111', 256, NULL, 31990000.00, 22
  UNION ALL SELECT 'iPhone 17 Pro', 'Titanium White', '#F5F5F7', 512, NULL, 36990000.00, 12

  UNION ALL SELECT 'iPhone 17 Pro Max', 'Titanium Black', '#111111', 512, NULL, 39990000.00, 10
  UNION ALL SELECT 'iPhone 17 Pro Max', 'Titanium White', '#F5F5F7', 1000, NULL, 46990000.00, 6

  -- ======================
  -- MacBook Air M1..M5
  -- ======================
  UNION ALL SELECT 'MacBook Air M1', 'Space Gray', '#6B7280', 256, 8,  22990000.00, 25
  UNION ALL SELECT 'MacBook Air M1', 'Silver',     '#D1D5DB', 512, 16, 27990000.00, 15

  UNION ALL SELECT 'MacBook Air M2', 'Midnight',   '#111827', 256, 8,  25990000.00, 25
  UNION ALL SELECT 'MacBook Air M2', 'Starlight',  '#F5F5F7', 512, 16, 30990000.00, 15

  UNION ALL SELECT 'MacBook Air M3', 'Space Gray', '#6B7280', 512, 16, 34990000.00, 18
  UNION ALL SELECT 'MacBook Air M3', 'Silver',     '#D1D5DB', 1000, 16, 39990000.00, 10

  UNION ALL SELECT 'MacBook Air M4', 'Space Gray', '#6B7280', 512, 16, 38990000.00, 14
  UNION ALL SELECT 'MacBook Air M4', 'Silver',     '#D1D5DB', 1000, 24, 45990000.00, 8

  UNION ALL SELECT 'MacBook Air M5', 'Space Gray', '#6B7280', 1000, 24, 49990000.00, 10
  UNION ALL SELECT 'MacBook Air M5', 'Silver',     '#D1D5DB', 2000, 32, 59990000.00, 5

  -- ======================
  -- MacBook Pro M1..M5
  -- ======================
  UNION ALL SELECT 'MacBook Pro M1', 'Space Gray', '#6B7280', 512, 16, 32990000.00, 18
  UNION ALL SELECT 'MacBook Pro M1', 'Silver',     '#D1D5DB', 1000, 16, 37990000.00, 10

  UNION ALL SELECT 'MacBook Pro M2', 'Space Gray', '#6B7280', 512, 16, 36990000.00, 16
  UNION ALL SELECT 'MacBook Pro M2', 'Silver',     '#D1D5DB', 1000, 24, 43990000.00, 8

  UNION ALL SELECT 'MacBook Pro M3', 'Space Black','#111111', 1000, 24, 49990000.00, 10
  UNION ALL SELECT 'MacBook Pro M3', 'Silver',     '#D1D5DB', 2000, 32, 59990000.00, 6

  UNION ALL SELECT 'MacBook Pro M4', 'Space Black','#111111', 1000, 24, 54990000.00, 8
  UNION ALL SELECT 'MacBook Pro M4', 'Silver',     '#D1D5DB', 2000, 36, 65990000.00, 5

  UNION ALL SELECT 'MacBook Pro M5', 'Space Black','#111111', 2000, 36, 69990000.00, 6
  UNION ALL SELECT 'MacBook Pro M5', 'Silver',     '#D1D5DB', 4000, 48, 89990000.00, 3
) v ON v.pname = p.name;

-- =========================================
-- 3) CHECK
-- =========================================
SELECT p.id, p.name, pv.id variant_id, pv.color, pv.color_hex, pv.storage_gb, pv.ram_gb, pv.price, pv.image_url
FROM products p
JOIN product_variants pv ON pv.product_id = p.id
ORDER BY p.id, pv.storage_gb, pv.ram_gb;
