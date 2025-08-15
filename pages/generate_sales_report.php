<?php
include '../includes/session.php';
if (!isStoreOwner()) {
    header("Location: ../unauthorized.php");
    exit;
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
                s.sale_date,
                s.total_amount,
                s.payment_type,
                c.customer_name,
                sd.quantity,
                sd.price_per_item AS item_price,
                p.product_name
            FROM sales s
            LEFT JOIN customers c ON s.customer_id = c.id
            LEFT JOIN sales_details sd ON s.id = sd.sale_id
            LEFT JOIN products p ON sd.product_id = p.id
            WHERE DATE(s.sale_date) BETWEEN ? AND ?
            ORDER BY s.sale_date ASC, s.id ASC"; // Order by sale ID to group items
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
                'sale_date' => $item['sale_date'],
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

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="content">
    <h1>Sales Report</h1>
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
                        <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($sale['sale_date']))); ?></td>
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

    <p><a href="reports.php">Back to Reports</a></p>
</div>

<?php include '../includes/footer.php'; ?>