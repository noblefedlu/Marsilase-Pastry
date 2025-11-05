-- Marsilase Pastry - COMPLETE DATABASE SCHEMA
-- Includes all tables for the main application and enhanced admin system

CREATE DATABASE IF NOT EXISTS marsilase_pastry;
USE marsilase_pastry;

-- Drop tables if they exist (in reverse order due to foreign key constraints)
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS toppings;
DROP TABLE IF EXISTS flavors;
DROP TABLE IF EXISTS cake_sizes;
DROP TABLE IF EXISTS cakes;
DROP TABLE IF EXISTS blocked_ips;
DROP TABLE IF EXISTS failed_login_attempts;
DROP TABLE IF EXISTS admin_logs;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS banners;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS admins;

-- Admins table
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret VARCHAR(255),
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products tables with enhanced columns
CREATE TABLE cakes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    price DECIMAL(10,2) NOT NULL,
    color VARCHAR(7) DEFAULT '#C2865A',
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    category VARCHAR(50) DEFAULT 'general',
    serves INT DEFAULT 4,
    preparation_time VARCHAR(50) DEFAULT '2-4 hours',
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cake sizes with price multipliers
CREATE TABLE cake_sizes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    size VARCHAR(50) NOT NULL,
    description VARCHAR(255),
    price DECIMAL(10,2) NOT NULL,
    weight VARCHAR(50),
    serves_min INT DEFAULT 2,
    serves_max INT DEFAULT 4,
    is_active BOOLEAN DEFAULT TRUE
);

-- Flavors
CREATE TABLE flavors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    type ENUM('cake', 'ice_cream', 'soft_drink', 'hot_drink') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Toppings
CREATE TABLE toppings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    category ENUM('sauce', 'fruit', 'nut', 'cream', 'decoration') DEFAULT 'decoration'
);

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(255) UNIQUE NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    delivery_address TEXT NOT NULL,
    delivery_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    delivery_time VARCHAR(50) DEFAULT '09:00-12:00',
    special_instructions TEXT,
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT 'cash',
    payment_notes TEXT,
    refund_amount DECIMAL(10,2) DEFAULT 0,
    refund_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Order items table
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_type ENUM('cake', 'ice_cream', 'soft_drink', 'hot_drink') NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    flavor VARCHAR(255),
    size VARCHAR(255),
    special_notes TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Contact messages table
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Banners table for content management
CREATE TABLE banners (
    id INT PRIMARY KEY AUTO_INCREMENT,
    banner_type ENUM('home', 'promo', 'sidebar', 'popup') DEFAULT 'home',
    image_url VARCHAR(500) NOT NULL,
    title VARCHAR(255),
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Settings table for system configuration
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(255) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admin activity logs
CREATE TABLE admin_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    action VARCHAR(255) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
);

-- Failed login attempts tracking
CREATE TABLE failed_login_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, attempt_time),
    INDEX idx_username_time (username, attempt_time)
);

