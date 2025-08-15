<?php
include '../config/db.php'; // Include the database configuration file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'owner'; // Default role for registered users

    if ($password !== $confirm_password) {
        header("Location: register.php?error=Passwords do not match");
        exit();
    }

    // Check if username already exists
    $stmt_check = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        header("Location: register.php?error=Username already exists");
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user into the database
    $stmt_insert = $conn->prepare("INSERT INTO users (username, password, role, created_at) VALUES (?, ?, ?, NOW())");
    $stmt_insert->bind_param("sss", $username, $hashed_password, $role);

    if ($stmt_insert->execute()) {
        header("Location: ../index.php?success=Registration successful, please log in");
    } else {
        header("Location: register.php?error=Registration failed: " . $stmt_insert->error);
    }

    $stmt_insert->close();
    $conn->close();
} else {
    header("Location: register.php");
    exit();
}
?>