<?php
session_start();
include '../includes/session.php';
if (!isStoreOwner()) {
    header("Location: ../unauthorized.php");
    exit;
}
include '../config/db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $customer_id = $_GET['id'];

    $sql = "DELETE FROM customers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customer_id);

    if ($stmt->execute()) {
        header("Location: customers.php?success=Customer deleted successfully");
    } else {
        header("Location: customers.php?error=Error deleting customer: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: customers.php?error=Invalid customer ID for deletion");
    exit;
}
?>