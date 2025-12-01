<?php
session_start();
require_once '../common/connection.php';
requireOwner();

header('Content-Type: application/json');

$cake_id = $_GET['id'] ?? 0;

if ($cake_id) {
    $stmt = $conn->prepare("SELECT * FROM cakes WHERE id = ?");
    $stmt->bind_param("i", $cake_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cake = $result->fetch_assoc();
    
    if ($cake) {
        echo json_encode($cake);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Cake not found']);
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No cake ID provided']);
}

$conn->close();
?>