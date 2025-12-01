<?php
session_start();
require_once './common/connection.php';
requireRole('admin');

echo "<h2>Checking Messages in Database</h2>";

// Check if messages table exists
$result = $conn->query("SHOW TABLES LIKE 'messages'");
if ($result->num_rows > 0) {
    echo "✅ Messages table exists<br>";
    
    // Check messages count
    $count_result = $conn->query("SELECT COUNT(*) as count FROM messages");
    $message_count = $count_result->fetch_assoc()['count'];
    echo "Total messages in database: $message_count<br><br>";
    
    // Show all messages
    $messages_result = $conn->query("SELECT * FROM messages ORDER BY created_at DESC");
    if ($messages_result->num_rows > 0) {
        echo "<h3>All Messages:</h3>";
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Subject</th><th>Read</th><th>Date</th></tr>";
        while ($message = $messages_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$message['id']}</td>";
            echo "<td>{$message['name']}</td>";
            echo "<td>{$message['email']}</td>";
            echo "<td>{$message['subject']}</td>";
            echo "<td>" . ($message['is_read'] ? '✅ Yes' : '❌ No') . "</td>";
            echo "<td>{$message['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>No messages found in database.</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Messages table does not exist!</p>";
    echo "<p>Run the SQL query in the messages.php file to create the table.</p>";
}

$conn->close();
?>