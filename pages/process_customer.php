<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'update') {
        $id = intval($_POST['id']);
        $customer_name = $_POST['customer_name'];
        $contact_info = $_POST['contact_info'];

        $stmt = $conn->prepare("UPDATE customer SET customer_name = ?, contact_info = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssii", $customer_name, $contact_info, $id, $user_id);

        if ($stmt->execute()) {
            header("Location: customers.php?success=Customer updated");
        } else {
            header("Location: customers.php?error=Update failed");
        }
    } elseif ($action === 'add') {
        $customer_name = $_POST['customer_name'];
        $contact_info = $_POST['contact_info'];

        $stmt = $conn->prepare("INSERT INTO customer (customer_name, contact_info, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $customer_name, $contact_info, $user_id);

        if ($stmt->execute()) {
            header("Location: customers.php?success=Customer added");
        } else {
            header("Location: customers.php?error=Add failed");
        }
    }
} elseif ($action === 'delete') {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM customer WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);

    if ($stmt->execute()) {
        header("Location: customers.php?success=Customer deleted");
    } else {
        header("Location: customers.php?error=Delete failed");
    }
} else {
    header("Location: customers.php");
}

$conn->close();
?>