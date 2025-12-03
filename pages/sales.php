<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: /smart-cashier-system/index.php");
    exit();
}

include '../config/db.php';

$products_query = "SELECT id, product_name, price FROM product";
$products_result = $conn->query($products_query);

$customers_query = "SELECT id, customer_name FROM customer";
$customers_result = $conn->query($customers_query);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/smart-cashier-system/">
    <title>Sales - Smart Cashier System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container-with-sidebar">
        <?php include '../includes/sidebar.php'; ?>

        <div class="container">
            <header>
                <h1>Make a Sale</h1>
            </header>

            <section>
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <h2>New Sale Form</h2>

                <form action="pages/process_sale.php" method="POST" id="saleForm" onsubmit="return validateForm()">
                    <div class="form-group">
                        <label for="customer_id">Customer (Optional):</label>
                        <select id="customer_id" name="customer_id">
                            <option value="">Anonymous</option>
                            <?php while ($row = $customers_result->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>">
                                    <?php echo htmlspecialchars($row['customer_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="payment_type">Payment Type:</label>
                        <select id="payment_type" name="payment_type" required onchange="toggleCashFields()">
                            <option value="cash">Cash</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>

                    <div id="itemsContainer">
                        <div class="item-row" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: center;">
                            <div class="form-group">
                                <label>Product:</label>
                                <select name="product_id[]" required class="product-select" onchange="calculateSubtotal(this.closest('.item-row'))" style="width: 200px;">
                                    <option value="">Select Product</option>
                                    <?php 
                                    $products_result->data_seek(0);
                                    while ($row = $products_result->fetch_assoc()): ?>
                                        <option value="<?php echo $row['id']; ?>" data-price="<?php echo $row['price']; ?>">
                                            <?php echo htmlspecialchars($row['product_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Quantity:</label>
                                <input type="number" name="quantity[]" min="1" required class="quantity-input" onchange="calculateSubtotal(this.closest('.item-row'))">
                            </div>
                            <div class="form-group">
                                <label>Subtotal:</label>
                                <span>₱<span class="subtotal">0.00</span></span>
                            </div>
                            <div class="form-group">
                                <button type="button" class="button small" onclick="removeItemRow(this)">Remove</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="button" class="button" onclick="addItemRow()">Add Another Item</button>
                    </div>

                    <div id="cashFields" style="display: block;">
                        <div class="form-group">
                            <label for="cash_tendered">Cash Tendered:</label>
                            <input type="number" id="cash_tendered" name="cash_tendered" step="0.01" min="0" onchange="calculateTotal()">
                        </div>
                        <div class="form-group">
                            <label>Total Amount: </label><span id="total_amount">0.00</span>
                        </div>
                        <div class="form-group">
                            <label>Change: </label><span id="change">0.00</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="button">Complete Sale</button>
                    </div>
                </form>
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

        function toggleCashFields() {
            const paymentType = document.getElementById('payment_type').value;
            document.getElementById('cashFields').style.display = paymentType === 'cash' ? 'block' : 'none';
        }

        function calculateSubtotal(row) {
            const price = parseFloat(row.querySelector('.product-select').selectedOptions[0].dataset.price) || 0;
            const qty = parseInt(row.querySelector('.quantity-input').value) || 0;
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
                const qty = row.querySelector('.quantity-input').value;
                if (qty <= 0) {
                    alert('Quantity must be greater than 0');
                    valid = false;
                }
            });
            const paymentType = document.getElementById('payment_type').value;
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
        toggleCashFields();

        // Auto-scroll to container on load
        window.onload = function() {
            const container = document.querySelector('.container');
            if (container) {
                container.scrollIntoView({ behavior: 'smooth' });
            }
        };
    </script>
</body>
</html>