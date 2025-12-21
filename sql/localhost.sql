-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 16, 2025 at 01:26 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `apple_store`
--
CREATE DATABASE IF NOT EXISTS `apple_store` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `apple_store`;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('active','converted','abandoned') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'converted', '2025-12-16 12:48:14', '2025-12-16 12:48:14'),
(2, 3, 'active', '2025-12-16 12:48:14', '2025-12-16 12:48:14'),
(3, 2, 'abandoned', '2025-12-16 12:48:14', '2025-12-16 12:48:14');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cart_id` bigint(20) UNSIGNED NOT NULL,
  `variant_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `variant_id`, `quantity`, `unit_price`, `created_at`) VALUES
(1, 2, 201, 1, 22990000.00, '2025-12-16 12:48:14'),
(2, 2, 401, 1, 24990000.00, '2025-12-16 12:48:14');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_no` varchar(32) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `cart_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending_payment','paid','processing','shipped','completed','cancelled') NOT NULL DEFAULT 'pending_payment',
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_name` varchar(120) NOT NULL,
  `shipping_phone` varchar(20) NOT NULL,
  `shipping_address` varchar(255) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_no`, `user_id`, `cart_id`, `status`, `subtotal`, `shipping_fee`, `discount_amount`, `total_amount`, `shipping_name`, `shipping_phone`, `shipping_address`, `note`, `created_at`, `updated_at`) VALUES
