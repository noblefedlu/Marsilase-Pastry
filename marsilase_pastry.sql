-- marsilase_pastry.sql

CREATE DATABASE IF NOT EXISTS marsilase_pastry;
USE marsilase_pastry;

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
    price DECIMAL(10,2) NOT NULL,
    color VARCHAR(255) DEFAULT '#8B4513',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ice_creams (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    color VARCHAR(7) DEFAULT '#D4A574',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE soft_drinks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    color VARCHAR(7) DEFAULT '#FF6B6B',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE hot_drinks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    color VARCHAR(7) DEFAULT '#8B4513',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cake sizes with price multipliers
CREATE TABLE cake_sizes (
    id varchar(255) NOT NULL PRIMARY KEY,
    name varchar(255) NOT NULL,
    priceModifier decimal(5,2) NOT NULL
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
    name VARCHAR(255) NOT NULL
);

-- Orders - SIMPLIFIED VERSION
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(255) UNIQUE NOT NULL,
    customer_name VARCHAR(255) NOT NULL DEFAULT 'Customer',
    customer_phone VARCHAR(255) NOT NULL DEFAULT '0000000000',
    delivery_address TEXT NOT NULL DEFAULT 'Store Pickup',
    delivery_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Order items - SIMPLIFIED VERSION
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_type ENUM('cake', 'ice_cream', 'soft_drink', 'hot_drink') NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    flavor VARCHAR(255) NOT NULL,
    size VARCHAR(255),
    quantity INT DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Insert default data
INSERT INTO admins (username, password_hash, full_name, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'super_admin');

-- Cake sizes with multipliers
INSERT INTO cake_sizes (id, name, priceModifier) VALUES
('small', 'Small (0.5kg)', 1.00),
('medium', 'Medium (1kg)', 2.00),
('large', 'Large (2kg)', 3.00);

INSERT INTO flavors (name, type) VALUES 
('Vanilla', 'cake'), ('Chocolate', 'cake'), ('Strawberry', 'cake'), ('Red Velvet', 'cake'),
('Vanilla', 'ice_cream'), ('Chocolate', 'ice_cream'), ('Strawberry', 'ice_cream'), ('Mint Chocolate', 'ice_cream'),
('Original', 'soft_drink'), ('Cola', 'soft_drink'), ('Orange', 'soft_drink'), ('Lemon', 'soft_drink'),
('Regular', 'hot_drink'), ('Strong', 'hot_drink'), ('Light', 'hot_drink');

INSERT INTO toppings (name) VALUES 
('Chocolate Sauce'), ('Caramel'), ('Nuts'), ('Sprinkles'), ('Whipped Cream'), ('Cherry'), ('Cookie Crumbles');

-- Different prices for different cakes (small size prices)
INSERT INTO cakes (name, description, price, color) VALUES 
('Chocolate Fantasy', 'Rich chocolate cake with creamy chocolate frosting', 75.00, '#7B3F00'),
('Vanilla Dream', 'Classic vanilla cake with buttercream frosting', 60.00, '#F5F5DC'),
('Strawberry Delight', 'Fresh strawberry cake with strawberry filling', 80.00, '#FFB6C1');

INSERT INTO ice_creams (name, description, price, color) VALUES 
('Classic Vanilla', 'Creamy vanilla ice cream', 120.00, '#FFF8DC'),
('Chocolate Heaven', 'Rich chocolate ice cream', 130.00, '#8B4513'),
('Strawberry Swirl', 'Strawberry ice cream with real fruit', 125.00, '#FF69B4');

INSERT INTO soft_drinks (name, description, price, color) VALUES 
('Cola', 'Refreshing cola drink', 50.00, '#8B0000'),
('Orange Fizz', 'Sparkling orange beverage', 45.00, '#FF8C00'),
('Lemon Lime', 'Tangy lemon-lime refreshment', 45.00, '#32CD32');

INSERT INTO hot_drinks (name, description, price, color) VALUES 
('Espresso', 'Strong black coffee', 60.00, '#4B3621'),
('Cappuccino', 'Espresso with steamed milk foam', 80.00, '#D2B48C'),
('Hot Chocolate', 'Rich chocolate drink', 70.00, '#8B4513');