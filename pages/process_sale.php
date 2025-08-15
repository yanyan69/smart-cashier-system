<?php
include '../includes/session.php';

if (!isStoreOwner()) {
    header("Location: ../unauthorized.php");
    exit;
}

include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $products = $_POST['products'] ?? []; // Use null coalescing operator for safety
    $payment_type = $_POST['payment_type'] ?? 'cash'; // Default to cash if not set
    $customer_id = ($_POST['payment_type'] === 'credit' && isset($_POST['customer_id'])) ? $_POST['customer_id'] : null;
    $total_amount = $_POST['total_amount'] ?? 0; // Default to 0 if not set

    // Start transaction for data integrity
    $conn->begin_transaction();
    $sale_successful = true;
    $sale_id = null;
    $error_message = null;

    // 1. Record the sale
    $sql_sale = "INSERT INTO sales (customer_id, sale_date, total_amount, payment_type)
                 VALUES (?, NOW(), ?, ?)";
    $stmt_sale = $conn->prepare($sql_sale);
    $stmt_sale->bind_param("ids", $customer_id, $total_amount, $payment_type);

    if ($stmt_sale->execute()) {
        $sale_id = $conn->insert_id;

        // 2. Record the individual sale items and update product stock
        foreach ($products as $item) {
            $product_id = $item['product_id'] ?? null;
            $quantity = $item['quantity'] ?? 0;

            if ($product_id === null || $quantity <= 0) {
                $error_message = "Invalid product data received.";
                $sale_successful = false;
                break;
            }

            // Fetch product price and stock
            $sql_product = "SELECT price, stock FROM products WHERE id = ?";
            $stmt_product = $conn->prepare($sql_product);
            $stmt_product->bind_param("i", $product_id);
            $stmt_product->execute();
            $result_product = $stmt_product->get_result();

            if ($result_product->num_rows === 1) {
                $product_data = $result_product->fetch_assoc();
                $unit_price = $product_data['price'];
                $current_stock = $product_data['stock'];

                if ($current_stock >= $quantity) {
                    $subtotal = $unit_price * $quantity;
                    // **ENSURE THIS LINE IS EXACTLY AS BELOW:**
                    $sql_item = "INSERT INTO sales_details (sale_id, product_id, quantity, price_per_item)
                                 VALUES (?, ?, ?, ?)";
                    $stmt_item = $conn->prepare($sql_item);
                    $stmt_item->bind_param("iiid", $sale_id, $product_id, $quantity, $unit_price);

                    if (!$stmt_item->execute()) {
                        $sale_successful = false;
                        $error_message = "Error recording sale item: " . $stmt_item->error;
                        break; // Stop if item insertion fails
                    }
                    $stmt_item->close();

                    // Update product stock
                    $new_stock = $current_stock - $quantity;
                    $sql_update_stock = "UPDATE products SET stock = ? WHERE id = ?";
                    $stmt_update_stock = $conn->prepare($sql_update_stock);
                    $stmt_update_stock->bind_param("ii", $new_stock, $product_id);

                    if (!$stmt_update_stock->execute()) {
                        $sale_successful = false;
                        $error_message = "Error updating product stock: " . $stmt_update_stock->error;
                        break; // Stop if stock update fails
                    }
                    $stmt_update_stock->close();

                } else {
                    $sale_successful = false;
                    $error_message = "Insufficient stock for product ID: " . $product_id;
                    break; // Stop if insufficient stock
                }
            } else {
                $sale_successful = false;
                $error_message = "Product not found with ID: " . $product_id;
                break; // Stop if product not found
            }
            $stmt_product->close();
        }

        // 3. Handle credit sale
        if ($sale_successful && $payment_type === 'credit') {
            if ($customer_id) {
                $sql_credit = "INSERT INTO credits (customer_id, total_credit, balance, last_payment_date, status, created_at)
                               VALUES (?, ?, ?, NOW(), 'unpaid', NOW())";
                $stmt_credit = $conn->prepare($sql_credit);
                $stmt_credit->bind_param("idd", $customer_id, $total_amount, $total_amount);

                if (!$stmt_credit->execute()) {
                    $sale_successful = false;
                    $error_message = "Error recording credit: " . $stmt_credit->error;
                }
                $stmt_credit->close();
            } else {
                $sale_successful = false;
                $error_message = "Customer must be selected for credit sale.";
            }
        }

        if ($sale_successful) {
            $conn->commit();
            header("Location: sales.php?success=Sale recorded successfully. Sale ID: " . $sale_id);
            exit;
        } else {
            $conn->rollback();
            header("Location: sales.php?error=Error processing sale: " . $error_message);
            exit;
        }

    } else {
        header("Location: sales.php?error=Error recording sale header: " . $stmt_sale->error);
        exit;
    }

    $stmt_sale->close();
    $conn->close();

} else {
    header("Location: sales.php");
    exit;
}
?>