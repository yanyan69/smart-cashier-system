<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: /smart-cashier-system/index.php");
    exit();
}

include '../config/db.php';

// Get today's date in YYYY-MM-DD format
$today = date('Y-m-d');

// For Inventory Report: Fetch all products, sort by stock low to high
$inventory_query = "SELECT id, product_name, price, stock FROM product ORDER BY stock ASC";
$inventory_result = $conn->query($inventory_query);

$low_stock_threshold = 10; // Define low stock as < 10

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/smart-cashier-system/">
    <title>Reports - Smart Cashier System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container-with-sidebar">
        <?php include '../includes/sidebar.php'; ?>

        <div class="container">
            <header>
                <h1>Reports</h1>
            </header>

            <section>
                <p>Generate reports on sales, inventory, and customer credits for different time periods.</p>

                <h2>Sales Report</h2>
                <form action="pages/generate_sales_report.php" method="GET">
                    <div class="form-group">
                        <label for="start_date">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo $today; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">End Date:</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo $today; ?>" required>
                    </div>
                    <button type="submit" class="button">Generate Sales Report</button>
                </form>

                <h2>Inventory Report</h2>
                <p>Current stock levels. Low stock items (below <?php echo $low_stock_threshold; ?>) are highlighted in red.</p>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = $inventory_result->fetch_assoc()): ?>
                            <tr <?php if ($product['stock'] < $low_stock_threshold) echo 'style="background-color: #ffcccc;"'; ?>>
                                <td><?php echo $product['id']; ?></td>
                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo $product['stock']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <h2>Credit Report</h2>
                <p>View outstanding credits summary.</p>
                <a href="pages/credits.php" class="button">Go to Credits</a>
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