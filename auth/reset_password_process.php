<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        header("Location: reset_password_form.php?token=$token&error=Passwords do not match");
        exit();
    }

    $stmt = $conn->prepare("SELECT id FROM `user` WHERE reset_token = ? AND reset_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $update_stmt = $conn->prepare("UPDATE `user` SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
        $update_stmt->bind_param("si", $hashed_password, $user['id']);
        if ($update_stmt->execute()) {
            header("Location: ../index.php?success=Password reset successful. Please login.");
        } else {
            header("Location: reset_password_form.php?token=$token&error=Failed to update password.");
        }
    } else {
        header("Location: ../index.php?error=Invalid or expired token.");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../index.php");
    exit();
}
?>