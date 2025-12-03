<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Smart Business Cashier System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container-with-sidebar">
        <?php include '../includes/sidebar.php'; ?>
        <div class="container">
            <header>
                <h1>Register</h1>
            </header>
            <section>
                <form action="register_process.php" method="POST">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="button">Register</button>
                    </div>
                    <p><a href="../index.php">Already have an account? Login here.</a></p>
                    <?php
                        if (isset($_GET['error'])) {
                            echo '<div class="alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
                        }
                        if (isset($_GET['success'])) {
                            echo '<div class="alert-success">' . htmlspecialchars($_GET['success']) . '</div>';
                        }
                    ?>
                </form>
            </section>
            <footer>
                <p>&copy; 2025 Smart Business Cashier System</p>
            </footer>
        </div>
    </div>
</body>
</html>