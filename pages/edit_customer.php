<?php
session_start();
include '../includes/session.php';
if (!isStoreOwner()) {
    header("Location: ../unauthorized.php");
    exit;
}
include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = $_POST['customer_name'];
    $contact_info = $_POST['contact_info'];

    $sql = "INSERT INTO customers (customer_name, contact_info, created_at)
            VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $customer_name, $contact_info);

    if ($stmt->execute()) {
        header("Location: customers.php?success=Customer added successfully");
    } else {
        header("Location: customers.php?error=Error adding customer: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: customers.php");
    exit;
}
?>