<?php
$servername = "localhost";
$username = "root"; // or your username
$password = ""; // or your password
$dbname = "marsilase_pastry"; // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// DON'T close the connection here!
?>