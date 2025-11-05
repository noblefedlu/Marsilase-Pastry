<?php
session_start();

// Define the root directory and config path
$root_dir = dirname(dirname(__FILE__));
$config_path = $root_dir . '/config.php';

// Check if config file exists before requiring it
if (!file_exists($config_path)) {
    die("Configuration file not found. Please check if config.php exists in the root directory.");
}

require_once $config_path;

// Check database connection
if (!$conn) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../?page=admin-login');
    exit;
}

$message = '';
$error = '';

// SQL schema for all required tables
$sql_schema = [
    'admins' => "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    'cakes' => "CREATE TABLE IF NOT EXISTS cakes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        category VARCHAR(50),
        is_featured BOOLEAN DEFAULT FALSE,
        is_active BOOLEAN DEFAULT TRUE,
        image_url VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    'orders' => "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(50) UNIQUE NOT NULL,
        customer_name VARCHAR(100) NOT NULL,
        customer_phone VARCHAR(20) NOT NULL,
        customer_email VARCHAR(100),
        delivery_address TEXT NOT NULL,
        delivery_date DATE NOT NULL,
        delivery_time VARCHAR(50) NOT NULL,
        special_instructions TEXT,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    
    'order_items' => "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_type VARCHAR(50) DEFAULT 'cake',
        product_id INT,
        product_name VARCHAR(100) NOT NULL,
        flavor VARCHAR(50),
        size VARCHAR(50),
        quantity INT NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )",
    
    'contact_messages' => "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        subject VARCHAR(200),
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )"
];

