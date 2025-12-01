<?php
session_start();
require_once '../common/connection.php';

echo "<h2>Login Debug Information</h2>";

// Test database connection
if ($conn) {
    echo "✅ Database connected successfully<br>";
} else {
    echo "❌ Database connection failed<br>";
    exit;
}

// Check what's in the owners table
echo "<h3>1. Owners Table Contents:</h3>";
$result = $conn->query("SELECT id, username, password_hash, is_active FROM owners");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>ID</th><th>Username</th><th>Password Hash</th><th>Active</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['username']}</td>";
        echo "<td>" . substr($row['password_hash'], 0, 20) . "...</td>";
        echo "<td>" . ($row['is_active'] ? '✅ Yes' : '❌ No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ No owners found in database<br>";
}

// Test password verification
echo "<h3>2. Password Verification Test:</h3>";
$test_username = 'owner';
$test_password = 'owner123';

$stmt = $conn->prepare("SELECT password_hash FROM owners WHERE username = ?");
$stmt->bind_param("s", $test_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $owner = $result->fetch_assoc();
    $hash = $owner['password_hash'];
    
    echo "Stored hash: " . $hash . "<br>";
    echo "Test password: 'owner123'<br>";
    
    if (password_verify($test_password, $hash)) {
        echo "✅ Password verification SUCCESSFUL!<br>";
    } else {
        echo "❌ Password verification FAILED!<br>";
        
        // Let's see what's wrong
        echo "Debug info:<br>";
        echo "- Hash length: " . strlen($hash) . "<br>";
        echo "- Hash algorithm: " . (password_get_info($hash)['algoName'] ?? 'Unknown') . "<br>";
        
        // Test creating a new hash
        $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
        echo "- New hash for 'owner123': " . $new_hash . "<br>";
        echo "- New hash verification: " . (password_verify($test_password, $new_hash) ? '✅ Works' : '❌ Fails') . "<br>";
    }
} else {
    echo "❌ Owner account not found<br>";
}

// Check admins table for fallback
echo "<h3>3. Admins Table (Fallback):</h3>";
$result = $conn->query("SELECT username, password_hash, role FROM admins WHERE role = 'super_admin'");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>Username</th><th>Password Hash</th><th>Role</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['username']}</td>";
        echo "<td>" . substr($row['password_hash'], 0, 20) . "...</td>";
        echo "<td>{$row['role']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ No super_admin accounts found<br>";
}

$conn->close();
?>