<?php
include '../includes/session.php';
if (!isStoreOwner()) {
    header("Location: ../unauthorized.php");
    exit;
}
include '../config/db.php';

// Get today's date in YYYY-MM-DD format
$today = date('Y-m-d');
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="content">
    <h1>Reports</h1>
    <p>Generate reports on sales, inventory, and customer credits for different time periods.</p>

    <h2>Sales Report</h2>
    <form action="generate_sales_report.php" method="GET">
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

    <h2>Inventory Report (Coming Soon)</h2>
    <p>View current stock levels and low stock items.</p>

    <h2>Credit Report (Coming Soon)</h2>
    <p>View outstanding credit balances and payment history.</p>
</div>

<?php include '../includes/footer.php'; ?>