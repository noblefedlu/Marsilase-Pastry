<?php
session_start();
require_once '../config.php';

// Only allow registration if no owner exists yet, or if current user is owner
$result = $conn->query("SELECT COUNT(*) as owner_count FROM admins WHERE role = 'owner'");
$owner_count = $result->fetch_assoc()['owner_count'];

// If owners exist, check if current user is owner
if ($owner_count > 0) {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['admin_role'] !== 'owner') {
        header('Location: index.php');
        exit;
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    
    // Validation
    if (empty($username) || empty($password) || empty($confirm_password) || empty($full_name)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters long';
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username already exists';
        } else {
            // Create the owner account
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $role = 'owner';
            
            $stmt = $conn->prepare("INSERT INTO admins (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $password_hash, $full_name, $role);
            
            if ($stmt->execute()) {
                $success = 'Owner account created successfully!';
                
                // If this is the first owner, automatically log them in
                if ($owner_count == 0) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $stmt->insert_id;
                    $_SESSION['admin_username'] = $username;
                    $_SESSION['admin_full_name'] = $full_name;
                    $_SESSION['admin_role'] = $role;
                    
                    header('Location: index.php');
                    exit;
                }
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
    <title>Register Owner - Marsilase Pastry</title>
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
        
        .register-container {
            width: 100%;
            max-width: 500px;
            padding: 20px;
        }
        
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            padding: 2.5rem;
            border: none;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .register-icon {
            font-size: 3.5rem;
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        
        .register-title {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }
        
        .register-subtitle {
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
        
        .btn-register {
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
        
        .btn-register:hover {
            background: #34495e;
            transform: translateY(-2px);
        }
        
        .access-links {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #ecf0f1;
        }
        
        .access-links a {
            color: #3498db;
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
        
        .owner-badge {
            background: #e74c3c;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: -10px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
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
                <h2 class="register-title">Register Owner Account</h2>
                <p class="register-subtitle">Create the system owner account</p>
                <span class="owner-badge">OWNER REGISTRATION</span>
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
                    <input type="text" class="form-control" name="full_name" placeholder="Full Name" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <input type="text" class="form-control" name="username" placeholder="Username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    <small class="text-muted">Minimum 3 characters</small>
                </div>

                <div class="mb-3 password-container">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <button type="button" class="password-toggle" data-target="password">
                        <i class="bi bi-eye"></i>
                    </button>
                    <div class="password-strength" id="passwordStrength"></div>
                    <small class="text-muted">Minimum 6 characters</small>
                </div>

                <div class="mb-3 password-container">
                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Confirm Password" required>
                    <button type="button" class="password-toggle" data-target="confirmPassword">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>

                <button type="submit" class="btn btn-register">
                    <i class="bi bi-person-plus me-2"></i>Create Owner Account
                </button>
            </form>

            <div class="access-links">
                <p class="text-muted mb-2">Already have an account?</p>
                <a href="login.php">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Go to Login
                </a>
                <br>
                <a href="../admin/login.php" class="mt-2 d-inline-block">
                    <i class="bi bi-person-gear me-1"></i>Admin Login
                </a>
            </div>
        </div>
    </div>

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

            // Password strength indicator
            const passwordInput = document.getElementById('password');
            const strengthBar = document.getElementById('passwordStrength');
            
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                if (password.length >= 6) strength += 25;
                if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 25;
                if (password.match(/\d/)) strength += 25;
                if (password.match(/[^a-zA-Z\d]/)) strength += 25;
                
                strengthBar.style.width = strength + '%';
                
                if (strength < 50) {
                    strengthBar.style.backgroundColor = '#dc3545';
                } else if (strength < 75) {
                    strengthBar.style.backgroundColor = '#ffc107';
                } else {
                    strengthBar.style.backgroundColor = '#28a745';
                }
            });

            // Form validation
            document.getElementById('registerForm').addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    return false;
                }
                
                if (password.length < 6) {
                    e.preventDefault();
                    alert('Password must be at least 6 characters long!');
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