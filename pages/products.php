<?php
// Start the session to access user data
session_start();

// Check if the user is logged in and has the 'owner' role; redirect if not
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: /smart-cashier-system/assets/html/index.html");
    exit();
}

include '../config/db.php';

// Handle form submission for adding a new product
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $unit = $_POST['unit']; // New unit field

    // Prepare and execute insert statement
    $stmt = $conn->prepare("INSERT INTO product (product_name, description, category, price, stock, unit) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdis", $product_name, $description, $category, $price, $stock, $unit);

    if ($stmt->execute()) {
        // Success - redirect to refresh the page
        header("Location: products.php");
        exit();
    } else {
        $error = "Error adding product: " . $conn->error;
    }

    $stmt->close();
}

// Fetch all products
$products_query = "SELECT * FROM product";
$products_result = $conn->query($products_query);

$conn->close();
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
    <title>Products - Smart Cashier System</title>
    <!-- Link to external CSS stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Main container with sidebar -->
    <div class="container-with-sidebar">
        <?php include '../includes/sidebar.php'; ?>
        <!-- Main content container -->
        <div class="container">
            <!-- Header section -->
            <header>
                <h1>Manage Products</h1>
            </header>
            <!-- Products management section -->
            <section>
                <!-- Display error if any -->
                <?php if (isset($error)): ?>
                    <div class="alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Product list section -->
                <h2>Product List</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Unit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($products_result->num_rows > 0): ?>
                            <?php while ($row = $products_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td>$<?php echo number_format($row['price'], 2); ?></td>
                                    <td><?php echo $row['stock']; ?></td>
                                    <td><?php echo htmlspecialchars($row['unit'] ?? 'N/A'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No products found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Button to show add form -->
                <div class="form-group">
                    <button type="button" class="button" onclick="toggleAddForm()">Add Product</button>
                </div>

                <!-- Form for adding a new product (hidden by default) -->
                <div id="addProductForm" style="display: none;">
                    <h2>Add New Product</h2>
                    <form action="pages/products.php" method="POST">
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
                            <input type="number" id="price" name="price" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="stock">Stock:</label>
                            <input type="number" id="stock" name="stock" required>
                        </div>
                        <div class="form-group">
                            <label for="unit">Unit (e.g., kg, pcs, boxes):</label>
                            <input type="text" id="unit" name="unit" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="button">Add Product</button>
                        </div>
                    </form>
                </div>
            </section>
            <!-- Footer section -->
            <footer>
                <p>&copy; 2025 Techlaro Company</p>
            </footer>
        </div>
    </div>
    <script>
        function toggleAddForm() {
            const form = document.getElementById('addProductForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
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