<?php
// admin/login.php
session_start();

// Define database credentials directly
$db_config = [
    'servername' => 'localhost',
    'username' => 'root',
    'password' => '',
    'dbname' => 'marsilase_pastry'
];

// Create database connection
$conn = new mysqli(
    $db_config['servername'],
    $db_config['username'], 
    $db_config['password'],
    $db_config['dbname']
);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error_message = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Check if connection is valid
    if (!$conn) {
        $error_message = "Database connection failed!";
    } else {
        // Use the correct table name 'admin_users' from your SQL
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ? AND is_active = 1");
        
        // Check if prepare was successful
        if ($stmt === false) {
            $error_message = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("s", $username);
            
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $admin = $result->fetch_assoc();
                    
                    // Verify password - using password_hash column from your SQL
                    if (password_verify($password, $admin['password_hash'])) {
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_id'] = $admin['id'];
                        $_SESSION['admin_name'] = $admin['full_name'];
                        $_SESSION['admin_username'] = $admin['username'];
                        $_SESSION['admin_email'] = $admin['email'];
                        $_SESSION['admin_role'] = $admin['role'];
                        
                        // Update last login
                        $update_stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                        if ($update_stmt) {
                            $update_stmt->bind_param("i", $admin['id']);
                            $update_stmt->execute();
                            $update_stmt->close();
                        }
                        
                        // Log login activity
                        logAdminAction($conn, $admin['id'], 'Login', 'Admin logged into the system');
                        
                        header('Location: dashboard.php');
                        exit;
                    }
                }
                
                $error_message = "Invalid username or password!";
                logAdminAction($conn, null, 'Failed Login Attempt', "Failed login attempt for username: $username");
            } else {
                $error_message = "Login failed. Please try again.";
            }
            
            $stmt->close();
        }
    }
}

// Function to log admin actions
function logAdminAction($conn, $admin_id, $action, $description) {
    $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $stmt->bind_param("isss", $admin_id, $action, $description, $ip_address);
        $stmt->execute();
        $stmt->close();
    }
}

// Simple sanitize function
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #f56e10 0%, #f8fafc 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .brand-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .brand-logo i {
            font-size: 3rem;
            color: #f56e10;
            margin-bottom: 1rem;
        }
        
        .form-control {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #f56e10;
            box-shadow: 0 0 0 3px rgba(245, 110, 16, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #f56e10 0%, #e7540a 100%);
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 110, 16, 0.3);
        }
        
        .register-link {
            color: #f56e10;
            text-decoration: none;
            font-weight: 500;
        }
        
        .register-link:hover {
            color: #e7540a;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="brand-logo">
                <i class="bi bi-cake2"></i>
                <h3 class="fw-bold">Marsilase Pastry</h3>
                <p class="text-muted">Admin Panel</p>
            </div>
            
            <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($error_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <form method="POST" id="loginForm">
                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-person text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="username" name="username" required 
                               placeholder="Enter your username" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-lock text-muted"></i>
                        </span>
                        <input type="password" class="form-control border-start-0" id="password" name="password" required 
                               placeholder="Enter your password">
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>
            </form>
            
            <div class="text-center mt-4">
                <a href="admin-register.php" class="register-link">
                    <i class="bi bi-person-plus me-1"></i>Create Admin Account
                </a>
            </div>
            
            <div class="text-center mt-2">
                <small class="text-muted">
                    Default: admin / admin123
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing in...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>