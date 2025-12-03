<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: /smart-cashier-system/index.php?error=Unauthorized");
    exit();
}

if (!isset($_GET['sale_id'])) {
    header("Location: sales.php?error=Missing sale ID");
    exit();
}

$sale_id = intval($_GET['sale_id']);
include '../config/db.php';

// Fetch sale details including cash_tendered and change_given
$sale_query = "SELECT s.total_amount, s.payment_type, s.created_at, s.cash_tendered, s.change_given, c.customer_name 
               FROM sale s LEFT JOIN customer c ON s.customer_id = c.id WHERE s.id = $sale_id";
$sale = $conn->query($sale_query)->fetch_assoc();

// Fetch items
$items_query = "SELECT p.product_name, si.quantity, si.price_at_sale 
                FROM sale_item si JOIN product p ON si.product_id = p.id WHERE si.sale_id = $sale_id";
$items_result = $conn->query($items_query);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/smart-cashier-system/">
    <title>Receipt - Smart Cashier System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .receipt { border: 1px solid #ccc; padding: 20px; max-width: 400px; margin: auto; background: #fff; color: #000; }
        .receipt h2 { text-align: center; }
        .receipt table { width: 100%; border-collapse: collapse; }
        .receipt th, .receipt td { border: 1px solid #ddd; padding: 8px; }
        @media print {
            body * { visibility: hidden; }
            .receipt, .receipt * { visibility: visible; }
            .receipt { position: absolute; left: 0; top: 0; width: 100%; max-width: none; margin: 0; padding: 0; }
            .container-with-sidebar .sidebar, footer, .form-group { display: none; }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>
    <div class="container-with-sidebar">
        <?php include '../includes/sidebar.php'; ?>
        <div class="container">
            <header>
                <h1>Transaction Receipt</h1>
            </header>

            <section class="receipt" id="receipt-content">
                <h2>Techlaro Cashier System</h2>
                <p>Transaction ID: #<?php echo sprintf('%06d', $sale_id); ?></p>
                <p>Date: <?php echo $sale['created_at']; ?></p>
                <p>Customer: <?php echo $sale['customer_name'] ?? 'Anonymous'; ?></p>
                <p>Payment Type: <?php echo ucfirst($sale['payment_type']); ?></p>

                <table>
                    <thead>
                        <tr><th>Product</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr>
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

                <p><strong>Total: ₱<?php echo number_format($sale['total_amount'], 2); ?></strong></p>
                <?php if ($sale['payment_type'] === 'cash'): ?>
                    <p>Cash Tendered: ₱<?php echo number_format($sale['cash_tendered'] ?? 0, 2); ?></p>
                    <p>Change: ₱<?php echo number_format($sale['change_given'] ?? 0, 2); ?></p>
                <?php endif; ?>
            </section>

            <div class="form-group">
                <button onclick="window.print()" class="button">Print Receipt</button>
                <button onclick="saveAsPDF()" class="button">Save as PDF</button>
                <a href="pages/sales.php" class="button">Make Another Sale</a>
            </div>

            <footer>
                <p>&copy; 2025 Techlaro Company</p>
            </footer>
        </div>
    </div>
    <script>
        window.onload = function() {
            const container = document.querySelector('.container');
            if (container) {
                container.scrollIntoView({ behavior: 'smooth' });
            }
        };

        function saveAsPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            const element = document.getElementById('receipt-content');
            doc.html(element, {
                callback: function (doc) {
                    doc.save('receipt_<?php echo $sale_id; ?>.pdf');
                },
                x: 10,
                y: 10,
                width: 190,
                windowWidth: element.scrollWidth
            });
        }
    </script>
</body>
</html>