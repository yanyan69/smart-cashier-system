<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: /smart-cashier-system/index.php");
    exit();
}

include '../config/db.php';

// Pagination setup
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10; // Items per page
$offset = ($page - 1) * $limit;

// Filters
$customer_filter = isset($_GET['customer']) ? $conn->real_escape_string($_GET['customer']) : '';
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_payment'])) {
    $credit_id = intval($_POST['credit_id']);
    $payment_amount = floatval($_POST['payment_amount']);

    if ($payment_amount <= 0) {
        header("Location: credits.php?error=Invalid payment amount");
        exit();
    }

    $stmt = $conn->prepare("SELECT amount_owed, amount_paid FROM credit WHERE id = ?");
    $stmt->bind_param("i", $credit_id);
    $stmt->execute();
    $credit = $stmt->get_result()->fetch_assoc();

    if (!$credit) {
        header("Location: credits.php?error=Credit not found");
        exit();
    }

    $remaining = $credit['amount_owed'] - $credit['amount_paid'];
    if ($payment_amount > $remaining) {
        header("Location: credits.php?error=Payment exceeds remaining balance");
        exit();
    }

    $new_paid = $credit['amount_paid'] + $payment_amount;
    $status = ($remaining - $payment_amount <= 0) ? 'paid' : ($new_paid > 0 ? 'partially_paid' : 'unpaid');

    $stmt = $conn->prepare("UPDATE credit SET amount_paid = ?, status = ? WHERE id = ?");
    $stmt->bind_param("dsi", $new_paid, $status, $credit_id);
    $stmt->execute();

    $stmt = $conn->prepare("INSERT INTO payment_history (credit_id, payment_amount) VALUES (?, ?)");
    $stmt->bind_param("id", $credit_id, $payment_amount);
    $stmt->execute();

    header("Location: credits.php?success=Payment recorded successfully");
    exit();
}

// Fetch total outstanding
$total_outstanding_query = "SELECT SUM(amount_owed - amount_paid) AS total FROM credit WHERE status != 'paid'";
$total_outstanding = $conn->query($total_outstanding_query)->fetch_assoc()['total'] ?? 0;

// Fetch credits with filters and pagination
$where = "WHERE cr.status != 'paid'";
if ($customer_filter) $where .= " AND c.customer_name LIKE '%$customer_filter%'";
if ($status_filter) $where .= " AND cr.status = '$status_filter'";
$credits_query = "SELECT cr.id, c.customer_name, cr.amount_owed, cr.amount_paid, cr.status, cr.created_at 
                  FROM credit cr JOIN customer c ON cr.customer_id = c.id 
                  $where ORDER BY cr.created_at DESC LIMIT $limit OFFSET $offset";
$credits_result = $conn->query($credits_query);

// Total count for pagination
$count_query = "SELECT COUNT(*) AS total FROM credit cr JOIN customer c ON cr.customer_id = c.id $where";
$total_items = $conn->query($count_query)->fetch_assoc()['total'];
$total_pages = ceil($total_items / $limit);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/smart-cashier-system/">
    <title>Credits - Smart Cashier System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container-with-sidebar">
        <?php include '../includes/sidebar.php'; ?>

        <div class="container">
            <header>
                <h1>Manage Credits</h1>
            </header>

            <section>
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <h2>Filters</h2>
                <form method="GET">
                    <div class="form-group">
                        <label for="customer">Customer Name:</label>
                        <input type="text" id="customer" name="customer" value="<?php echo htmlspecialchars($customer_filter); ?>">
                    </div>
                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select id="status" name="status">
                            <option value="">All Outstanding</option>
                            <option value="unpaid" <?php if ($status_filter === 'unpaid') echo 'selected'; ?>>Unpaid</option>
                            <option value="partially_paid" <?php if ($status_filter === 'partially_paid') echo 'selected'; ?>>Partially Paid</option>
                        </select>
                    </div>
                    <button type="submit" class="button">Apply Filters</button>
                </form>

                <h2>Outstanding Credits (Total: ₱<?php echo number_format($total_outstanding, 2); ?>)</h2>
                <div class="table-wrapper" style="overflow-x: auto;"> <!-- Added wrapper for overflow -->
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Amount Owed</th>
                                <th>Amount Paid</th>
                                <th>Remaining</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                                <th>History</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($credit = $credits_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $credit['id']; ?></td>
                                    <td><?php echo htmlspecialchars($credit['customer_name']); ?></td>
                                    <td>₱<?php echo number_format($credit['amount_owed'], 2); ?></td>
                                    <td>₱<?php echo number_format($credit['amount_paid'], 2); ?></td>
                                    <td>₱<?php echo number_format($credit['amount_owed'] - $credit['amount_paid'], 2); ?></td>
                                    <td><?php echo ucfirst($credit['status']); ?></td>
                                    <td><?php echo $credit['created_at']; ?></td>
                                    <td>
                                        <form action="pages/credits.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="credit_id" value="<?php echo $credit['id']; ?>">
                                            <input type="number" name="payment_amount" step="0.01" min="0.01" placeholder="Payment Amount" required>
                                            <button type="submit" name="record_payment" class="button small">Pay</button>
                                        </form>
                                    </td>
                                    <td>
                                        <button onclick="toggleHistory(<?php echo $credit['id']; ?>)">View History</button>
                                        <div id="history-<?php echo $credit['id']; ?>" style="display:none;">
                                            <?php
                                            $conn = new mysqli('127.0.0.1:3307', 'root', '', 'cashier_db');
                                            $history_query = "SELECT payment_amount, payment_date FROM payment_history WHERE credit_id = " . $credit['id'] . " ORDER BY payment_date DESC";
                                            $history_result = $conn->query($history_query);
                                            if ($history_result->num_rows > 0): ?>
                                                <ul>
                                                    <?php while ($hist = $history_result->fetch_assoc()): ?>
                                                        <li>₱<?php echo number_format($hist['payment_amount'], 2); ?> on <?php echo $hist['payment_date']; ?></li>
                                                    <?php endwhile; ?>
                                                </ul>
                                            <?php else: ?>
                                                <p>No payments yet.</p>
                                            <?php endif; 
                                            $conn->close();
                                            ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination links -->
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="credits.php?page=<?php echo $i; ?>&customer=<?php echo urlencode($customer_filter); ?>&status=<?php echo urlencode($status_filter); ?>" <?php if ($i === $page) echo 'class="active"'; ?>><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            </section>

            <footer>
                <p>&copy; 2025 Techlaro Company</p>
            </footer>
        </div>
    </div>

    <script>
        function toggleHistory(id) {
            const historyDiv = document.getElementById(`history-${id}`);
            historyDiv.style.display = historyDiv.style.display === 'none' ? 'block' : 'none';
        }
        window.onload = function() {
            const container = document.querySelector('.container');
            if (container) {
                container.scrollIntoView({ behavior: 'smooth' });
            }
        };
    </script>
</body>
</html>