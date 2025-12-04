<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header("Location: /smart-cashier-system/index.php");
    exit();
}

include '../config/db.php';

$user_id = $_SESSION['user_id'];

if (isset($_GET['credit_id'])) {
    $credit_id = intval($_GET['credit_id']);
    $stmt = $conn->prepare("SELECT cr.id AS credit_id, c.customer_name, cr.amount_owed, cr.amount_paid, cr.status 
                            FROM credit cr LEFT JOIN customer c ON cr.customer_id = c.id 
                            WHERE cr.id = ? AND cr.user_id = ?");
    $stmt->bind_param("ii", $credit_id, $user_id);
    $stmt->execute();
    $credit = $stmt->get_result()->fetch_assoc();
    if (!$credit) {
        header("Location: credits.php?error=Invalid credit ID");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $amount_paid = floatval($_POST['amount_paid']);

        $conn->begin_transaction();
        $payment_successful = true;
        $error_message = "";

        // Record payment
        $sql_payment = "INSERT INTO payment_history (credit_id, payment_amount) VALUES (?, ?)";
        $stmt_payment = $conn->prepare($sql_payment);
        $stmt_payment->bind_param("id", $credit_id, $amount_paid);
        if (!$stmt_payment->execute()) {
            $payment_successful = false;
            $error_message = "Error recording payment: " . $stmt_payment->error;
        }
        $stmt_payment->close();

        // Update credit balance
        if ($payment_successful) {
            $sql_update_credit = "UPDATE credit SET amount_paid = amount_paid + ?, status = 
                                  CASE WHEN amount_paid + ? >= amount_owed THEN 'paid' 
                                       WHEN amount_paid + ? > 0 THEN 'partially_paid' 
                                       ELSE 'unpaid' END 
                                  WHERE id = ? AND user_id = ?";
            $stmt_update_credit = $conn->prepare($sql_update_credit);
            $stmt_update_credit->bind_param("ddii", $amount_paid, $amount_paid, $amount_paid, $credit_id, $user_id);
            if (!$stmt_update_credit->execute()) {
                $payment_successful = false;
                $error_message = "Error updating credit: " . $stmt_update_credit->error;
            }
            $stmt_update_credit->close();
        }

        if ($payment_successful) {
            $conn->commit();
            header("Location: credits.php?success=Payment recorded successfully");
            exit();
        } else {
            $conn->rollback();
            header("Location: record_payment.php?credit_id=" . $credit_id . "&error=" . urlencode($error_message));
            exit();
        }
    }
} else {
    header("Location: credits.php?error=Invalid credit ID");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/smart-cashier-system/">
    <title>Record Payment - Smart Cashier System</title>
    <link id="theme-stylesheet" rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container-with-sidebar">
        <?php include '../includes/sidebar.php'; ?>
        <div class="container">
            <header>
                <h1>Record Payment</h1>
            </header>
            <section>
                <p>Record a payment for the credit of <strong><?php echo htmlspecialchars($credit['customer_name']); ?></strong>.</p>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <form method="POST" action="record_payment.php?credit_id=<?php echo $credit['credit_id']; ?>">
                    <div class="form-group">
                        <label for="amount_paid">Amount Paid:</label>
                        <input type="number" step="0.01" id="amount_paid" name="amount_paid" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="button">Record Payment</button>
                    </div>
                    <p><a href="credits.php">Back to Credits</a></p>
                </form>
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
    </script>
</body>
</html>