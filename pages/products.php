<?php include '../includes/session.php'; ?>
<?php
// Check if the user is a store owner
if (!isStoreOwner()) {
    header("Location: ../unauthorized.php"); // Redirect if not a store owner
    exit;
}

// Include database connection
include '../config/db.php';

// Fetch products from the database
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
$products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Close database connection
$conn->close();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="content">
    <h1>Product Management</h1>
    <p>Here you can add new products, edit existing ones, and manage your inventory stock levels.</p>

    <h2>Current Products</h2>
    <?php if (empty($products)): ?>
        <p>No products added yet.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['id']); ?></td>
                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                        <td><?php echo htmlspecialchars($product['category']); ?></td>
                        <td>₱ <?php echo htmlspecialchars(number_format($product['price'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($product['stock']); ?></td>
                        <td><?php echo htmlspecialchars($product['created_at']); ?></td>
                        <td>
                            <a href="edit_product.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="button small">Edit</a>
                            <a href="delete_product.php?id=<?php echo htmlspecialchars($product['id']); ?>" class="button small alert">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h2>Add New Product</h2>
    <form action="add_product.php" method="POST">
        <div class="form-group">
            <label for="product_name">Product Name:</label>
            <input type="text" id="product_name" name="product_name" required>
        </div>
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description"></textarea>
        </div>
        <div class="form-group">
            <label for="category">Category:</label>
            <input type="text" id="category" name="category">
        </div>
        <div class="form-group">
            <label for="price">Price:</label>
            <input type="number" step="0.01" id="price" name="price" required>
        </div>
        <div class="form-group">
            <label for="stock">Stock Quantity:</label>
            <input type="number" id="stock" name="stock" value="0" required>
        </div>
        <div class="form-group">
            <button type="submit" class="button">Add Product</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>