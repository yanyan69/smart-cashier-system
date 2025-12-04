<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: /smart-cashier-system/index.php");
    exit();
}

include '../config/db.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$today = date('Y-m-d');

// Inventory Report: With scoping, pagination (no tags, but sorted by stock)
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$where = ($role === 'owner') ? "WHERE (user_id = $user_id OR user_id IS NULL)" : "";

$total_query = "SELECT COUNT(*) FROM product $where";
$total = $conn->query($total_query)->fetch_row()[0];
$pages = ceil($total / $limit);

$inventory_query = "SELECT id, product_name, price, stock, unit FROM product $where ORDER BY stock ASC LIMIT $limit OFFSET $offset";
$inventory_result = $conn->query($inventory_query);

$low_stock_threshold = 10;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/smart-cashier-system/">
    <title>Reports - Smart Cashier System</title>
    <link id="theme-stylesheet" rel="stylesheet" href="assets/css/style.css">
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
                    <div class="form-group date-filter" style="display: flex; gap: 10px;">
                        <div>
                            <label for="start_date">Start Date:</label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo $today; ?>" required>
                        </div>
                        <div>
                            <label for="end_date">End Date:</label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo $today; ?>" required>
                        </div>
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
                                <td><?php echo $product['stock']; ?> <?php echo $product['unit']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" <?php if ($i == $page) echo 'class="active"'; ?>><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>

                <h2>Credit Report</h2>
                <p>View outstanding credits summary.</p>
                <a href="pages/credits.php" class="button">Go to Credits</a>
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