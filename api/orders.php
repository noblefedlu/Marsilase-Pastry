<?php
session_start();
include '../config.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_POST['action'] === 'submit_order') {
    
    // Debug info
    error_log("=== ORDER SUBMISSION STARTED ===");
    
    if (empty($_SESSION['cart'])) {
        echo json_encode(['success' => false, 'message' => 'Your cart is empty']);
        exit;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Calculate total
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += floatval($item['total_price'] ?? 0);
        }
        
        // Generate order number
        $order_number = 'ORD' . date('YmdHis') . rand(100, 999);
        
        // Use provided values or defaults
        $name = trim($_POST['name'] ?? 'Customer');
        $phone = trim($_POST['phone'] ?? '0000000000');
        $address = trim($_POST['address'] ?? 'Store Pickup');
        $date = $_POST['date'] ?? date('Y-m-d');
        $instructions = trim($_POST['instructions'] ?? '');
        
        // Insert order - FIXED: Removed delivery_instructions if column doesn't exist
        $stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, customer_phone, delivery_address, delivery_date, total_amount) VALUES (?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Prepare failed for orders: " . $conn->error);
        }
        
        $stmt->bind_param("sssssd", 
            $order_number,
            $name,
            $phone,
            $address,
            $date,
            $total
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create order: " . $stmt->error);
        }
        
        $order_id = $conn->insert_id;
        $stmt->close();
        
        // Insert order items - FIXED: Simplified to avoid JSON issues
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_type, product_id, product_name, flavor, size, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Prepare failed for order items: " . $conn->error);
        }
        
        foreach ($_SESSION['cart'] as $index => $item) {
            // Simplified - skip toppings and special_notes for now
            $product_type = $item['product_type'] ?? 'cake';
            $product_id = $item['product_id'] ?? '0';
            $product_name = $item['product_name'] ?? 'Unknown Product';
            $flavor = $item['flavor'] ?? 'Default';
            $size = $item['size'] ?? '';
            $quantity = intval($item['quantity'] ?? 1);
            $unit_price = floatval($item['unit_price'] ?? 0);
            $total_price = floatval($item['total_price'] ?? 0);
            
            $stmt->bind_param("issssiidd",
                $order_id,
                $product_type,
                $product_id,
                $product_name,
                $flavor,
                $size,
                $quantity,
                $unit_price,
                $total_price
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert order item $index: " . $stmt->error);
            }
        }
        
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        // Clear cart
        $_SESSION['cart'] = [];
        
        echo json_encode([
            'success' => true, 
            'order_id' => $order_id, 
            'order_number' => $order_number,
            'message' => 'Order placed successfully!'
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("ORDER SUBMISSION ERROR: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'There was an error submitting your order. Please try again. Error: ' . $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

// Close connection
if (isset($conn)) {
    $conn->close();
}
?>