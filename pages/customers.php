<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: /smart-cashier-system/index.php");
    exit();
}

include '../config/db.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

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

                <h2>Customer List</h2>
                <form method="GET">
                    <input type="text" name="search" placeholder="Search by name..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="button">Search</button>
                </form>

                <div class="table-responsive">
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
                                        <a href="#" class="button small" onclick="showEditForm(<?php echo $row['id']; ?>, '<?php echo addslashes(htmlspecialchars($row['customer_name'])); ?>', '<?php echo addslashes(htmlspecialchars($row['contact_info'] ?? '')); ?>'); return false;">Edit</a>
                                        <a href="pages/process_customer.php?action=delete&id=<?php echo $row['id']; ?>" class="button small danger" onclick="return confirm('Are you sure?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="customers.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" <?php if ($i == $page) echo 'class="active"'; ?>><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>

                <button onclick="showAddForm()" class="button">Add New Customer</button>

                <div id="add-modal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow: auto; justify-content: center; align-items: center;">
                    <div class="modal-content" style="width: 80%; max-width: 600px; max-height: 80vh; overflow-y: auto; padding: 20px 20px 40px 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); background-color: #1e1e1e;">
                        <span id="close-add-modal" class="close" style="cursor: pointer; float: right; font-size: 24px;">×</span>
                        <h2>Add Customer</h2>
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
                    </div>
                </div>

                <div id="edit-modal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow: auto; justify-content: center; align-items: center;">
                    <div class="modal-content" style="width: 80%; max-width: 600px; max-height: 80vh; overflow-y: auto; padding: 20px 20px 40px 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); background-color: #1e1e1e;">
                        <span id="close-edit-modal" class="close" style="cursor: pointer; float: right; font-size: 24px;">×</span>
                        <h2>Edit Customer</h2>
                        <form action="pages/process_customer.php?action=update" method="POST">
                            <input type="hidden" id="edit_id" name="id">
                            <div class="form-group">
                                <label for="edit_customer_name">Customer Name:</label>
                                <input type="text" id="edit_customer_name" name="customer_name" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_contact_info">Contact Info:</label>
                                <input type="text" id="edit_contact_info" name="contact_info">
                            </div>
                            <div class="form-group">
                                <button type="submit" class="button">Update Customer</button>
                            </div>
                        </form>
                    </div>
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
        document.addEventListener('DOMContentLoaded', () => {
            const editModal = document.getElementById('edit-modal');
            const closeEditModal = document.getElementById('close-edit-modal');
            if (closeEditModal && editModal) {
                closeEditModal.addEventListener('click', () => {
                    editModal.style.display = 'none';
                });
            }
            if (editModal) {
                window.addEventListener('click', (event) => {
                    if (event.target === editModal) {
                        editModal.style.display = 'none';
                    }
                });
            }

            const addModal = document.getElementById('add-modal');
            const closeAddModal = document.getElementById('close-add-modal');
            if (closeAddModal && addModal) {
                closeAddModal.addEventListener('click', () => {
                    addModal.style.display = 'none';
                });
            }
            if (addModal) {
                window.addEventListener('click', (event) => {
                    if (event.target === addModal) {
                        addModal.style.display = 'none';
                    }
                });
            }
        });
        function showEditForm(id, name, contact) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_customer_name').value = name;
            document.getElementById('edit_contact_info').value = contact;
            document.getElementById('edit-modal').style.display = 'flex';
        }
        function showAddForm() {
            document.getElementById('add-modal').style.display = 'flex';
        }
    </script>
</body>
</html>