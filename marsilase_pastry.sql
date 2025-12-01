-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 24, 2025 at 11:45 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `marsilase_pastry`
--
CREATE DATABASE IF NOT EXISTS `marsilase_pastry` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `marsilase_pastry`;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','moderator') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `full_name`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$6ZnJLsI/.$J9qG6G8p6p6e6p6e6p6e6p6e6p6e6p6e6p6e6p6e6p6e6p6e', 'System Administrator', 'super_admin', 1, '2025-11-21 14:55:43', '2025-11-21 14:55:43');

-- --------------------------------------------------------

--
-- Table structure for table `cakes`
--

CREATE TABLE `cakes` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT 0.00,
  `color` varchar(7) DEFAULT '#C2865A',
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  `serves` int(11) DEFAULT 4,
  `preparation_time` varchar(50) DEFAULT '2-4 hours',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cakes`
--

INSERT INTO `cakes` (`id`, `name`, `description`, `image_url`, `price`, `discount_price`, `color`, `is_active`, `is_featured`, `category_id`, `serves`, `preparation_time`, `created_at`, `updated_at`) VALUES
(1, 'Classic Torta', 'Traditional layered torta with buttercream. Perfect for birthdays and celebrations with elegant design.', './image/Classic Torta.jpg', 1800.00, 1600.00, '#7B3F00', 1, 1, 2, 8, '2-4 hours', '2025-11-21 14:55:43', '2025-11-21 14:55:43'),
(2, 'Chocolate Torta', 'Rich chocolate torta with ganache. Multiple layers of chocolate cake with chocolate buttercream.', './image/Chocolate Torta.jpg', 2200.00, 0.00, '#654321', 1, 1, 2, 10, '2-4 hours', '2025-11-21 14:55:43', '2025-11-21 14:55:43'),
(3, 'Fruit Torta', 'Fresh fruit torta with whipped cream. Light sponge cake topped with seasonal fresh fruits.', './image/Fruit Torta.jpg', 2000.00, 1800.00, '#FF69B4', 1, 1, 2, 6, '2-4 hours', '2025-11-21 14:55:43', '2025-11-21 14:55:43'),
(4, 'Chocolate Mini Cake', 'Rich chocolate mini cake with chocolate ganache. Dense, moist chocolate cake with smooth ganache topping.', './image/Chocolate Cake Slice.jpg', 150.00, 0.00, '#7B3F00', 1, 1, 4, 1, '2-4 hours', '2025-11-21 14:55:43', '2025-11-21 14:55:43'),
(5, 'Red Velvet Mini Cake', 'Classic red velvet mini cake with cream cheese. Vibrant red cake with tangy cream cheese frosting.', './image/Red Velvet Slice.jpg', 160.00, 140.00, '#DC143C', 1, 1, 4, 1, '2-4 hours', '2025-11-21 14:55:43', '2025-11-21 14:55:43'),
(6, 'Cheesecake Mini', 'Creamy cheesecake mini with berry compote. Smooth and rich with graham cracker crust and fresh berries.', './image/Cheesecake Slice.jpg', 170.00, 150.00, '#FFB6C1', 1, 1, 4, 1, '2-4 hours', '2025-11-21 14:55:43', '2025-11-21 14:55:43');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image_url`, `is_active`, `display_order`, `created_at`) VALUES
(1, 'Cookies', 'Delicious homemade cookies baked fresh daily with love and premium ingredients', NULL, 1, 1, '2025-11-21 14:55:43'),
(2, 'Torta Cake', 'Special torta cakes for celebrations, custom-designed for your special occasions', NULL, 1, 2, '2025-11-21 14:55:43'),
(3, 'Arabian Sweets', 'Traditional Arabian sweets and desserts crafted with authentic recipes', NULL, 1, 3, '2025-11-21 14:55:43'),
(4, 'Mini cakes', 'Adorable mini cakes perfect for individual servings or small gatherings', NULL, 1, 4, '2025-11-21 14:55:43');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(255) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(255) NOT NULL,
  `delivery_address` text NOT NULL,
  `delivery_date` date NOT NULL,
  `delivery_time` varchar(50) DEFAULT '09:00-12:00',
  `special_instructions` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','preparing','ready','out_for_delivery','delivered','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `customer_name`, `customer_phone`, `delivery_address`, `delivery_date`, `delivery_time`, `special_instructions`, `total_amount`, `status`, `payment_status`, `created_at`, `updated_at`) VALUES
