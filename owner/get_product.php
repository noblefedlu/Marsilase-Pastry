<?php
// owner/get_product.php
session_start();
require_once '../common/connection.php';
requireOwner();

header('Content-Type: application/json');

$product_id = $_GET['id'] ?? 0;

if ($product_id) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if ($product) {
        echo json_encode($product);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No product ID provided']);
}

$conn->close();
?>