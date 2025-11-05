<?php
// contact.php - This would be in your main pages directory
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_contact') {
    require_once 'config.php';
    
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Basic validation
    if (empty($name) || empty($email) || empty($message)) {
        $_SESSION['contact_error'] = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['contact_error'] = "Please enter a valid email address.";
    } else {
        // Save to database
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
        
        if ($stmt->execute()) {
            $_SESSION['contact_success'] = "Thank you for your message! We'll get back to you soon.";
            
            // Clear form
            unset($_POST);
        } else {
            $_SESSION['contact_error'] = "Sorry, there was an error sending your message. Please try again.";
        }
        $stmt->close();
    }
    
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
?>