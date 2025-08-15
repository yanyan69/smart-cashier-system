<?php
include '../includes/session.php';
if (!isStoreOwner()) {
    header("Location: ../unauthorized.php");
    exit;
}
include '../config/db.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = $_GET['id'];

    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        header("Location: products.php?success=Product deleted successfully");
    } else {
        // Check if the error is due to a foreign key constraint
        if ($conn->errno == 1451) { // Error code 1451 is for "Cannot delete or update a parent row: a foreign key constraint fails"
            header("Location: products.php?error=Error: This product cannot be deleted because it has associated sales records.");
        } else {
            // For other errors, display the generic MySQL error
            header("Location: products.php?error=Error deleting product: " . $stmt->error);
        }
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: products.php?error=Invalid product ID for deletion");
    exit;
}
?>