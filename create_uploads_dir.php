<?php
// create_uploads_dir.php - Run this once
$upload_dir = 'uploads/products/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
    echo "Uploads directory created successfully at: $upload_dir";
    echo "<br>Directory structure created:";
    echo "<pre>";
    echo "your-project/
├── uploads/
│   └── products/
│       └── (product images will be stored here)";
    echo "</pre>";
} else {
    echo "Uploads directory already exists at: $upload_dir";
}

// Also check if directory is writable
echo "<br>Directory writable: " . (is_writable($upload_dir) ? " Yes" : "❌ No");

// Show current permissions
echo "<br>Current directory permissions: ";
echo substr(sprintf('%o', fileperms($upload_dir)), -4);
?>