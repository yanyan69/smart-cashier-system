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
$sale_ids_query = "SELECT id FROM sale WHERE user_id = ? AND created_at >= ? AND created_at < DATE_ADD(?, INTERVAL 1 DAY)";
$stmt = $conn->prepare($sale_ids_query);
$stmt->bind_param("iss", $user_id, $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
$sale_ids = [];
while ($row = $result->fetch_assoc()) {
    $sale_ids[] = $row['id'];
}
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/smart-cashier-system/">
    <title>Sales Report - Smart Cashier System</title>
    <link id="theme-stylesheet" rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Enhanced report styles similar to receipt */
        .report-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            font-family: Arial, sans-serif;
            width: 100%;
        }
        .report-header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .report-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .report-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .report-info div {
            flex: 1;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .report-table th, .report-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .report-table th {
            background-color: #f4f4f4;
        }
        .report-total {
            text-align: right;
            font-weight: bold;
            font-size: 18px;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        .report-footer {
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
            .print-button, .sidebar, header, footer, .back-button { display: none; }
            .report-container { margin: 0; padding: 0; border: none; box-shadow: none; }
            body { background: none; }
        }
        /* Responsive adjustments */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        @media (max-width: 768px) {
            .report-container {
                padding: 10px;
                margin: 10px;
            }
            .report-table th, .report-table td {
                padding: 4px;
                font-size: 12px;
            }
            .report-header h1 {
                font-size: 18px;
            }
            .report-info {
                flex-direction: column;
            }
            .report-info div:last-child {
                text-align: left;
                margin-top: 10px;
            }
            .report-total {
                font-size: 16px;
            }
            .report-footer {
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container-with-sidebar">
        <?php include '../includes/sidebar.php'; ?>

        <div class="container">
            <header>
                <h1>Sales Report</h1>
            </header>

            <section>
                <div class="report-container">
                    <div class="report-header">
                        <h1>Smart Cashier System Sales Report</h1>
                        <p>Date Range: <?php echo $start_date; ?> to <?php echo $end_date; ?></p>
                    </div>

                    <div class="report-info">
                        <div>
                            <strong>Store:</strong> Techlaro Company<br>
                            <strong>Address:</strong> Sample Address, City, Country
                        </div>
                        <div style="text-align: right;">
                            <strong>Report Generated:</strong> <?php echo date('Y-m-d H:i:s'); ?>
                        </div>
                    </div>

                    <?php
                    if (!empty($sale_ids)) {
                        $placeholders = implode(',', array_fill(0, count($sale_ids), '?'));
                        $item_query = "SELECT p.product_name, SUM(s.quantity) AS total_quantity, SUM(s.quantity * s.price_at_sale) AS total_subtotal 
                                       FROM sale_item s JOIN product p ON s.product_id = p.id 
                                       WHERE s.sale_id IN ($placeholders) 
                                       GROUP BY p.product_name";
                        $item_stmt = $conn->prepare($item_query);
                        $item_stmt->bind_param(str_repeat('i', count($sale_ids)), ...$sale_ids);
                        $item_stmt->execute();
                        $items_result = $item_stmt->get_result();
                        $item_stmt->close();

                        $grand_total = 0;

                        echo '<div class="table-responsive">';
                        echo '<table class="report-table">';
                        echo '<thead><tr><th>Product</th><th>Total Quantity Sold</th><th>Total Amount</th></tr></thead>';
                        echo '<tbody>';
                        while ($item = $items_result->fetch_assoc()) {
                            $total_subtotal = $item['total_subtotal'];
                            $grand_total += $total_subtotal;
                            echo "<tr><td>" . htmlspecialchars($item['product_name']) . "</td><td>" . $item['total_quantity'] . "</td><td>₱" . number_format($total_subtotal, 2) . "</td></tr>";
                        }
                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';

                        echo '<div class="report-total">';
                        echo 'Grand Total: ₱' . number_format($grand_total, 2);
                        echo '</div>';
                    } else {
                        echo "<p>No sales in the selected period.</p>";
                    }
                    ?>

                    <div class="report-footer">
                        <p>For BIR Submission - Sales Summary</p>
                        <p>&copy; 2025 Techlaro Company</p>
                    </div>
                </div>

                <button class="print-button" onclick="window.print()">Print Report</button>
                <a href="pages/reports.php" class="button back-button" style="display: block; margin: 20px auto; text-align: center; width: fit-content;">Back to Reports</a>
            </section>

            <footer>
                <p>&copy; 2025 Techlaro Company</p>
            </footer>
        </div>
    </div>
    <script src="assets/js/scripts.js"></script>
    <script>
        window.onload = function() {
            const container = document.querySelector('.container');
            if (container) {
                container.scrollIntoView({ behavior: 'smooth' });
            }
        };
    </script>
</body>
</html>

<?php
$conn->close();
?>