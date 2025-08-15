<?php include '../includes/session.php'; ?>
<?php
// Check if the user is a store owner
if (!isStoreOwner()) {
    header("Location: ../unauthorized.php"); // Redirect if not a store owner
    exit;
}

// Database connection
if (!isset($conn)) {
    $host = "localhost";
    $dbname = "cashier_db";
    $username = "root";
    $password = "admin";

    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}

// Fetch Total Sales for Today
$today_start = date("Y-m-d 00:00:00");
$today_end = date("Y-m-d 23:59:59");
$sales_query = "SELECT SUM(total_amount) AS total_sales FROM sales WHERE sale_date BETWEEN '$today_start' AND '$today_end'";
$sales_result = $conn->query($sales_query);
$total_sales_today = $sales_result->fetch_assoc()['total_sales'] ?: '0.00';

// Fetch Total Outstanding Credits
$credits_query = "SELECT SUM(balance) AS total_credits FROM credits WHERE status IN ('unpaid', 'partially_paid')";
$credits_result = $conn->query($credits_query);
$total_outstanding_credits = $credits_result->fetch_assoc()['total_credits'] ?: '0.00';

// Fetch Low Stock Items
$low_stock_query = "SELECT product_name FROM products WHERE stock < 5 LIMIT 5"; // Using 'stock' instead of 'stock_quantity'
$low_stock_result = $conn->query($low_stock_query);
$low_stock_items = [];
if ($low_stock_result->num_rows > 0) {
    while ($row = $low_stock_result->fetch_assoc()) {
        $low_stock_items[] = htmlspecialchars($row['product_name']);
    }
}

// Close the database connection if it's not managed elsewhere
if (!isset($_SESSION['db_connection'])) { // Assuming you might store the connection in the session
    $conn->close();
}

?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="content">
    <h1>Store Owner Dashboard</h1>
    <p>Welcome to your dashboard! Here you can see an overview of your store's activities, including recent sales, low stock items, and outstanding credits.</p>

    <div class="dashboard-widgets">
        <div class="widget">
            <h3>Total Sales (Today)</h3>
            <p>₱ <?php echo number_format($total_sales_today, 2); ?></p>
        </div>
        <div class="widget">
            <h3>Outstanding Credits</h3>
            <p>₱ <?php echo number_format($total_outstanding_credits, 2); ?></p>
        </div>
        <div class="widget">
            <h3>Low Stock Items</h3>
            <ul>
                <?php if (!empty($low_stock_items)): ?>
                    <?php foreach ($low_stock_items as $item): ?>
                        <li><?php echo $item; ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No low stock items at the moment.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>