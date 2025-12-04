<?php
session_start();
include '../includes/session.php';
if (!isStoreOwner()) {
    header("Location: ../unauthorized.php");
    exit;
}
include '../config/db.php';
include '../includes/functions.php'; // For logMessage if needed

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

if ($action === 'add' && $_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $unit = $_POST['unit'];
    $category = isset($_POST['category']) ? $_POST['category'] : '';

    $sql = "INSERT INTO product (product_name, price, stock, unit, category, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sddss", $product_name, $price, $stock, $unit, $category);

    if ($stmt->execute()) {
        logMessage("Product added: $product_name");
        header("Location: products.php?success=Product added successfully");
    } else {
        header("Location: products.php?error=Error adding product: " . $stmt->error);
    }

    $stmt->close();
} elseif ($action === 'update' && $_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $unit = $_POST['unit'];
    $category = isset($_POST['category']) ? $_POST['category'] : '';

    $sql = "UPDATE product SET product_name = ?, price = ?, stock = ?, unit = ?, category = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sddssi", $product_name, $price, $stock, $unit, $category, $id);

    if ($stmt->execute()) {
        logMessage("Product updated: ID $id - $product_name");
        header("Location: products.php?success=Product updated successfully");
    } else {
        header("Location: products.php?error=Error updating product: " . $stmt->error);
    }

    $stmt->close();
} elseif ($action === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $sql = "DELETE FROM product WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        logMessage("Product deleted: ID $id");
        header("Location: products.php?success=Product deleted successfully");
    } else {
        header("Location: products.php?error=Error deleting product: " . $stmt->error);
    }

    $stmt->close();
} else {
    header("Location: products.php?error=Invalid action");
}

$conn->close();
exit;