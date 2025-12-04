<?php
// includes/db_connect.php
// Database connection settings

$servername = "127.0.0.1:3307";
$username = "root";  // Default XAMPP username
$password = "";      // Default XAMPP password (empty)
$dbname = "cashier_db";  // Changed to match your existing database

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>