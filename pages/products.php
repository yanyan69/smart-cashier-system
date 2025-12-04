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
$tags = isset($_GET['tags']) ? $_GET['tags'] : [];

$where_clauses = [];
if ($role === 'owner') $where_clauses[] = "(user_id = $user_id OR user_id IS NULL)";
if ($search) $where_clauses[] = "product_name LIKE '%$search%'";
if (!empty($tags)) {
    foreach ($tags as $tag) {
        $tag = $conn->real_escape_string($tag);
        $where_clauses[] = "category LIKE '%$tag%'";
    }
}
$where = !empty($where_clauses) ? "WHERE " . implode(' AND ', $where_clauses) : "";

$total_query = "SELECT COUNT(*) FROM product $where";
$total = $conn->query($total_query)->fetch_row()[0];
$pages = ceil($total / $limit);

$products_query = "SELECT id, product_name, price, stock, unit, category FROM product $where LIMIT $limit OFFSET $offset";
$products_result = $conn->query($products_query);

// Fetch unique tags for filter
$tags_query = "SELECT DISTINCT category FROM product $where";
$tags_result = $conn->query($tags_query);
$all_tags = [];
while ($row = $tags_result->fetch_assoc()) {
    if ($row['category']) {
        $tags_arr = explode(',', $row['category']);
        foreach ($tags_arr as $tag) {
            $trimmed = trim($tag);
            if ($trimmed) $all_tags[$trimmed] = true;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/smart-cashier-system/">
    <title>Products - Smart Cashier System</title>
    <link id="theme-stylesheet" rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container-with-sidebar">
        <?php include '../includes/sidebar.php'; ?>
        <div class="container">
            <header>
                <h1>Products</h1>
            </header>
            <section>
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <h2>Product List</h2>

                <form method="GET">
                    <input type="text" name="search" placeholder="Search by name..." value="<?php echo htmlspecialchars($search); ?>">
                    <select multiple name="tags[]">
                        <?php foreach (array_keys($all_tags) as $tag): ?>
                            <option value="<?php echo htmlspecialchars($tag); ?>" <?php if (in_array($tag, $tags)) echo 'selected'; ?>><?php echo htmlspecialchars($tag); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="button">Search/Filter</button>
                </form>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Unit</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = $products_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo $product['stock']; ?></td>
                                    <td><?php echo htmlspecialchars($product['unit']); ?></td>
                                    <td>
                                        <a href="#" class="button small" onclick="showEditForm(<?php echo $product['id']; ?>, '<?php echo addslashes(htmlspecialchars($product['product_name'])); ?>', <?php echo $product['price']; ?>, <?php echo $product['stock']; ?>, '<?php echo addslashes(htmlspecialchars($product['unit'])); ?>', '<?php echo addslashes(htmlspecialchars($product['category'] ?? '')); ?>'); return false;">Edit</a>
                                        <a href="pages/process_product.php?action=delete&id=<?php echo $product['id']; ?>" class="button small danger" onclick="return confirm('Are you sure?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="products.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&tags=<?php echo implode('&tags[]=', array_map('urlencode', $tags)); ?>" <?php if ($i == $page) echo 'class="active"'; ?>><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>

                <button onclick="showAddForm()" class="button">Add New Product</button>

                <div id="add-modal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow: auto; justify-content: center; align-items: center;">
                    <div class="modal-content" style="width: 80%; max-width: 600px; max-height: 80vh; overflow-y: auto; padding: 20px 20px 40px 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); background-color: #1e1e1e;">
                        <span id="close-add-modal" class="close" style="cursor: pointer; float: right; font-size: 24px;">×</span>
                        <h2>Add Product</h2>
                        <form action="pages/process_product.php?action=add" method="POST">
                            <div class="form-group">
                                <label for="product_name">Product Name:</label>
                                <input type="text" id="product_name" name="product_name" required>
                            </div>
                            <div class="form-group">
                                <label for="price">Price:</label>
                                <input type="number" step="0.01" id="price" name="price" required>
                            </div>
                            <div class="form-group">
                                <label for="stock">Stock:</label>
                                <input type="number" id="stock" name="stock" required>
                            </div>
                            <div class="form-group">
                                <label for="unit">Unit:</label>
                                <input type="text" id="unit" name="unit" required>
                            </div>
                            <div class="form-group">
                                <label for="category">Category/Tags (comma-separated):</label>
                                <input type="text" id="category" name="category">
                            </div>
                            <div class="form-group">
                                <button type="submit" class="button">Add Product</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="edit-modal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow: auto; justify-content: center; align-items: center;">
                    <div class="modal-content" style="width: 80%; max-width: 600px; max-height: 80vh; overflow-y: auto; padding: 20px 20px 40px 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); background-color: #1e1e1e;">
                        <span id="close-edit-modal" class="close" style="cursor: pointer; float: right; font-size: 24px;">×</span>
                        <h2>Edit Product</h2>
                        <form action="pages/process_product.php?action=update" method="POST">
                            <input type="hidden" id="edit_id" name="id">
                            <div class="form-group">
                                <label for="edit_product_name">Product Name:</label>
                                <input type="text" id="edit_product_name" name="product_name" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_price">Price:</label>
                                <input type="number" step="0.01" id="edit_price" name="price" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_stock">Stock:</label>
                                <input type="number" id="edit_stock" name="stock" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_unit">Unit:</label>
                                <input type="text" id="edit_unit" name="unit" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_category">Category/Tags (comma-separated):</label>
                                <input type="text" id="edit_category" name="category">
                            </div>
                            <div class="form-group">
                                <button type="submit" class="button">Update Product</button>
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
        function showEditForm(id, name, price, stock, unit, category) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_product_name').value = name;
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_stock').value = stock;
            document.getElementById('edit_unit').value = unit;
            document.getElementById('edit_category').value = category;
            document.getElementById('edit-modal').style.display = 'flex';
        }
        function showAddForm() {
            document.getElementById('add-modal').style.display = 'flex';
        }
    </script>
</body>
</html>