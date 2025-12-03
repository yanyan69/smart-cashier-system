<?php
// Start the session to access user data
session_start();

// Check if the user is logged in and has the 'owner' role; redirect if not
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: /smart-cashier-system/assets/html/index.html");
    exit();
}

include '../config/db.php';

// Handle form submission for adding a new customer
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = $_POST['customer_name'];
    $contact_info = $_POST['contact_info'];

    // Prepare and execute insert statement (total_purchases defaults to 0)
    $stmt = $conn->prepare("INSERT INTO customer (customer_name, contact_info) VALUES (?, ?)");
    $stmt->bind_param("ss", $customer_name, $contact_info);

    if ($stmt->execute()) {
        // Success - redirect to refresh the page
        header("Location: customers.php");
        exit();
    } else {
        $error = "Error adding customer: " . $conn->error;
    }

    $stmt->close();
}

// Fetch all customers
$customers_query = "SELECT * FROM customer";
$customers_result = $conn->query($customers_query);

$customers = [];
if ($customers_result->num_rows > 0) {
    while ($row = $customers_result->fetch_assoc()) {
        $customer_id = $row['id'];

        // Calculate cash purchases: sum of total_amount from cash sales
        $cash_purchases_query = "SELECT SUM(total_amount) AS cash_purchases FROM sale WHERE customer_id = $customer_id AND payment_type = 'cash'";
        $cash_purchases_result = $conn->query($cash_purchases_query);
        $row['cash_purchases'] = $cash_purchases_result->fetch_assoc()['cash_purchases'] ?? 0;

        // Calculate outstanding credits: sum of (amount_owed - amount_paid) from credits where status != 'paid'
        $outstanding_credits_query = "SELECT SUM(amount_owed - amount_paid) AS outstanding_credits FROM credit WHERE customer_id = $customer_id AND status != 'paid'";
        $outstanding_credits_result = $conn->query($outstanding_credits_query);
        $row['outstanding_credits'] = $outstanding_credits_result->fetch_assoc()['outstanding_credits'] ?? 0;

        $customers[] = $row;
    }
}

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
    <title>Customers - Smart Cashier System</title>
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
                <h1>Manage Customers</h1>
            </header>
            <!-- Customers management section -->
            <section>
                <!-- Display error if any -->
                <?php if (isset($error)): ?>
                    <div class="alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Customer list section -->
                <h2>Customer List</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Total Purchases (Excluding Credits)</th>
                            <th>Outstanding Credits</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($customers)): ?>
                            <?php foreach ($customers as $row): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact_info']); ?></td>
                                    <td>$<?php echo number_format($row['cash_purchases'], 2); ?></td>
                                    <td>$<?php echo number_format($row['outstanding_credits'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No customers found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Button to show add form -->
                <div class="form-group">
                    <button type="button" class="button" onclick="toggleAddForm()">Add Customer</button>
                </div>

                <!-- Form for adding a new customer (hidden by default) -->
                <div id="addCustomerForm" style="display: none;">
                    <h2>Add New Customer</h2>
                    <form action="pages/customers.php" method="POST">
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
            </section>
            <!-- Footer section -->
            <footer>
                <p>&copy; 2025 Techlaro Company</p>
            </footer>
        </div>
    </div>
    <script>
        function toggleAddForm() {
            const form = document.getElementById('addCustomerForm');
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