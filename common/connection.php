<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'marsilase_pastry';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8mb4");

/**
 * Check if user has required role
 * @param string $role 'owner' or 'admin'
 */
function requireRole($role) {
    if ($role === 'owner') {
        if (!isset($_SESSION['owner_logged_in']) || $_SESSION['owner_logged_in'] !== true) {
            header('Location: ../owner/login.php');
            exit;
        }
    } elseif ($role === 'admin') {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: ../admin/login.php');
            exit;
        }
    }
}

/**
 * Check if user is logged in as owner
 */
function requireOwner() {
    if (!isset($_SESSION['owner_logged_in']) || $_SESSION['owner_logged_in'] !== true) {
        header('Location: ../owner/login.php');
        exit;
    }
}

/**
 * Check if user has owner permissions for specific action
 */
function checkOwnerPermission($permission_key) {
    if (!isset($_SESSION['owner_id'])) {
        return false;
    }
    
    global $conn;
    $stmt = $conn->prepare("
        SELECT op.permission_value 
        FROM owner_permissions op 
        WHERE op.owner_id = ? AND op.permission_key = ?
    ");
    $stmt->bind_param("is", $_SESSION['owner_id'], $permission_key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $permission = $result->fetch_assoc();
        return (bool)$permission['permission_value'];
    }
    
    return false;
}

/**
 * Check if owner has specific permission
 * This is the main permission checking function
 */
function checkPermission($requiredPermission) {
    global $conn;
    
    // If not logged in as owner, no permissions
    if (!isset($_SESSION['owner_logged_in']) || $_SESSION['owner_logged_in'] !== true) {
        return false;
    }
    
    // Full security level has all permissions
    if (isset($_SESSION['owner_security_level']) && $_SESSION['owner_security_level'] === 'full') {
        return true;
    }
    
    $owner_id = $_SESSION['owner_id'];
    $stmt = $conn->prepare("SELECT permission_value FROM owner_permissions WHERE owner_id = ? AND permission_key = ?");
    $stmt->bind_param("is", $owner_id, $requiredPermission);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $permission = $result->fetch_assoc();
        return (bool)$permission['permission_value'];
    }
    
    return false;
}

/**
 * Require specific permission for a page
 * Redirects to access denied if permission not granted
 */
function requirePermission($requiredPermission) {
    if (!checkPermission($requiredPermission)) {
        header('Location: index.php?error=access_denied');
        exit;
    }
}

/**
 * Get owner details
 */
function getOwnerDetails($owner_id = null) {
    global $conn;
    
    if ($owner_id === null && isset($_SESSION['owner_id'])) {
        $owner_id = $_SESSION['owner_id'];
    }
    
    if ($owner_id) {
        $stmt = $conn->prepare("SELECT * FROM owners WHERE id = ?");
        $stmt->bind_param("i", $owner_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    
    return null;
}
?>