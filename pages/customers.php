<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: /smart-cashier-system/index.php");
    exit();
}

include '../config/db.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Pagination and search
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$where = ($role === 'owner') ? "WHERE user_id = $user_id" : "";
if ($search) $where .= ($where ? " AND" : " WHERE") . " customer_name LIKE '%$search%'";

$total_query = "SELECT COUNT(*) FROM customer $where";
$total = $conn->query($total_query)->fetch_row()[0];
$pages = ceil($total / $limit);

$customers_query = "SELECT id, customer_name, contact_info FROM customer $where LIMIT $limit OFFSET $offset";
$customers_result = $conn->query($customers_query);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/smart-cashier-system/">
    <title>Customers - Smart Cashier System</title>
    <link id="theme-stylesheet" rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container-with-sidebar">
        <?php include '../includes/sidebar.php'; ?>

        <div class="container">
            <header>
                <h1>Customers</h1>
            </header>

            <section>
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <h2>Add New Customer</h2>
                <form action="pages/process_customer.php?action=add" method="POST">
                    <div class="form-group">
                        <label for="customer_name">Customer Name:</label>
                        <input type="text" id="customer_name" name="customer_name" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_info">Contact Info:</label>
                        <input type="text" id="contact_info" name="contact_info">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="button">Add Customer</button>
                    </div>
                </form>

                <h2>Customer List</h2>
                <form method="GET">
                    <input type="text" name="search" placeholder="Search by name..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="button">Search</button>
                </form>

                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Contact Info</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $customers_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['contact_info'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="pages/edit_customer.php?id=<?php echo $row['id']; ?>" class="button small">Edit</a>
                                    <a href="pages/process_customer.php?action=delete&id=<?php echo $row['id']; ?>" class="button small danger" onclick="return confirm('Are you sure?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="customers.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" <?php if ($i == $page) echo 'class="active"'; ?>><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>

                <?php
                if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
                    include '../config/db.php'; // Reconnect for edit
                    $id = intval($_GET['id']);
                    $where_edit = ($role === 'owner') ? "AND user_id = $user_id" : "";
                    $stmt = $conn->prepare("SELECT * FROM customer WHERE id = ? $where_edit");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $customer = $stmt->get_result()->fetch_assoc();
                    if ($customer) {
                ?>
                <h2>Edit Customer</h2>
                <form action="pages/process_customer.php?action=update" method="POST">
                    <input type="hidden" name="id" value="<?php echo $customer['id']; ?>">
                    <div class="form-group">
                        <label for="customer_name">Customer Name:</label>
                        <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($customer['customer_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_info">Contact Info:</label>
                        <input type="text" id="contact_info" name="contact_info" value="<?php echo htmlspecialchars($customer['contact_info'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="button">Update Customer</button>
                    </div>
                </form>
                <?php
                    } else {
                        echo '<div class="alert-danger">Customer not found or unauthorized.</div>';
                    }
                    $stmt->close();
                    $conn->close();
                }
                ?>
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