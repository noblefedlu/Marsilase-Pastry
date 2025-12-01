<?php
session_start();
require_once '../common/connection.php';
requireOwner();

echo "<h2>Image Upload Diagnostic</h2>";
echo "<pre>";

// Check basic PHP settings
echo "=== PHP Settings ===\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";

// Check uploads directory
$upload_dir = '../uploads/products/';
echo "\n=== Directory Check ===\n";
echo "Upload directory: $upload_dir\n";
echo "Directory exists: " . (is_dir($upload_dir) ? 'YES' : 'NO') . "\n";
if (is_dir($upload_dir)) {
    echo "Is writable: " . (is_writable($upload_dir) ? 'YES' : 'NO') . "\n";
    echo "Permissions: " . substr(sprintf('%o', fileperms($upload_dir)), -4) . "\n";
} else {
    // Try to create it
    if (mkdir($upload_dir, 0755, true)) {
        echo "✅ Created directory successfully\n";
    } else {
        echo "❌ Failed to create directory\n";
    }
}

// Test form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "\n=== Form Submission Data ===\n";
    echo "POST data: " . print_r($_POST, true) . "\n";
    echo "FILES data: " . print_r($_FILES, true) . "\n";
    
    if (isset($_FILES['test_image'])) {
        $file = $_FILES['test_image'];
        echo "File error code: " . $file['error'] . "\n";
        
        if ($file['error'] === 0) {
            $filename = 'test_' . uniqid() . '.jpg';
            $target_path = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                echo "✅ File moved successfully to: $target_path\n";
                echo "File exists: " . (file_exists($target_path) ? 'YES' : 'NO') . "\n";
                echo "File size: " . filesize($target_path) . " bytes\n";
            } else {
                echo "❌ Failed to move uploaded file\n";
                echo "Temp file exists: " . (file_exists($file['tmp_name']) ? 'YES' : 'NO') . "\n";
            }
        } else {
            echo "File upload error: " . $file['error'] . "\n";
        }
    }
}

echo "</pre>";
?>

<h3>Test Upload Form</h3>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="test_image" accept="image/*" required>
    <button type="submit">Test Upload</button>
</form>