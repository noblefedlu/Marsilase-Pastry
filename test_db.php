<?php
require_once 'config.php';

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
} else {
    echo "Database connected successfully!";
    
    // Test orders table
    $result = $conn->query("SELECT 1 FROM orders LIMIT 1");
    if ($result) {
        echo "<br>Orders table exists";
    } else {
        echo "<br>Orders table error: " . $conn->error;
    }
    
    $conn->close();
}
?>