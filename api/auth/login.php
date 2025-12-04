<?php
session_start(); // Start the session at the very beginning

// Database credentials (replace with your actual values)
$host = "127.0.0.1:3307"; // Updated to include port from your my.ini
$dbname = "cashier_db";
$username = "root";
$password = ""; // Empty password as per your config.inc.php

// Establish database connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['username']) && isset($input['password'])) {
    $username = $input['username'];
    $password = $input['password'];

    error_log("Login attempt for username: " . $username);

    $stmt = $conn->prepare("SELECT id, username, password, role FROM `user` WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        error_log("User found, verifying password...");
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            echo json_encode(['status' => 'success', 'role' => $user['role']]);
            exit();
        } else {
            error_log("Invalid password for user: " . $username);
            echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
            exit();
        }
    } else {
        error_log("User not found: " . $username);
        echo json_encode(['status' => 'error', 'message' => 'Invalid username']);
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}
?>