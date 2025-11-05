<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if cart exists and has items
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Your cart is empty. Please add items to your cart first.'
    ]);
    exit;
}

// Handle order submission
if ($_POST['action'] === 'submit_order') {
    submitOrder();
} else {
    // Handle other cart actions
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_to_cart':
            addToCart();
            break;
        case 'update_cart':
            updateCart();
            break;
        case 'remove_from_cart':
            removeFromCart();
            break;
        case 'clear_cart':
            clearCart();
            break;
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            exit;
    }
}

function submitOrder() {
    global $conn;
    
    try {
        // Validate required fields
        $required_fields = ['customer_name', 'customer_phone', 'customer_email', 'delivery_address', 'delivery_date'];
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (empty(trim($_POST[$field] ?? ''))) {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            throw new Exception('Please fill in all required fields: ' . implode(', ', $missing_fields));
        }

        // Validate email
        $email = trim($_POST['customer_email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address.');
        }

        // Validate delivery date
        $delivery_date = $_POST['delivery_date'];
        $min_date = date('Y-m-d', strtotime('+1 day'));
        if ($delivery_date < $min_date) {
            throw new Exception('Delivery date must be at least tomorrow.');
        }

        // Calculate order total
        $order_total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $order_total += $item['total_price'];
        }

        // Add delivery fee
        $delivery_fee = ($order_total < 500) ? 50.00 : 0.00;
        $final_total = $order_total + $delivery_fee;

        // Generate order number
        $order_number = 'ORD-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -6));

        // Start database transaction
        $conn->begin_transaction();

        try {
            // Insert order
            $stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, customer_phone, customer_email, delivery_address, delivery_date, delivery_time, special_instructions, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            
            if (!$stmt) {
                throw new Exception('Database prepare failed: ' . $conn->error);
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
            
            if (!$bind_result) {
                throw new Exception('Database bind failed: ' . $stmt->error);
            }
            
            $execute_result = $stmt->execute();
            if (!$execute_result) {
                throw new Exception('Database execute failed: ' . $stmt->error);
            }
            
            $db_order_id = $conn->insert_id;
            $stmt->close();

            // Insert order items
            foreach ($_SESSION['cart'] as $item) {
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_type, product_id, product_name, flavor, size, quantity, unit_price, total_price, special_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                if (!$stmt) {
                    throw new Exception('Database prepare items failed: ' . $conn->error);
                }
                
                $product_type = $item['product_type'] ?? 'cake';
                $product_id = $item['product_id'] ?? 0;
                $product_name = $item['product_name'] ?? 'Unknown Product';
                $flavor = $item['flavor'] ?? 'Custom';
                $size = $item['size'] ?? 'Standard';
                $quantity = $item['quantity'] ?? 1;
                $unit_price = $item['unit_price'] ?? 0;
                $total_price = $item['total_price'] ?? 0;
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
                    throw new Exception('Database bind items failed: ' . $stmt->error);
                }
                
                $execute_result = $stmt->execute();
                if (!$execute_result) {
                    throw new Exception('Database execute items failed: ' . $stmt->error);
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
            echo json_encode([
                'success' => true,
                'message' => 'Order placed successfully!',
                'order_id' => $order_number,
                'db_order_id' => $db_order_id
            ]);

        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            throw new Exception('Transaction failed: ' . $e->getMessage());
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function addToCart() {
    // Validate required fields
    $required_fields = ['product_type', 'product_id', 'product_name', 'quantity', 'unit_price', 'total_price'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            exit;
        }
    }

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add to cart
    $cart_item_id = uniqid('item_');
    $_SESSION['cart'][$cart_item_id] = [
        'cart_item_id' => $cart_item_id,
        'product_type' => $_POST['product_type'],
        'product_id' => $_POST['product_id'],
        'product_name' => $_POST['product_name'],
        'flavor' => $_POST['flavor'] ?? 'Custom',
        'size' => $_POST['size'] ?? 'Standard',
        'quantity' => intval($_POST['quantity']),
        'special_notes' => $_POST['special_notes'] ?? '',
        'unit_price' => floatval($_POST['unit_price']),
        'total_price' => floatval($_POST['total_price']),
        'added_at' => date('Y-m-d H:i:s')
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Item added to cart successfully!',
        'cart_count' => count($_SESSION['cart']),
        'cart_item' => $_SESSION['cart'][$cart_item_id]
    ]);
}

function updateCart() {
    $cart_item_id = $_POST['cart_item_id'] ?? '';
    $quantity = intval($_POST['quantity'] ?? 0);

    if (empty($cart_item_id) || !isset($_SESSION['cart'][$cart_item_id])) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        exit;
    }

    if ($quantity <= 0) {
        // Remove item if quantity is 0 or less
        unset($_SESSION['cart'][$cart_item_id]);
    } else {
        // Update quantity and total price
        $_SESSION['cart'][$cart_item_id]['quantity'] = $quantity;
        $_SESSION['cart'][$cart_item_id]['total_price'] = $quantity * $_SESSION['cart'][$cart_item_id]['unit_price'];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Cart updated successfully'
    ]);
}

function removeFromCart() {
    $cart_item_id = $_POST['cart_item_id'] ?? '';

    if (empty($cart_item_id) || !isset($_SESSION['cart'][$cart_item_id])) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found']);
        exit;
    }

    unset($_SESSION['cart'][$cart_item_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Item removed from cart'
    ]);
}

function clearCart() {
    $_SESSION['cart'] = [];
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart cleared successfully'
    ]);
}
?>