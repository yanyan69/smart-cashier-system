<?php
session_start();

function connect_db() {
    $host = "localhost"; // Replace with your database host
    $username = "your_db_user"; // Replace with your database username
    $password = "your_db_password"; // Replace with your database password
    $database = "your_db_name"; // Replace with your database name

    $conn = mysqli_connect($host, $username, $password, $database);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    return $conn;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function redirect_if_not_logged_in() {
    if (!is_logged_in()) {
        header("Location: /smart-cashier-system/login.php");
        exit;
    }
}

function is_admin() {
    return $_SESSION['role'] === 'admin';
}
?>