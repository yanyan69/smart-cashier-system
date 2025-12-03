<?php
session_start();
$conn = new mysqli('127.0.0.1:3307', 'root', '', 'cashier_db');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
echo 'Connected successfully';
?>