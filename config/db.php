<?php
// Database credentials (replace with your actual values)
$host = "127.0.0.1:3307"; // Updated to include port from your my.ini
$dbname = "cashier_db";
$username = "root";
$password = ""; // Empty password as per your config.inc.php

// Establish database connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>