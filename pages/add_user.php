<?php
include '../includes/session.php';
if (!isAdmin()) {
    header("Location: ../unauthorized.php");
    exit;
}
include '../config/db.php';
include '../includes/functions.php'; // Include the functions file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Check if username already exists
    $stmt_check = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        header("Location: admin_panel.php?action=manage_users&error=Username already exists");
        exit;
    }
    $stmt_check->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt_insert = $conn->prepare("INSERT INTO users (username, password, role, created_at) VALUES (?, ?, ?, NOW())");
    $stmt_insert->bind_param("sss", $username, $hashed_password, $role);

    if ($stmt_insert->execute()) {
        logMessage("Admin user '{$_SESSION['username']}' added new user '{$username}' with role '{$role}'.", 'admin');
        header("Location: admin_panel.php?action=manage_users&success=User added successfully");
    } else {
        logMessage("Error adding user '{$username}': " . $stmt_insert->error, 'error');
        header("Location: admin_panel.php?action=manage_users&error=Error adding user: " . $stmt_insert->error);
    }

    $stmt_insert->close();
    $conn->close();
} else {
    header("Location: admin_panel.php?action=manage_users");
    exit;
}
?>