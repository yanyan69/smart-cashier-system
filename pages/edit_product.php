<?php
// edit_product.php
include '../includes/session.php';
if (!isStoreOwner()) {
    header("Location: ../unauthorized.php");
    exit;
}
include '../config/db.php';

$error = null;
$success = null;

// Handle GET request to display the edit form
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = $_GET['id'];
    // Fetch product details based on the ID
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
        $error = "Product not found";
    }

    // Include header and sidebar
    include '../includes/header.php';
    include '../includes/sidebar.php';
    ?>

    <div class="content">
        <h1>Edit Product</h1>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="edit_product.php" method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id'] ?? ''); ?>">
            <div class="form-group">
                <label for="product_name">Product Name:</label>
                <input type="text" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product['product_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="category">Category:</label>
                <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($product['category'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="stock">Stock Quantity:</label>
                <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($product['stock'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <button type="submit" class="button">Update Product</button>
            </div>
        </form>
    </div>

    <?php
    // Include footer
    include '../includes/footer.php';

} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle POST request to update the product
    $id = $_POST['id'];
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    $sql = "UPDATE products SET product_name=?, description=?, category=?, price=?, stock=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdiii", $product_name, $description, $category, $price, $stock, $id);

    if ($stmt->execute()) {
        header("Location: products.php?success=Product updated successfully");
        exit;
    } else {
        $error = "Error updating product: " . $stmt->error;
        // Include header and sidebar to display the error
        include '../includes/header.php';
        include '../includes/sidebar.php';
        ?>
        <div class="content">
            <h1>Edit Product</h1>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <a href="products.php" class="button">Back to Products</a>
        </div>
        <?php
        include '../includes/footer.php';
    }

    $stmt->close();
    $conn->close();

} else {
    // If accessed directly without ID or POST request
    header("Location: products.php");
    exit;
}
?>