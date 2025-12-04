<?php
session_start();
include '../config/db.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_id = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : null;
    $payment_type = $_POST['payment_type'];
    $product_ids = $_POST['product_id'];
    $quantities = $_POST['quantity'];

    $total_amount = 0;
    foreach ($product_ids as $index => $product_id) {
        $qty = $quantities[$index];
        $stmt = $conn->prepare("SELECT price, stock FROM product WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $product_id, $user_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        if ($product && $product['stock'] >= $qty) {
            $subtotal = $product['price'] * $qty;
            $total_amount += $subtotal;
        } else {
            header("Location: sales.php?error=Invalid product or insufficient stock");
            exit();
        }
    }

    // Insert sale
    $stmt = $conn->prepare("INSERT INTO sale (user_id, customer_id, total_amount, payment_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iids", $user_id, $customer_id, $total_amount, $payment_type);
    $stmt->execute();
    $sale_id = $stmt->insert_id;

    // Insert sale items and update stock
    foreach ($product_ids as $index => $product_id) {
        $qty = $quantities[$index];
        $stmt = $conn->prepare("SELECT price FROM product WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $price = $stmt->get_result()->fetch_assoc()['price'];

        $stmt = $conn->prepare("INSERT INTO sale_item (sale_id, product_id, quantity, price_at_sale) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $sale_id, $product_id, $qty, $price);
        $stmt->execute();

        // Update stock
        $stmt = $conn->prepare("UPDATE product SET stock = stock - ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $qty, $product_id, $user_id);
        $stmt->execute();
    }

    if ($payment_type === 'credit') {
        // Insert credit
        $stmt = $conn->prepare("INSERT INTO credit (user_id, customer_id, sale_id, amount_owed) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $user_id, $customer_id, $sale_id, $total_amount);
        $stmt->execute();
    } else {
        // Handle cash, perhaps log payment
    }

    header("Location: sales.php?success=Sale completed");
    exit();
} elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM sale WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    if ($stmt->execute()) {
        header("Location: sales.php?success=Sale deleted");
    } else {
        header("Location: sales.php?error=Failed to delete sale");
    }
}

$conn->close();
?>