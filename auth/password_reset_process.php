<?php
session_start();
require_once '../config/db.php'; // Use db.php for connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];

    if (empty($username)) {
        header("Location: password_reset.php?error=Please enter your username.");
        exit();
    }

    $stmt = $conn->prepare("SELECT id FROM `user` WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];

        // Secure token generation
        $reset_token = bin2hex(random_bytes(16));
        $expiry_time = date("Y-m-d H:i:s", time() + 3600); // 1 hour

        $update_stmt = $conn->prepare("UPDATE `user` SET reset_token = ?, reset_expiry = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $reset_token, $expiry_time, $user_id);
        $update_stmt->execute();

        // No email: For testing, display the link (in production, send via email or other means)
        $reset_link = "http://localhost/smart-cashier-system/auth/reset_password_form.php?token=$reset_token";
        header("Location: password_reset.php?success=Reset link generated (for testing): $reset_link");
    } else {
        header("Location: password_reset.php?error=Username not found.");
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: password_reset.php");
    exit();
}
?>