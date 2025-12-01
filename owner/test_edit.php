<?php
session_start();
require_once '../common/connection.php';
requireOwner();

echo "<h2>Testing Edit Functionality</h2>";

// Test if get_cake.php works
$test_cake_id = 1; // Change this to an existing cake ID
echo "<h3>Testing get_cake.php for cake ID: $test_cake_id</h3>";

$url = "get_cake.php?id=$test_cake_id";
$response = file_get_contents($url);
echo "<pre>Response: " . htmlspecialchars($response) . "</pre>";

// Check if cake exists in database
$stmt = $conn->prepare("SELECT * FROM cakes WHERE id = ?");
$stmt->bind_param("i", $test_cake_id);
$stmt->execute();
$result = $stmt->get_result();
$cake = $result->fetch_assoc();

if ($cake) {
    echo "<p style='color: green;'>✅ Cake found in database:</p>";
    echo "<pre>" . print_r($cake, true) . "</pre>";
} else {
    echo "<p style='color: red;'>❌ Cake not found in database. Available cakes:</p>";
    
    $all_cakes = $conn->query("SELECT id, name FROM cakes ORDER BY id");
    if ($all_cakes->num_rows > 0) {
        echo "<ul>";
        while ($cake = $all_cakes->fetch_assoc()) {
            echo "<li>ID: {$cake['id']} - {$cake['name']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No cakes in database.</p>";
    }
}

$conn->close();
?>