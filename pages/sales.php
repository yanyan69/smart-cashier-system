<?php include '../includes/session.php'; ?>
<?php
// Check if the user is a store owner
if (!isStoreOwner()) {
    header("Location: ../unauthorized.php"); // Redirect if not a store owner
    exit;
}

// Include database connection
include '../config/db.php';

// Fetch all products for the sales form
$sql = "SELECT id, product_name, price, stock FROM products WHERE stock > 0";
$result = $conn->query($sql);
$available_products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $available_products[] = $row;
    }
}

// Fetch all customers for credit sales option
$sql_customers = "SELECT id, customer_name FROM customers";
$result_customers = $conn->query($sql_customers);
$customers = [];
if ($result_customers->num_rows > 0) {
    while ($row = $result_customers->fetch_assoc()) {
        $customers[] = $row;
    }
}

// Close database connection (we'll open it again in the processing script)
$conn->close();
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="content">
    <h1>Sales Transactions</h1>
    <p>Record new sales, select products, specify quantities, and process payments (cash or credit).</p>

    <form id="salesForm" action="process_sale.php" method="POST">
        <h2>New Sale</h2>
        <div id="saleItems">
            <div class="sale-item">
                <div class="form-group">
                    <label for="product_id_1">Product:</label>
                    <select class="product-select" id="product_id_1" name="products[1][product_id]" required>
                        <option value="">-- Select Product --</option>
                        <?php foreach ($available_products as $product): ?>
                            <option value="<?php echo htmlspecialchars($product['id']); ?>" data-price="<?php echo htmlspecialchars($product['price']); ?>" data-stock="<?php echo htmlspecialchars($product['stock']); ?>">
                                <?php echo htmlspecialchars($product['product_name']); ?> (₱<?php echo htmlspecialchars(number_format($product['price'], 2)); ?> - Stock: <?php echo htmlspecialchars($product['stock']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="quantity_1">Quantity:</label>
                    <input type="number" class="quantity-input" id="quantity_1" name="products[1][quantity]" value="1" min="1" required>
                </div>

            </div>
        </div>
        <button type="button" class="button">Remove</button>
        <button type="button" id="addItem" class="button">Add Item</button>

        <div class="form-group">
            <label for="payment_type">Payment Type:</label>
            <select id="payment_type" name="payment_type" required>
                <option value="cash">Cash</option>
                <option value="credit">Credit ("Utang")</option>
            </select>
        </div>

        <div id="creditDetails" style="display: none;">
            <div class="form-group">
                <label for="customer_id">Customer (for Credit):</label>
                <select id="customer_id" name="customer_id">
                    <option value="">-- Select Customer --</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo htmlspecialchars($customer['id']); ?>">
                            <?php echo htmlspecialchars($customer['customer_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="total_amount">Total Amount:</label>
            <input type="text" id="total_amount" name="total_amount" value="0.00" readonly>
        </div>

        <div class="form-group">
            <button type="submit" class="button">Process Sale</button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addItemButton = document.getElementById('addItem');
        const saleItemsContainer = document.getElementById('saleItems');
        const paymentTypeSelect = document.getElementById('payment_type');
        const creditDetailsDiv = document.getElementById('creditDetails');
        let itemCount = 1;

        addItemButton.addEventListener('click', function() {
            itemCount++;
            const newItem = document.querySelector('.sale-item').cloneNode(true);
            newItem.querySelectorAll('select, input').forEach(el => {
                const newId = el.id.replace(/_\d+$/, `_${itemCount}`);
                const newName = el.name.replace(/\[\d+\]/, `[${itemCount}]`);
                el.id = newId;
                el.name = newName;
                if (el.tagName === 'INPUT' && el.type === 'number') {
                    el.value = 1;
                } else if (el.tagName === 'SELECT') {
                    el.selectedIndex = 0;
                }
            });
            const removeButton = newItem.querySelector('.remove-item');
            removeButton.addEventListener('click', function() {
                if (saleItemsContainer.children.length > 1) {
                    newItem.remove();
                    calculateTotal();
                }
            });
            saleItemsContainer.appendChild(newItem);
        });

        saleItemsContainer.addEventListener('change', function(event) {
            if (event.target.classList.contains('product-select') || event.target.classList.contains('quantity-input')) {
                calculateTotal();
            }
        });

        paymentTypeSelect.addEventListener('change', function() {
            creditDetailsDiv.style.display = this.value === 'credit' ? 'block' : 'none';
            if (this.value === 'cash') {
                document.getElementById('customer_id').value = ''; // Reset customer selection
            }
        });

        function calculateTotal() {
            let total = 0;
            const items = saleItemsContainer.querySelectorAll('.sale-item');
            items.forEach(item => {
                const productSelect = item.querySelector('.product-select');
                const quantityInput = item.querySelector('.quantity-input');
                if (productSelect && quantityInput) {
                    const price = parseFloat(productSelect.options[productSelect.selectedIndex]?.getAttribute('data-price')) || 0;
                    const quantity = parseInt(quantityInput.value) || 0;
                    total += price * quantity;
                }
            });
            document.getElementById('total_amount').value = total.toFixed(2);
        }

        // Initial calculation
        calculateTotal();

        // Add remove functionality for the initial item
        const initialRemoveButton = document.querySelector('.remove-item');
        initialRemoveButton.addEventListener('click', function() {
            if (saleItemsContainer.children.length > 1) {
                document.querySelector('.sale-item').remove();
                calculateTotal();
            }
        });
    });
</script>