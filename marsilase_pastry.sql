-- Marsilase Pastry Database Schema
-- Updated with professional color scheme and enhanced data

CREATE DATABASE IF NOT EXISTS marsilase_pastry;
USE marsilase_pastry;

-- Drop tables if they exist (in reverse order due to foreign key constraints)
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS toppings;
DROP TABLE IF EXISTS flavors;
DROP TABLE IF EXISTS cake_sizes;
DROP TABLE IF EXISTS hot_drinks;
DROP TABLE IF EXISTS soft_drinks;
DROP TABLE IF EXISTS ice_creams;
DROP TABLE IF EXISTS cakes;
DROP TABLE IF EXISTS admins;

-- Admins table
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products tables
CREATE TABLE cakes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    price DECIMAL(10,2) NOT NULL,
    color VARCHAR(7) DEFAULT '#C2865A',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ice_creams (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    price DECIMAL(10,2) NOT NULL,
    color VARCHAR(7) DEFAULT '#C2865A',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE soft_drinks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    price DECIMAL(10,2) NOT NULL,
    color VARCHAR(7) DEFAULT '#C2865A',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE hot_drinks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    price DECIMAL(10,2) NOT NULL,
    color VARCHAR(7) DEFAULT '#C2865A',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cake sizes with price multipliers
CREATE TABLE cake_sizes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    size VARCHAR(50) NOT NULL,
    description VARCHAR(255),
    price DECIMAL(10,2) NOT NULL,
    weight VARCHAR(50)
);

-- Flavors
CREATE TABLE flavors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    type ENUM('cake', 'ice_cream', 'soft_drink', 'hot_drink') NOT NULL
);

-- Toppings
CREATE TABLE toppings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) DEFAULT 0.00
);

-- Orders
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(255) UNIQUE NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    delivery_address TEXT NOT NULL,
    customer_address TEXT,
    delivery_date DATE NOT NULL,
    special_instructions TEXT,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Order items
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_type ENUM('cake', 'ice_cream', 'soft_drink', 'hot_drink') NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    flavor VARCHAR(255),
    size VARCHAR(255),
    quantity INT DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Insert default admin
INSERT INTO admins (username, password_hash, full_name, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'super_admin');

-- Insert cake sizes
INSERT INTO cake_sizes (size, description, price, weight) VALUES
('Small', 'Perfect for 2-4 people', 1200.00, '0.5kg'),
('Medium', 'Ideal for 6-8 people', 1800.00, '1kg'),
('Large', 'Great for 10-12 people', 2500.00, '2kg');

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

-- Insert toppings
INSERT INTO toppings (name, price) VALUES 
('Chocolate Sauce', 50.00),
('Caramel Drizzle', 45.00),
('Fresh Fruits', 80.00),
('Whipped Cream', 30.00),
('Nuts', 60.00),
('Sprinkles', 25.00),
('Cookie Crumbles', 40.00),
('Edible Flowers', 100.00);

-- Insert cakes with professional images and updated prices
INSERT INTO cakes (name, description, image_url, price, color) VALUES 
('Chocolate Fantasy', 'Rich chocolate cake with creamy chocolate frosting and decadent toppings', 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1200.00, '#7B3F00'),
('Vanilla Dream', 'Light vanilla sponge with buttercream frosting and fresh fruit decorations', 'https://images.unsplash.com/photo-1558306783-4e1738d4dc3c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1100.00, '#F5F5DC'),
('Red Velvet Delight', 'Classic red velvet with cream cheese frosting and elegant decorations', 'https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1350.00, '#DC143C'),
('Caramel Crunch', 'Moist caramel cake with crunchy toppings and caramel drizzle', 'https://images.unsplash.com/photo-1586985289688-ca3cf47d3e6e?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1250.00, '#D2691E'),
('Lemon Zest', 'Tangy lemon cake with citrus frosting and lemon zest', 'https://images.unsplash.com/photo-1571115764595-644a1f56a55c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1150.00, '#FFD700'),
('Strawberry Bliss', 'Fresh strawberry cake with cream and real strawberry pieces', 'https://images.unsplash.com/photo-1519861155730-0a1d6d4ebf02?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1300.00, '#FF69B4'),
('Blueberry Cheesecake', 'Creamy cheesecake with blueberry compote and graham crust', 'https://images.unsplash.com/photo-1535254973040-607b474cb50d?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1400.00, '#4B0082'),
('Coffee Mocha', 'Rich coffee-flavored cake with mocha buttercream', 'https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1280.00, '#654321'),
('Raspberry White Chocolate', 'White chocolate cake with raspberry filling and cream', 'https://images.unsplash.com/photo-1571115764595-644a1f56a55c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 1450.00, '#FFB6C1');

-- Insert sample ice creams
INSERT INTO ice_creams (name, description, image_url, price, color) VALUES 
('Classic Vanilla', 'Creamy vanilla ice cream made with real vanilla beans', 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 350.00, '#FFF8DC'),
('Chocolate Heaven', 'Rich chocolate ice cream with chocolate chunks', 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 380.00, '#8B4513'),
('Strawberry Swirl', 'Strawberry ice cream with real fruit swirl', 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 370.00, '#FF69B4');

-- Insert sample soft drinks
INSERT INTO soft_drinks (name, description, image_url, price, color) VALUES 
('Cola', 'Refreshing cola drink with perfect carbonation', 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 120.00, '#8B0000'),
('Orange Fizz', 'Sparkling orange beverage with natural flavor', 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 110.00, '#FF8C00'),
('Lemon Lime', 'Tangy lemon-lime refreshment', 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 110.00, '#32CD32');

-- Insert sample hot drinks
INSERT INTO hot_drinks (name, description, image_url, price, color) VALUES 
('Espresso', 'Strong black coffee made from premium beans', 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 150.00, '#4B3621'),
('Cappuccino', 'Espresso with steamed milk foam and cocoa', 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 180.00, '#D2B48C'),
('Hot Chocolate', 'Rich chocolate drink with whipped cream', 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 170.00, '#8B4513');

-- Display confirmation message
SELECT 'Marsilase Pastry Database created successfully!' AS message;