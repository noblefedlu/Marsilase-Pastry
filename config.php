<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$database_host = 'localhost';
$database_username = 'root';
$database_password = '';
$database_name = 'marsilase_pastry';

// Create connection
$conn = mysqli_connect($database_host, $database_username, $database_password, $database_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>

