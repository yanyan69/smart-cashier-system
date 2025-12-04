<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: /smart-cashier-system/index.php");
    exit();
}

include '../config/db.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// For sale form: Fetch products and customers with scoping
$where_product = ($role === 'owner') ? "WHERE (user_id = $user_id OR user_id IS NULL)" : "";
$products_query = "SELECT id, product_name, price, stock FROM product $where_product"; // Added stock fetch
$products_result = $conn->query($products_query);

$where_customer = ($role === 'owner') ? "WHERE user_id = $user_id" : "";
$customers_query = "SELECT id, customer_name FROM customer $where_customer";
$customers_result = $conn->query($customers_query);

// For sales list: Pagination, search, filters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$payment_types = isset($_GET['payment_types']) ? $_GET['payment_types'] : []; // For filters (e.g., cash, credit)

$where_clauses = [];
if ($role === 'owner') $where_clauses[] = "s.user_id = $user_id";
if ($search) $where_clauses[] = "(c.customer_name LIKE '%$search%' OR s.payment_type LIKE '%$search%')";
if (!empty($payment_types)) {
    $types_escaped = array_map(function($type) use ($conn) { return $conn->real_escape_string($type); }, $payment_types);
    $where_clauses[] = "s.payment_type IN ('" . implode("','", $types_escaped) . "')";
}
$where = !empty($where_clauses) ? "WHERE " . implode(' AND ', $where_clauses) : "";

$total_query = "SELECT COUNT(*) FROM sale s LEFT JOIN customer c ON s.customer_id = c.id $where";
$total = $conn->query($total_query)->fetch_row()[0];
$pages = ceil($total / $limit);

$sales_query = "SELECT s.id, s.total_amount, s.payment_type, s.created_at, c.customer_name 
                FROM sale s LEFT JOIN customer c ON s.customer_id = c.id $where 
                ORDER BY s.created_at DESC LIMIT $limit OFFSET $offset";
$sales_result = $conn->query($sales_query);

