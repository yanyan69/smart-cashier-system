<?php
session_start();
include '../includes/session.php';
if (!isStoreOwner()) {
    header("Location: ../unauthorized.php");
    exit;
}
include '../config/db.php';

// Fetch credit details if ID is provided
if (isset($_GET['credit_id']) && is_numeric($_GET['credit_id'])) {
    $credit_id = $_GET['credit_id'];
    $sql = "SELECT
                c.id AS credit_id,
                cust.id AS customer_id,
                cust.customer_name,
                c.total_credit,
                c.balance
            FROM credits c
            JOIN customers cust ON c.customer_id = cust.id
            WHERE c.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $credit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $credit = $result->fetch_assoc();
    } else {
        header("Location: credits.php?error=Credit record not found");
        exit;
    }
    $stmt->close();
} else {
    header("Location: credits.php?error=Invalid credit ID");
    exit;
}

// Handle payment submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("POST Request Received");
    error_log("$_POST contents: " . print_r($_POST, true));
    if (isset($_POST['credit_id'])) {
        error_log("credit_id is set: " . $_POST['credit_id']);
    } else {
        error_log("credit_id is NOT set");
    }
    if (isset($_POST['amount_paid'])) {
        error_log("amount_paid is set: " . $_POST['amount_paid']);
    } else {
        error_log("amount_paid is NOT set");
    }

    if (isset($_POST['credit_id']) && is_numeric($_POST['credit_id'])) {
        $credit_id = $_POST['credit_id'];
        $amount_paid = $_POST['amount_paid'];

        if (!is_numeric($amount_paid) || $amount_paid <= 0) {
            header("Location: record_payment.php?credit_id=" . $credit_id . "&error=Invalid payment amount");
            exit;
        }

        // Start transaction
        $conn->begin_transaction();
        $payment_successful = true;
        $error_message = null; // Initialize error message

        // 1. Insert the payment record
        $sql_payment = "INSERT INTO credit_payments (credit_id, payment_date, amount_paid)
                        VALUES (?, NOW(), ?)";
        $stmt_payment = $conn->prepare($sql_payment);
        $stmt_payment->bind_param("id", $credit_id, $amount_paid);
        if (!$stmt_payment->execute()) {
            $payment_successful = false;
            $error_message = "Error recording payment: " . $stmt_payment->error;
            error_log("Payment INSERT error: " . $stmt_payment->error);
        }
        $stmt_payment->close();

        // 2. Update the credit balance
        if ($payment_successful) {
            $sql_update_credit = "UPDATE credits SET balance = balance - ?, last_payment_date = NOW() WHERE id = ?";
            $stmt_update_credit = $conn->prepare($sql_update_credit);
            $stmt_update_credit->bind_param("di", $amount_paid, $credit_id);
            if (!$stmt_update_credit->execute()) {
                $payment_successful = false;
                $error_message = "Error updating credit balance: " . $stmt_update_credit->error;
                error_log("Balance UPDATE error: " . $stmt_update_credit->error);
            }
            $stmt_update_credit->close();
        }

        // 3. Update the credit status if necessary
        if ($payment_successful) {
            $sql_update_status = "UPDATE credits SET status =
                                    CASE
                                        WHEN balance <= 0 THEN 'fully paid'
                                        WHEN balance < total_credit THEN 'partially paid'
                                        ELSE 'unpaid'
                                    END
                                  WHERE id = ?";
            $stmt_update_status = $conn->prepare($sql_update_status);
            $stmt_update_status->bind_param("i", $credit_id);
            if (!$stmt_update_status->execute()) {
                $payment_successful = false;
                $error_message = "Error updating credit status: " . $stmt_update_status->error;
                error_log("Status UPDATE error: " . $stmt_update_status->error);
            }
            $stmt_update_status->close();
        }

        if ($payment_successful) {
            $conn->commit();
            header("Location: credits.php?success=Payment recorded successfully");
            exit;
        } else {
            $conn->rollback();
            header("Location: record_payment.php?credit_id=" . $credit_id . "&error=" . urlencode($error_message));
            exit;
        }

        $conn->close();
    } else {
        header("Location: credits.php?error=Invalid credit ID");
        exit;
    }
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<div class="content">
    <h1>Record Payment</h1>
    <p>Record a payment for the credit of <strong><?php echo htmlspecialchars($credit['customer_name']); ?></strong>.</p>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <form method="POST" action="record_payment.php">
        <input type="hidden" name="credit_id" value="<?php echo htmlspecialchars($credit['credit_id']); ?>">
        <div class="form-group">
            <label for="amount_paid">Amount Paid:</label>
            <input type="number" step="0.01" id="amount_paid" name="amount_paid" required>
        </div>
        <div class="form-group">
            <button type="submit" class="button">Record Payment</button>
        </div>
        <p><a href="credits.php">Back to Credits</a></p>
    </form>
</div>

<?php include '../includes/footer.php'; ?>