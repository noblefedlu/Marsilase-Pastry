<?php
$servername = "localhost";
$username = "root"; // or your username
$password = ""; // or your password
$dbname = "marsilase_pastry"; // your database name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Connection successful
// Don't close the connection here!
?>