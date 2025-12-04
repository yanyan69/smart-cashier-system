<?php
session_start();
include '../config/db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Fetch sales in range
$sale_ids_query = "SELECT id FROM sale WHERE user_id = ? AND created_at BETWEEN ? AND ?";
$stmt = $conn->prepare($sale_ids_query);
$stmt->bind_param("iss", $user_id, $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
$sale_ids = [];
while ($row = $result->fetch_assoc()) {
    $sale_ids[] = $row['id'];
}

echo "<h1>Sales Report from $start_date to $end_date</h1>";

if (!empty($sale_ids)) {
    $placeholders = implode(',', array_fill(0, count($sale_ids), '?'));
    $item_query = "SELECT s.sale_id, p.product_name, s.quantity, (s.quantity * s.price_at_sale) AS subtotal FROM sale_item s JOIN product p ON s.product_id = p.id WHERE s.sale_id IN ($placeholders)";
    $item_stmt = $conn->prepare($item_query);
    $item_stmt->bind_param(str_repeat('i', count($sale_ids)), ...$sale_ids);
    $item_stmt->execute();
    $items_result = $item_stmt->get_result();

    echo "<table border='1'><tr><th>Sale ID</th><th>Product</th><th>Quantity</th><th>Subtotal</th></tr>";
    while ($item = $items_result->fetch_assoc()) {
        echo "<tr><td>{$item['sale_id']}</td><td>{$item['product_name']}</td><td>{$item['quantity']}</td><td>₱" . number_format($item['subtotal'], 2) . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>No sales in the selected period.</p>";
}

$conn->close();
?>