<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - Smart Cashier System</title>
    <link id="theme-stylesheet" rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container-with-sidebar">
        <?php include '../includes/sidebar.php'; ?>
        <div class="container">
            <header>
                <h1>Password Reset</h1>
            </header>
            <section>
                <?php
                    if (isset($_GET['error'])) {
                        echo '<div class="alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
                    }
                    if (isset($_GET['success'])) {
                        echo '<div class="alert-success">' . htmlspecialchars($_GET['success']) . '</div>';
                    }
                ?>
                <form action="password_reset_process.php" method="POST">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="button">Reset Password</button>
                    </div>
                    <p><a href="../index.php">Back to Login</a></p>
                </form>
                <p class="note">Note: This is a basic password reset form. A more secure implementation would involve email verification.</p>
            </section>
            <footer>
                <p>&copy; 2025 Techlaro Company</p>
            </footer>
        </div>
    </div>
    <script src="assets/js/scripts.js"></script>
</body>
</html>