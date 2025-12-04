<?php
include '../includes/session.php';
if (!isStoreOwner()) {
    header("Location: ../unauthorized.php");
    exit;
}
include '../config/db.php'; // Assuming this is the correct DB config path

$sale_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($sale_id <= 0) {
    header("Location: sales.php?error=Invalid sale ID");
    exit;
}

// Fetch sale details
$sql_sale = "SELECT s.*, c.customer_name FROM sale s LEFT JOIN customer c ON s.customer_id = c.id WHERE s.id = ?";
$stmt_sale = $conn->prepare($sql_sale);
$stmt_sale->bind_param("i", $sale_id);
$stmt_sale->execute();
$sale = $stmt_sale->get_result()->fetch_assoc();
$stmt_sale->close();

if (!$sale) {
    header("Location: sales.php?error=Sale not found");
    exit;
}

// Fetch sale items
$sql_items = "SELECT si.*, p.product_name FROM sale_item si JOIN product p ON si.product_id = p.id WHERE si.sale_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $sale_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();
$stmt_items->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/smart-cashier-system/">
    <title>Receipt #<?php echo $sale_id; ?> - Smart Cashier System</title>
    <link id="theme-stylesheet" rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Enhanced receipt styles */
        .receipt-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            font-family: Arial, sans-serif;
        }
        .receipt-header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .receipt-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .receipt-info div {
            flex: 1;
        }
        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .receipt-table th, .receipt-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .receipt-table th {
            background-color: #f4f4f4;
        }
        .receipt-total {
            text-align: right;
            font-weight: bold;
            font-size: 18px;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        .receipt-footer {
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 12px;
            color: #666;
        }
        .print-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        /* Print-specific styles */
        @media print {
            .print-button, .sidebar, header, footer { display: none; }
            .receipt-container { margin: 0; padding: 0; border: none; box-shadow: none; }
            body { background: none; }
        }
    </style>
</head>
<body>
    <div class="container-with-sidebar">
        <?php include '../includes/sidebar.php'; ?>

        <div class="container">
            <header>
                <h1>Receipt</h1>
            </header>

            <section>
                <div class="receipt-container">
                    <div class="receipt-header">
                        <h1>Smart Cashier System Receipt</h1>
                        <p>Receipt ID: #<?php echo $sale_id; ?></p>
                        <p>Date: <?php echo $sale['created_at']; ?></p>
                    </div>

                    <div class="receipt-info">
                        <div>
                            <strong>Customer:</strong> <?php echo htmlspecialchars($sale['customer_name'] ?? 'Anonymous'); ?><br>
                            <strong>Payment Type:</strong> <?php echo ucfirst($sale['payment_type']); ?>
                        </div>
                        <div style="text-align: right;">
                            <strong>Store:</strong> Techlaro Company<br>
                            <strong>Address:</strong> Sample Address, City, Country
                        </div>
                    </div>

                    <table class="receipt-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $items_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>₱<?php echo number_format($item['price_at_sale'], 2); ?></td>
                                    <td>₱<?php echo number_format($item['quantity'] * $item['price_at_sale'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                    <div class="receipt-total">
                        Total Amount: ₱<?php echo number_format($sale['total_amount'], 2); ?>
                    </div>

                    <div class="receipt-footer">
                        <p>Thank you for your purchase!</p>
                        <p>&copy; 2025 Techlaro Company</p>
                    </div>
                </div>

                <button class="print-button" onclick="window.print()">Print Receipt</button>
            </section>

            <footer>
                <p>&copy; 2025 Techlaro Company</p>
            </footer>
        </div>
    </div>
    <script src="assets/js/scripts.js"></script>
</body>
</html>