<?php
// Start the session to access user data
session_start();

// Check if the user is logged in and has the 'owner' role; redirect if not
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: /smart-cashier-system/assets/html/index.html");
    exit();
}

// Database credentials (updated for custom port and empty password)
$host = "127.0.0.1:3307"; // Host with port from my.ini configuration
$dbname = "cashier_db"; // Database name
$username = "root"; // Database username
$password = ""; // Empty password as per config

// Establish the database connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check for connection errors and terminate if failed
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch total sales data from the 'sale' table
$total_sales_query = "SELECT SUM(total_amount) AS total_sales FROM sale";
$total_sales_result = $conn->query($total_sales_query);
$total_sales = $total_sales_result->fetch_assoc()['total_sales'] ?? 0;

// Fetch total products
$total_products_query = "SELECT COUNT(*) AS total_products FROM product WHERE created_at > '1970-01-01'";
$total_products_result = $conn->query($total_products_query);
$total_products = $total_products_result->fetch_assoc()['total_products'] ?? 0;

// Fetch total customers
$total_customers_query = "SELECT COUNT(*) AS total_customers FROM customer WHERE created_at > '1970-01-01'";
$total_customers_result = $conn->query($total_customers_query);
$total_customers = $total_customers_result->fetch_assoc()['total_customers'] ?? 0;

// Fetch outstanding credits
$outstanding_credits_query = "SELECT SUM(amount_owed - amount_paid) AS outstanding FROM credit WHERE status != 'paid'";
$outstanding_credits_result = $conn->query($outstanding_credits_query);
$outstanding_credits = $outstanding_credits_result->fetch_assoc()['outstanding'] ?? 0;

// Fetch low stock products from the 'product' table
$low_stock_query = "SELECT product_name, stock FROM product WHERE stock < 10";
$low_stock_result = $conn->query($low_stock_query);

// Fetch recent sales with customer info using JOIN on 'sale' and 'customer' tables
$recent_sales_query = "SELECT s.id, c.customer_name, s.total_amount, s.created_at 
                      FROM sale s LEFT JOIN customer c ON s.customer_id = c.id 
                      ORDER BY s.created_at DESC LIMIT 5";
$recent_sales_result = $conn->query($recent_sales_query);

// Close the database connection
$conn->close();

// Determine greeting based on time of day
$hour = date('G');
if ($hour >= 5 && $hour < 12) {
    $greeting = "Good morning";
} elseif ($hour >= 12 && $hour < 18) {
    $greeting = "Good afternoon";
} else {
    $greeting = "Good evening";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags for character set and responsive design -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Base tag to fix relative paths across different directories -->
    <base href="/smart-cashier-system/">
    <!-- Page title -->
    <title>Dashboard - Smart Cashier System</title>
    <!-- Link to external CSS stylesheet -->
    <link id="theme-stylesheet" rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Main container with sidebar -->
    <div class="container-with-sidebar">
        <?php include '../includes/sidebar.php'; ?>
        <!-- Main content container -->
        <div class="container">
            <!-- Header section -->
            <header>
                <h1>Store Owner Dashboard</h1>
                <p><?php echo $greeting . ", " . htmlspecialchars($_SESSION['username']) . "!"; ?></p>
            </header>
            <!-- Dashboard overview section -->
            <section>
                <!-- Overview heading -->
                <h2>Overview</h2>
                <!-- Display statistics in a vertical table -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>Statistic</th>
                            <th>Value</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Total Sales</td>
                            <td>$<?php echo number_format($total_sales, 2); ?></td>
                            <td><a href="pages/sales.php" class="button small">View Sales</a></td>
                        </tr>
                        <tr>
                            <td>Total Products</td>
                            <td><?php echo $total_products; ?></td>
                            <td><a href="pages/products.php" class="button small">Manage Products</a></td>
                        </tr>
                        <tr>
                            <td>Total Customers</td>
                            <td><?php echo $total_customers; ?></td>
                            <td><a href="pages/customers.php" class="button small">Manage Customers</a></td>
                        </tr>
                        <tr>
                            <td>Outstanding Credits</td>
                            <td>$<?php echo number_format($outstanding_credits, 2); ?></td>
                            <td><a href="pages/credits.php" class="button small">Manage Credits</a></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Low stock products section -->
                <h2>Low Stock Products</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Stock</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($low_stock_result->num_rows > 0): ?>
                            <?php while ($row = $low_stock_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td><?php echo $row['stock']; ?></td>
                                    <td><a href="pages/products.php" class="button small">Restock</a></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">No low stock products.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Recent sales section -->
                <h2>Recent Sales</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_sales_result->num_rows > 0): ?>
                            <?php while ($row = $recent_sales_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['customer_name'] ?? 'Anonymous'); ?></td>
                                    <td>$<?php echo number_format($row['total_amount'], 2); ?></td>
                                    <td><?php echo $row['created_at']; ?></td>
                                    <td><a href="pages/receipt.php?sale_id=<?php echo $row['id']; ?>" class="button small">View Receipt</a></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No recent sales.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div style="text-align: center; margin-top: 10px;">
                    <a href="pages/reports.php" class="button">View All Reports</a>
                </div>
            </section>
            <!-- Footer section -->
            <footer>
                <p>&copy; 2025 Techlaro Company</p>
            </footer>
        </div>
    </div>
    <!-- Link to external JavaScript file -->
    <script src="assets/js/dashboard.js"></script>
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