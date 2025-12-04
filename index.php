<?php
session_start();

if (isset($_SESSION['user_id'])) {
    // User is logged in, redirect to the appropriate dashboard based on their role
    if ($_SESSION['role'] === 'admin') {
        header("Location: pages/admin_panel.php?action=overview");
    } else {
        header("Location: pages/dashboard.php");
    }
    exit;
} else {
    // User is not logged in, render the login page with the shared sidebar
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Base tag to fix relative paths across different directories -->
    <base href="/smart-cashier-system/">
    <title>Smart Business Cashier and Inventory Management System</title>
    <link id="theme-stylesheet" rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container-with-sidebar">
        <?php include 'includes/sidebar.php'; ?>
        <div class="container">
            <header>
                <h1>Smart Business Cashier and Inventory Management System</h1>
            </header>
            <h2>Welcome! Please Login</h2>
            <section>
                <div id="error-message" class="alert-danger" style="display: none;"></div>
                <?php
                    if (isset($_GET['error'])) {
                        echo '<div class="alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
                    }
                ?>
                <form id="loginForm" method="POST">  <!-- Removed action to rely on JS -->
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="button">Login</button>
                    </div>
                    <p>Don't have an account? <a href="auth/register.php">Register here.</a></p>
                    <p>Forgot your password?<a href="auth/password_reset.php">Click here.</a></p>
                </form>
                <noscript>
                    <p>JavaScript is required for login. Please enable it or submit the form directly to "auth/login_process.php".</p>
                </noscript>
            </section>
            <footer>
                <p>&copy; 2025 Techlaro Company</p>
            </footer>
        </div>
    </div>
    <script src="assets/js/auth.js"></script>
    <script src="assets/js/scripts.js"></script>
</body>
</html>
<?php } ?>