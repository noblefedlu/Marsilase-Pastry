-- Marsilase Pastry Database Schema - Complete Version
SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `marsilase_pastry` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `marsilase_pastry`;

-- Table structure for table `admins`
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('super_admin','admin','moderator') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `cakes`
CREATE TABLE `cakes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `color` varchar(20) DEFAULT '#d4af37',
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `cake_sizes`
CREATE TABLE `cake_sizes` (
  `id` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `priceModifier` decimal(3,2) DEFAULT 1.00,
  `serves` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `flavors`
CREATE TABLE `flavors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `type` enum('cake') NOT NULL DEFAULT 'cake',
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_name` (`type`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `toppings`
CREATE TABLE `toppings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `orders`
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `delivery_address` text NOT NULL,
  `customer_address` text,
  `delivery_date` date NOT NULL,
  `delivery_time` varchar(20) DEFAULT NULL,
  `special_instructions` text,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','preparing','out_for_delivery','delivered','cancelled') DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `status` (`status`),
  KEY `delivery_date` (`delivery_date`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `order_items`
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_type` enum('cake') NOT NULL DEFAULT 'cake',
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `flavor` varchar(50) NOT NULL,
  `size` varchar(20) DEFAULT 'N/A',
  `toppings` text,
  `special_notes` text,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_type` (`product_type`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `testimonials`
CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(100) NOT NULL,
  `customer_photo` varchar(255) DEFAULT NULL,
  `rating` int(11) NOT NULL DEFAULT 5,
  `comment` text NOT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `contacts`
CREATE TABLE `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for table `site_settings`
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default data for cake_sizes
INSERT INTO `cake_sizes` (`id`, `name`, `description`, `priceModifier`, `serves`) VALUES
('small', 'Small (0.5kg)', 'Perfect for 2-4 people', 1.00, 4),
('medium', 'Medium (1kg)', 'Ideal for 6-8 people', 1.80, 8),
('large', 'Large (2kg)', 'Great for 12-16 people', 3.20, 16),
('xlarge', 'Extra Large (3kg)', 'For parties of 20-24 people', 4.50, 24);

-- Insert default data for flavors (cake only)
INSERT INTO `flavors` (`name`, `type`) VALUES
('Vanilla', 'cake'),
('Chocolate', 'cake'),
('Strawberry', 'cake'),
('Red Velvet', 'cake'),
('Lemon', 'cake'),
('Coffee', 'cake'),
('Carrot', 'cake'),
('Marble', 'cake'),
('Coconut', 'cake'),
('Orange', 'cake');

-- Insert default data for toppings
INSERT INTO `toppings` (`name`, `price`) VALUES
('Chocolate Sauce', 0.00),
('Caramel', 0.00),
('Nuts', 0.00),
('Sprinkles', 0.00),
('Whipped Cream', 0.00),
('Fresh Fruits', 0.00),
('Cookie Crumbs', 0.00),
('Edible Flowers', 0.00),
('Chocolate Shavings', 0.00),
('Gold Leaf', 50.00),
('Custom Message', 0.00),
('Birthday Toppers', 25.00);

-- Insert default data for cakes with image URLs
INSERT INTO `cakes` (`name`, `description`, `price`, `image_url`, `color`, `is_featured`, `is_active`) VALUES
('Golden Celebration Cake', 'Rich golden cake with premium ingredients and elegant decoration. Perfect for birthdays and anniversaries.', 450.00, 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80', '#d4af37', 1, 1),
('Chocolate Symphony', 'Decadent chocolate cake with multiple layers of chocolate goodness. A chocolate lover''s dream come true.', 380.00, 'https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80', '#8b4513', 1, 1),
('Strawberry Dream', 'Light sponge cake with fresh strawberries and cream. Refreshing and perfect for summer celebrations.', 420.00, 'https://images.unsplash.com/photo-1464305795204-6f5bbfc7fb81?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80', '#e91e63', 1, 1),
('Red Velvet Delight', 'Classic red velvet with cream cheese frosting. Elegant and timeless choice for any occasion.', 400.00, 'https://images.unsplash.com/photo-1586788680434-30d324b2d46f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80', '#c2185b', 0, 1),
('Lemon Drizzle Cake', 'Zesty lemon cake with sweet lemon glaze. Tangy and refreshing with a perfect balance of sweetness.', 350.00, 'https://images.unsplash.com/photo-1576618148400-f54bed99fcfd?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80', '#ffeb3b', 0, 1),
('Carrot Cake Special', 'Moist carrot cake with cream cheese frosting and walnuts. Packed with flavor and texture.', 390.00, 'https://images.unsplash.com/photo-1547412185-5d0b6b482757?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80', '#ff9800', 0, 1),
('Coffee Delight Cake', 'Rich coffee-flavored cake with creamy mocha frosting. Perfect for coffee enthusiasts.', 420.00, 'https://images.unsplash.com/photo-1559620192-032c4bc4674e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80', '#795548', 0, 1),
('Vanilla Dream Cake', 'Classic vanilla cake with buttercream frosting. Simple, elegant, and always a crowd-pleaser.', 370.00, 'https://images.unsplash.com/photo-1558301214-0c68d8bf5b24?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80', '#f5f5f5', 0, 1),
('Berry Bliss Cake', 'Mixed berry cake with fresh fruit toppings. Bursting with natural fruit flavors.', 430.00, 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=600&q=80', '#e91e63', 0, 1);

-- Insert default admin user (password: admin123)
INSERT INTO `admins` (`username`, `password_hash`, `full_name`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'super_admin'),
('manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Store Manager', 'admin'),
('staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Store Staff', 'moderator');

-- Insert sample testimonials
INSERT INTO `testimonials` (`customer_name`, `customer_photo`, `rating`, `comment`, `is_approved`, `is_featured`) VALUES
('Sarah Johnson', 'https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80', 5, 'The Golden Celebration Cake was absolutely stunning! It tasted even better than it looked. Perfect for my daughter''s birthday!', 1, 1),
('Michael Chen', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80', 5, 'Ordered the Chocolate Symphony for our anniversary. The cake was rich, moist, and beautifully decorated. Excellent service!', 1, 1),
('Emily Rodriguez', 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80', 5, 'The Strawberry Dream cake was a hit at our office party! Fresh, light, and not too sweet. Will definitely order again.', 1, 1),
('David Thompson', NULL, 4, 'Great quality cakes and professional service. The Red Velvet was delicious and arrived right on time for the event.', 1, 0),
('Lisa Wang', 'https://images.unsplash.com/photo-1544725176-7c40e5a71c5e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80', 5, 'Amazing attention to detail! The custom message and decorations were exactly what I requested. Highly recommended!', 1, 1);

-- Insert sample orders for demonstration
INSERT INTO `orders` (`order_number`, `customer_name`, `customer_phone`, `customer_email`, `delivery_address`, `delivery_date`, `total_amount`, `status`) VALUES
('ORD-20231201-001', 'Sarah Johnson', '+251911223344', 'sarah.johnson@email.com', 'Bole Road, Addis Ababa, Ethiopia', '2023-12-15', 450.00, 'delivered'),
('ORD-20231202-002', 'Michael Chen', '+251922334455', 'michael.chen@email.com', 'Kazanchis, Addis Ababa, Ethiopia', '2023-12-16', 760.00, 'delivered'),
('ORD-20231203-003', 'Emily Rodriguez', '+251933445566', 'emily.rodriguez@email.com', 'Megenagna, Addis Ababa, Ethiopia', '2023-12-17', 420.00, 'preparing'),
('ORD-20231204-004', 'David Thompson', '+251944556677', 'david.thompson@email.com', 'CMC, Addis Ababa, Ethiopia', '2023-12-18', 800.00, 'confirmed'),
('ORD-20231205-005', 'Lisa Wang', '+251955667788', 'lisa.wang@email.com', 'Bole Michael, Addis Ababa, Ethiopia', '2023-12-19', 390.00, 'pending');

-- Insert sample order items
INSERT INTO `order_items` (`order_id`, `product_type`, `product_id`, `product_name`, `flavor`, `size`, `toppings`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 'cake', 1, 'Golden Celebration Cake', 'Vanilla', 'small', '["Fresh Fruits", "Custom Message"]', 1, 450.00, 450.00),
(2, 'cake', 2, 'Chocolate Symphony', 'Chocolate', 'medium', '["Chocolate Sauce", "Nuts"]', 1, 684.00, 684.00),
(2, 'cake', 3, 'Strawberry Dream', 'Strawberry', 'small', '["Whipped Cream"]', 1, 420.00, 420.00),
(3, 'cake', 3, 'Strawberry Dream', 'Strawberry', 'small', '[]', 1, 420.00, 420.00),
(4, 'cake', 1, 'Golden Celebration Cake', 'Vanilla', 'large', '["Gold Leaf", "Custom Message"]', 1, 1440.00, 1440.00),
(5, 'cake', 6, 'Carrot Cake Special', 'Carrot', 'small', '["Nuts"]', 1, 390.00, 390.00);

-- Insert site settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('store_name', 'Marsilase Pastry', 'text', 'The name of the pastry shop'),
('store_phone', '+251-967-318-674', 'text', 'Primary contact phone number'),
('store_email', 'marsilasepastry@gmail.com', 'text', 'Primary contact email'),
('store_address', 'Narzät, Ethiopia', 'text', 'Physical store address'),
('delivery_fee', '0', 'number', 'Delivery fee amount (0 for free delivery)'),
('free_delivery_threshold', '500', 'number', 'Minimum order amount for free delivery'),
('opening_hours', '{"monday": "9:00 AM - 9:00 PM", "tuesday": "9:00 AM - 9:00 PM", "wednesday": "9:00 AM - 9:00 PM", "thursday": "9:00 AM - 9:00 PM", "friday": "9:00 AM - 10:00 PM", "saturday": "9:00 AM - 10:00 PM", "sunday": "10:00 AM - 8:00 PM"}', 'json', 'Store opening hours'),
('social_media', '{"facebook": "https://facebook.com/marsilasepastry", "instagram": "https://instagram.com/marsilasepastry", "twitter": "https://twitter.com/marsilasepastry"}', 'json', 'Social media links'),
('about_us', 'Founded in 2010, Marsilase Pastry has been dedicated to crafting premium handcrafted cakes that bring joy to every celebration. Our passion for baking and commitment to quality ingredients ensure that each cake is a masterpiece of flavor and design.', 'text', 'About us description');

SET FOREIGN_KEY_CHECKS=1;
COMMIT;