(11, 'ORD-20251124114154-202303', 'Noble Fedlu', '0967318674', 'Addis Ababa\r\nNot Available', '2025-11-25', '09:00-12:00', '', 250.00, 'pending', 'pending', '2025-11-24 10:41:54', '2025-11-24 10:41:54');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_type` enum('cake','product') NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `flavor` varchar(255) DEFAULT NULL,
  `size` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `special_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_type`, `product_id`, `product_name`, `flavor`, `size`, `quantity`, `unit_price`, `total_price`, `special_notes`) VALUES
(15, 11, 'product', 45, 'Baklava', '0', 'Standard', 1, 200.00, 200.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `owners`
--

CREATE TABLE `owners` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `profile_image` varchar(500) DEFAULT NULL,
  `security_level` enum('full','limited','financial_only') DEFAULT 'full',
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `account_locked_until` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owners`
--

INSERT INTO `owners` (`id`, `username`, `password_hash`, `full_name`, `email`, `phone`, `profile_image`, `security_level`, `two_factor_enabled`, `two_factor_secret`, `last_login`, `login_attempts`, `account_locked_until`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'owner', '$2y$10$Gr5uDalB9V.Yfz5jIFYRseG6WB3dIStcvr3BRszUqNYXYbghhzKrG', 'Marsilase Owner', 'owner@marsilase.com', NULL, NULL, 'full', 0, NULL, '2025-11-22 12:34:36', 0, NULL, 1, NULL, '2025-11-21 14:55:43', '2025-11-22 12:34:36'),
(2, 'backup_owner', '$2y$10$Gr5uDalB9V.Yfz5jIFYRseG6WB3dIStcvr3BRszUqNYXYbghhzKrG', 'Backup Owner', 'backup@marsilase.com', NULL, NULL, 'full', 0, NULL, '2025-11-22 11:36:47', 0, NULL, 1, NULL, '2025-11-21 14:55:43', '2025-11-22 11:36:47'),
(3, 'finance_owner', '$2y$10$Gr5uDalB9V.Yfz5jIFYRseG6WB3dIStcvr3BRszUqNYXYbghhzKrG', 'Finance Manager', 'finance@marsilase.com', NULL, NULL, 'financial_only', 0, NULL, '2025-11-22 12:05:45', 0, NULL, 1, NULL, '2025-11-21 14:55:43', '2025-11-22 12:05:45');

-- --------------------------------------------------------

--
-- Table structure for table `owner_permissions`
--

CREATE TABLE `owner_permissions` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `permission_key` varchar(255) NOT NULL,
  `permission_value` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `owner_permissions`
--

INSERT INTO `owner_permissions` (`id`, `owner_id`, `permission_key`, `permission_value`, `created_at`) VALUES
(1, 1, 'manage_products', 1, '2025-11-21 14:55:43'),
(2, 1, 'manage_admins', 1, '2025-11-21 14:55:43'),
(3, 1, 'view_reports', 1, '2025-11-21 14:55:43'),
(4, 1, 'manage_orders', 1, '2025-11-21 14:55:43'),
(5, 1, 'system_settings', 1, '2025-11-21 14:55:43'),
(6, 1, 'financial_reports', 1, '2025-11-21 14:55:43'),
(7, 2, 'manage_products', 0, '2025-11-21 14:55:43'),
(8, 2, 'view_reports', 0, '2025-11-21 14:55:43'),
(9, 2, 'manage_orders', 0, '2025-11-21 14:55:43'),
(10, 2, 'financial_reports', 0, '2025-11-21 14:55:43'),
(11, 3, 'view_reports', 0, '2025-11-21 14:55:43'),
(12, 3, 'financial_reports', 0, '2025-11-21 14:55:43'),
(13, 3, 'manage_products', 0, '2025-11-22 11:12:21'),
(14, 3, 'view_reports', 0, '2025-11-22 11:12:21'),
(15, 3, 'financial_reports', 0, '2025-11-22 11:12:21'),
(16, 3, 'view_reports', 0, '2025-11-22 11:12:30'),
(17, 3, 'financial_reports', 0, '2025-11-22 11:12:30'),
(18, 3, 'view_reports', 0, '2025-11-22 11:14:29'),
(19, 3, 'system_settings', 0, '2025-11-22 11:14:29'),
(20, 3, 'financial_reports', 0, '2025-11-22 11:14:29'),
(21, 3, 'manage_admins', 0, '2025-11-22 11:14:43'),
(22, 3, 'view_reports', 0, '2025-11-22 11:14:43'),
(23, 3, 'system_settings', 0, '2025-11-22 11:14:43'),
(24, 3, 'financial_reports', 0, '2025-11-22 11:14:43'),
(25, 3, 'view_reports', 1, '2025-11-22 11:15:57'),
(26, 3, 'financial_reports', 1, '2025-11-22 11:15:57'),
(27, 2, 'view_reports', 0, '2025-11-22 11:16:59'),
(28, 2, 'financial_reports', 0, '2025-11-22 11:16:59'),
(38, 2, 'manage_admins', 1, '2025-11-22 11:36:34'),
(39, 2, 'view_reports', 1, '2025-11-22 11:36:34'),
(40, 2, 'manage_orders', 1, '2025-11-22 11:36:34'),
(41, 2, 'financial_reports', 1, '2025-11-22 11:36:34');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'Cookies',
  `image_path` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `category_id`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES
