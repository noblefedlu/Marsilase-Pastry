<?php
session_start();
include '../config.php';

// Redirect if already logged in as admin
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && $_SESSION['admin_role'] === 'admin') {
    header('Location: index.php');
    exit;
}

// Clear any existing session when accessing admin login
session_destroy();
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password_hash, full_name, role, is_active FROM admins WHERE username = ? AND role = 'admin'");
        
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                
                if ($admin['is_active'] && password_verify($password, $admin['password_hash'])) {
                    // Successful login
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_full_name'] = $admin['full_name'];
                    $_SESSION['admin_role'] = $admin['role'];
                    
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Invalid credentials or account inactive';
                }
            } else {
                $error = 'Invalid admin credentials - Contact owner to create an account';
            }
            $stmt->close();
        } else {
            $error = 'Database error';
        }
    }
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
        body {
            background: linear-gradient(135deg, #3498db, #2980b9);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 2.5rem;
            border: none;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-icon {
            font-size: 3.5rem;
            color: #3498db;
            margin-bottom: 1rem;
        }
        
        .login-title {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }
        
        .login-subtitle {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .form-control {
            padding: 12px 15px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            margin-bottom: 1.2rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .btn-login {
            background: #3498db;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            width: 100%;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .access-links {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #ecf0f1;
        }
        
        .access-links a {
            color: #2c3e50;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .access-links a:hover {
            text-decoration: underline;
        }
        
        .password-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #7f8c8d;
            cursor: pointer;
        }
        
        .alert {
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .admin-badge {
            background: #3498db;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-icon">
                    <i class="bi bi-person-gear"></i>
                </div>
                <h2 class="login-title">Admin Access</h2>
                <p class="login-subtitle">Order management panel</p>
                <span class="admin-badge">ADMIN PORTAL</span>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <div class="mb-3">
                    <input type="text" class="form-control" name="username" placeholder="Admin Username" required autofocus>
                </div>

                <div class="mb-3 password-container">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <button type="button" class="password-toggle" id="passwordToggle">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="bi bi-person-gear me-2"></i>Access Admin Panel
                </button>
            </form>

            <!-- <div class="access-links">
                <p class="text-muted mb-2">Need different access?</p>
                <a href="../owner/login.php">
                    <i class="bi bi-shield-lock me-1"></i>Go to Owner Login
                </a>
                <br>
                <a href="register.php" class="mt-2 d-inline-block">
                    <i class="bi bi-person-plus me-1"></i>Register New Owner
                </a>
            </div> -->
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password toggle functionality
            const passwordToggle = document.getElementById('passwordToggle');
            const passwordInput = document.getElementById('password');
            
            passwordToggle.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle eye icon
                if (type === 'password') {
                    this.innerHTML = '<i class="bi bi-eye"></i>';
                } else {
                    this.innerHTML = '<i class="bi bi-eye-slash"></i>';
                }
            });
            
            // Clear form on load
            document.getElementById('loginForm').reset();
        });
    </script>
</body>
</html>
<?php 
if (isset($conn) && $conn) {
    $conn->close(); 
}
?>