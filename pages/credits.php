<?php
// Inferred structure for credits.php based on similar pages like customers.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: /smart-cashier-system/index.php");
    exit();
}

include '../config/db.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Pagination and search for credits
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$where = ($role === 'owner') ? "WHERE c.user_id = $user_id" : "";
if ($search) $where .= ($where ? " AND" : " WHERE") . " (cu.customer_name LIKE '%$search%' OR c.status LIKE '%$search%')";

$total_query = "SELECT COUNT(*) FROM credit c LEFT JOIN customer cu ON c.customer_id = cu.id $where";
$total = $conn->query($total_query)->fetch_row()[0];
$pages = ceil($total / $limit);

$credits_query = "SELECT c.id, cu.customer_name, c.amount_owed, c.amount_paid, c.status, c.created_at 
                  FROM credit c LEFT JOIN customer cu ON c.customer_id = cu.id $where 
                  ORDER BY c.created_at DESC LIMIT $limit OFFSET $offset";
$credits_result = $conn->query($credits_query);

// Unique statuses for filter (assuming statuses like 'pending', 'paid')
$statuses_query = "SELECT DISTINCT c.status FROM credit c $where";
$statuses_result = $conn->query($statuses_query);
$all_statuses = [];
while ($row = $statuses_result->fetch_assoc()) {
    $all_statuses[] = $row['status'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/smart-cashier-system/">
    <title>Credits - Smart Cashier System</title>
    <link id="theme-stylesheet" rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container-with-sidebar">
        <?php include '../includes/sidebar.php'; ?>

        <div class="container">
            <header>
                <h1>Credits</h1>
            </header>

            <section>
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <h2>Credit List</h2>
                <form method="GET">
                    <input type="text" name="search" placeholder="Search by customer or status..." value="<?php echo htmlspecialchars($search); ?>">
                    <select multiple name="statuses[]">
                        <?php foreach ($all_statuses as $status): ?>
                            <option value="<?php echo htmlspecialchars($status); ?>" <?php if (in_array($status, $statuses ?? [])) echo 'selected'; ?>><?php echo ucfirst($status); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="button">Filter</button>
                </form>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Amount Owed</th>
                                <th>Amount Paid</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($credit = $credits_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $credit['id']; ?></td>
                                    <td><?php echo htmlspecialchars($credit['customer_name'] ?? 'Anonymous'); ?></td>
                                    <td>₱<?php echo number_format($credit['amount_owed'], 2); ?></td>
                                    <td>₱<?php echo number_format($credit['amount_paid'], 2); ?></td>
                                    <td><?php echo ucfirst($credit['status']); ?></td>
                                    <td><?php echo $credit['created_at']; ?></td>
                                    <td>
                                        <a href="pages/edit_credit.php?id=<?php echo $credit['id']; ?>" class="button small">Edit</a>
                                        <a href="pages/process_credit.php?action=delete&id=<?php echo $credit['id']; ?>" class="button small danger" onclick="return confirm('Are you sure?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="credits.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" <?php if ($i == $page) echo 'class="active"'; ?>><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
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