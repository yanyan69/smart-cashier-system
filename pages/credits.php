<?php include '../includes/session.php'; ?>
<?php
// Check if the user is a store owner
if (!isStoreOwner()) {
    header("Location: ../unauthorized.php"); // Redirect if not a store owner
    exit;
}

// Include database connection
include '../config/db.php';

// Fetch all credit records with customer information
$sql = "SELECT
    c.id AS credit_id,
    cust.id AS customer_id,
    cust.customer_name,
    c.total_credit,
    c.balance,
    c.status,
    c.last_payment_date,
    c.created_at -- Now this column exists
FROM credits c
JOIN customers cust ON c.customer_id = cust.id
ORDER BY c.status, c.balance DESC, c.last_payment_date ASC";
$result = $conn->query($sql);
$credits = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $credits[] = $row;
    }
}

// Close database connection (we'll open it again for processing payments)
$conn->close();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="content">
    <h1>Credits Management</h1>
    <p>Here you can view and manage customer credit records ("utang"). You can track outstanding balances, record payments, and update credit statuses.</p>

    <h2>Outstanding Credits</h2>
    <?php if (empty($credits)): ?>
        <p>No outstanding credits.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Credit ID</th>
                    <th>Customer Name</th>
                    <th>Total Credit</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Last Payment Date</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($credits as $credit): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($credit['credit_id']); ?></td>
                        <td><?php echo htmlspecialchars($credit['customer_name']); ?></td>
                        <td>₱ <?php echo htmlspecialchars(number_format($credit['total_credit'], 2)); ?></td>
                        <td>₱ <?php echo htmlspecialchars(number_format($credit['balance'], 2)); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $credit['status']))); ?></td>
                        <td><?php echo htmlspecialchars($credit['last_payment_date'] ? date('Y-m-d', strtotime($credit['last_payment_date'])) : 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($credit['created_at']))); ?></td>
                        <td>
                            <a href="record_payment.php?credit_id=<?php echo htmlspecialchars($credit['credit_id']); ?>" class="button small">Record Payment</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>