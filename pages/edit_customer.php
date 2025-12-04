<?php
session_start();
include '../includes/session.php';
if (!isStoreOwner()) {
    header("Location: ../unauthorized.php");
    exit;
}
include '../config/db.php';

if (isset($_GET['id'])) {
    $customer_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT customer_name, contact_info FROM `customer` WHERE id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = $_POST['customer_name'];
    $contact_info = $_POST['contact_info'];

    $stmt = $conn->prepare("UPDATE `customer` SET customer_name = ?, contact_info = ? WHERE id = ?");
    $stmt->bind_param("ssi", $customer_name, $contact_info, $customer_id);

    if ($stmt->execute()) {
        header("Location: customers.php?success=Customer updated successfully");
    } else {
        header("Location: customers.php?error=Error updating customer");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/smart-cashier-system/">
    <title>Edit Customer</title>
    <link id="theme-stylesheet" rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container-with-sidebar">
        <?php include '../includes/sidebar.php'; ?>
        <div class="container">
            <header><h1>Edit Customer</h1></header>
            <section>
                <form action="edit_customer.php?id=<?php echo $customer_id; ?>" method="POST">
                    <div class="form-group">
                        <label for="customer_name">Name:</label>
                        <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($customer['customer_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_info">Contact Info:</label>
                        <input type="text" id="contact_info" name="contact_info" value="<?php echo htmlspecialchars($customer['contact_info']); ?>">
                    </div>
                    <button type="submit" class="button">Update Customer</button>
                </form>
            </section>
            <footer><p>&copy; 2025 Techlaro Company</p></footer>
        </div>
    </div>
    <script src="assets/js/scripts.js"></script>
</body>
</html>