<?php
session_start();
require_once '../config.php';

// Redirect to dashboard if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

// Clear any existing admin session when accessing login page
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_full_name']);
unset($_SESSION['admin_role']);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';
    
    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password';
        } else {
            $stmt = $conn->prepare("SELECT id, username, password_hash, full_name, role, is_active FROM admins WHERE username = ?");
            
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
                        $error = 'Invalid credentials';
                    }
                } else {
                    $error = 'Invalid credentials';
                }
                $stmt->close();
            } else {
                $error = 'Database error';
            }
        }
    } elseif ($action === 'forgot_password') {
        $username = trim($_POST['reset_username'] ?? '');
        
        if (empty($username)) {
            $error = 'Please enter your username';
        } else {
            // Check if username exists
            $stmt = $conn->prepare("SELECT id, username, full_name FROM admins WHERE username = ? AND is_active = 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                
                // Generate a temporary password
                $temp_password = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 8);
                $password_hash = password_hash($temp_password, PASSWORD_DEFAULT);
                
                // Update password in database
                $update_stmt = $conn->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
                $update_stmt->bind_param("si", $password_hash, $admin['id']);
                
                if ($update_stmt->execute()) {
                    $success = "Password reset successful! Your temporary password is: <strong>$temp_password</strong><br>Please login and change it immediately.";
                } else {
                    $error = 'Failed to reset password. Please try again.';
                }
                $update_stmt->close();
            } else {
                $error = 'Username not found or account is inactive';
            }
            $stmt->close();
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
            background: #f8f9fa;
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
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-icon {
            font-size: 3rem;
            color: #8B4513;
            margin-bottom: 1rem;
        }
        
        .login-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: #666;
            font-size: 0.9rem;
        }
        
        .form-control {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        
        .form-control:focus {
            border-color: #8B4513;
            box-shadow: 0 0 0 2px rgba(139, 69, 19, 0.1);
        }
        
        .btn-login {
            background: #8B4513;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            width: 100%;
            font-weight: 500;
            margin-bottom: 1rem;
        }
        
        .btn-login:hover {
            background: #654321;
        }
        
        .btn-forgot {
            background: transparent;
            color: #8B4513;
            border: none;
            padding: 0;
            text-decoration: underline;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            cursor: pointer;
        }
        
        .btn-forgot:hover {
            color: #654321;
        }
        
        .register-link {
            text-align: center;
            margin-top: 1rem;
        }
        
        .register-link a {
            color: #8B4513;
            text-decoration: none;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        
        .password-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .password-toggle:hover {
            color: #8B4513;
        }
        
        .password-toggle:focus {
            outline: none;
        }
        
        .forgot-password-form {
            display: none;
        }
        
        .back-to-login {
            background: none;
            border: none;
            color: #8B4513;
            padding: 0;
            text-decoration: underline;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            cursor: pointer;
        }
        
        .back-to-login:hover {
            color: #654321;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-icon">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <h2 class="login-title">Admin Login</h2>
                <p class="login-subtitle">Sign in to your account</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                <?= $success ?>
            </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" id="loginForm">
                <input type="hidden" name="action" value="login">
                
                <div class="mb-3">
                    <input type="text" class="form-control" name="username" placeholder="Username" required autofocus>
                </div>

                <div class="mb-3 password-container">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <button type="button" class="password-toggle" id="passwordToggle">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>
            </form>

            <div class="text-center">
                <button type="button" class="btn-forgot" id="showForgotPassword">
                    Forgot your password?
                </button>
            </div>

            <!-- Forgot Password Form -->
            <form method="POST" id="forgotPasswordForm" class="forgot-password-form">
                <input type="hidden" name="action" value="forgot_password">
                
                <button type="button" class="back-to-login" id="backToLogin">
                    <i class="bi bi-arrow-left me-1"></i>Back to Login
                </button>
                
                <div class="mb-3">
                    <label class="form-label">Enter your username to reset password:</label>
                    <input type="text" class="form-control" name="reset_username" placeholder="Username" required>
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="bi bi-key me-2"></i>Reset Password
                </button>
            </form>

            <div class="register-link">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Clear form fields when page loads
            document.getElementById('loginForm').reset();
            
            // Focus on username field
            document.querySelector('input[name="username"]').focus();
            
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
            
            // Forgot password toggle
            const showForgotPassword = document.getElementById('showForgotPassword');
            const backToLogin = document.getElementById('backToLogin');
            const loginForm = document.getElementById('loginForm');
            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            const registerLink = document.querySelector('.register-link');
            
            showForgotPassword.addEventListener('click', function() {
                loginForm.style.display = 'none';
                forgotPasswordForm.style.display = 'block';
                this.style.display = 'none';
                registerLink.style.display = 'none';
            });
            
            backToLogin.addEventListener('click', function() {
                loginForm.style.display = 'block';
                forgotPasswordForm.style.display = 'none';
                showForgotPassword.style.display = 'block';
                registerLink.style.display = 'block';
                
                // Clear forgot password form
                document.querySelector('input[name="reset_username"]').value = '';
                
                // Focus on username field
                document.querySelector('input[name="username"]').focus();
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