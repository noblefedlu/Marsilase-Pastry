<?php
session_start();
require_once '../config.php';

// Clear any existing admin session when accessing login page
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_full_name']);
unset($_SESSION['admin_role']);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        if (!$conn) {
            $error = 'Database connection failed: ' . mysqli_connect_error();
        } else {
            $stmt = $conn->prepare("SELECT id, username, password_hash, full_name, role, is_active FROM admins WHERE username = ?");
            
            if ($stmt === false) {
                $error = 'Failed to prepare statement: ' . $conn->error;
            } else {
                $stmt->bind_param("s", $username);
                
                if (!$stmt->execute()) {
                    $error = 'Failed to execute query: ' . $stmt->error;
                } else {
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows === 1) {
                        $admin = $result->fetch_assoc();
                        
                        if ($admin['is_active'] && password_verify($password, $admin['password_hash'])) {
                            // Successful login - set session variables
                            $_SESSION['admin_logged_in'] = true;
                            $_SESSION['admin_id'] = $admin['id'];
                            $_SESSION['admin_username'] = $admin['username'];
                            $_SESSION['admin_full_name'] = $admin['full_name'];
                            $_SESSION['admin_role'] = $admin['role'];
                            
                            // Redirect to admin dashboard
                            header('Location: index.php');
                            exit;
                        } else {
                            $error = 'Invalid credentials or account inactive';
                            // Log failed attempt
                            error_log("Failed admin login attempt for username: $username");
                        }
                    } else {
                        $error = 'Invalid credentials';
                        // Don't reveal whether username exists
                        error_log("Failed admin login attempt for username: $username");
                    }
                }
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Authentication - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #8B4513;
            --secondary: #D4A574;
            --light: #FFF8F0;
            --dark: #5D4037;
        }
        
        .auth-container {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary) 0%, var(--dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .auth-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
        }
        
        .auth-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--dark) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            border-bottom: 4px solid var(--secondary);
        }
        
        .auth-body {
            padding: 2rem;
            background: white;
        }
        
        .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.2rem rgba(212, 165, 116, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            font-weight: 600;
            padding: 12px;
        }
        
        .btn-primary:hover {
            background-color: var(--dark);
            border-color: var(--dark);
        }
        
        .brand-logo {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .back-to-site {
            background-color: var(--secondary);
            border-color: var(--secondary);
            color: var(--dark);
            font-weight: 600;
        }
        
        .back-to-site:hover {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        
        .security-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
        }
        
        .form-control {
            border-left: none;
        }
        
        .auth-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--secondary);
        }
    </style>
</head>
<body class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h4 class="mb-1 fw-bold">Administrator Access</h4>
            <p class="mb-0 opacity-75">Secure Authentication Required</p>
        </div>
        
        <div class="auth-body">
            <div class="security-notice">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle text-warning me-2 fs-5"></i>
                    <div>
                        <strong class="d-block">Restricted Access Area</strong>
                        <small class="d-block">This portal is for authorized administrators only.</small>
                    </div>
                </div>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <div>
                    <strong>Authentication Failed</strong>
                    <div class="small"><?= htmlspecialchars($error) ?></div>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold">
                        <i class="bi bi-person me-1"></i>Administrator Username
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-person-badge text-primary"></i>
                        </span>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?= htmlspecialchars($username ?? '') ?>" 
                               placeholder="Enter administrator username" required autofocus
                               autocomplete="username">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold">
                        <i class="bi bi-key me-1"></i>Password
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock text-primary"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Enter your secure password" required
                               autocomplete="current-password">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3 fw-bold py-2">
                    <i class="bi bi-shield-check me-2"></i>Authenticate & Proceed
                </button>
                
                <div class="d-grid gap-2">
                    <a href="../index.php" class="btn back-to-site w-100">
                        <i class="bi bi-arrow-left me-2"></i>Return to Main Site
                    </a>
                    <div class="text-center">
                        <a href="register.php" class="text-decoration-none text-primary fw-semibold small">
                            <i class="bi bi-person-plus me-1"></i>Request Administrator Access
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const username = document.getElementById('username');
            const password = document.getElementById('password');
            
            // Focus on username field
            username.focus();
            
            // Add some client-side validation
            loginForm.addEventListener('submit', function(e) {
                let isValid = true;
                
                if (username.value.trim() === '') {
                    isValid = false;
                    username.classList.add('is-invalid');
                } else {
                    username.classList.remove('is-invalid');
                }
                
                if (password.value === '') {
                    isValid = false;
                    password.classList.add('is-invalid');
                } else {
                    password.classList.remove('is-invalid');
                }
                
                if (!isValid) {
                    e.preventDefault();
                    // Show first error field
                    if (username.value.trim() === '') {
                        username.focus();
                    } else if (password.value === '') {
                        password.focus();
                    }
                }
            });
            
            // Remove invalid class when user starts typing
            username.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    this.classList.remove('is-invalid');
                }
            });
            
            password.addEventListener('input', function() {
                if (this.value !== '') {
                    this.classList.remove('is-invalid');
                }
            });
        });
    </script>
</body>
</html>
<?php 
if (isset($conn) && $conn) {
    $conn->close(); 
}
?>