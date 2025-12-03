-- database.sql
-- this script initializes the mysql database schema for the system.
-- it creates the database if not exists and defines tables with appropriate data types, constraints, and relationships.
-- foreign keys with cascade or set null actions maintain referential integrity during deletions.
-- indexes facilitate efficient querying, particularly for time-based reports and stock monitoring.
-- check constraints (compatible with mysql 8+) prevent invalid data entries, such as negative stock.

CREATE DATABASE IF NOT EXISTS cashier_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE cashier_db;

CREATE TABLE IF NOT EXISTS `user` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('owner', 'admin') DEFAULT 'owner',
  `reset_token` VARCHAR(255),
  `reset_expiry` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `product` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `category` VARCHAR(50),
  `price` DECIMAL(10,2) NOT NULL,
  `stock` INT NOT NULL CHECK (stock >= 0),
  `unit` VARCHAR(50) NOT NULL DEFAULT 'pcs',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_stock` (`stock`)
);

CREATE TABLE IF NOT EXISTS `customer` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_name` VARCHAR(100) NOT NULL,
  `contact_info` VARCHAR(100),
  `total_purchases` DECIMAL(10,2) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `sale` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_type` ENUM('cash', 'credit') NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customer`(`id`) ON DELETE SET NULL,
  INDEX `idx_created_at` (`created_at`)
);

CREATE TABLE IF NOT EXISTS `sale_item` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sale_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL CHECK (quantity > 0),
  `price_at_sale` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`sale_id`) REFERENCES `sale`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `product`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `credit` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `sale_id` INT NOT NULL,
  `amount_owed` DECIMAL(10,2) NOT NULL,
  `amount_paid` DECIMAL(10,2) DEFAULT 0,
  `status` ENUM('unpaid', 'partially_paid', 'paid') DEFAULT 'unpaid',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customer`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`sale_id`) REFERENCES `sale`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `event` VARCHAR(255) NOT NULL,
  `timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `payment_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `credit_id` INT NOT NULL,
  `payment_amount` DECIMAL(10,2) NOT NULL,
  `payment_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`credit_id`) REFERENCES `credit`(`id`) ON DELETE CASCADE
);