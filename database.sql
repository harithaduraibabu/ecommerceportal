-- ========================================================
-- Smart E-Commerce Web Application Database Script
-- Database Name: ecommerceportal
-- Technology: MySQL / MariaDB (phpMyAdmin compatible)
-- ========================================================

CREATE DATABASE IF NOT EXISTS `ecommerceportal`;
USE `ecommerceportal`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `products`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `description` TEXT NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `stock` INT NOT NULL DEFAULT 10,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `wishlist`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_user_product` (`user_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `orders`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT NOT NULL,
  `customer_name` VARCHAR(100) NOT NULL,
  `customer_phone` VARCHAR(20) NOT NULL,
  `delivery_address` TEXT NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT 'Cash on Delivery',
  `order_status` ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `order_items`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `quantity` INT NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Seed Data for table `users`
-- Default Admin: admin@ecommerce.com / admin123
-- Default User: rahul@gmail.com / user123
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`) VALUES
(1, 'System Administrator', 'admin@ecommerce.com', '9876543210', '$2y$10$XXfaD8lydhh8KaFcUxPRTOGj/qmZ4QhWrroLk//8PwpTn3VDZ/wzm', 'admin'),
(2, 'Rahul Sharma', 'rahul@gmail.com', '9123456789', '$2y$10$tiMLqzAghrRVw6ST6Pp66ulWYPPd24xx1X4I/hk7OaLRiyOZZtA1e', 'user')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- --------------------------------------------------------
-- Seed Data for table `products`
-- --------------------------------------------------------
INSERT INTO `products` (`id`, `name`, `category`, `price`, `description`, `image`, `stock`) VALUES
(1, 'Wireless Noise-Cancelling Headphones', 'Audio', 2999.00, 'Premium over-ear Bluetooth headphones with active noise cancellation, deep bass, 30-hour battery life, and comfortable memory-foam ear cushions.', 'headphones.jpg', 25),
(2, 'Smart Fitness Watch Series 5', 'Wearables', 3499.00, 'Full touch HD screen smartwatch with continuous heart rate monitoring, SpO2 sensor, sleep tracker, multiple sports modes, and 7-day battery life.', 'smartwatch.jpg', 18),
(3, 'Ultra-Slim Mechanical Keyboard', 'Accessories', 2199.00, 'RGB backlit compact mechanical gaming and typing keyboard with responsive tactile blue switches and durable aluminum frame.', 'keyboard.jpg', 15),
(4, 'Ergonomic Wireless Gaming Mouse', 'Accessories', 1299.00, 'High-precision optical wireless mouse with 6 programmable buttons, adjustable DPI up to 6400, and long-lasting rechargeable battery.', 'mouse.jpg', 30),
(5, 'Portable Bluetooth Speaker (Waterproof)', 'Audio', 1899.00, 'Rugged IPX7 waterproof portable speaker delivering 360-degree stereo sound, punchy bass, and up to 14 hours of continuous playtime.', 'speaker.jpg', 20),
(6, 'Professional 4K Ultra HD Webcam', 'Electronics', 3999.00, 'Crystal clear 4K USB webcam with dual built-in noise-reducing microphones, privacy shutter, and auto low-light correction.', 'webcam.jpg', 12),
(7, 'Fast Wireless Charging Stand 15W', 'Electronics', 1199.00, 'Universal Qi-certified 15W fast wireless charging pad with dual coil design for landscape and portrait phone positioning.', 'charger.jpg', 40),
(8, 'Compact USB-C Multiport Hub', 'Electronics', 1499.00, '7-in-1 aluminum USB-C hub with 4K HDMI port, 3 USB 3.0 ports, SD/MicroSD card reader, and 100W Power Delivery pass-through.', 'hub.jpg', 22)
ON DUPLICATE KEY UPDATE `id`=`id`;

-- --------------------------------------------------------
-- Seed Data for table `orders` & `order_items` (Sample Order)
-- --------------------------------------------------------
INSERT INTO `orders` (`id`, `order_number`, `user_id`, `customer_name`, `customer_phone`, `delivery_address`, `total_amount`, `payment_method`, `order_status`, `created_at`) VALUES
(1, 'ORD-2026-1001', 2, 'Rahul Sharma', '9123456789', 'Flat 402, Sunshine Apartments, MG Road, Bengaluru, Karnataka - 560001', 5198.00, 'Cash on Delivery', 'Processing', NOW() - INTERVAL 2 DAY)
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `subtotal`) VALUES
(1, 1, 1, 'Wireless Noise-Cancelling Headphones', 2999.00, 1, 2999.00),
(2, 1, 3, 'Ultra-Slim Mechanical Keyboard', 2199.00, 1, 2199.00)
ON DUPLICATE KEY UPDATE `id`=`id`;

INSERT INTO `wishlist` (`user_id`, `product_id`) VALUES
(2, 2)
ON DUPLICATE KEY UPDATE `id`=`id`;

