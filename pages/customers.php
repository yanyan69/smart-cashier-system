<?php include '../includes/session.php'; ?>
<?php
// Check if the user is a store owner
if (!isStoreOwner()) {
    header("Location: ../unauthorized.php"); // Redirect if not a store owner
    exit;
}

// Include database connection
include '../config/db.php';

// Fetch customers from the database
$sql = "SELECT * FROM customers";
$result = $conn->query($sql);
$customers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}

// Close database connection
$conn->close();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="content">
    <h1>Customer Management</h1>
    <p>On this page, you can create, view, edit, and delete customer profiles. You can store essential details such as customer names and contact information.</p>

    <h2>Current Customers</h2>
    <?php if (empty($customers)): ?>
        <p>No customers added yet.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact Info</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer['id']); ?></td>
                        <td><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['contact_info']); ?></td>
                        <td><?php echo htmlspecialchars($customer['created_at']); ?></td>
                        <td>
                            <a href="edit_customer.php?id=<?php echo htmlspecialchars($customer['id']); ?>" class="button small">Edit</a>
                            <a href="delete_customer.php?id=<?php echo htmlspecialchars($customer['id']); ?>" class="button small alert">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h2>Add New Customer</h2>
    <form action="add_customer.php" method="POST">
        <div class="form-group">
            <label for="customer_name">Customer Name:</label>
            <input type="text" id="customer_name" name="customer_name" required>
        </div>
        <div class="form-group">
            <label for="contact_info">Contact Information:</label>
            <input type="text" id="contact_info" name="contact_info">
        </div>
        <div class="form-group">
            <button type="submit" class="button">Add Customer</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>