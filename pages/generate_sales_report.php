<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: ../index.php");
    exit();
}

include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];

    // Validate the date inputs (you might want more robust validation)
    if (empty($start_date) || empty($end_date) || strtotime($end_date) < strtotime($start_date)) {
        header("Location: reports.php?error=Invalid date range");
        exit;
    }

    // Fetch sales data with items
    $sql = "SELECT
                s.id AS sale_id,
                s.created_at,
                s.total_amount,
                s.payment_type,
                c.customer_name,
                si.quantity,
                si.price_at_sale AS item_price,
                p.product_name
            FROM sale s
            LEFT JOIN customer c ON s.customer_id = c.id
            LEFT JOIN sale_item si ON s.id = si.sale_id
            LEFT JOIN product p ON si.product_id = p.id
            WHERE DATE(s.created_at) BETWEEN ? AND ?
            ORDER BY s.created_at ASC, s.id ASC"; // Order by sale ID to group items
    $stmt = $conn->prepare($sql);

    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $sales_data = [];
    while ($row = $result->fetch_assoc()) {
        $sales_data[] = $row;
    }
    $stmt->close();
    $conn->close();

    // Process sales data to group items by sale ID
    $processed_sales_data = [];
    foreach ($sales_data as $item) {
        $sale_id = $item['sale_id'];
        if (!isset($processed_sales_data[$sale_id])) {
            $processed_sales_data[$sale_id] = [
                'sale_id' => $item['sale_id'],
                'created_at' => $item['created_at'],
                'total_amount' => $item['total_amount'],
                'payment_type' => $item['payment_type'],
                'customer_name' => $item['customer_name'],
                'items' => []
            ];
        }
        if ($item['product_name']) { // Only add item if product name exists (meaning there was a sale_item)
            $processed_sales_data[$sale_id]['items'][] = [
                'product_name' => $item['product_name'],
                'quantity' => $item['quantity'],
                'item_price' => $item['item_price']
            ];
        }
        $processed_sales_data[$sale_id]['total_amount'] = $item['total_amount']; // Ensure total amount is correct
        $processed_sales_data[$sale_id]['payment_type'] = $item['payment_type']; // Ensure payment type is correct
        $processed_sales_data[$sale_id]['customer_name'] = $item['customer_name']; // Ensure customer name is correct
    }

    $sales_data = array_values($processed_sales_data); // Reset keys to be sequential
} else {
    header("Location: reports.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/smart-cashier-system/">
    <title>Sales Report - Smart Cashier System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container-with-sidebar">
        <?php include '../includes/sidebar.php'; ?>
        <div class="container">
            <header>
                <h1>Sales Report</h1>
            </header>
            <section>
                <p>Sales from <?php echo htmlspecialchars(date('Y-m-d', strtotime($start_date))); ?> to <?php echo htmlspecialchars(date('Y-m-d', strtotime($end_date))); ?></p>

                <?php if (empty($sales_data)): ?>
                    <p>No sales recorded within the selected date range.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sale ID</th>
                                <th>Sale Date</th>
                                <th>Total Amount</th>
                                <th>Payment Type</th>
                                <th>Customer Name</th>
                                <th>Items Sold</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales_data as $sale): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($sale['sale_id']); ?></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($sale['created_at']))); ?></td>
                                    <td>₱ <?php echo htmlspecialchars(number_format($sale['total_amount'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($sale['payment_type'])); ?></td>
                                    <td><?php echo htmlspecialchars($sale['customer_name'] ?: 'N/A'); ?></td>
                                    <td>
                                        <ul>
                                            <?php if (!empty($sale['items'])): ?>
                                                <?php foreach ($sale['items'] as $item): ?>
                                                    <li><?php echo htmlspecialchars($item['quantity']); ?> x <?php echo htmlspecialchars($item['product_name']); ?> (₱ <?php echo htmlspecialchars(number_format($item['item_price'], 2)); ?>)</li>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <li>No items recorded for this sale.</li>
                                            <?php endif; ?>
                                        </ul>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2">Total Sales:</th>
                                <th>
                                    ₱ <?php
                                        $total_sales = 0;
                                        foreach ($sales_data as $sale) {
                                            $total_sales += $sale['total_amount'];
                                        }
                                        echo htmlspecialchars(number_format($total_sales, 2));
                                    ?>
                                </th>
                                <th colspan="3"></th>
                            </tr>
                        </tfoot>
                    </table>
                <?php endif; ?>

                <p><a href="pages/reports.php">Back to Reports</a></p>
            </section>
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
    </script>
</body>
</html>