(1001, 'AS-20251214-0001', 2, 1, 'completed', 37990000.00, 50000.00, 0.00, 38040000.00, 'Nguyễn Văn A', '0903334444', 'Số 1, đường ABC, Q.1, TP.HCM', NULL, '2025-12-14 10:00:00', '2025-12-16 12:48:14'),
(1002, 'AS-20251214-0002', 3, NULL, 'pending_payment', 47980000.00, 0.00, 1000000.00, 46980000.00, 'Trần Thị B', '0905556666', 'Số 5, đường XYZ, TP. Hà Nội', 'Giao hàng buổi chiều', '2025-12-14 14:30:00', '2025-12-16 12:48:14');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `variant_id` bigint(20) UNSIGNED NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `variant_desc` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `line_total` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `variant_id`, `product_name`, `variant_desc`, `quantity`, `unit_price`, `line_total`) VALUES
(1, 1001, 102, 'iPhone 15 Pro Max', 'Titanium White / 512GB', 1, 37990000.00, 37990000.00),
(2, 1002, 301, 'MacBook Pro 14 inch M3 Max', 'Space Gray / 512GB / RAM 16GB / M3 Pro', 1, 49990000.00, 49990000.00),
(3, 1002, 401, 'MacBook Air 13 inch M2', 'Starlight / 256GB / RAM 8GB / M2', 1, 24990000.00, 24990000.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `method` enum('cod','bank_transfer','online_gateway') NOT NULL,
  `payment_tier` enum('FULL','INSTALLMENT_3','INSTALLMENT_6') NOT NULL DEFAULT 'FULL',
  `amount` decimal(12,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'VND',
  `status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `transaction_ref` varchar(100) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `method`, `payment_tier`, `amount`, `currency`, `status`, `transaction_ref`, `paid_at`, `created_at`) VALUES
(1, 1001, 'online_gateway', 'FULL', 38040000.00, 'VND', 'paid', 'TXN-ABC-12345', '2025-12-14 10:05:00', '2025-12-16 12:48:14'),
(2, 1002, 'bank_transfer', 'INSTALLMENT_3', 46980000.00, 'VND', 'pending', NULL, NULL, '2025-12-16 12:48:14');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(191) NOT NULL,
  `name` varchar(200) NOT NULL,
  `product_type` enum('iphone','macbook') NOT NULL,
  `brand` varchar(50) NOT NULL DEFAULT 'Apple',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `slug`, `name`, `product_type`, `brand`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'iphone-15-pro-max', 'iPhone 15 Pro Max', 'iphone', 'Apple', 'Chip A17 Bionic, camera 48MP.', 1, '2025-12-16 12:48:14', '2025-12-16 12:48:14'),
(2, 'iphone-15', 'iPhone 15', 'iphone', 'Apple', 'Chip A16 Bionic, camera kép.', 1, '2025-12-16 12:48:14', '2025-12-16 12:48:14'),
(3, 'macbook-pro-m3', 'MacBook Pro 14 inch M3', 'macbook', 'Apple', 'Hiệu năng cực cao cho dân chuyên nghiệp.', 1, '2025-12-16 12:48:14', '2025-12-16 13:24:29'),
(4, 'macbook-air-m2', 'MacBook Air 13 inch M2', 'macbook', 'Apple', 'Thiết kế mỏng nhẹ, pin trâu.', 1, '2025-12-16 12:48:14', '2025-12-16 12:48:14');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `variant_sku` varchar(64) NOT NULL,
  `color` varchar(40) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `storage_gb` int(10) UNSIGNED DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `ram_gb` int(10) UNSIGNED DEFAULT NULL,
  `cpu` varchar(80) DEFAULT NULL,
  `screen_inch` decimal(4,1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `variant_sku`, `color`, `image_url`, `storage_gb`, `price`, `stock`, `is_active`, `ram_gb`, `cpu`, `screen_inch`, `created_at`, `updated_at`) VALUES
(101, 1, 'IP15PM-BL-256', 'Titanium Black(#333333)', NULL, 256, 34990000.00, 50, 1, NULL, NULL, NULL, '2025-12-16 12:48:14', '2025-12-16 16:46:50'),
(102, 1, 'IP15PM-WH-512', 'Titanium White(#F5F5F7)', NULL, 512, 37990000.00, 30, 1, NULL, NULL, NULL, '2025-12-16 12:48:14', '2025-12-16 16:46:46'),
(201, 2, 'IP15-PINK-128', 'Pink(#EADAF1)', NULL, 128, 22990000.00, 100, 1, NULL, NULL, NULL, '2025-12-16 12:48:14', '2025-12-16 16:47:17'),
(202, 2, 'IP15-BLUE-256', 'Blue(#A5BEDD)', NULL, 256, 25990000.00, 80, 1, NULL, NULL, NULL, '2025-12-16 12:48:14', '2025-12-16 16:47:29'),
(301, 3, 'MBP14-M3-16-512', 'Space Gray(#B9C49A)', NULL, 512, 49990000.00, 15, 1, 16, 'Apple M3 Pro', 14.2, '2025-12-16 12:48:14', '2025-12-16 16:47:48'),
(302, 3, 'MBP14-M3-36-1T', 'Silver(#A7A7A7)', NULL, 1000, 65990000.00, 5, 1, 36, 'Apple M3 Max', 14.2, '2025-12-16 12:48:14', '2025-12-16 16:49:33'),
(401, 4, 'MBA13-M2-8-256', 'Starlight(#F0E6D3)', NULL, 256, 24990000.00, 40, 1, 8, 'Apple M2', 13.6, '2025-12-16 12:48:14', '2025-12-16 16:50:20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(191) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `status` enum('active','blocked') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `full_name`, `phone`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin@apple.com', 'hashed_password_admin_123', 'Quản Trị Viên', '0901112222', 'admin', 'active', '2025-12-16 12:48:14', '2025-12-16 12:48:14'),
(2, 'customer_a@gmail.com', 'hashed_password_custA_456', 'Nguyễn Văn A', '0903334444', 'customer', 'active', '2025-12-16 12:48:14', '2025-12-16 12:48:14'),
(3, 'customer_b@gmail.com', 'hashed_password_custB_789', 'Trần Thị B', '0905556666', 'customer', 'active', '2025-12-16 12:48:14', '2025-12-16 12:48:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_carts_user_status` (`user_id`,`status`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cart_variant` (`cart_id`,`variant_id`),
  ADD KEY `fk_cart_items_variant` (`variant_id`),
  ADD KEY `idx_cart_items_cart` (`cart_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_no` (`order_no`),
  ADD KEY `fk_orders_cart` (`cart_id`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_created` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_items_variant` (`variant_id`),
  ADD KEY `idx_order_items_order` (`order_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `idx_payments_status` (`status`),
  ADD KEY `idx_payments_method` (`method`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_products_type` (`product_type`),
  ADD KEY `idx_products_active` (`is_active`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `variant_sku` (`variant_sku`),
  ADD KEY `idx_variants_product` (`product_id`),
  ADD KEY `idx_variants_active` (`is_active`),
  ADD KEY `idx_variants_price` (`price`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1003;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=402;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `fk_carts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_cart_items_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cart_items_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_items_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `fk_variants_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
--
-- Database: `DA_cuoiky_php`
--
CREATE DATABASE IF NOT EXISTS `DA_cuoiky_php` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `DA_cuoiky_php`;
--
-- Database: `dbsinhvien`
--
CREATE DATABASE IF NOT EXISTS `dbsinhvien` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `dbsinhvien`;

-- --------------------------------------------------------

--
-- Table structure for table `sinhvien_tbl`
--

CREATE TABLE `sinhvien_tbl` (
  `MaSV` int(11) NOT NULL,
  `TenSV` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `sinhvien_tbl`
--

INSERT INTO `sinhvien_tbl` (`MaSV`, `TenSV`) VALUES
(1, 'Nguyen Ti'),
(2, 'Tran Suu'),
(3, 'Le Dan'),
(9, 'SV9'),
(19, 'SV19'),
(29, 'SV29'),
(12321, 'cuong');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sinhvien_tbl`
--
ALTER TABLE `sinhvien_tbl`
  ADD PRIMARY KEY (`MaSV`);
--
-- Database: `DsSinhVien`
--
CREATE DATABASE IF NOT EXISTS `DsSinhVien` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `DsSinhVien`;
--
-- Database: `EmbeddingDB`
--
CREATE DATABASE IF NOT EXISTS `EmbeddingDB` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `EmbeddingDB`;

-- --------------------------------------------------------

--
-- Table structure for table `Face`
--

CREATE TABLE `Face` (
  `id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Face`
--
ALTER TABLE `Face`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_face` (`id_user`);

--
-- Indexes for table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Face`
--
ALTER TABLE `Face`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Face`
--
ALTER TABLE `Face`
  ADD CONSTRAINT `fk_user_face` FOREIGN KEY (`id_user`) REFERENCES `User` (`id`);
--
-- Database: `phpmyadmin`
--
CREATE DATABASE IF NOT EXISTS `phpmyadmin` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `phpmyadmin`;

-- --------------------------------------------------------

--
-- Table structure for table `pma__bookmark`
--

CREATE TABLE `pma__bookmark` (
  `id` int(11) NOT NULL,
  `dbase` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `query` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Bookmarks';

-- --------------------------------------------------------

--
-- Table structure for table `pma__central_columns`
--

CREATE TABLE `pma__central_columns` (
  `db_name` varchar(64) NOT NULL,
  `col_name` varchar(64) NOT NULL,
  `col_type` varchar(64) NOT NULL,
  `col_length` text DEFAULT NULL,
  `col_collation` varchar(64) NOT NULL,
  `col_isNull` tinyint(1) NOT NULL,
  `col_extra` varchar(255) DEFAULT '',
  `col_default` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Central list of columns';

-- --------------------------------------------------------

--
-- Table structure for table `pma__column_info`
--

CREATE TABLE `pma__column_info` (
  `id` int(5) UNSIGNED NOT NULL,
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `column_name` varchar(64) NOT NULL DEFAULT '',
  `comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `mimetype` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `transformation` varchar(255) NOT NULL DEFAULT '',
  `transformation_options` varchar(255) NOT NULL DEFAULT '',
  `input_transformation` varchar(255) NOT NULL DEFAULT '',
  `input_transformation_options` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Column information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__designer_settings`
--

CREATE TABLE `pma__designer_settings` (
  `username` varchar(64) NOT NULL,
  `settings_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Settings related to Designer';

-- --------------------------------------------------------

--
-- Table structure for table `pma__export_templates`
--

CREATE TABLE `pma__export_templates` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL,
  `export_type` varchar(10) NOT NULL,
  `template_name` varchar(64) NOT NULL,
  `template_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved export templates';

-- --------------------------------------------------------

--
-- Table structure for table `pma__favorite`
--

CREATE TABLE `pma__favorite` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Favorite tables';

-- --------------------------------------------------------

--
-- Table structure for table `pma__history`
--

CREATE TABLE `pma__history` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db` varchar(64) NOT NULL DEFAULT '',
  `table` varchar(64) NOT NULL DEFAULT '',
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp(),
  `sqlquery` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='SQL history for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__navigationhiding`
--

CREATE TABLE `pma__navigationhiding` (
  `username` varchar(64) NOT NULL,
  `item_name` varchar(64) NOT NULL,
  `item_type` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Hidden items of navigation tree';

-- --------------------------------------------------------

--
-- Table structure for table `pma__pdf_pages`
--

CREATE TABLE `pma__pdf_pages` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `page_nr` int(10) UNSIGNED NOT NULL,
  `page_descr` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='PDF relation pages for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__recent`
--

CREATE TABLE `pma__recent` (
  `username` varchar(64) NOT NULL,
  `tables` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Recently accessed tables';

--
-- Dumping data for table `pma__recent`
--

INSERT INTO `pma__recent` (`username`, `tables`) VALUES
('root', '[{\"db\":\"apple_store\",\"table\":\"product_variants\"},{\"db\":\"apple_store\",\"table\":\"products\"},{\"db\":\"apple_store\",\"table\":\"users\"},{\"db\":\"apple_store\",\"table\":\"payments\"},{\"db\":\"apple_store\",\"table\":\"orders\"},{\"db\":\"apple_store\",\"table\":\"carts\"},{\"db\":\"dbsinhvien\",\"table\":\"sinhvien_tbl\"},{\"db\":\"EmbeddingDB\",\"table\":\"Face\"},{\"db\":\"web02\",\"table\":\"SinhVien\"},{\"db\":\"web01\",\"table\":\"Employee\"}]');

-- --------------------------------------------------------

--
-- Table structure for table `pma__relation`
--

CREATE TABLE `pma__relation` (
  `master_db` varchar(64) NOT NULL DEFAULT '',
  `master_table` varchar(64) NOT NULL DEFAULT '',
  `master_field` varchar(64) NOT NULL DEFAULT '',
  `foreign_db` varchar(64) NOT NULL DEFAULT '',
  `foreign_table` varchar(64) NOT NULL DEFAULT '',
  `foreign_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Relation table';

-- --------------------------------------------------------

--
-- Table structure for table `pma__savedsearches`
--

CREATE TABLE `pma__savedsearches` (
  `id` int(5) UNSIGNED NOT NULL,
  `username` varchar(64) NOT NULL DEFAULT '',
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `search_name` varchar(64) NOT NULL DEFAULT '',
  `search_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Saved searches';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_coords`
--

CREATE TABLE `pma__table_coords` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `pdf_page_number` int(11) NOT NULL DEFAULT 0,
  `x` float UNSIGNED NOT NULL DEFAULT 0,
  `y` float UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table coordinates for phpMyAdmin PDF output';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_info`
--

CREATE TABLE `pma__table_info` (
  `db_name` varchar(64) NOT NULL DEFAULT '',
  `table_name` varchar(64) NOT NULL DEFAULT '',
  `display_field` varchar(64) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Table information for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__table_uiprefs`
--

CREATE TABLE `pma__table_uiprefs` (
  `username` varchar(64) NOT NULL,
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `prefs` text NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tables'' UI preferences';

-- --------------------------------------------------------

--
-- Table structure for table `pma__tracking`
--

CREATE TABLE `pma__tracking` (
  `db_name` varchar(64) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `schema_snapshot` text NOT NULL,
  `schema_sql` text DEFAULT NULL,
  `data_sql` longtext DEFAULT NULL,
  `tracking` set('UPDATE','REPLACE','INSERT','DELETE','TRUNCATE','CREATE DATABASE','ALTER DATABASE','DROP DATABASE','CREATE TABLE','ALTER TABLE','RENAME TABLE','DROP TABLE','CREATE INDEX','DROP INDEX','CREATE VIEW','ALTER VIEW','DROP VIEW') DEFAULT NULL,
  `tracking_active` int(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Database changes tracking for phpMyAdmin';

-- --------------------------------------------------------

--
-- Table structure for table `pma__userconfig`
--

CREATE TABLE `pma__userconfig` (
  `username` varchar(64) NOT NULL,
  `timevalue` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `config_data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User preferences storage for phpMyAdmin';

--
-- Dumping data for table `pma__userconfig`
--

INSERT INTO `pma__userconfig` (`username`, `timevalue`, `config_data`) VALUES
('root', '2025-12-16 12:26:14', '{\"lang\":\"en_GB\",\"Console\\/Mode\":\"collapse\"}');

-- --------------------------------------------------------

--
-- Table structure for table `pma__usergroups`
--

CREATE TABLE `pma__usergroups` (
  `usergroup` varchar(64) NOT NULL,
  `tab` varchar(64) NOT NULL,
  `allowed` enum('Y','N') NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='User groups with configured menu items';

-- --------------------------------------------------------

--
-- Table structure for table `pma__users`
--

CREATE TABLE `pma__users` (
  `username` varchar(64) NOT NULL,
  `usergroup` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Users and their assignments to user groups';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pma__central_columns`
--
ALTER TABLE `pma__central_columns`
  ADD PRIMARY KEY (`db_name`,`col_name`);

--
-- Indexes for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `db_name` (`db_name`,`table_name`,`column_name`);

--
-- Indexes for table `pma__designer_settings`
--
ALTER TABLE `pma__designer_settings`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_user_type_template` (`username`,`export_type`,`template_name`);

--
-- Indexes for table `pma__favorite`
--
ALTER TABLE `pma__favorite`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__history`
--
ALTER TABLE `pma__history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`,`db`,`table`,`timevalue`);

--
-- Indexes for table `pma__navigationhiding`
--
ALTER TABLE `pma__navigationhiding`
  ADD PRIMARY KEY (`username`,`item_name`,`item_type`,`db_name`,`table_name`);

--
-- Indexes for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  ADD PRIMARY KEY (`page_nr`),
  ADD KEY `db_name` (`db_name`);

--
-- Indexes for table `pma__recent`
--
ALTER TABLE `pma__recent`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__relation`
--
ALTER TABLE `pma__relation`
  ADD PRIMARY KEY (`master_db`,`master_table`,`master_field`),
  ADD KEY `foreign_field` (`foreign_db`,`foreign_table`);

--
-- Indexes for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_savedsearches_username_dbname` (`username`,`db_name`,`search_name`);

--
-- Indexes for table `pma__table_coords`
--
ALTER TABLE `pma__table_coords`
  ADD PRIMARY KEY (`db_name`,`table_name`,`pdf_page_number`);

--
-- Indexes for table `pma__table_info`
--
ALTER TABLE `pma__table_info`
  ADD PRIMARY KEY (`db_name`,`table_name`);

--
-- Indexes for table `pma__table_uiprefs`
--
ALTER TABLE `pma__table_uiprefs`
  ADD PRIMARY KEY (`username`,`db_name`,`table_name`);

--
-- Indexes for table `pma__tracking`
--
ALTER TABLE `pma__tracking`
  ADD PRIMARY KEY (`db_name`,`table_name`,`version`);

--
-- Indexes for table `pma__userconfig`
--
ALTER TABLE `pma__userconfig`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `pma__usergroups`
--
ALTER TABLE `pma__usergroups`
  ADD PRIMARY KEY (`usergroup`,`tab`,`allowed`);

--
-- Indexes for table `pma__users`
--
ALTER TABLE `pma__users`
  ADD PRIMARY KEY (`username`,`usergroup`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pma__bookmark`
--
ALTER TABLE `pma__bookmark`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__column_info`
--
ALTER TABLE `pma__column_info`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__export_templates`
--
ALTER TABLE `pma__export_templates`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__history`
--
ALTER TABLE `pma__history`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__pdf_pages`
--
ALTER TABLE `pma__pdf_pages`
  MODIFY `page_nr` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pma__savedsearches`
--
ALTER TABLE `pma__savedsearches`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
