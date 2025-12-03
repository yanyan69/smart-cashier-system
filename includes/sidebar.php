<?php
// includes/sidebar.php
// This file generates the sidebar based on the user's login status and role.

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="sidebar">
    <h2>Techlaro</h2>
    <ul class="nav-list">
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <!-- Admin Sidebar Links -->
                <li><a href="/smart-cashier-system/pages/admin_panel.php?action=overview">Dashboard</a></li>
                <li><a href="/smart-cashier-system/pages/admin_panel.php?action=manage_users">Manage Users</a></li>
                <li><a href="/smart-cashier-system/pages/admin_panel.php?action=database_backups">Database Backups</a></li>
                <li><a href="/smart-cashier-system/pages/admin_panel.php?action=system_logs">System Logs</a></li>
                <li><a href="/smart-cashier-system/pages/about_us.php">About Us</a></li>
                <li><a href="/smart-cashier-system/auth/logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a></li>
            <?php else: ?>
                <!-- Store Owner Sidebar Links -->
                <li><a href="/smart-cashier-system/pages/dashboard.php">Dashboard</a></li>
                <li><a href="/smart-cashier-system/pages/products.php">Products</a></li>
                <li><a href="/smart-cashier-system/pages/sales.php">Sales</a></li>
                <li><a href="/smart-cashier-system/pages/credits.php">Credits</a></li>
                <li><a href="/smart-cashier-system/pages/customers.php">Customers</a></li>
                <li><a href="/smart-cashier-system/pages/reports.php">Reports</a></li>
                <li><a href="/smart-cashier-system/pages/about_us.php">About Us</a></li>
                <li><a href="/smart-cashier-system/auth/logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a></li>
            <?php endif; ?>
        <?php else: ?>
            <!-- Guest Sidebar Links -->
            <li><a href="/smart-cashier-system/index.php">Login</a></li>
            <li><a href="/smart-cashier-system/auth/register.php">Register</a></li>
            <li><a href="/smart-cashier-system/pages/about_us.php">About Us</a></li>
        <?php endif; ?>
    </ul>
</div>