<?php
session_start();
include '../config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    
    if (empty($username) || empty($password) || empty($confirm_password) || empty($full_name)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username already exists';
        } else {
            // Create new admin with pending status (requires super admin approval)
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'moderator'; // Default role for public registration
            $is_active = 0; // Inactive until approved by super admin
            
            $stmt = $conn->prepare("INSERT INTO admins (username, password_hash, full_name, role, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $username, $password_hash, $full_name, $role, $is_active);
            
            if ($stmt->execute()) {
                $success = 'Admin account created successfully! It will be activated after approval by a super administrator.';
                // Clear form on success
                $_POST = array();
            } else {
                $error = 'Failed to create admin account. Please try again.';
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin - Marsilase Pastry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-50: #FFF6E9;
            --primary-100: #5F372B;
            --primary-200: #4A2B22;
            --text-dark: #5F372B;
        }
        
        body {
            background: var(--primary-50);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .register-container {
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
        }
        
        .register-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(95, 55, 43, 0.1);
            border: 1px solid #F5E6D6;
            padding: 2rem;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .register-header i {
            font-size: 2.5rem;
            color: var(--primary-100);
            margin-bottom: 1rem;
        }
        
        .btn-primary {
            background: var(--primary-100);
            border: none;
            padding: 0.75rem;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background: var(--primary-200);
        }
        
        .btn-outline-primary {
            border-color: var(--primary-100);
            color: var(--primary-100);
        }
        
        .btn-outline-primary:hover {
            background: var(--primary-100);
            color: white;
        }
        
        .form-control:focus {
            border-color: var(--primary-100);
            box-shadow: 0 0 0 0.2rem rgba(95, 55, 43, 0.1);
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
            color: var(--primary-100);
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .approval-notice {
            background: var(--primary-50);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary-100);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="register-card">
                <div class="register-header">
                    <i class="bi bi-person-plus"></i>
                    <h3 class="fw-bold text-dark">Register Admin Account</h3>
                    <p class="text-muted">Create a new administrator account</p>
                </div>
                
                <div class="approval-notice">
                    <i class="bi bi-info-circle text-primary"></i>
                    <small class="text-muted">
                        Your account will be created as a moderator and will require approval from a super administrator before you can access the admin panel.
                    </small>
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
                    <?= htmlspecialchars($success) ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" id="registerForm">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" class="form-control" name="full_name" 
                               value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" 
                               placeholder="Enter your full name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" class="form-control" name="username" 
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                               placeholder="Choose a username" required>
                    </div>
                    
                    <div class="mb-3 password-container">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Enter password" required minlength="6">
                        <button type="button" class="password-toggle" data-target="password">
                            <i class="bi bi-eye"></i>
                        </button>
                        <div class="form-text">Minimum 6 characters</div>
                    </div>
                    
                    <div class="mb-4 password-container">
                        <label class="form-label fw-semibold">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm password" required minlength="6">
                        <button type="button" class="password-toggle" data-target="confirm_password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary py-2 fw-semibold">
                            <i class="bi bi-person-plus me-2"></i>Register Admin Account
                        </button>
                        <a href="login.php" class="btn btn-outline-primary py-2">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Back to Login
                        </a>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <a href="../index.php" class="text-muted text-decoration-none small">
                        <i class="bi bi-arrow-left me-1"></i>Back to Main Site
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password toggle functionality
            document.querySelectorAll('.password-toggle').forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    
                    // Toggle eye icon
                    if (type === 'password') {
                        this.innerHTML = '<i class="bi bi-eye"></i>';
                    } else {
                        this.innerHTML = '<i class="bi bi-eye-slash"></i>';
                    }
                });
            });
            
            // Clear form on success
            <?php if ($success): ?>
            document.getElementById('registerForm').reset();
            <?php endif; ?>
            
            // Form validation
            document.getElementById('registerForm').addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    document.getElementById('confirm_password').focus();
                    return false;
                }
                
                if (password.length < 6) {
                    e.preventDefault();
                    alert('Password must be at least 6 characters long!');
                    document.getElementById('password').focus();
                    return false;
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