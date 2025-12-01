<?php
// contact_process.php - For your main website
include '../config.php'; // Your database connection file

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validate inputs
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    }
    
    if (empty($subject)) {
        $subject = 'Message from ' . $name;
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    try {
        // Save to database
        $stmt = $conn->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $subject, $message);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Thank you for your message! We will get back to you soon.']);
        } else {
            echo json_encode(['success' => false, 'errors' => ['Failed to send message. Please try again.']]);
        }
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'errors' => ['Database error: ' . $e->getMessage()]]);
    }
} else {
    echo json_encode(['success' => false, 'errors' => ['Invalid request method']]);
}

$conn->close();
?>