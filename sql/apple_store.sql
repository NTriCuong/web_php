	-- Tạo database
	CREATE DATABASE IF NOT EXISTS apple_store
	  CHARACTER SET utf8mb4
	  COLLATE utf8mb4_unicode_ci;

	USE apple_store;

	-- 1) USERS: khách hàng & admin
	CREATE TABLE users (
	  id            BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	  email         VARCHAR(191) NOT NULL UNIQUE,
	  password_hash VARCHAR(255) NOT NULL,
	  full_name     VARCHAR(120) NOT NULL,
	  phone         VARCHAR(20),
	  role          ENUM('customer','admin') NOT NULL DEFAULT 'customer',
	  status        ENUM('active','blocked') NOT NULL DEFAULT 'active',
	  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
	) ENGINE=InnoDB;

	-- 2) PRODUCTS: chỉ 2 type chính: iphone/macbook
	CREATE TABLE products (
	  id          BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	  slug        VARCHAR(191) NOT NULL UNIQUE,
	  name        VARCHAR(200) NOT NULL,
	  product_type ENUM('iphone','macbook') NOT NULL,
	  brand       VARCHAR(50) NOT NULL DEFAULT 'Apple',
	  description TEXT,
	  is_active   TINYINT(1) NOT NULL DEFAULT 1,
	  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	  INDEX idx_products_type (product_type),
	  INDEX idx_products_active (is_active)
	) ENGINE=InnoDB;

	-- 3) PRODUCT_VARIANTS: biến thể theo màu/dung lượng/cấu hình
	CREATE TABLE product_variants (
	  id           BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	  product_id   BIGINT UNSIGNED NOT NULL,
	  variant_sku  VARCHAR(64) NOT NULL UNIQUE,

	  -- Thuộc tính chung
	  color        VARCHAR(40),
	  storage_gb   INT UNSIGNED,     -- iPhone/Mac có thể dùng
	  price        DECIMAL(12,2) NOT NULL,
	  stock        INT NOT NULL DEFAULT 0,
	  is_active    TINYINT(1) NOT NULL DEFAULT 1,

	  -- Thuộc tính riêng cho MacBook (có thể NULL cho iPhone)
	  ram_gb       INT UNSIGNED,
	  cpu          VARCHAR(80),
	  screen_inch  DECIMAL(4,1),

	  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

	  CONSTRAINT fk_variants_product
		FOREIGN KEY (product_id) REFERENCES products(id)
		ON DELETE CASCADE ON UPDATE CASCADE,

	  INDEX idx_variants_product (product_id),
	  INDEX idx_variants_active (is_active),
	  INDEX idx_variants_price (price)
	) ENGINE=InnoDB;

	-- 4) CARTS: 1 user có thể có nhiều giỏ (lịch sử), thường dùng 1 giỏ active
	CREATE TABLE carts (
	  id         BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	  user_id    BIGINT UNSIGNED NOT NULL,
	  status     ENUM('active','converted','abandoned') NOT NULL DEFAULT 'active',
	  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

	  CONSTRAINT fk_carts_user
		FOREIGN KEY (user_id) REFERENCES users(id)
		ON DELETE CASCADE ON UPDATE CASCADE,

	  INDEX idx_carts_user_status (user_id, status)
	) ENGINE=InnoDB;
	-- 5) CART_ITEMS
	CREATE TABLE cart_items (
	  id            BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	  cart_id       BIGINT UNSIGNED NOT NULL,
	  variant_id    BIGINT UNSIGNED NOT NULL,
	  quantity      INT NOT NULL,
	  unit_price    DECIMAL(12,2) NOT NULL, -- snapshot giá lúc bỏ vào giỏ
	  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

	  CONSTRAINT fk_cart_items_cart
		FOREIGN KEY (cart_id) REFERENCES carts(id)
		ON DELETE CASCADE ON UPDATE CASCADE,

	  CONSTRAINT fk_cart_items_variant
		FOREIGN KEY (variant_id) REFERENCES product_variants(id)
		ON DELETE RESTRICT ON UPDATE CASCADE,

	  UNIQUE KEY uq_cart_variant (cart_id, variant_id),
	  INDEX idx_cart_items_cart (cart_id)
	) ENGINE=InnoDB;

	-- 6) ORDERS
	CREATE TABLE orders (
	  id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	  order_no        VARCHAR(32) NOT NULL UNIQUE,
	  user_id         BIGINT UNSIGNED NOT NULL,
	  cart_id         BIGINT UNSIGNED NULL,

	  status          ENUM('pending_payment','paid','processing','shipped','completed','cancelled')
					  NOT NULL DEFAULT 'pending_payment',

	  subtotal        DECIMAL(12,2) NOT NULL DEFAULT 0,
	  shipping_fee    DECIMAL(12,2) NOT NULL DEFAULT 0,
	  discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
	  total_amount    DECIMAL(12,2) NOT NULL DEFAULT 0,

	  shipping_name   VARCHAR(120) NOT NULL,
	  shipping_phone  VARCHAR(20) NOT NULL,
	  shipping_address VARCHAR(255) NOT NULL,
	  note            VARCHAR(255),

	  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

	  CONSTRAINT fk_orders_user
		FOREIGN KEY (user_id) REFERENCES users(id)
		ON DELETE RESTRICT ON UPDATE CASCADE,

	  CONSTRAINT fk_orders_cart
		FOREIGN KEY (cart_id) REFERENCES carts(id)
		ON DELETE SET NULL ON UPDATE CASCADE,

	  INDEX idx_orders_user (user_id),
	  INDEX idx_orders_status (status),
	  INDEX idx_orders_created (created_at)
	) ENGINE=InnoDB;

	-- 7) ORDER_ITEMS: lưu snapshot tên/variant để tránh đổi tên sau này làm sai lịch sử
	CREATE TABLE order_items (
	  id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	  order_id        BIGINT UNSIGNED NOT NULL,
	  variant_id      BIGINT UNSIGNED NOT NULL,

	  product_name    VARCHAR(200) NOT NULL,
	  variant_desc    VARCHAR(255) NOT NULL, -- ví dụ "Black / 256GB / RAM 16GB / M3"
	  quantity        INT NOT NULL,
	  unit_price      DECIMAL(12,2) NOT NULL,
	  line_total      DECIMAL(12,2) NOT NULL,

	  CONSTRAINT fk_order_items_order
		FOREIGN KEY (order_id) REFERENCES orders(id)
		ON DELETE CASCADE ON UPDATE CASCADE,

	  CONSTRAINT fk_order_items_variant
		FOREIGN KEY (variant_id) REFERENCES product_variants(id)
		ON DELETE RESTRICT ON UPDATE CASCADE,

	  INDEX idx_order_items_order (order_id)
	) ENGINE=InnoDB;

	-- 8) PAYMENTS: 3 mức thanh toán (FULL / INSTALLMENT_3 / INSTALLMENT_6)
	CREATE TABLE payments (
	  id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	  order_id        BIGINT UNSIGNED NOT NULL UNIQUE, -- 1 đơn = 1 bản ghi payment (cơ bản)
	  method          ENUM('cod','bank_transfer','online_gateway') NOT NULL,
	  payment_tier    ENUM('FULL','INSTALLMENT_3','INSTALLMENT_6') NOT NULL DEFAULT 'FULL',
	  amount          DECIMAL(12,2) NOT NULL,
	  currency        CHAR(3) NOT NULL DEFAULT 'VND',
	  status          ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
	  transaction_ref VARCHAR(100),
	  paid_at         DATETIME NULL,
	  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

	  CONSTRAINT fk_payments_order
		FOREIGN KEY (order_id) REFERENCES orders(id)
		ON DELETE CASCADE ON UPDATE CASCADE,

	  INDEX idx_payments_status (status),
	  INDEX idx_payments_method (method)
	) ENGINE=InnoDB;