<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../index.php?error=Unauthorized");
    exit();
}

include '../config/db.php';

$customer_id = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : null;
$payment_type = $_POST['payment_type'];
$product_ids = $_POST['product_id'];
$quantities = $_POST['quantity'];
$total_amount = 0;

// Calculate total and validate stock
for ($i = 0; $i < count($product_ids); $i++) {
    $product_id = intval($product_ids[$i]);
    $quantity = intval($quantities[$i]);

    $product_query = "SELECT price, stock FROM product WHERE id = $product_id";
    $product = $conn->query($product_query)->fetch_assoc();

    if ($product['stock'] < $quantity) {
        header("Location: sales.php?error=Insufficient stock for product ID $product_id");
        exit();
    }

    $total_amount += $product['price'] * $quantity;
}

$cash_tendered = ($payment_type === 'cash') ? floatval($_POST['cash_tendered']) : null;
$change_given = ($payment_type === 'cash') ? $cash_tendered - $total_amount : null;

if ($payment_type === 'cash' && $change_given < 0) {
    header("Location: sales.php?error=Insufficient cash tendered");
    exit();
}

// Insert sale
$stmt = $conn->prepare("INSERT INTO sale (customer_id, total_amount, payment_type, cash_tendered, change_given) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("idssd", $customer_id, $total_amount, $payment_type, $cash_tendered, $change_given);
$stmt->execute();
$sale_id = $stmt->insert_id;

// Insert sale items and update stock
for ($i = 0; $i < count($product_ids); $i++) {
    $product_id = intval($product_ids[$i]);
    $quantity = intval($quantities[$i]);

    $product_query = "SELECT price FROM product WHERE id = $product_id";
    $price_at_sale = $conn->query($product_query)->fetch_assoc()['price'];

    $stmt = $conn->prepare("INSERT INTO sale_item (sale_id, product_id, quantity, price_at_sale) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $sale_id, $product_id, $quantity, $price_at_sale);
    $stmt->execute();

    // Update stock
    $conn->query("UPDATE product SET stock = stock - $quantity WHERE id = $product_id");
}

// If customer, update total_purchases
if ($customer_id) {
    $conn->query("UPDATE customer SET total_purchases = total_purchases + $total_amount WHERE id = $customer_id");
}

// If credit, insert credit
if ($payment_type === 'credit') {
    $stmt = $conn->prepare("INSERT INTO credit (customer_id, sale_id, amount_owed, amount_paid, status) VALUES (?, ?, ?, 0, 'unpaid')");
    $stmt->bind_param("iid", $customer_id, $sale_id, $total_amount);
    $stmt->execute();
}

$conn->close();

header("Location: receipt.php?sale_id=$sale_id&success=Sale completed successfully");
exit();
?>