// Sample data
$sample_data = [
    'admins' => [
        "INSERT IGNORE INTO admins (username, password_hash, full_name, role) VALUES 
        ('admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'System Administrator', 'super_admin'),
        ('manager', '" . password_hash('manager123', PASSWORD_DEFAULT) . "', 'Shop Manager', 'admin')"
    ],
    
    'cakes' => [
        "INSERT IGNORE INTO cakes (name, description, price, category, is_featured, image_url) VALUES 
        ('Chocolate Delight', 'Rich chocolate cake with creamy frosting and chocolate shavings', 25.99, 'chocolate', TRUE, 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400'),
        ('Vanilla Dream', 'Classic vanilla cake with buttercream frosting and fresh berries', 22.99, 'vanilla', TRUE, 'https://images.unsplash.com/photo-1559620192-032c4bc4674e?w=400'),
        ('Red Velvet', 'Moist red velvet cake with cream cheese frosting', 28.99, 'special', TRUE, 'https://images.unsplash.com/photo-1586788680434-30d324b2d46f?w=400'),
        ('Fruit Fantasy', 'Light sponge cake with mixed fresh fruits and whipped cream', 24.99, 'fruit', FALSE, 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=400'),
        ('Caramel Swirl', 'Caramel infused cake with caramel drizzle and nuts', 26.99, 'caramel', FALSE, 'https://images.unsplash.com/photo-1563729784474-d77dbb933a9e?w=400')"
    ]
];

// Handle database setup
if (isset($_POST['action']) && $_POST['action'] === 'setup_database') {
    $success_count = 0;
    $error_count = 0;
    
    foreach ($sql_schema as $table => $sql) {
        if ($conn->query($sql)) {
            $success_count++;
        } else {
            $error_count++;
            $error .= "Error creating table $table: " . $conn->error . "<br>";
        }
    }
    
    // Insert sample data
    foreach ($sample_data as $table => $queries) {
        foreach ($queries as $query) {
            if ($conn->query($query)) {
                $success_count++;
            } else {
                $error_count++;
                $error .= "Error inserting sample data into $table: " . $conn->error . "<br>";
            }
        }
    }
    
    if ($error_count === 0) {
        $message = "✅ Database setup completed successfully! Created all tables and sample data.";
    } else {
        $message = "⚠️ Database setup partially completed. $success_count operations successful, $error_count failed.";
    }
}

// Check current table status
$existing_tables = [];
$tables_result = $conn->query("SHOW TABLES");
if ($tables_result) {
    while ($row = $tables_result->fetch_array()) {
        $existing_tables[] = $row[0];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --cream: #FFF5E1;
            --orange: #FF914D;
            --white: #FFFFFF;
            --brown: #3A2E1F;
            --light-orange: #FFE8D6;
            --glass-bg: rgba(255, 255, 255, 0.25);
            --glass-border: rgba(255, 255, 255, 0.18);
        }
        
        body {
            background: var(--cream);
            color: var(--brown);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .setup-card {
            background: linear-gradient(135deg, var(--white) 0%, var(--light-orange) 100%);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            border: none;
        }
        
        .btn-primary {
            background: var(--orange);
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: #E5813D;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 145, 77, 0.4);
        }
        
        .btn-danger {
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
        }
        
        .table-status {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 10px;
        }
        
        .status-exists { background: #198754; }
        .status-missing { background: #dc3545; }
        
        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h2 mb-1">Database Setup</h1>
                        <p class="text-muted mb-0">Initialize and configure your database</p>
                    </div>
                    <a href="../?page=admin-dashboard" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Back to Dashboard
                    </a>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success glass-card border-0 fade-in">
                        <i class="bi bi-check-circle me-2"></i>
                        <?= $message ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger glass-card border-0 fade-in">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <div class="glass-card p-4 fade-in">
                    <h4 class="mb-4"><i class="bi bi-database me-2"></i>Database Status</h4>
                    
                    <div class="setup-card">
                        <h5 class="mb-3">Table Status</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Table Name</th>
                                        <th>Status</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sql_schema as $table => $sql): ?>
                                    <tr>
                                        <td><strong><?= $table ?></strong></td>
                                        <td>
                                            <span class="table-status <?= in_array($table, $existing_tables) ? 'status-exists' : 'status-missing' ?>"></span>
                                            <?= in_array($table, $existing_tables) ? 'Exists' : 'Missing' ?>
                                        </td>
                                        <td class="text-muted small">
                                            <?= match($table) {
                                                'admins' => 'Administrator accounts and permissions',
                                                'cakes' => 'Product catalog and pricing',
                                                'orders' => 'Customer orders and delivery information',
                                                'order_items' => 'Individual items within each order',
                                                'contact_messages' => 'Customer inquiries and feedback',
                                                default => 'System table'
                                            } ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="setup-card">
                        <h5 class="mb-3">Setup Actions</h5>
                        
                        <div class="alert alert-warning mb-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> This will create all necessary database tables and insert sample data. 
                            Existing data will be preserved (using INSERT IGNORE).
                        </div>
                        
                        <form method="POST">
                            <input type="hidden" name="action" value="setup_database">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Database Host</label>
                                        <input type="text" class="form-control" value="<?= defined('DB_HOST') ? htmlspecialchars(constant('DB_HOST'), ENT_QUOTES, 'UTF-8') : '' ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Database Name</label>
                                        <input type="text" class="form-control" value="<?= defined('DB_NAME') ? htmlspecialchars(constant('DB_NAME'), ENT_QUOTES, 'UTF-8') : '' ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="confirmSetup" required>
                                    <label class="form-check-label" for="confirmSetup">
                                        I understand this will modify the database structure
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-gear me-2"></i>
                                    Run Database Setup
                                </button>
                                
                                <a href="?page=admin-debug-orders" class="btn btn-outline-primary">
                                    <i class="bi bi-bug me-2"></i>
                                    Run Diagnostics
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="setup-card">
                        <h5 class="mb-3">Sample Data</h5>
                        <p class="text-muted">The setup will create:</p>
                        <ul class="text-muted">
                            <li>2 administrator accounts (admin/admin123, manager/manager123)</li>
                            <li>5 sample cake products with images</li>
                            <li>Database structure for orders and messages</li>
                        </ul>
                        
                        <div class="mt-3">
                            <h6>Default Login Credentials:</h6>
                            <div class="bg-light rounded p-3">
                                <strong>Super Admin:</strong> admin / admin123<br>
                                <strong>Admin:</strong> manager / manager123
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add confirmation for setup
            const setupForm = document.querySelector('form');
            setupForm.addEventListener('submit', function(e) {
                if (!document.getElementById('confirmSetup').checked) {
                    e.preventDefault();
                    alert('Please confirm that you understand this will modify the database.');
                }
            });
        });
    </script>
</body>
</html>
<?php 
// Close connection only if it exists and is valid
if (isset($conn) && $conn) {
    $conn->close();
}
?>