-- Blocked IPs table
CREATE TABLE blocked_ips (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ip_address VARCHAR(45) UNIQUE NOT NULL,
    reason TEXT,
    blocked_by INT,
    blocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (blocked_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_ip_active (ip_address, is_active)
);

-- =============================================================================
-- INSERT SAMPLE DATA
-- =============================================================================

-- Insert default admin (password: admin123)
INSERT INTO admins (username, password_hash, full_name, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'super_admin'),
('manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Store Manager', 'admin'),
('staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Store Staff', 'moderator');

-- Insert cake sizes with enhanced data
INSERT INTO cake_sizes (size, description, price, weight, serves_min, serves_max) VALUES
('Small', 'Perfect for 2-4 people', 50.00, '0.5kg', 2, 4),
('Medium', 'Ideal for 4-6 people', 100.00, '1kg', 4, 6),
('Large', 'Great for 8-12 people', 180.00, '2kg', 8, 12);

-- Insert flavors
INSERT INTO flavors (name, type) VALUES 
('Vanilla', 'cake'),
('Chocolate', 'cake'),
('Strawberry', 'cake'),
('Red Velvet', 'cake'),
('Lemon', 'cake'),
('Caramel', 'cake'),
('Coffee', 'cake'),
('Vanilla', 'ice_cream'),
('Chocolate', 'ice_cream'),
('Strawberry', 'ice_cream'),
('Mint Chocolate', 'ice_cream'),
('Original', 'soft_drink'),
('Cola', 'soft_drink'),
('Orange', 'soft_drink'),
('Lemon', 'soft_drink'),
('Regular', 'hot_drink'),
('Strong', 'hot_drink'),
('Light', 'hot_drink');

-- Insert toppings with categories
INSERT INTO toppings (name, price, category) VALUES 
('Chocolate Sauce', 50.00, 'sauce'),
('Caramel Drizzle', 45.00, 'sauce'),
('Fresh Fruits', 80.00, 'fruit'),
('Whipped Cream', 30.00, 'cream'),
('Mixed Nuts', 60.00, 'nut'),
('Colorful Sprinkles', 25.00, 'decoration'),
('Cookie Crumbles', 40.00, 'decoration'),
('Edible Flowers', 100.00, 'decoration'),
('Chocolate Chips', 35.00, 'decoration'),
('Coconut Flakes', 30.00, 'decoration');

-- Insert cakes with professional image URLs and stock quantities
INSERT INTO cakes (name, description, image_url, price, color, is_featured, category, serves, stock_quantity) VALUES 
('Chocolate Fantasy', 'Rich chocolate cake with creamy chocolate frosting and decadent toppings. Perfect for chocolate lovers and special celebrations.', 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1200.00, '#7B3F00', TRUE, 'chocolate', 8, 15),
('Vanilla Dream', 'Light vanilla sponge with buttercream frosting and fresh fruit decorations. A classic choice for any occasion.', 'https://images.unsplash.com/photo-1587664278273-8c2c3c8c44f4?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1100.00, '#F5F5DC', TRUE, 'vanilla', 6, 12),
('Red Velvet Delight', 'Classic red velvet with cream cheese frosting and elegant decorations. Perfect for weddings and anniversaries.', 'https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1350.00, '#DC143C', TRUE, 'special', 10, 8),
('Caramel Crunch', 'Moist caramel cake with crunchy toppings and caramel drizzle. A sweet treat for caramel enthusiasts.', 'https://images.unsplash.com/photo-1586985289688-ca3cf47d3e6e?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1250.00, '#D2691E', FALSE, 'caramel', 6, 10),
('Lemon Zest', 'Tangy lemon cake with citrus frosting and lemon zest. Refreshing and perfect for summer occasions.', 'https://images.unsplash.com/photo-1576618148400-f54bed99c9a3?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1150.00, '#FFD700', FALSE, 'fruit', 6, 14),
('Strawberry Bliss', 'Fresh strawberry cake with cream and real strawberry pieces. Bursting with fruity flavors.', 'https://images.unsplash.com/photo-1464305795204-6f5bbfc7fb81?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1300.00, '#FF69B4', TRUE, 'fruit', 8, 9),
('Blueberry Cheesecake', 'Creamy cheesecake with blueberry compote and graham crust. Rich and indulgent dessert experience.', 'https://images.unsplash.com/photo-1535254973040-607b474cb50d?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1400.00, '#4B0082', TRUE, 'special', 8, 6),
('Coffee Mocha', 'Rich coffee-flavored cake with mocha buttercream. The perfect blend for coffee lovers.', 'https://images.unsplash.com/photo-1576402187878-974f70c890a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1280.00, '#654321', FALSE, 'coffee', 6, 11),
('Raspberry White Chocolate', 'White chocolate cake with raspberry filling and cream. Elegant and sophisticated flavor combination.', 'https://images.unsplash.com/photo-1519915028121-7d8e1be4d1b1?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1450.00, '#FFB6C1', TRUE, 'special', 8, 7),
('Tropical Paradise', 'Coconut and pineapple cake with tropical fruit compote. A taste of the tropics in every bite.', 'https://images.unsplash.com/photo-1488477181946-6428a0291777?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1320.00, '#32CD32', FALSE, 'fruit', 8, 13),
('Black Forest', 'Classic German chocolate cake with cherries and whipped cream. Timeless favorite for all ages.', 'https://images.unsplash.com/photo-1571115764595-644a1f56a55c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1380.00, '#2F4F4F', TRUE, 'chocolate', 10, 10),
('Matcha Green Tea', 'Japanese-inspired matcha green tea cake with white chocolate. Unique and refreshing flavor profile.', 'https://images.unsplash.com/photo-1562448079-2d36c1e63c59?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1420.00, '#228B22', FALSE, 'special', 6, 5);

-- Insert sample orders with realistic data
INSERT INTO orders (order_number, customer_name, customer_phone, customer_email, delivery_address, delivery_date, total_amount, status, payment_status) VALUES
('ORD-20231215-ABC123', 'Alice Johnson', '+251-911-223344', 'alice@example.com', 'Bole Road, Addis Ababa', '2023-12-20', 2450.00, 'delivered', 'paid'),
('ORD-20231216-DEF456', 'Michael Bekele', '+251-922-334455', 'michael@example.com', 'Kirkos Subcity, Addis Ababa', '2023-12-22', 1380.00, 'preparing', 'paid'),
('ORD-20231217-GHI789', 'Sarah Tesfaye', '+251-933-445566', 'sarah@example.com', 'Megenagna, Addis Ababa', '2023-12-18', 3200.00, 'ready', 'paid'),
('ORD-20231218-JKL012', 'David Hailu', '+251-944-556677', 'david@example.com', 'Cazanchise, Addis Ababa', '2023-12-25', 1150.00, 'pending', 'pending'),
('ORD-20231219-MNO345', 'Elena Girma', '+251-955-667788', 'elena@example.com', 'Bole Michael, Addis Ababa', '2023-12-21', 2760.00, 'out_for_delivery', 'paid');

-- Insert order items
INSERT INTO order_items (order_id, product_type, product_id, product_name, quantity, unit_price, total_price, flavor, size) VALUES
(1, 'cake', 1, 'Chocolate Fantasy', 2, 1200.00, 2400.00, 'Chocolate', 'Medium'),
(1, 'cake', 3, 'Red Velvet Delight', 1, 1350.00, 1350.00, 'Red Velvet', 'Small'),
(2, 'cake', 12, 'Black Forest', 1, 1380.00, 1380.00, 'Chocolate', 'Medium'),
(3, 'cake', 7, 'Blueberry Cheesecake', 2, 1400.00, 2800.00, 'Vanilla', 'Large'),
(3, 'cake', 2, 'Vanilla Dream', 1, 1100.00, 1100.00, 'Vanilla', 'Small'),
(4, 'cake', 5, 'Lemon Zest', 1, 1150.00, 1150.00, 'Lemon', 'Medium'),
(5, 'cake', 9, 'Raspberry White Chocolate', 2, 1450.00, 2900.00, 'Vanilla', 'Medium');

-- Insert settings
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'Marsilase Pastry', 'text', 'Website name'),
('site_email', 'info@marsilasepastry.com', 'text', 'Contact email'),
('site_phone', '+251-911-223344', 'text', 'Contact phone'),
('site_address', 'Bole Road, Addis Ababa, Ethiopia', 'text', 'Business address'),
('currency', 'ETB', 'text', 'Default currency'),
('timezone', 'Africa/Addis_Ababa', 'text', 'Default timezone'),
('business_hours', '8:00 AM - 10:00 PM', 'text', 'Business operating hours'),
('delivery_fee', '50.00', 'number', 'Default delivery fee'),
('min_order_amount', '200.00', 'number', 'Minimum order amount for delivery'),
('delivery_radius', '20', 'number', 'Delivery radius in kilometers'),
('preparation_time', '45', 'number', 'Average preparation time in minutes'),
('admin_session_timeout', '60', 'number', 'Admin session timeout in minutes'),
('max_login_attempts', '5', 'number', 'Maximum login attempts before lockout'),
('password_min_length', '8', 'number', 'Minimum password length for admin accounts'),
('low_stock_threshold', '10', 'number', 'Low stock alert threshold'),
('meta_title', 'Marsilase Pastry - Premium Cakes & Desserts in Addis Ababa', 'text', 'Default meta title'),
('meta_description', 'Order delicious custom cakes, pastries and desserts in Addis Ababa. Fresh ingredients, beautiful designs, perfect for every occasion.', 'text', 'Default meta description'),
('meta_keywords', 'cakes, pastries, desserts, Addis Ababa, birthday cakes, wedding cakes, custom cakes, Ethiopian bakery', 'text', 'Default meta keywords'),
('role_permissions_super_admin', '["dashboard","orders","products","customers","messages","payments","analytics","content","settings","admin_management","security"]', 'json', 'Super Admin permissions'),
('role_permissions_admin', '["dashboard","orders","products","customers","messages","payments","analytics","content"]', 'json', 'Admin permissions'),
('role_permissions_moderator', '["dashboard","orders","products","customers","messages","analytics"]', 'json', 'Moderator permissions');

-- Insert banners
INSERT INTO banners (banner_type, image_url, title, description, is_active) VALUES
('home', 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80', 'Premium Custom Cakes', 'Order beautiful custom cakes for any occasion. Fresh ingredients, perfect designs.', TRUE),
('home', 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80', 'Fresh Daily Pastries', 'Freshly baked pastries made with love and the finest ingredients.', TRUE),
('promo', 'https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'Special Wedding Offer', 'Get 15% off on wedding cakes ordered this month!', TRUE),
('sidebar', 'https://images.unsplash.com/photo-1576402187878-974f70c890a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80', 'New Coffee Flavors', 'Try our new coffee-infused cake collection', TRUE);

-- Insert contact messages
INSERT INTO contact_messages (name, email, phone, subject, message, status) VALUES
('John Smith', 'john@example.com', '+251-911-112233', 'Wedding Cake Inquiry', 'Hello, I would like to inquire about a wedding cake for 80 people for my wedding next month. What are your available designs and flavors?', 'read'),
('Sarah Johnson', 'sarah@example.com', '+251-922-223344', 'Birthday Cake Order', 'I need a birthday cake for my daughter''s 8th birthday. She loves unicorns and the color pink. Can you create something special?', 'replied'),
('Michael Brown', 'michael@example.com', '+251-933-334455', 'Corporate Event Catering', 'We are planning a corporate event for 150 people and need dessert catering. Can you provide a quote for assorted pastries and cakes?', 'unread'),
('Elena Garcia', 'elena@example.com', '+251-944-445566', 'Allergy Information', 'Do you have gluten-free or vegan cake options? I have dietary restrictions but would love to order a cake for an anniversary.', 'read'),
('Thomas Wilson', 'thomas@example.com', '+251-955-556677', 'Bulk Order Discount', 'I need to order 10 cakes for a community event. Do you offer bulk order discounts?', 'unread');

-- Insert admin activity logs
INSERT INTO admin_logs (admin_id, action, description, ip_address) VALUES
(1, 'Login', 'Admin logged into the system', '192.168.1.100'),
(1, 'Order Update', 'Updated order status to delivered for ORD-20231215-ABC123', '192.168.1.100'),
(1, 'Product Update', 'Updated stock quantity for Chocolate Fantasy', '192.168.1.100'),
(2, 'Login', 'Manager logged into the system', '192.168.1.101'),
(2, 'Message Reply', 'Replied to customer inquiry from Sarah Johnson', '192.168.1.101'),
(3, 'Login', 'Staff member logged into the system', '192.168.1.102'),
(3, 'Order Create', 'Created new order ORD-20231219-MNO345', '192.168.1.102');

-- Insert sample failed login attempts (for security monitoring)
INSERT INTO failed_login_attempts (username, ip_address, user_agent) VALUES
('admin', '192.168.1.200', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
('unknown', '192.168.1.201', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'),
('test', '192.168.1.202', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36');

-- =============================================================================
-- CREATE INDEXES FOR PERFORMANCE
-- =============================================================================

-- Orders indexes
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_created_at ON orders(created_at);
CREATE INDEX idx_orders_payment_status ON orders(payment_status, created_at);
CREATE INDEX idx_orders_customer_phone ON orders(customer_phone);

-- Order items indexes
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_order_items_product_type ON order_items(product_type, product_id);

-- Cakes indexes
CREATE INDEX idx_cakes_category_active ON cakes(category, is_active);
CREATE INDEX idx_cakes_featured ON cakes(is_featured, is_active);
CREATE INDEX idx_cakes_stock ON cakes(stock_quantity, is_active);

-- Contact messages indexes
CREATE INDEX idx_contact_messages_status ON contact_messages(status, created_at);
CREATE INDEX idx_contact_messages_email ON contact_messages(email);

-- Banners indexes
CREATE INDEX idx_banners_type_active ON banners(banner_type, is_active);

-- Admin logs indexes
CREATE INDEX idx_admin_logs_admin_time ON admin_logs(admin_id, created_at);
CREATE INDEX idx_admin_logs_action_time ON admin_logs(action, created_at);

-- Settings indexes
CREATE INDEX idx_settings_key ON settings(setting_key);

-- =============================================================================
-- DISPLAY DATABASE SUMMARY
-- =============================================================================

SELECT '=== MARSILASE PASTRY DATABASE CREATED SUCCESSFULLY ===' AS message;

-- Table counts summary
SELECT 
    'Table Counts Summary' AS section,
    'Admins' AS table_name,
    COUNT(*) AS record_count
FROM admins
UNION ALL SELECT 'Table Counts Summary', 'Cakes', COUNT(*) FROM cakes
UNION ALL SELECT 'Table Counts Summary', 'Orders', COUNT(*) FROM orders
UNION ALL SELECT 'Table Counts Summary', 'Order Items', COUNT(*) FROM order_items
UNION ALL SELECT 'Table Counts Summary', 'Contact Messages', COUNT(*) FROM contact_messages
UNION ALL SELECT 'Table Counts Summary', 'Settings', COUNT(*) FROM settings
UNION ALL SELECT 'Table Counts Summary', 'Banners', COUNT(*) FROM banners;

-- Business overview
SELECT 
    'Business Overview' AS section,
    'Total Revenue (ETB)' AS metric,
    SUM(total_amount) AS value
FROM orders 
WHERE payment_status = 'paid'
UNION ALL SELECT 
    'Business Overview',
    'Pending Orders',
    COUNT(*)
FROM orders 
WHERE status = 'pending'
UNION ALL SELECT 
    'Business Overview',
    'Featured Products',
    COUNT(*)
FROM cakes 
WHERE is_featured = TRUE AND is_active = TRUE
UNION ALL SELECT 
    'Business Overview',
    'Low Stock Items',
    COUNT(*)
FROM cakes 
WHERE stock_quantity <= 10 AND stock_quantity > 0 AND is_active = TRUE
UNION ALL SELECT 
    'Business Overview',
    'Unread Messages',
    COUNT(*)
FROM contact_messages 
WHERE status = 'unread';

-- Product categories summary
SELECT 
    'Product Categories' AS section,
    category,
    COUNT(*) AS product_count,
    SUM(stock_quantity) AS total_stock
FROM cakes 
WHERE is_active = TRUE
GROUP BY category
ORDER BY product_count DESC;

-- Recent orders summary
SELECT 
    'Recent Orders' AS section,
    order_number,
    customer_name,
    total_amount,
    status,
    DATE(created_at) AS order_date
FROM orders 
ORDER BY created_at DESC 
LIMIT 5;

SELECT '=== DATABASE SETUP COMPLETE ===' AS final_message;
SELECT 'Admin Login: username="admin", password="admin123"' AS login_info;
SELECT 'Access your admin panel at: /admin/login.php' AS admin_url;