<?php
session_start();
include '../config.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_POST['action'] === 'submit_order') {
    error_log("Order submission started");
    
    // Validate required fields - use default values if not provided
    $name = trim($_POST['name'] ?? 'Customer');
    $phone = trim($_POST['phone'] ?? '0000000000');
    $address = trim($_POST['address'] ?? 'Delivery Address');
    $date = $_POST['date'] ?? date('Y-m-d');
    $instructions = trim($_POST['instructions'] ?? '');
    
    if (empty($_SESSION['cart'])) {
        echo json_encode(['success' => false, 'message' => 'Your cart is empty']);
        exit;
    }
    
    // Start transaction
    $conn->autocommit(FALSE);
    $success = true;
    
    try {
        // Calculate total
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += floatval($item['total_price'] ?? 0);
        }
        
        // Generate order number
        $order_number = 'ORD' . date('YmdHis') . rand(100, 999);
        
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, customer_phone, delivery_address, delivery_date, delivery_instructions, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Prepare failed for orders: " . $conn->error);
        }
        
        $stmt->bind_param("ssssssd", 
            $order_number,
            $name,
            $phone,
            $address,
            $date,
            $instructions,
            $total
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create order: " . $stmt->error);
        }
        
        $order_id = $conn->insert_id;
        $stmt->close();
        
        // Insert order items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_type, product_id, product_name, flavor, size, toppings, special_notes, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Prepare failed for order items: " . $conn->error);
        }
        
        foreach ($_SESSION['cart'] as $index => $item) {
            // Handle toppings data safely
            $toppings_data = $item['toppings'] ?? '';
            if (is_array($toppings_data)) {
                $toppings_json = json_encode($toppings_data);
            } else {
                $toppings_json = $toppings_data;
                // If it's a string but contains JSON, decode and re-encode to ensure it's valid
                if (!empty($toppings_json) && $toppings_json[0] === '[') {
                    $decoded = json_decode($toppings_json, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $toppings_json = json_encode($decoded);
                    }
                }
            }
            
            // Ensure all required fields have values
            $product_type = $item['product_type'] ?? '';
            $product_id = $item['product_id'] ?? '';
            $product_name = $item['product_name'] ?? '';
            $flavor = $item['flavor'] ?? '';
            $size = $item['size'] ?? '';
            $special_notes = $item['special_notes'] ?? '';
            $quantity = intval($item['quantity'] ?? 1);
            $unit_price = floatval($item['unit_price'] ?? 0);
            $total_price = floatval($item['total_price'] ?? 0);
            
            $stmt->bind_param("isssssssidd",
                $order_id,
                $product_type,
                $product_id,
                $product_name,
                $flavor,
                $size,
                $toppings_json,
                $special_notes,
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
        if (!$conn->commit()) {
            throw new Exception("Commit failed: " . $conn->error);
        }
        
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
        error_log("Order submission error: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'There was an error submitting your order. Please try again.'
        ]);
    }
    
    // Restore autocommit mode
    $conn->autocommit(TRUE);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>