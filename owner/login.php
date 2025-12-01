<?php
session_start();
require_once '../common/connection.php';

// Redirect if already logged in as owner
if (isset($_SESSION['owner_logged_in']) && $_SESSION['owner_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // DEBUG: Log the login attempt
        error_log("Owner login attempt: username=$username");
        
        // Check owners table first
        $stmt = $conn->prepare("SELECT * FROM owners WHERE username = ?");
        
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $owner = $result->fetch_assoc();
                error_log("Owner found: " . $owner['username'] . ", active: " . $owner['is_active']);
                
                // Check if account is active
                if (!$owner['is_active']) {
                    $error = 'Account is deactivated. Please contact system administrator.';
                }
                // Check if account is locked
                elseif ($owner['account_locked_until'] && strtotime($owner['account_locked_until']) > time()) {
                    $lock_time = date('g:i A', strtotime($owner['account_locked_until']));
                    $error = "Account temporarily locked until $lock_time. Please try again later.";
                } 
                // Verify password
                elseif (password_verify($password, $owner['password_hash'])) {
                    error_log("Password verification SUCCESSFUL for: " . $owner['username']);
                    
                    // Successful login - update last login and reset attempts
                    $update_stmt = $conn->prepare("UPDATE owners SET last_login = NOW(), login_attempts = 0, account_locked_until = NULL WHERE id = ?");
                    $update_stmt->bind_param("i", $owner['id']);
                    $update_stmt->execute();
                    $update_stmt->close();
                    
                    // Set owner session
                    $_SESSION['owner_logged_in'] = true;
                    $_SESSION['owner_id'] = $owner['id'];
                    $_SESSION['owner_username'] = $owner['username'];
                    $_SESSION['owner_full_name'] = $owner['full_name'];
                    $_SESSION['owner_email'] = $owner['email'];
                    $_SESSION['owner_security_level'] = $owner['security_level'];
                    
                    error_log("Owner login successful: " . $owner['username']);
                    header('Location: index.php');
                    exit;
                } else {
                    error_log("Password verification FAILED for: " . $owner['username']);
                    
                    // Increment login attempts
                    $attempts = $owner['login_attempts'] + 1;
                    $locked_until = null;
                    
                    // Lock account after 5 failed attempts for 30 minutes
                    if ($attempts >= 5) {
                        $locked_until = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                        error_log("Account locked for: " . $owner['username']);
                    }
                    
                    $update_stmt = $conn->prepare("UPDATE owners SET login_attempts = ?, account_locked_until = ? WHERE id = ?");
                    $update_stmt->bind_param("isi", $attempts, $locked_until, $owner['id']);
                    $update_stmt->execute();
                    $update_stmt->close();
                    
                    $error = 'Invalid credentials' . ($attempts >= 3 ? " ($attempts/5 attempts)" : "");
                }
            } else {
                error_log("Owner not found in owners table: $username");
                
                // Fallback to admins table for backward compatibility
                $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ? AND role = 'super_admin' AND is_active = 1");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $admin = $result->fetch_assoc();
                    error_log("Fallback to admin account: " . $admin['username']);
                    
                    if (password_verify($password, $admin['password_hash'])) {
                        error_log("Admin password verification SUCCESSFUL");
                        
                        // Set session as owner (from admin table)
                        $_SESSION['owner_logged_in'] = true;
                        $_SESSION['owner_id'] = $admin['id'];
                        $_SESSION['owner_username'] = $admin['username'];
                        $_SESSION['owner_full_name'] = $admin['full_name'];
                        $_SESSION['owner_email'] = $admin['email'] ?? 'owner@marsilase.com';
                        $_SESSION['owner_security_level'] = 'full';
                        
                        header('Location: index.php');
                        exit;
                    } else {
                        error_log("Admin password verification FAILED");
                        $error = 'Invalid credentials';
                    }
                } else {
                    error_log("No owner or admin account found: $username");
                    $error = 'Invalid owner credentials - account not found';
                }
            }
            $stmt->close();
        } else {
            error_log("Database statement preparation failed");
            $error = 'Database error. Please check your configuration.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Login - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50, #34495e);
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
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            padding: 2.5rem;
            border: none;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-icon {
            font-size: 3.5rem;
            color: #2c3e50;
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
            border-color: #2c3e50;
            box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
        }
        
        .btn-login {
            background: #2c3e50;
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
            background: #34495e;
            transform: translateY(-2px);
        }
        
        .owner-badge {
            background: #e74c3c;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
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
        
        .security-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.9rem;
        }
        
        .debug-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 10px;
            margin-top: 15px;
            font-size: 0.8rem;
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
                <h2 class="login-title">Owner Access</h2>
                <p class="login-subtitle">Enhanced Security System</p>
                <span class="owner-badge">OWNER PORTAL</span>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            
            <div class="debug-info">
                <strong>Troubleshooting:</strong><br>
                • Try username: <code>owner</code> and password: <code>owner123</code><br>
                • Or try admin login: <code>admin</code> / <code>admin123</code><br>
                • <a href="debug_login.php" target="_blank">Run Login Debug</a>
            </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" placeholder="Enter owner username" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? 'owner') ?>">
                </div>

                <div class="mb-3 password-container">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required value="owner123">
                    <button type="button" class="password-toggle" id="passwordToggle">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="bi bi-shield-lock me-2"></i>Access Owner Panel
                </button>
            </form>

            <div class="security-info">
                <h6><i class="bi bi-info-circle me-2"></i>Security Information</h6>
                <ul class="small mb-0">
                    <li>Account locks after 5 failed attempts</li>
                    <li>30-minute lockout period</li>
                    <li>Enhanced session security</li>
                </ul>
            </div>

            <div class="access-links mt-3 text-center">
                <p class="text-muted mb-2">Default Credentials:</p>
                <small class="text-muted">
                    Username: <code>owner</code> | Password: <code>owner123</code>
                </small>
                <br>
                <small class="text-muted">
                    Backup: <code>backup_owner</code> | Password: <code>owner123</code>
                </small>
                <br>
                <a href="../admin/login.php" class="mt-2 d-inline-block">
                    <i class="bi bi-person-gear me-1"></i>Admin Login
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordToggle = document.getElementById('passwordToggle');
            const passwordInput = document.getElementById('password');
            
            passwordToggle.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                if (type === 'password') {
                    this.innerHTML = '<i class="bi bi-eye"></i>';
                } else {
                    this.innerHTML = '<i class="bi bi-eye-slash"></i>';
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