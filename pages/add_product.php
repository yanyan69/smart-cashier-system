<?php
include '../includes/session.php';
if (!isStoreOwner()) {
    header("Location: ../unauthorized.php");
    exit;
}
include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    $sql = "INSERT INTO products (product_name, description, category, price, stock, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdii", $product_name, $description, $category, $price, $stock);

    if ($stmt->execute()) {
        header("Location: products.php?success=Product added successfully");
    } else {
        header("Location: products.php?error=Error adding product: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: products.php");
    exit;
}
?>