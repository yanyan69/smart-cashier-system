<?php
session_start();
include '../includes/session.php'; // You might want to include session here for user info
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unauthorized Access</title>
</head>
<body>
    <div class="container">
        <h1>Unauthorized Access</h1>
        <p>You do not have permission to view this page.</p>
        <?php if (isLoggedIn()): ?>
            <a href="../pages/dashboard.php" class="button">Go to Dashboard</a>
        <?php else: ?>
            <a href="../index.php" class="button">Go to Login</a>
        <?php endif; ?>
    </div>
</body>
</html>