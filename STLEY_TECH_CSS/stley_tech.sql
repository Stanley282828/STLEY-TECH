-- STLEY TECH database
-- Compatible with Hostinger MySQL/MariaDB via phpMyAdmin.
-- Select your Hostinger database first, then import this file.
-- No CREATE DATABASE, DEFINER, stored procedures, or server-specific paths are used.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;


CREATE TABLE IF NOT EXISTS `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(80) NOT NULL,
  `brand` VARCHAR(80) NOT NULL,
  `name` VARCHAR(160) NOT NULL,
  `description` TEXT NULL,
  `price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `currency` CHAR(3) NOT NULL DEFAULT 'PHP',
  `image_main` VARCHAR(255) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_products_slug` (`slug`),
  KEY `idx_products_brand` (`brand`),
  KEY `idx_products_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_variants` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `storage` VARCHAR(30) NOT NULL,
  `color` VARCHAR(50) NOT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `stock_quantity` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_product_variant` (`product_id`, `storage`, `color`),
  KEY `idx_variants_product` (`product_id`),
  CONSTRAINT `fk_variants_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(40) NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `account_type` ENUM('customer','member','admin') NOT NULL DEFAULT 'customer',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_reset_requests` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `username` VARCHAR(100) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(40) NOT NULL,
  `token_hash` VARCHAR(255) NULL,
  `requested_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `used_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_reset_email` (`email`),
  KEY `idx_reset_user` (`user_id`),
  CONSTRAINT `fk_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `orders` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR(30) NOT NULL,
  `user_id` INT UNSIGNED NULL,
  `customer_name` VARCHAR(160) NOT NULL,
  `customer_phone` VARCHAR(40) NOT NULL,
  `customer_email` VARCHAR(190) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `payment_method` ENUM('cod','gcash') NOT NULL,
  `status` ENUM('pending','confirmed','processing','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_orders_number` (`order_number`),
  KEY `idx_orders_email` (`customer_email`),
  KEY `idx_orders_status` (`status`),
  KEY `idx_orders_created` (`created_at`),
  KEY `idx_orders_user` (`user_id`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NULL,
  `variant_id` INT UNSIGNED NULL,
  `product_name` VARCHAR(160) NOT NULL,
  `storage` VARCHAR(30) NOT NULL,
  `color` VARCHAR(50) NOT NULL,
  `addons` VARCHAR(255) NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `line_total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_items_order` (`order_id`),
  KEY `idx_items_product` (`product_id`),
  CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_items_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(160) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `phone` VARCHAR(40) NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('new','read','replied','closed') NOT NULL DEFAULT 'new',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_messages_email` (`email`),
  KEY `idx_messages_status` (`status`),
  KEY `idx_messages_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `products` (`slug`, `brand`, `name`, `description`, `price`, `currency`, `image_main`) VALUES
('asus-rog-phone-9-pro', 'ASUS', 'ROG Phone 9 Pro', 'Gaming phone with AirTrigger controls.', 58990.00, 'PHP', 'rog9_main.jpg'),
('oppo-k13-turbo-pro', 'OPPO', 'K13 Turbo Pro', 'Performance phone with active cooling fan.', 32990.00, 'PHP', 'oppok13_main.jpg'),
('realme-gt-7-pro', 'realme', 'GT 7 Pro', 'Flagship phone with 120W charging.', 37500.00, 'PHP', 'realmegt7_main.jpg'),
('redmagic-11s-pro', 'RedMagic', '11S Pro', 'Gaming phone with a 24,000 RPM cooling fan.', 49500.00, 'PHP', 'redmagic11s_main.jpg');

INSERT IGNORE INTO `product_variants` (`product_id`, `storage`, `color`, `price`, `stock_quantity`) VALUES
(1, '256gb', 'black', 58990.00, 0), (1, '256gb', 'white', 58990.00, 0), (1, '512gb', 'black', 58990.00, 0), (1, '512gb', 'white', 58990.00, 0),
(2, '256gb', 'black', 32990.00, 0), (2, '256gb', 'white', 32990.00, 0), (2, '512gb', 'black', 32990.00, 0), (2, '512gb', 'white', 32990.00, 0),
(3, '256gb', 'black', 37500.00, 0), (3, '256gb', 'white', 37500.00, 0), (3, '512gb', 'black', 37500.00, 0), (3, '512gb', 'white', 37500.00, 0),
(4, '256gb', 'black', 49500.00, 0), (4, '256gb', 'white', 49500.00, 0), (4, '512gb', 'black', 49500.00, 0), (4, '512gb', 'white', 49500.00, 0);

SET FOREIGN_KEY_CHECKS = 1;

-- Import verification queries (optional; run after import):
-- SELECT COUNT(*) AS product_count FROM products;
-- SELECT COUNT(*) AS variant_count FROM product_variants;
-- SHOW TABLES;