// Unique payment types for filter
$payment_types_query = "SELECT DISTINCT s.payment_type FROM sale s $where";
$payment_types_result = $conn->query($payment_types_query);
$all_payment_types = [];
while ($row = $payment_types_result->fetch_assoc()) {
    $all_payment_types[] = $row['payment_type'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/smart-cashier-system/">
    <title>Sales - Smart Cashier System</title>
    <link id="theme-stylesheet" rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Responsive styles for the sale form */
        .item-row {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 10px;
        }
        .item-row > * {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container-with-sidebar">
        <?php include '../includes/sidebar.php'; ?>

        <div class="container">
            <header>
                <h1>Sales</h1>
            </header>

            <section>
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <h2>Sales List</h2>
                <form method="GET">
                    <input type="text" name="search" placeholder="Search by customer or type..." value="<?php echo htmlspecialchars($search); ?>">
                    <select multiple name="payment_types[]">
                        <?php foreach ($all_payment_types as $type): ?>
                            <option value="<?php echo htmlspecialchars($type); ?>" <?php if (in_array($type, $payment_types)) echo 'selected'; ?>><?php echo ucfirst($type); ?></option>
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
                                <th>Total Amount</th>
                                <th>Payment Type</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($sale = $sales_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $sale['id']; ?></td>
                                    <td><?php echo htmlspecialchars($sale['customer_name'] ?? 'Anonymous'); ?></td>
                                    <td>₱<?php echo number_format($sale['total_amount'], 2); ?></td>
                                    <td><?php echo ucfirst($sale['payment_type']); ?></td>
                                    <td><?php echo $sale['created_at']; ?></td>
                                    <td>
                                        <a href="pages/receipt.php?id=<?php echo $sale['id']; ?>" class="button small">View Receipt</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <a href="pages/sales.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&payment_types=<?php echo implode('&payment_types[]=', array_map('urlencode', $payment_types)); ?>" <?php if ($i == $page) echo 'class="active"'; ?>><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>

                <button onclick="showSaleForm()" class="button">Make a New Sale</button>

                <div id="sale-modal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow: auto; justify-content: center; align-items: center;">
                    <div class="modal-content" style="width: 80%; max-width: 800px; max-height: 80vh; overflow-y: auto; padding: 20px 20px 40px 20px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); background-color: #1e1e1e;">
                        <span id="close-sale-modal" class="close" style="cursor: pointer; float: right; font-size: 24px;">×</span>
                        <h2>Make a New Sale</h2>
                        <form action="pages/process_sale.php" method="POST" onsubmit="return validateForm()">
                            <div id="itemsContainer">
                                <div class="item-row form-group">
                                    <label for="product_id">Product:</label>
                                    <select id="product_id" name="product_id[]" class="product-select input" required>
                                        <option value="">Select Product</option>
                                        <?php mysqli_data_seek($products_result, 0); ?>
                                        <?php while ($product = $products_result->fetch_assoc()): ?>
                                            <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['price']; ?>" data-stock="<?php echo $product['stock']; ?>"><?php echo htmlspecialchars($product['product_name']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                    <label for="quantity">Quantity:</label>
                                    <input type="number" id="quantity" name="quantity[]" class="quantity-input input" min="1" required>
                                    <span>Subtotal: <span class="subtotal">0.00</span></span>
                                    <button type="button" class="button small add-item" onclick="addItemRow()">Add Item</button>
                                    <button type="button" class="button small danger remove-item" onclick="removeItemRow(this)">Remove</button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="customer_id">Customer (required for credit):</label>
                                <select id="customer_id" name="customer_id">
                                    <option value="">Anonymous</option>
                                    <?php mysqli_data_seek($customers_result, 0); ?>
                                    <?php while ($customer = $customers_result->fetch_assoc()): ?>
                                        <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['customer_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="payment_type">Payment Type:</label>
                                <select id="payment_type" name="payment_type" onchange="togglePaymentFields()" required>
                                    <option value="cash">Cash</option>
                                    <option value="credit">Credit</option>
                                </select>
                            </div>

                            <div id="cashFields" style="display: none;">
                                <div class="form-group">
                                    <label for="cash_tendered">Cash Tendered:</label>
                                    <input type="number" id="cash_tendered" name="cash_tendered" step="0.01" min="0">
                                </div>
                                <p>Change: <span id="change">0.00</span></p>
                            </div>

                            <p>Total: <span id="total_amount">0.00</span></p>

                            <button type="submit" class="button">Record Sale</button>
                        </form>
                    </div>
                </div>
            </section>

            <footer>
                <p>&copy; 2025 Techlaro Company</p>
            </footer>
        </div>
    </div>

    <script>
        function addItemRow() {
            const container = document.getElementById('itemsContainer');
            const row = container.firstElementChild.cloneNode(true);
            row.querySelector('select').value = '';
            row.querySelector('input').value = '';
            row.querySelector('.subtotal').textContent = '0.00';
            container.appendChild(row);
            attachEventListeners();
        }

        function removeItemRow(button) {
            if (document.querySelectorAll('.item-row').length > 1) {
                button.closest('.item-row').remove();
                calculateTotal();
            }
        }

        function togglePaymentFields() {
            const paymentType = document.getElementById('payment_type').value;
            document.getElementById('cashFields').style.display = paymentType === 'cash' ? 'block' : 'none';
            const customerSelect = document.getElementById('customer_id');
            customerSelect.required = (paymentType === 'credit');
            if (paymentType !== 'credit') {
                customerSelect.value = '';  // Reset to anonymous if not credit
            }
        }

        function calculateSubtotal(row) {
            const select = row.querySelector('.product-select');
            const price = parseFloat(select.selectedOptions[0].dataset.price) || 0;
            const stock = parseInt(select.selectedOptions[0].dataset.stock) || 0;
            let qty = parseInt(row.querySelector('.quantity-input').value) || 0;
            if (qty > stock) {
                alert('Insufficient stock! Available: ' + stock);
                qty = stock;
                row.querySelector('.quantity-input').value = qty;
            }
            const subtotal = price * qty;
            row.querySelector('.subtotal').textContent = subtotal.toFixed(2);
            calculateTotal();
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const subtotal = parseFloat(row.querySelector('.subtotal').textContent) || 0;
                total += subtotal;
            });
            document.getElementById('total_amount').textContent = total.toFixed(2);

            const tendered = parseFloat(document.getElementById('cash_tendered').value) || 0;
            const change = tendered - total;
            document.getElementById('change').textContent = (change >= 0 ? change : 0).toFixed(2);
        }

        function attachEventListeners() {
            document.querySelectorAll('.product-select, .quantity-input').forEach(input => {
                input.removeEventListener('change', () => calculateSubtotal(input.closest('.item-row')));
                input.addEventListener('change', () => calculateSubtotal(input.closest('.item-row')));
            });
            document.getElementById('cash_tendered').addEventListener('input', calculateTotal);
        }

        function validateForm() {
            let valid = true;
            document.querySelectorAll('.item-row').forEach(row => {
                const qty = parseInt(row.querySelector('.quantity-input').value) || 0;
                const stock = parseInt(row.querySelector('.product-select').selectedOptions[0].dataset.stock) || 0;
                if (qty <= 0) {
                    alert('Quantity must be greater than 0');
                    valid = false;
                } else if (qty > stock) {
                    alert('Insufficient stock for ' + row.querySelector('.product-select').value + '! Available: ' + stock);
                    valid = false;
                }
            });
            const paymentType = document.getElementById('payment_type').value;
            if (paymentType === 'credit' && document.getElementById('customer_id').value === '') {
                alert('Customer is required for credit payments');
                valid = false;
            }
            if (paymentType === 'cash') {
                const change = parseFloat(document.getElementById('change').textContent);
                if (change < 0) {
                    alert('Insufficient cash tendered');
                    valid = false;
                }
            }
            return valid;
        }

        // Initial setup
        attachEventListeners();
        togglePaymentFields();

        // Auto-scroll to container on load
        window.onload = function() {
            const container = document.querySelector('.container');
            if (container) {
                container.scrollIntoView({ behavior: 'smooth' });
            }
        };
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const saleModal = document.getElementById('sale-modal');
            const closeSaleModal = document.getElementById('close-sale-modal');
            if (closeSaleModal && saleModal) {
                closeSaleModal.addEventListener('click', () => {
                    saleModal.style.display = 'none';
                });
            }
            if (saleModal) {
                window.addEventListener('click', (event) => {
                    if (event.target === saleModal) {
                        saleModal.style.display = 'none';
                    }
                });
            }
        });
        function showSaleForm() {
            document.getElementById('sale-modal').style.display = 'flex';
        }
    </script>
    <script src="assets/js/scripts.js"></script>
</body>
</html>