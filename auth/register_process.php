<?php
session_start();
include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        header("Location: ../auth/register.php?error=Passwords do not match");
        exit();
    }

    // Check if username exists
    $stmt = $conn->prepare("SELECT username FROM `user` WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header("Location: ../auth/register.php?error=Username already exists");
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user (role defaults to 'owner' in DB)
    $stmt = $conn->prepare("INSERT INTO `user` (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);

    if ($stmt->execute()) {
        // Clear any default data if needed (e.g., truncate tables, but caution!)
        $conn->query("DELETE FROM `product` WHERE id > 0");  // Example: reset products
        $conn->query("DELETE FROM `customer` WHERE id > 0");
        header("Location: ../index.php?success=Registration successful. Please login.");
        exit();
    } else {
        header("Location: ../auth/register.php?error=Registration failed");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../auth/register.php");
    exit();
}
?>