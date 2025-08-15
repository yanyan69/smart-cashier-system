<?php
$host = 'localhost';
$user = 'root';
$pass = 'admin';
$dbname = 'cashier_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: uncomment to test
// echo "Database connected successfully!";
?>