<?php
require_once 'config.php';

echo "<h2>Database Setup for Marsilase Pastry</h2>";

// Check if admins table exists
$result = $conn->query("SHOW TABLES LIKE 'admins'");
if ($result->num_rows == 0) {
    // Create admins table
    $sql = "CREATE TABLE admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('owner', 'admin') DEFAULT 'owner',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql)) {
        echo "✅ Admins table created successfully<br>";
        
        // Create default owner account
        $username = 'owner';
        $password = 'owner123';
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $full_name = 'System Owner';
        
        $stmt = $conn->prepare("INSERT INTO admins (username, password_hash, full_name, role) VALUES (?, ?, ?, 'owner')");
        $stmt->bind_param("sss", $username, $password_hash, $full_name);
        
        if ($stmt->execute()) {
            echo "✅ Default owner account created<br>";
            echo "<strong>Username:</strong> owner<br>";
            echo "<strong>Password:</strong> owner123<br>";
            echo "<p style='color: red;'><strong>Important:</strong> Change the password after first login!</p>";
        } else {
            echo "❌ Failed to create owner account: " . $stmt->error . "<br>";
        }
        $stmt->close();
    } else {
        echo "❌ Failed to create admins table: " . $conn->error . "<br>";
    }
} else {
    echo "✅ Admins table already exists<br>";
}

// Check for other essential tables
$tables = ['products', 'orders', 'order_items', 'messages'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows == 0) {
        echo "⚠️ Table '$table' is missing (you may need to run your original setup)<br>";
    } else {
        echo "✅ Table '$table' exists<br>";
    }
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li><a href='owner/login.php' target='_blank'>Go to Owner Login</a> - Use credentials above</li>";
echo "<li><a href='admin/login.php' target='_blank'>Go to Admin Login</a> - Create admin accounts from owner panel</li>";
echo "<li><a href='index.php' target='_blank'>Go to Main Portal</a> - Choose access level</li>";
echo "</ol>";

$conn->close();
?>