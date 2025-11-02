
<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_POST['action'] === 'submit_order') {
    try {
        // Generate unique order number
        $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        
        // Get customer information
        $customer_name = trim($_POST['customer_name'] ?? '');
        $customer_phone = trim($_POST['customer_phone'] ?? '');
        $customer_email = trim($_POST['customer_email'] ?? '');
        $delivery_address = trim($_POST['delivery_address'] ?? '');
        $customer_address = trim($_POST['customer_address'] ?? $delivery_address);
        $delivery_date = $_POST['delivery_date'] ?? date('Y-m-d');
        $special_instructions = trim($_POST['special_instructions'] ?? '');
        
        // Validate required fields
        if (empty($customer_name) || empty($customer_phone) || empty($customer_email) || empty($delivery_address)) {
            throw new Exception('Please fill in all required fields');
        }
        
        // Validate email format
        if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address');
        }
        
        // Calculate total from cart
        $total_amount = 0;
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $total_amount += $item['total_price'];
            }
        } else {
            throw new Exception('Your cart is empty');
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert order
            $stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, customer_phone, customer_email, delivery_address, customer_address, delivery_date, special_instructions, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                throw new Exception('Failed to prepare order statement: ' . $conn->error);
            }
            
            $stmt->bind_param("ssssssssd", $order_number, $customer_name, $customer_phone, $customer_email, $delivery_address, $customer_address, $delivery_date, $special_instructions, $total_amount);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to create order: ' . $stmt->error);
            }
            
            $order_id = $conn->insert_id;
            $stmt->close();
            
            // Insert order items
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_type, product_id, product_name, flavor, size, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if (!$item_stmt) {
                throw new Exception('Failed to prepare order items statement: ' . $conn->error);
            }
            
            foreach ($_SESSION['cart'] as $item) {
                $size = $item['size'] ?? '';
                $item_stmt->bind_param(
                    "isisisidd", 
                    $order_id, 
                    $item['product_type'], 
                    $item['product_id'], 
                    $item['product_name'], 
                    $item['flavor'], 
                    $size, 
                    $item['quantity'], 
                    $item['unit_price'], 
                    $item['total_price']
                );
                
                if (!$item_stmt->execute()) {
                    throw new Exception('Failed to insert order item: ' . $item_stmt->error);
                }
            }
            
            $item_stmt->close();
            
            // Commit transaction
            $conn->commit();
            
            // Clear cart
            $_SESSION['cart'] = [];
            
            // Return success response
            echo json_encode([
                'success' => true, 
                'order_id' => $order_number,
                'message' => 'Order placed successfully!'
            ]);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            throw $e;
        }
        
    } catch (Exception $e) {
        error_log('Order submission error: ' . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid action'
    ]);
}

$conn->close();
?>