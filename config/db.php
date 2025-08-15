<?php
// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', 'admin');
define('DB_NAME', 'cashier_db');

// Optional: Database port
define('DB_PORT', '3306');

// Optional: Table prefix
define('DB_PREFIX', '');
// Establish database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>