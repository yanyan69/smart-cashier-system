<div class="sidebar">
    <h2>Techlaro</h2>
    <button class="toggle-button">
        <div class="bar"></div>
        <div class="bar"></div>
        <div class="bar"></div>
    </button>
    <ul class="nav-list">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'owner'): ?>
            <li><a href="../pages/dashboard.php">Dashboard</a></li>
            <li><a href="../pages/products.php">Products</a></li>
            <li><a href="../pages/sales.php">Sales</a></li>
            <li><a href="../pages/credits.php">Credits</a></li>
            <li><a href="../pages/customers.php">Customers</a></li>
            <li><a href="../pages/reports.php">Reports</a></li>
            <li><a href="../pages/about_us.php">About Us</a></li>
            <li><a href="../auth/logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a></li>
        <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li><a href="../pages/admin_panel.php?action=overview">Admin Dashboard</a></li>
            <li><a href="../pages/admin_panel.php?action=manage_users">Manage Users</a></li>
            <li><a href="../pages/admin_panel.php?action=backup_db">Database Backups</a></li>
            <li><a href="../pages/admin_panel.php?action=view_logs">System Logs</a></li>
            <li><a href="../pages/about_us.php">About Us</a></li>
            <li><a href="../auth/logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a></li>
        <?php else: ?>
            <li><a href="../index.php">Login</a></li>
            <li><a href="../auth/register.php">Register</a></li>
            <li><a href="../pages/about_us.php">About Us</a></li>
        <?php endif; ?>
    </ul>
</div>