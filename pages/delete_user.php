<?php
session_start();
include '../includes/session.php';
if (!isAdmin()) {
    header("Location: ../unauthorized.php");
    exit;
}
include '../config/db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = $_GET['id'];

    // Prevent deleting own account
    if ($_SESSION['user_id'] === $user_id) {
        header("Location: admin_panel.php?action=manage_users&error=You cannot delete your own account.");
        exit;
    }

    $stmt_delete = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt_delete->bind_param("i", $user_id);

    if ($stmt_delete->execute()) {
        header("Location: admin_panel.php?action=manage_users&success=User deleted successfully");
    } else {
        header("Location: admin_panel.php?action=manage_users&error=Error deleting user: " . $stmt_delete->error);
    }

    $stmt_delete->close();
    $conn->close();
} else {
    header("Location: admin_panel.php?action=manage_users&error=Invalid user ID for deletion");
    exit;
}
?>