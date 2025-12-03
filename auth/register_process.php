<?php
session_start();
include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

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