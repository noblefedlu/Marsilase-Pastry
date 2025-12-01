<?php
include '../config.php';

// Check if any super admin exists
$result = $conn->query("SELECT id FROM admins WHERE role = 'super_admin' AND is_active = 1");
if ($result->num_rows == 0) {
    // Create default super admin
    $username = 'superadmin';
    $password = 'admin123'; // Change this after first login
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $full_name = 'Super Administrator';
    
    $stmt = $conn->prepare("INSERT INTO admins (username, password_hash, full_name, role, is_active) VALUES (?, ?, ?, 'super_admin', 1)");
    $stmt->bind_param("sss", $username, $password_hash, $full_name);
    
    if ($stmt->execute()) {
        echo "<div style='padding: 20px; font-family: Arial;'>";
        echo "<h2>Super Admin Created Successfully!</h2>";
        echo "<p><strong>Username:</strong> superadmin</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p style='color: red; font-weight: bold;'>Please change the password after first login!</p>";
        echo "<a href='login.php' style='background: #5F372B; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login</a>";
        echo "</div>";
    } else {
        echo "Error creating super admin: " . $conn->error;
    }
    $stmt->close();
} else {
    echo "Super admin already exists! <a href='login.php'>Go to Login</a>";
}

$conn->close();
?>