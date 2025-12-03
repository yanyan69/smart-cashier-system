<?php
session_start();
require_once '../includes/functions.php'; // Assuming you have a functions.php for database connection

// Start the session (if not already started)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Retrieve the reset token from the URL
$token = $_GET['token'] ?? '';

// Check if the token is valid (exists in the database and hasn't expired)
if (!empty($token)) {
    $conn = connect_db();

    // Sanitize input (use prepared statements in a real application)
    $token = mysqli_real_escape_string($conn, $token);

    $query = "SELECT user_id FROM users WHERE reset_token = '$token' AND reset_expiry > NOW()";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        $user_id = $user['user_id'];
        // Token is valid, display the form to enter a new password
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reset Your Password</title>
            <link rel="stylesheet" href="../assets/css/style.css">
        </head>
        <body>
            <div class="container-with-sidebar">
                <?php include '../includes/sidebar.php'; ?>
                <div class="container">
                    <header>
                        <h1>Reset Your Password</h1>
                    </header>
                    <section>
                        <?php
                        if (isset($_SESSION['reset_password_error'])) {
                            echo '<div class="alert-danger">' . htmlspecialchars($_SESSION['reset_password_error']) . '</div>';
                            unset($_SESSION['reset_password_error']);
                        }
                        if (isset($_SESSION['reset_password_success'])) {
                            echo '<div class="alert-success">' . htmlspecialchars($_SESSION['reset_password_success']) . '</div>';
                            unset($_SESSION['reset_password_success']);
                        }
                        ?>
                        <form action="reset_password_process.php" method="POST">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            <div class="form-group">
                                <label for="new_password">New Password:</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password:</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="button">Reset Password</button>
                            </div>
                        </form>
                    </section>
                    <footer>
                        <p>&copy; <?php echo date("Y"); ?> Techlaro Company</p>
                    </footer>
                </div>
            </div>
        </body>
        </html>
        <?php
    } else {
        // Invalid or expired token, display an error message
        $_SESSION['error'] = "Invalid or expired password reset link.";
        header("Location: ../index.php"); // Redirect to login page with error
        exit();
    }

    mysqli_close($conn);

} else {
    // Token not provided in the URL, redirect to login page with an error
    $_SESSION['error'] = "Invalid password reset link.";
    header("Location: ../index.php");
    exit();
}
?>