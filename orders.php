<?php
// orders.php - Fixed version with proper price handling
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

header('Content-Type: application/json');

// Function to send clean JSON response
function sendJsonResponse($success, $message, $additional_data = []) {
    $response = array_merge([
        'success' => $success,
        'message' => $message
    ], $additional_data);
    
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    echo json_encode($response);
    exit;
}

// Check if this is an order submission
if (($_POST['action'] ?? '') !== 'submit_order') {
    sendJsonResponse(false, 'Invalid action');
}

// Check if cart exists and has items
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    sendJsonResponse(false, 'Your cart is empty. Please add items to your cart first.');
}

try {
    // Validate required fields
    $required_fields = ['customer_name', 'customer_phone', 'delivery_address', 'delivery_date'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (empty(trim($_POST[$field] ?? ''))) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        throw new Exception('Please fill in all required fields: ' . implode(', ', $missing_fields));
    }

    // Validate delivery date
    $delivery_date = $_POST['delivery_date'];
    $min_date = date('Y-m-d', strtotime('+1 day'));
    if ($delivery_date < $min_date) {
        throw new Exception('Delivery date must be at least tomorrow.');
    }

    // Check database connection
    if (!$conn || $conn->connect_error) {
        throw new Exception('Database connection failed. Please try again.');
    }

    // Calculate order total - FIXED: Use actual prices from cart
    $order_total = 0;
    foreach ($_SESSION['cart'] as $item) {
        // Ensure we're using the correct price from the item
        $item_price = floatval($item['total_price'] ?? $item['unit_price'] * $item['quantity']);
        $order_total += $item_price;
    }

    // Add delivery fee
    $delivery_fee = ($order_total < 500) ? 50.00 : 0.00;
    $final_total = $order_total + $delivery_fee;

    // Generate order number
    $order_number = 'ORD-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -6));

    // Get email from POST data or use empty string
    $email = $_POST['customer_email'] ?? '';

    // Start database transaction
    $conn->begin_transaction();

    try {
        // Check if the customer_email column exists
        $check_column_sql = "SHOW COLUMNS FROM orders LIKE 'customer_email'";
        $column_result = $conn->query($check_column_sql);
        $has_email_column = $column_result && $column_result->num_rows > 0;
        if ($column_result) $column_result->free();

        if ($has_email_column) {
            // Insert order with email column
            $stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, customer_phone, customer_email, delivery_address, delivery_date, delivery_time, special_instructions, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            
            if (!$stmt) {
                throw new Exception('Failed to prepare order statement: ' . $conn->error);
            }
            
            $delivery_time = $_POST['delivery_time'] ?? '09:00-12:00';
            $special_instructions = $_POST['special_instructions'] ?? '';
            
            $bind_result = $stmt->bind_param("ssssssssd", 
                $order_number,
                $_POST['customer_name'],
                $_POST['customer_phone'],
                $email,
                $_POST['delivery_address'],
                $delivery_date,
                $delivery_time,
                $special_instructions,
                $final_total
            );
        } else {
            // Insert order without email column
            $stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, customer_phone, delivery_address, delivery_date, delivery_time, special_instructions, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            
            if (!$stmt) {
                throw new Exception('Failed to prepare order statement: ' . $conn->error);
            }
            
            $delivery_time = $_POST['delivery_time'] ?? '09:00-12:00';
            $special_instructions = $_POST['special_instructions'] ?? '';
            
            $bind_result = $stmt->bind_param("sssssssd", 
                $order_number,
                $_POST['customer_name'],
                $_POST['customer_phone'],
                $_POST['delivery_address'],
                $delivery_date,
                $delivery_time,
                $special_instructions,
                $final_total
            );
        }
        
        if (!$bind_result) {
            throw new Exception('Failed to bind order parameters: ' . $stmt->error);
        }
        
        $execute_result = $stmt->execute();
        if (!$execute_result) {
            throw new Exception('Failed to execute order insertion: ' . $stmt->error);
        }
        
        $db_order_id = $conn->insert_id;
        $stmt->close();

        // Insert order items - FIXED: Ensure correct prices are stored
        foreach ($_SESSION['cart'] as $item) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_type, product_id, product_name, flavor, size, quantity, unit_price, total_price, special_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) {
                throw new Exception('Failed to prepare order item statement: ' . $conn->error);
            }
            
            $product_type = $item['product_type'] ?? 'cake';
            $product_id = $item['product_id'] ?? 0;
            $product_name = $item['product_name'] ?? 'Unknown Product';
            $flavor = $item['flavor'] ?? 'Custom';
            $size = $item['size'] ?? 'Standard';
            $quantity = intval($item['quantity'] ?? 1);
            
            // FIXED: Use the actual unit price and calculate total correctly
            $unit_price = floatval($item['unit_price'] ?? 0);
            $total_price = floatval($item['total_price'] ?? $unit_price * $quantity);
            
            // Double-check: If total_price seems wrong, recalculate it
            if ($total_price != ($unit_price * $quantity)) {
                $total_price = $unit_price * $quantity;
            }
            
            $special_notes = $item['special_notes'] ?? '';
            
            $bind_result = $stmt->bind_param(
                "isisisddss",
                $db_order_id,
                $product_type,
                $product_id,
                $product_name,
                $flavor,
                $size,
                $quantity,
                $unit_price,
                $total_price,
                $special_notes
            );
            
            if (!$bind_result) {
                throw new Exception('Failed to bind order item parameters: ' . $stmt->error);
            }
            
            $execute_result = $stmt->execute();
            if (!$execute_result) {
                throw new Exception('Failed to execute order item insertion: ' . $stmt->error);
            }
            
            $stmt->close();
        }

        // Commit transaction
        $conn->commit();

        // Save to session for thank you page
        if (!isset($_SESSION['orders'])) {
            $_SESSION['orders'] = [];
        }
        
        $_SESSION['orders'][$order_number] = [
            'order_id' => $order_number,
            'db_order_id' => $db_order_id,
            'customer_info' => [
                'name' => $_POST['customer_name'],
                'phone' => $_POST['customer_phone'],
                'email' => $email
            ],
            'delivery_info' => [
                'address' => $_POST['delivery_address'],
                'date' => $delivery_date,
                'time' => $delivery_time,
                'instructions' => $special_instructions
            ],
            'order_items' => $_SESSION['cart'],
            'pricing' => [
                'subtotal' => $order_total,
                'delivery_fee' => $delivery_fee,
                'total' => $final_total
            ],
            'order_status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Clear cart
        $_SESSION['cart'] = [];

        // Success response
        sendJsonResponse(true, 'Order placed successfully!', [
            'order_id' => $order_number,
            'db_order_id' => $db_order_id
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw new Exception('Order processing failed: ' . $e->getMessage());
    }

} catch (Exception $e) {
    error_log("Order submission error: " . $e->getMessage());
    sendJsonResponse(false, $e->getMessage());
}

if (isset($conn)) {
    $conn->close();
}
?>