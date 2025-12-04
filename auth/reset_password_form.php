<?php
session_start();
require_once '../config/db.php';

$token = $_GET['token'] ?? '';

if (!empty($token)) {
    $stmt = $conn->prepare("SELECT id FROM `user` WHERE reset_token = ? AND reset_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reset Your Password</title>
            <link id="theme-stylesheet" rel="stylesheet" href="assets/css/style.css">
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
                        if (isset($_GET['error'])) {
                            echo '<div class="alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
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
            <script src="assets/js/scripts.js"></script>
        </body>
        </html>
        <?php
    } else {
        header("Location: ../index.php?error=Invalid or expired token.");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../index.php?error=Invalid reset link.");
    exit();
}
?>