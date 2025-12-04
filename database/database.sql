CREATE DATABASE IF NOT EXISTS cashier_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE cashier_db;

-- USER TABLE
CREATE TABLE IF NOT EXISTS `user` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('owner','admin') DEFAULT 'owner',
  `reset_token` VARCHAR(255),
  `reset_expiry` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- PRODUCT TABLE
CREATE TABLE IF NOT EXISTS `product` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `product_name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `category` VARCHAR(50),
  `price` DECIMAL(10,2) NOT NULL,
  `stock` INT NOT NULL CHECK (`stock` >= 0),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `unit` VARCHAR(50) NOT NULL DEFAULT 'pcs',
  INDEX `idx_stock` (`stock`),
  FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE
);

-- CUSTOMER TABLE
CREATE TABLE IF NOT EXISTS `customer` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `customer_name` VARCHAR(100) NOT NULL,
  `contact_info` VARCHAR(100),
  `tags` VARCHAR(255),
  `total_purchases` DECIMAL(10,2) DEFAULT 0.00,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE
);

-- SALE TABLE
CREATE TABLE IF NOT EXISTS `sale` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `customer_id` INT,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_type` ENUM('cash','credit') NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `cash_tendered` DECIMAL(10,2),
  `change_given` DECIMAL(10,2),
  INDEX `idx_created_at` (`created_at`),
  FOREIGN KEY (`customer_id`) REFERENCES `customer`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE
);

-- SALE ITEM TABLE
CREATE TABLE IF NOT EXISTS `sale_item` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sale_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL CHECK (`quantity` > 0),
  `price_at_sale` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`sale_id`) REFERENCES `sale`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `product`(`id`) ON DELETE CASCADE
);

-- CREDIT TABLE
CREATE TABLE IF NOT EXISTS `credit` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `customer_id` INT NOT NULL,
  `sale_id` INT NOT NULL,
  `amount_owed` DECIMAL(10,2) NOT NULL,
  `amount_paid` DECIMAL(10,2) DEFAULT 0.00,
  `status` ENUM('unpaid','partially_paid','paid') DEFAULT 'unpaid',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customer`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`sale_id`) REFERENCES `sale`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE
);

-- PAYMENT HISTORY
CREATE TABLE IF NOT EXISTS `payment_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `credit_id` INT NOT NULL,
  `payment_amount` DECIMAL(10,2) NOT NULL,
  `payment_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`credit_id`) REFERENCES `credit`(`id`) ON DELETE CASCADE
);

-- LOG TABLE
CREATE TABLE IF NOT EXISTS `log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `event` VARCHAR(255) NOT NULL,
  `timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- LEGACY SALES TABLE (KEPT FOR COMPATIBILITY)
CREATE TABLE IF NOT EXISTS `sales` (
  `sale_id` INT AUTO_INCREMENT PRIMARY KEY,
  `sale_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(50),
  `change_amount` DECIMAL(10,2) DEFAULT 0.00,
  `user_id` INT NOT NULL
);
