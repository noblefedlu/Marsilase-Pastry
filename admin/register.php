<?php
session_start();
require_once '../config.php';

// Redirect to dashboard if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

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
            // Create new admin account
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'admin'; // Default role for new registrations
            
            $stmt = $conn->prepare("INSERT INTO admins (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $password_hash, $full_name, $role);
            
            if ($stmt->execute()) {
                // Auto-login after successful registration
                $admin_id = $conn->insert_id;
                
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin_id;
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_full_name'] = $full_name;
                $_SESSION['admin_role'] = $role;
                
                header('Location: index.php');
                exit;
            } else {
                $error = 'Failed to create account: ' . $stmt->error;
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
    <title>Admin Register - Marsilase Pastry</title>
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
        
        .register-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }
        
        .register-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .register-icon {
            font-size: 3rem;
            color: #8B4513;
            margin-bottom: 1rem;
        }
        
        .register-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .register-subtitle {
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
        
        .btn-register {
            background: #8B4513;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            width: 100%;
            font-weight: 500;
            margin-bottom: 1rem;
        }
        
        .btn-register:hover {
            background: #654321;
        }
        
        .login-link {
            text-align: center;
            margin-top: 1rem;
        }
        
        .login-link a {
            color: #8B4513;
            text-decoration: none;
        }
        
        .login-link a:hover {
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
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <div class="register-icon">
                    <i class="bi bi-person-plus"></i>
                </div>
                <h2 class="register-title">Create Account</h2>
                <p class="register-subtitle">Register as administrator</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" id="registerForm">
                <div class="mb-3">
                    <input type="text" class="form-control" name="full_name" placeholder="Full Name" required>
                </div>

                <div class="mb-3">
                    <input type="text" class="form-control" name="username" placeholder="Username" required>
                </div>

                <div class="mb-3 password-container">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <button type="button" class="password-toggle" id="passwordToggle">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>

                <div class="mb-3 password-container">
                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Confirm Password" required>
                    <button type="button" class="password-toggle" id="confirmPasswordToggle">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>

                <button type="submit" class="btn btn-register">
                    <i class="bi bi-person-plus me-2"></i>Create Account
                </button>
            </form>

            <div class="login-link">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Clear form fields when page loads
            document.getElementById('registerForm').reset();
            
            // Focus on first field
            document.querySelector('input[name="full_name"]').focus();
            
            // Password toggle functionality
            function setupPasswordToggle(toggleId, inputId) {
                const toggle = document.getElementById(toggleId);
                const input = document.getElementById(inputId);
                
                toggle.addEventListener('click', function() {
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    
                    // Toggle eye icon
                    if (type === 'password') {
                        this.innerHTML = '<i class="bi bi-eye"></i>';
                    } else {
                        this.innerHTML = '<i class="bi bi-eye-slash"></i>';
                    }
                });
            }
            
            // Setup both password fields
            setupPasswordToggle('passwordToggle', 'password');
            setupPasswordToggle('confirmPasswordToggle', 'confirmPassword');
        });
    </script>
</body>
</html>
<?php 
if (isset($conn) && $conn) {
    $conn->close(); 
}
?>