(20, 'Brownie Minis', 'Rich chocolate brownies, soft-center, fudgy.', 75.00, 4, 'Mini cakes', 'uploads/products/6920871b66269_Brownie Minis.jpg', 1, '2025-11-21 15:36:59', '2025-11-21 15:36:59'),
(21, 'Soft Mini Cake', 'Fluffy mini butter cakes with light glaze.', 76.00, 4, '0', 'uploads/products/692089c197da0_Soft cakes.jpg', 1, '2025-11-21 15:48:17', '2025-11-21 16:19:29'),
(22, 'Milk Cake Minis', 'Mini tres leches-style cakes soaked in sweet milk.', 80.00, 4, 'Mini cakes', 'uploads/products/69208a3788730_Milk Cake Minis.jpg', 1, '2025-11-21 15:50:15', '2025-11-21 15:50:15'),
(23, 'Opera Mini Cake', 'Thin coffee-soaked layers with chocolate ganache and buttercream.', 75.00, 4, 'Mini cakes', 'uploads/products/69208ab8c68e7_Opera Mini Cake.jpg', 1, '2025-11-21 15:52:24', '2025-11-21 15:52:24'),
(25, 'English Mini Cake', 'Classic butter cakes with dried fruits or vanilla notes.', 90.00, 4, '0', 'uploads/products/69208cf88f33d_English Mini Cake.jpg', 1, '2025-11-21 16:02:00', '2025-11-21 16:18:57'),
(26, 'Tiramisu Minis', 'Coffee-soaked mini sponge layered with mascarpone cream.', 70.00, 4, 'Mini cakes', 'uploads/products/692422ac05783_Tiramisu Minis.jpg', 1, '2025-11-24 09:17:32', '2025-11-24 09:17:32'),
(27, 'Boxegna ( Cream Puffs )', 'Light choux pastry filled with smooth vanilla cream and topped with a light dusting of sugar or chocolate drizzle. Soft inside, slightly crisp outside, perfect for bite-size desserts.', 80.00, 4, 'Mini cakes', 'uploads/products/6924241ca8fc3_Boxegna.jpg', 1, '2025-11-24 09:23:40', '2025-11-24 09:23:40'),
(28, 'Chocolate Cake', 'Rich chocolate sponge with smooth frosting.', 80.00, 4, 'Mini cakes', 'uploads/products/6924259e6d1a5_Chocolate Mini Cake.jpg', 1, '2025-11-24 09:30:06', '2025-11-24 09:30:06'),
(29, 'Red Velvet', 'Soft cocoa-red cake with cream cheese frosting.', 75.00, 4, 'Mini cakes', 'uploads/products/6924261631e61_Red Velvet Mini.jpg', 1, '2025-11-24 09:32:06', '2025-11-24 09:32:06'),
(31, 'Mousse Cake', 'Light, creamy mousse-based mini desserts.', 80.00, 4, 'Mini cakes', 'uploads/products/69242729cf4a1_Mousse cake.jpg', 1, '2025-11-24 09:36:41', '2025-11-24 09:36:41'),
(32, 'Chocolate Cake', 'Smooth cream-cheese filling over a biscuit base.', 85.00, 4, 'Mini cakes', 'uploads/products/692427e06a32d_Cheesecake Slice.jpg', 1, '2025-11-24 09:39:44', '2025-11-24 09:39:44'),
(33, 'Donuts', 'Soft donuts with glaze or chocolate topping.', 70.00, 4, '0', 'uploads/products/692428614acfd_Donut.jpg', 1, '2025-11-24 09:41:53', '2025-11-24 09:42:16'),
(34, 'Muffin', 'Moist mini muffins—vanilla, chocolate, or blueberry.', 70.00, 4, 'Mini cakes', 'uploads/products/692428d48a1be_Muffin.jpg', 1, '2025-11-24 09:43:48', '2025-11-24 09:43:48'),
(35, 'Birthday Cake', 'Fluffy layered cakes with cream, sprinkles, or custom designs.', 3400.00, 2, 'Torta Cake', 'uploads/products/6924295a52c7f_Birthday Cake.jpg', 1, '2025-11-24 09:46:02', '2025-11-24 09:46:02'),
(36, 'Fondant Cake', 'Smooth fondant-covered celebration cakes with decorations.', 4000.00, 2, '0', 'uploads/products/69242a1ea8a3d_Fondant Cake.jpg', 1, '2025-11-24 09:49:18', '2025-11-24 09:54:38'),
(37, 'Wedding Cake', 'Tiered cakes decorated with elegant themes.', 15000.00, 2, '0', 'uploads/products/69242a9420169_Wedding Cake.jpg', 1, '2025-11-24 09:51:16', '2025-11-24 09:54:20'),
(38, 'Bridal Shower Cake', 'Soft pastel-color designs for bridal parties.', 4500.00, 2, 'Torta Cake', 'uploads/products/69242b41ee491_Bridal Shower Cake.jpg', 1, '2025-11-24 09:54:09', '2025-11-24 09:54:09'),
(39, 'Baby Shower Cake', 'Cute baby-theme cakes in blue/pink.', 5000.00, 2, 'Torta Cake', 'uploads/products/69242c6cee343_Baby Shower Cake.jpg', 1, '2025-11-24 09:59:08', '2025-11-24 09:59:08'),
(40, 'Anniversary Cake', 'Romantic cakes with minimal or luxury decorations.', 5800.00, 2, 'Torta Cake', 'uploads/products/69242cca1ab2e_Anniversary Cake.jpg', 1, '2025-11-24 10:00:42', '2025-11-24 10:00:42'),
(41, 'Difo', 'Classic layered Ethiopian-style special event difo.', 1200.00, 2, '0', 'uploads/products/69242e1b709d2_Torta Difo.jpeg', 1, '2025-11-24 10:06:19', '2025-11-24 10:26:00'),
(42, 'Panettone Cake', 'Soft sweet bread with dried fruits.', 2500.00, 2, 'Torta Cake', 'uploads/products/69242f1591512_Panettone.jpg', 1, '2025-11-24 10:10:29', '2025-11-24 10:10:29'),
(43, 'Kunafa', 'Shredded pastry with cheese or cream soaked in syrup.', 280.00, 3, 'Arabian Sweets', 'uploads/products/69243001346a3_Kunafa.jpg', 1, '2025-11-24 10:14:25', '2025-11-24 10:14:25'),
(44, 'Basbousa', 'Moist semolina cake with coconut and syrup.', 200.00, 3, 'Arabian Sweets', 'uploads/products/69243237be233_Basbousa.jpg', 1, '2025-11-24 10:23:51', '2025-11-24 10:23:51'),
(45, 'Baklava', 'Crispy filo layers with nuts and honey syrup.', 200.00, 3, '0', 'uploads/products/69243272bc3b2_Baklava.jpg', 1, '2025-11-24 10:24:50', '2025-11-24 10:38:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `cakes`
--
ALTER TABLE `cakes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `owners`
--
ALTER TABLE `owners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `owner_permissions`
--
ALTER TABLE `owner_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cakes`
--
ALTER TABLE `cakes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `owners`
--
ALTER TABLE `owners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `owner_permissions`
--
ALTER TABLE `owner_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cakes`
--
ALTER TABLE `cakes`
  ADD CONSTRAINT `cakes_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `owners`
--
ALTER TABLE `owners`
  ADD CONSTRAINT `owners_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `owners` (`id`);

--
-- Constraints for table `owner_permissions`
--
ALTER TABLE `owner_permissions`
  ADD CONSTRAINT `owner_permissions_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;